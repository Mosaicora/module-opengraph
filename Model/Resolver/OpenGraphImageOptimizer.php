<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Resolver;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Psr\Log\LoggerInterface;

class OpenGraphImageOptimizer
{
    private const CACHE_BASE_PATH = 'mosaicora/opengraph/cache';

    public function __construct(
        private readonly ConfigProvider $config,
        private readonly Filesystem $filesystem,
        private readonly ImageFactory $imageFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger,
        private readonly MediaPathNormalizer $mediaPathNormalizer
    ) {
    }

    public function optimize(string $url, ?int $storeId = null): OptimizedImage
    {
        if ($url === '') {
            return new OptimizedImage('');
        }

        $relativePath = $this->mediaPathNormalizer->getMediaRelativePath($url);
        if ($relativePath === null) {
            return $this->mediaPathNormalizer->normalizeImageUrl($url) === $url
                ? new OptimizedImage($url)
                : new OptimizedImage('');
        }

        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if (!$mediaDirectory->isExist($relativePath)) {
            return new OptimizedImage('');
        }

        if (!$this->config->isImageOptimizationEnabled($storeId)) {
            return new OptimizedImage($url);
        }

        $width = $this->config->getOptimizedImageWidth($storeId);
        $height = $this->config->getOptimizedImageHeight($storeId);
        $mode = $this->config->getOptimizedImageResizeMode($storeId);
        $sourceAbsolutePath = $mediaDirectory->getAbsolutePath($relativePath);
        $sourceMtime = (int)($mediaDirectory->stat($relativePath)['mtime'] ?? 0);
        $cacheRelativePath = $this->getCacheRelativePath($relativePath, $sourceMtime, $width, $height, $mode);

        if (!$mediaDirectory->isExist($cacheRelativePath)) {
            try {
                $driver = $mediaDirectory->getDriver();
                $mediaDirectory->create($driver->getParentDirectory($cacheRelativePath));
                $this->generate(
                    $sourceAbsolutePath,
                    $driver->getParentDirectory($mediaDirectory->getAbsolutePath($cacheRelativePath)),
                    $this->getFileName($cacheRelativePath),
                    $width,
                    $height,
                    $mode,
                    $storeId
                );
            } catch (\Throwable $exception) {
                $this->logger->critical($exception);
                return new OptimizedImage($url);
            }
        }

        [$actualWidth, $actualHeight] = $this->getDimensions((string)$mediaDirectory->readFile($cacheRelativePath));

        return new OptimizedImage(
            rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/')
                . '/'
                . $cacheRelativePath,
            $actualWidth,
            $actualHeight
        );
    }

    private function generate(
        string $sourceAbsolutePath,
        string $targetDirectoryAbsolutePath,
        string $targetFileName,
        int $width,
        int $height,
        string $mode,
        ?int $storeId
    ): void {
        $image = $this->imageFactory->create($sourceAbsolutePath);
        $image->quality(90);
        $image->keepAspectRatio(true);
        $image->keepTransparency(true);
        $image->constrainOnly(false);

        if ($mode === ConfigProvider::RESIZE_MODE_SCALE) {
            $image->keepFrame(true);
            $image->backgroundColor($this->config->getOptimizedImageBackgroundColor($storeId));
            $image->resize($width, $height);
        } else {
            $this->resizeCover($image, $width, $height);
        }

        $image->save($targetDirectoryAbsolutePath, $targetFileName);
    }

    private function resizeCover(\Magento\Framework\Image $image, int $width, int $height): void
    {
        $sourceWidth = max(1, (int)$image->getOriginalWidth());
        $sourceHeight = max(1, (int)$image->getOriginalHeight());
        $scale = max($width / $sourceWidth, $height / $sourceHeight);
        $resizeWidth = (int)ceil($sourceWidth * $scale);
        $resizeHeight = (int)ceil($sourceHeight * $scale);

        $image->keepFrame(false);
        $image->resize($resizeWidth, $resizeHeight);

        $left = max(0, (int)floor(($resizeWidth - $width) / 2));
        $right = max(0, $resizeWidth - $width - $left);
        $top = max(0, (int)floor(($resizeHeight - $height) / 2));
        $bottom = max(0, $resizeHeight - $height - $top);
        $image->crop($top, $left, $right, $bottom);
    }

    private function getCacheRelativePath(
        string $relativePath,
        int $sourceMtime,
        int $width,
        int $height,
        string $mode
    ): string {
        $hash = sha1($relativePath . '|' . $sourceMtime . '|' . $width . '|' . $height . '|' . $mode);

        return self::CACHE_BASE_PATH . '/' . $width . 'x' . $height . '/' . $mode . '/' . $hash . '.jpg';
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function getFileName(string $path): string
    {
        $position = strrpos($path, '/');

        return $position === false ? $path : substr($path, $position + 1);
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function getDimensions(string $contents): array
    {
        set_error_handler(static fn (): bool => true);
        try {
            $dimensions = getimagesizefromstring($contents);
        } finally {
            restore_error_handler();
        }

        if (!is_array($dimensions)) {
            return [null, null];
        }

        return [(int)$dimensions[0], (int)$dimensions[1]];
    }
}
