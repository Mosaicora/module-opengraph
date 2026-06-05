<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Resolver;

use Magento\Framework\DataObject;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;

class ValueResolver
{
    public function __construct(
        private readonly DefaultAttributeMatcher $attributeMatcher,
        private readonly TextSanitizer $textSanitizer
    ) {
    }

    public function resolveText(
        DataObject $entity,
        string $pageType,
        string $field,
        string $modeAttribute,
        string $sourceAttribute,
        string $customAttribute,
        ?int $storeId = null,
        int $maxLength = 300
    ): string {
        $mode = (string)($entity->getData($modeAttribute) ?: ConfigProvider::MODE_AUTO);

        if ($mode === ConfigProvider::MODE_CUSTOM) {
            return $this->textSanitizer->clean($entity->getData($customAttribute), $maxLength);
        }

        if ($mode === ConfigProvider::MODE_ATTRIBUTE) {
            $source = (string)$entity->getData($sourceAttribute);
            return $source !== '' ? $this->textSanitizer->clean($entity->getData($source), $maxLength) : '';
        }

        return $this->textSanitizer->clean(
            $this->attributeMatcher->resolveFirstValue($entity, $pageType, $field, $storeId),
            $maxLength
        );
    }
}
