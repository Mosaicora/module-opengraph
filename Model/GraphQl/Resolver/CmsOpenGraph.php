<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\GraphQl\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mosaicora\OpenGraph\Model\GraphQl\MetadataFormatter;
use Mosaicora\OpenGraph\Model\MetadataProvider;

class CmsOpenGraph implements ResolverInterface
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
        $identifier = trim((string)($value['identifier'] ?? ''));
        if ($identifier === '') {
            throw new GraphQlInputException(__('CMS page identifier is required.'));
        }

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $metadata = $this->metadataProvider->getCms($identifier, $storeId);
        if ($metadata === null) {
            throw new GraphQlInputException(__('CMS page metadata could not be resolved.'));
        }

        return $this->formatter->format($metadata);
    }
}
