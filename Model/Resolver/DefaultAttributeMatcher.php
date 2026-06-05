<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Resolver;

use Magento\Framework\DataObject;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;

class DefaultAttributeMatcher
{
    public function __construct(
        private readonly ConfigProvider $config
    ) {
    }

    /**
     * @return string[]
     */
    public function getCandidates(string $pageType, string $field, ?int $storeId = null): array
    {
        $configured = $this->config->getConfiguredAttribute($pageType, $field, $storeId);
        $candidates = $configured !== '' ? [$configured] : [];

        foreach ($this->config->getDefaultAttributeCandidates($pageType, $field) as $candidate) {
            if (!in_array($candidate, $candidates, true)) {
                $candidates[] = $candidate;
            }
        }

        return $candidates;
    }

    public function resolveFirstValue(DataObject $entity, string $pageType, string $field, ?int $storeId = null): string
    {
        foreach ($this->getCandidates($pageType, $field, $storeId) as $attributeCode) {
            $value = $this->normalize($entity->getData($attributeCode));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function normalize(mixed $value): string
    {
        if (is_array($value)) {
            return '';
        }

        $value = trim((string)$value);
        return $value === 'no_selection' ? '' : $value;
    }
}
