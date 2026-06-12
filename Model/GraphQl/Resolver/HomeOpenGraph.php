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
use Mosaicora\OpenGraph\Model\GraphQl\MetadataFormatter;
use Mosaicora\OpenGraph\Model\MetadataProvider;

class HomeOpenGraph implements ResolverInterface
{
    private const STORE_CONFIG_CACHE_TAG = 'gql_store_config';

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
        $page = $this->metadataProvider->getHomePage($storeId);
        $result = $this->formatter->format($this->metadataProvider->getHomeWithPage($storeId, $page));
        $result['_cache_identities'] = [
            Config::CACHE_TAG,
            self::STORE_CONFIG_CACHE_TAG,
            sprintf('%s_%s', self::STORE_CONFIG_CACHE_TAG, $storeId),
        ];

        if ($page instanceof Page && $page->getId()) {
            $result['_cache_identities'][] = Page::CACHE_TAG;
            $result['_cache_identities'][] = Page::CACHE_TAG . '_' . $page->getId();
        }

        return $result;
    }
}
