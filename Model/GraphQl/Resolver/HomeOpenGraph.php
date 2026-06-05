<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\GraphQl\Resolver;

use Magento\Cms\Model\Page;
use Magento\Framework\App\Config;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity;
use Mosaicora\OpenGraph\Model\GraphQl\MetadataFormatter;
use Mosaicora\OpenGraph\Model\MetadataProvider;

class HomeOpenGraph implements ResolverInterface
{
    public function __construct(
        private readonly MetadataProvider $metadataProvider,
        private readonly MetadataFormatter $formatter
    ) {
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $result = $this->formatter->format($this->metadataProvider->getHome($storeId));
        $result['_cache_identities'] = [
            Config::CACHE_TAG,
            ConfigIdentity::CACHE_TAG,
            sprintf('%s_%s', ConfigIdentity::CACHE_TAG, $storeId),
        ];

        $page = $this->metadataProvider->getHomePage($storeId);
        if ($page instanceof Page && $page->getId()) {
            $result['_cache_identities'][] = Page::CACHE_TAG;
            $result['_cache_identities'][] = Page::CACHE_TAG . '_' . $page->getId();
        }

        return $result;
    }
}
