<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    public const XML_PATH_ENABLED = 'mosaicora_opengraph/general/enabled';
    public const XML_PATH_SITE_NAME = 'mosaicora_opengraph/general/site_name';
    public const XML_PATH_DEFAULT_IMAGE = 'mosaicora_opengraph/general/default_image';
    public const XML_PATH_TWITTER_ENABLED = 'mosaicora_opengraph/general/twitter_enabled';
    public const XML_PATH_TWITTER_CARD = 'mosaicora_opengraph/general/twitter_card';
    public const XML_PATH_REMOVE_COMPETING_TAGS = 'mosaicora_opengraph/general/remove_competing_tags';
    public const XML_PATH_IMAGE_OPTIMIZATION_ENABLED = 'mosaicora_opengraph/image_optimization/enabled';
    public const XML_PATH_IMAGE_OPTIMIZATION_WIDTH = 'mosaicora_opengraph/image_optimization/width';
    public const XML_PATH_IMAGE_OPTIMIZATION_HEIGHT = 'mosaicora_opengraph/image_optimization/height';
    public const XML_PATH_IMAGE_OPTIMIZATION_RESIZE_MODE = 'mosaicora_opengraph/image_optimization/resize_mode';
    public const XML_PATH_IMAGE_OPTIMIZATION_BACKGROUND = 'mosaicora_opengraph/image_optimization/background_color';

    public const MODE_AUTO = 'auto';
    public const MODE_ATTRIBUTE = 'attribute';
    public const MODE_CUSTOM = 'custom';
    public const RESIZE_MODE_COVER = 'cover';
    public const RESIZE_MODE_SCALE = 'scale';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isTwitterEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_TWITTER_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isRemoveCompetingTagsEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_REMOVE_COMPETING_TAGS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSiteName(?int $storeId = null): string
    {
        return (string)$this->getValue(self::XML_PATH_SITE_NAME, $storeId);
    }

    public function getDefaultImage(?int $storeId = null): string
    {
        return (string)$this->getValue(self::XML_PATH_DEFAULT_IMAGE, $storeId);
    }

    public function getTwitterCard(?int $storeId = null): string
    {
        return (string)($this->getValue(self::XML_PATH_TWITTER_CARD, $storeId) ?: 'summary_large_image');
    }

    public function isImageOptimizationEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_IMAGE_OPTIMIZATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getOptimizedImageWidth(?int $storeId = null): int
    {
        return max(1, (int)($this->getValue(self::XML_PATH_IMAGE_OPTIMIZATION_WIDTH, $storeId) ?: 1200));
    }

    public function getOptimizedImageHeight(?int $storeId = null): int
    {
        return max(1, (int)($this->getValue(self::XML_PATH_IMAGE_OPTIMIZATION_HEIGHT, $storeId) ?: 630));
    }

    public function getOptimizedImageResizeMode(?int $storeId = null): string
    {
        $mode = (string)($this->getValue(self::XML_PATH_IMAGE_OPTIMIZATION_RESIZE_MODE, $storeId)
            ?: self::RESIZE_MODE_SCALE);

        return in_array($mode, [self::RESIZE_MODE_COVER, self::RESIZE_MODE_SCALE], true)
            ? $mode
            : self::RESIZE_MODE_SCALE;
    }

    /**
     * @return int[]
     */
    public function getOptimizedImageBackgroundColor(?int $storeId = null): array
    {
        $value = (string)($this->getValue(self::XML_PATH_IMAGE_OPTIMIZATION_BACKGROUND, $storeId) ?: '255,255,255');
        $parts = array_map('intval', explode(',', $value));

        if (count($parts) !== 3) {
            return [255, 255, 255];
        }

        return array_map(static fn (int $part): int => max(0, min(255, $part)), $parts);
    }

    public function getConfiguredAttribute(string $pageType, string $field, ?int $storeId = null): string
    {
        return (string)$this->getValue("mosaicora_opengraph/{$pageType}/{$field}_attribute", $storeId);
    }

    /**
     * @return string[]
     */
    public function getDefaultAttributeCandidates(string $pageType, string $field): array
    {
        $defaults = [
            'product' => [
                'title' => ['meta_title', 'name'],
                'description' => ['meta_description', 'short_description', 'description'],
                'image' => ['open_graph_image', 'image', 'small_image'],
            ],
            'category' => [
                'title' => ['meta_title', 'name'],
                'description' => ['meta_description', 'description'],
                'image' => ['image'],
            ],
            'cms' => [
                'title' => ['meta_title', 'title'],
                'description' => ['meta_description', 'content'],
                'image' => [],
            ],
        ];

        return $defaults[$pageType][$field] ?? [];
    }

    private function getValue(string $path, ?int $storeId = null): mixed
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
