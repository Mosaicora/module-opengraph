<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Resolver;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Image;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Resolver\MediaPathNormalizer;
use Mosaicora\OpenGraph\Model\Resolver\OpenGraphImageOptimizer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OpenGraphImageOptimizerTest extends TestCase
{
    private string $mediaRoot;

    protected function setUp(): void
    {
        $this->mediaRoot = sys_get_temp_dir() . '/mosaicora-og-test-' . uniqid('', true);
        mkdir($this->mediaRoot, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->mediaRoot);
    }

    public function testRemoteImageUrlReturnsUnchanged(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->never())->method('getDirectoryWrite');

        $optimizer = $this->createOptimizer(
            filesystem: $filesystem,
            imageFactory: $this->createStub(ImageFactory::class)
        );

        self::assertSame('https://cdn.example.test/image.jpg', $optimizer->optimize('https://cdn.example.test/image.jpg')->getUrl());
    }

    public function testMissingLocalImageReturnsEmpty(): void
    {
        $optimizer = $this->createOptimizer(imageFactory: $this->createStub(ImageFactory::class));

        self::assertSame(
            '',
            $optimizer->optimize('https://example.test/media/catalog/product/missing.jpg')->getUrl()
        );
    }

    public function testUnsafeLocalImagePathReturnsEmptyWithoutFilesystemLookup(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->never())->method('getDirectoryWrite');

        $optimizer = $this->createOptimizer(
            filesystem: $filesystem,
            imageFactory: $this->createStub(ImageFactory::class)
        );

        self::assertSame('', $optimizer->optimize('../secret.jpg')->getUrl());
    }

    public function testExistingLocalImageIsValidatedWhenOptimizationIsDisabled(): void
    {
        $source = 'catalog/product/source.jpg';
        $this->writeFile($source, 'source');
        $imageFactory = $this->createMock(ImageFactory::class);
        $imageFactory->expects($this->never())->method('create');

        $optimizer = $this->createOptimizer(
            imageFactory: $imageFactory,
            optimizationEnabled: false
        );

        self::assertSame(
            'https://example.test/media/' . $source,
            $optimizer->optimize('https://example.test/media/' . $source)->getUrl()
        );
        self::assertSame(
            '',
            $optimizer->optimize('https://example.test/media/catalog/product/missing.jpg')->getUrl()
        );
    }

    public function testExistingCachedImageIsReused(): void
    {
        $source = 'catalog/product/source.jpg';
        $this->writeFile($source, 'source');
        $cachePath = $this->cachePath($source, ConfigProvider::RESIZE_MODE_COVER);
        $this->writeFile($cachePath, $this->tinyPng());

        $imageFactory = $this->createMock(ImageFactory::class);
        $imageFactory->expects($this->never())->method('create');

        $optimizer = $this->createOptimizer(imageFactory: $imageFactory);

        self::assertSame(
            'https://example.test/media/' . $cachePath,
            $optimizer->optimize('https://example.test/media/' . $source)->getUrl()
        );
    }

    public function testCachePathChangesWhenSourceMtimeChanges(): void
    {
        $source = 'catalog/product/source.jpg';
        $this->writeFile($source, 'source');
        $sourcePath = $this->mediaRoot . '/' . $source;

        self::assertTrue(touch($sourcePath, 1000));
        clearstatcache(true, $sourcePath);
        $firstPath = $this->cachePath($source, ConfigProvider::RESIZE_MODE_COVER);

        self::assertTrue(touch($sourcePath, 2000));
        clearstatcache(true, $sourcePath);
        $secondPath = $this->cachePath($source, ConfigProvider::RESIZE_MODE_COVER);

        self::assertNotSame($firstPath, $secondPath);
    }

    public function testCoverModeResizesToCoverAndCropsCenter(): void
    {
        $source = 'catalog/product/source.jpg';
        $this->writeFile($source, 'source');

        $image = $this->createMock(Image::class);
        $image->method('getOriginalWidth')->willReturn(600);
        $image->method('getOriginalHeight')->willReturn(600);
        $image->expects($this->once())->method('keepFrame')->with(false);
        $image->expects($this->once())->method('resize')->with(1200, 1200);
        $image->expects($this->once())->method('crop')->with(285, 0, 0, 285);
        $image->expects($this->once())
            ->method('save')
            ->willReturnCallback($this->saveTinyPng());

        $this->createOptimizerForImage($image, ConfigProvider::RESIZE_MODE_COVER)
            ->optimize('https://example.test/media/' . $source);
    }

    public function testScaleModeFitsAndPads(): void
    {
        $source = 'catalog/product/source.jpg';
        $this->writeFile($source, 'source');

        $image = $this->createMock(Image::class);
        $image->expects($this->once())->method('keepFrame')->with(true);
        $image->expects($this->once())->method('backgroundColor')->with([255, 255, 255]);
        $image->expects($this->once())->method('resize')->with(1200, 630);
        $image->expects($this->never())->method('crop');
        $image->expects($this->once())
            ->method('save')
            ->willReturnCallback($this->saveTinyPng());

        $this->createOptimizerForImage($image, ConfigProvider::RESIZE_MODE_SCALE)
            ->optimize('https://example.test/media/' . $source);
    }

    private function createOptimizerForImage(Image $image, string $mode): OpenGraphImageOptimizer
    {
        $imageFactory = $this->createMock(ImageFactory::class);
        $imageFactory->expects($this->once())->method('create')->willReturn($image);

        return $this->createOptimizer(imageFactory: $imageFactory, mode: $mode);
    }

    private function createOptimizer(
        ?Filesystem $filesystem = null,
        ?ImageFactory $imageFactory = null,
        string $mode = ConfigProvider::RESIZE_MODE_COVER,
        bool $optimizationEnabled = true
    ): OpenGraphImageOptimizer {
        $config = $this->createStub(ConfigProvider::class);
        $config->method('isImageOptimizationEnabled')->willReturn($optimizationEnabled);
        $config->method('getOptimizedImageWidth')->willReturn(1200);
        $config->method('getOptimizedImageHeight')->willReturn(630);
        $config->method('getOptimizedImageResizeMode')->willReturn($mode);
        $config->method('getOptimizedImageBackgroundColor')->willReturn([255, 255, 255]);

        $store = $this->createMock(Store::class);
        $store->method('getBaseUrl')->with(UrlInterface::URL_TYPE_MEDIA)->willReturn('https://example.test/media/');

        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        return new OpenGraphImageOptimizer(
            $config,
            $filesystem ?? $this->createFilesystem(),
            $imageFactory ?? $this->createStub(ImageFactory::class),
            $storeManager,
            $this->createStub(LoggerInterface::class),
            new MediaPathNormalizer($storeManager)
        );
    }

    private function createFilesystem(): Filesystem
    {
        $directory = $this->createStub(WriteInterface::class);
        $directory->method('getAbsolutePath')->willReturnCallback(
            fn (?string $path = null): string => rtrim($this->mediaRoot . '/' . ltrim((string)$path, '/'), '/')
        );
        $directory->method('isExist')->willReturnCallback(
            fn (?string $path = null): bool => file_exists(rtrim($this->mediaRoot . '/' . ltrim((string)$path, '/'), '/'))
        );
        $directory->method('stat')->willReturnCallback(
            fn (string $path): array => stat($this->mediaRoot . '/' . ltrim($path, '/')) ?: []
        );
        $directory->method('readFile')->willReturnCallback(
            fn (string $path): string => (string)file_get_contents($this->mediaRoot . '/' . ltrim($path, '/'))
        );
        $directory->method('create')->willReturnCallback(
            function (?string $path = null): bool {
                $absolutePath = rtrim($this->mediaRoot . '/' . ltrim((string)$path, '/'), '/');
                return is_dir($absolutePath) || mkdir($absolutePath, 0777, true);
            }
        );
        $driver = $this->createStub(DriverInterface::class);
        $driver->method('getParentDirectory')->willReturnCallback(
            static function (string $path): string {
                $position = strrpos($path, '/');

                return $position === false ? '' : substr($path, 0, $position);
            }
        );
        $directory->method('getDriver')->willReturn($driver);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->with(DirectoryList::MEDIA)->willReturn($directory);

        return $filesystem;
    }

    private function writeFile(string $relativePath, string $contents): void
    {
        $absolutePath = $this->mediaRoot . '/' . $relativePath;
        if (!is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0777, true);
        }

        file_put_contents($absolutePath, $contents);
    }

    private function cachePath(string $source, string $mode): string
    {
        $hash = sha1($source . '|' . filemtime($this->mediaRoot . '/' . $source) . '|1200|630|' . $mode);
        return 'mosaicora/opengraph/cache/1200x630/' . $mode . '/' . $hash . '.jpg';
    }

    private function tinyPng(): string
    {
        return (string)base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        );
    }

    private function saveTinyPng(): callable
    {
        return function (string $destination, string $newName): void {
            $this->writeFile(
                ltrim(substr($destination . '/' . $newName, strlen($this->mediaRoot)), '/'),
                $this->tinyPng()
            );
        };
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = array_diff(scandir($path) ?: [], ['.', '..']);
        foreach ($items as $item) {
            $itemPath = $path . '/' . $item;
            is_dir($itemPath) ? $this->deleteDirectory($itemPath) : unlink($itemPath);
        }

        rmdir($path);
    }
}
