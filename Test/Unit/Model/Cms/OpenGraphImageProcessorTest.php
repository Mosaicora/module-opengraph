<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Cms;

use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Model\Cms\OpenGraphImageProcessor;
use Mosaicora\OpenGraph\Model\Resolver\MediaPathNormalizer;
use PHPUnit\Framework\TestCase;

class OpenGraphImageProcessorTest extends TestCase
{
    public function testMovesTemporaryUploadAndReturnsRelativePath(): void
    {
        $uploader = $this->createMock(ImageUploader::class);
        $uploader->expects($this->once())
            ->method('moveFileFromTmp')
            ->with('image.jpg', true)
            ->willReturn('mosaicora/opengraph/image.jpg');

        $processor = new OpenGraphImageProcessor(
            $uploader,
            $this->createMediaPathNormalizer()
        );

        self::assertSame(
            'mosaicora/opengraph/image.jpg',
            $processor->normalizeImageValue([['name' => 'image.jpg', 'tmp_name' => 'tmp/image.jpg']])
        );
    }

    public function testNormalizesMediaGalleryPath(): void
    {
        $processor = new OpenGraphImageProcessor(
            $this->createStub(ImageUploader::class),
            $this->createMediaPathNormalizer()
        );

        self::assertSame(
            'mosaicora/opengraph/gallery.jpg',
            $processor->normalizeImageValue([['url' => '/media/mosaicora/opengraph/gallery.jpg']])
        );
    }

    public function testUnsafeMediaGalleryPathIsRejected(): void
    {
        $processor = new OpenGraphImageProcessor(
            $this->createStub(ImageUploader::class),
            $this->createMediaPathNormalizer()
        );

        self::assertNull($processor->normalizeImageValue([['url' => '/media/../secret.jpg']]));
    }

    public function testTemporaryFileMoveFailureAbortsSave(): void
    {
        $uploader = $this->createStub(ImageUploader::class);
        $uploader->method('moveFileFromTmp')
            ->willThrowException(new LocalizedException(__('Unable to move image.')));

        $processor = new OpenGraphImageProcessor($uploader, $this->createMediaPathNormalizer());

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Unable to move image.');

        $processor->normalizeImageValue([['name' => 'image.jpg', 'tmp_name' => '/tmp/image.jpg']]);
    }

    public function testTemporaryUploadRequiresFileName(): void
    {
        $processor = new OpenGraphImageProcessor(
            $this->createStub(ImageUploader::class),
            $this->createMediaPathNormalizer()
        );

        $this->expectException(LocalizedException::class);
        $processor->normalizeImageValue([['tmp_name' => '/tmp/image.jpg']]);
    }

    private function createMediaPathNormalizer(): MediaPathNormalizer
    {
        $store = $this->createStub(Store::class);
        $store->method('getBaseUrl')->willReturn('https://example.test/media/');

        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        return new MediaPathNormalizer($storeManager);
    }
}
