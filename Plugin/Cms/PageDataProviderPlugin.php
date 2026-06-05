<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Plugin\Cms;

use Mosaicora\OpenGraph\Model\Resolver\MediaPathNormalizer;

class PageDataProviderPlugin
{
    public function __construct(
        private readonly MediaPathNormalizer $mediaPathNormalizer
    ) {
    }

    /**
     * Normalize stored CMS image paths into the array structure expected by imageUploader.
     *
     * @param array<int|string, array<string, mixed>> $result
     * @return array<int|string, array<string, mixed>>
     */
    public function afterGetData(object $subject, array $result): array
    {
        foreach ($result as $pageId => $pageData) {
            $imageValue = $pageData['og_image_custom'] ?? null;
            if (!is_string($imageValue) || trim($imageValue) === '') {
                continue;
            }

            $imageUrl = $this->mediaPathNormalizer->normalizeImageUrl($imageValue);
            if ($imageUrl === '') {
                continue;
            }

            $result[$pageId]['og_image_custom'] = [[
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                'name' => basename($imageValue),
                'url' => $imageUrl,
            ]];
        }

        return $result;
    }
}
