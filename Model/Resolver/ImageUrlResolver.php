<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Resolver;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;

class ImageUrlResolver
{
    public function __construct(
        private readonly ConfigProvider $config,
        private readonly DefaultAttributeMatcher $attributeMatcher,
        private readonly OpenGraphImageOptimizer $imageOptimizer,
        private readonly MediaPathNormalizer $mediaPathNormalizer
    ) {
    }

    public function resolveProductImage(Product $product, ?int $storeId = null): string
    {
        return $this->resolveProductImageData($product, $storeId)->getUrl();
    }

    public function resolveProductImageData(Product $product, ?int $storeId = null): OptimizedImage
    {
        foreach ($this->attributeMatcher->getCandidates('product', 'image', $storeId) as $attributeCode) {
            $url = $this->buildCatalogProductMediaUrl($product->getData($attributeCode));
            if ($url !== '') {
                $image = $this->imageOptimizer->optimize($url, $storeId);
                if ($image->getUrl() !== '') {
                    return $image;
                }
            }
        }

        return $this->resolveDefaultImageData($storeId);
    }

    public function resolveCategoryImage(Category $category, ?int $storeId = null): string
    {
        return $this->resolveCategoryImageData($category, $storeId)->getUrl();
    }

    public function resolveCategoryImageData(Category $category, ?int $storeId = null): OptimizedImage
    {
        $mode = (string)($category->getData('og_image_mode') ?: ConfigProvider::MODE_AUTO);

        if ($mode === ConfigProvider::MODE_CUSTOM) {
            $url = $this->buildOpenGraphMediaUrl($category->getData('og_image_custom'));
            return $url !== ''
                ? $this->imageOptimizer->optimize($url, $storeId)
                : new OptimizedImage('');
        }

        $url = $this->buildCategoryMediaUrl($category->getData('image'));
        if ($url !== '') {
            $image = $this->imageOptimizer->optimize($url, $storeId);
            if ($image->getUrl() !== '') {
                return $image;
            }
        }

        return $this->resolveDefaultImageData($storeId);
    }

    public function resolveCmsImage(DataObject $page, ?int $storeId = null): string
    {
        return $this->resolveCmsImageData($page, $storeId)->getUrl();
    }

    public function resolveCmsImageData(DataObject $page, ?int $storeId = null): OptimizedImage
    {
        $mode = (string)($page->getData('og_image_mode') ?: ConfigProvider::MODE_AUTO);

        if ($mode === ConfigProvider::MODE_CUSTOM) {
            $url = $this->buildOpenGraphMediaUrl($page->getData('og_image_custom'));
            return $url !== ''
                ? $this->imageOptimizer->optimize($url, $storeId)
                : new OptimizedImage('');
        }

        return $this->resolveDefaultImageData($storeId);
    }

    public function resolveDefaultImage(?int $storeId = null): string
    {
        return $this->resolveDefaultImageData($storeId)->getUrl();
    }

    public function resolveDefaultImageData(?int $storeId = null): OptimizedImage
    {
        return $this->imageOptimizer->optimize(
            $this->buildGenericMediaUrl($this->config->getDefaultImage($storeId), 'mosaicora/opengraph/default'),
            $storeId
        );
    }

    private function buildCatalogProductMediaUrl(mixed $value): string
    {
        return $this->buildGenericMediaUrl($value, 'catalog/product');
    }

    private function buildCategoryMediaUrl(mixed $value): string
    {
        return $this->buildGenericMediaUrl($value, 'catalog/category');
    }

    private function buildOpenGraphMediaUrl(mixed $value): string
    {
        return $this->buildGenericMediaUrl($value, 'mosaicora/opengraph');
    }

    private function buildGenericMediaUrl(mixed $value, string $basePath = ''): string
    {
        return $this->mediaPathNormalizer->normalizeImageUrl($value, $basePath);
    }
}
