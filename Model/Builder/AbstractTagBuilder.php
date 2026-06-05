<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Builder;

use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Resolver\ImageUrlResolver;
use Mosaicora\OpenGraph\Model\Resolver\OptimizedImage;
use Mosaicora\OpenGraph\Model\Resolver\PageUrlResolver;

abstract class AbstractTagBuilder
{
    public function __construct(
        protected readonly ConfigProvider $config,
        protected readonly ImageUrlResolver $imageUrlResolver,
        protected readonly StoreManagerInterface $storeManager,
        protected readonly PageUrlResolver $pageUrlResolver
    ) {
    }

    /**
     * @param  array<string, string> $tags
     * @return array<string, string>
     */
    protected function withCommonTags(array $tags, string $type, string $url, ?int $storeId = null): array
    {
        $tags = array_merge(
            [
            'og:type' => $type,
            'og:url' => $url,
            ],
            $tags
        );

        $siteName = $this->config->getSiteName($storeId);
        if ($siteName === '') {
            $siteName = (string)$this->storeManager->getStore()->getFrontendName();
        }

        if ($siteName !== '') {
            $tags['og:site_name'] = $siteName;
        }

        if ($this->config->isTwitterEnabled($storeId)) {
            $tags['twitter:card'] = $this->config->getTwitterCard($storeId);
            foreach (['title', 'description', 'image'] as $field) {
                $ogKey = 'og:' . $field;
                if (!empty($tags[$ogKey])) {
                    $tags['twitter:' . $field] = $tags[$ogKey];
                }
            }
        }

        return $tags;
    }

    /**
     * @param  array<string, string> $tags
     * @return array<string, string>
     */
    protected function withImageTags(array $tags, OptimizedImage $image): array
    {
        if ($image->getUrl() === '') {
            return $tags;
        }

        $tags['og:image'] = $image->getUrl();

        if ($image->getWidth() !== null && $image->getHeight() !== null) {
            $tags['og:image:width'] = (string)$image->getWidth();
            $tags['og:image:height'] = (string)$image->getHeight();
        }

        return $tags;
    }

    protected function getStoreId(): ?int
    {
        return (int)$this->storeManager->getStore()->getId();
    }
}
