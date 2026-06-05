<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\GraphQl;

use Magento\Framework\DataObject;
use Mosaicora\OpenGraph\Model\Resolver\DefaultAttributeMatcher;

class OpenGraphAttributeCodes
{
    private const TEXT_ATTRIBUTES = [
        'og_title_mode',
        'og_title_attribute',
        'og_title_custom',
        'og_description_mode',
        'og_description_attribute',
        'og_description_custom',
    ];

    public function __construct(
        private readonly DefaultAttributeMatcher $attributeMatcher
    ) {
    }

    /**
     * @param DataObject[] $entities
     * @return string[]
     */
    public function getProductCodes(array $entities, int $storeId): array
    {
        return $this->getCodes(
            'product',
            $entities,
            $storeId,
            ['open_graph_image', 'image', 'small_image']
        );
    }

    /**
     * @param DataObject[] $entities
     * @return string[]
     */
    public function getCategoryCodes(array $entities, int $storeId): array
    {
        return $this->getCodes(
            'category',
            $entities,
            $storeId,
            ['og_image_mode', 'og_image_custom', 'image']
        );
    }

    /**
     * @param DataObject[] $entities
     * @param string[] $imageAttributes
     * @return string[]
     */
    private function getCodes(string $pageType, array $entities, int $storeId, array $imageAttributes): array
    {
        $codes = array_merge(self::TEXT_ATTRIBUTES, $imageAttributes);

        foreach (['title', 'description', 'image'] as $field) {
            foreach ($this->attributeMatcher->getCandidates($pageType, $field, $storeId) as $candidate) {
                $codes[] = $candidate;
            }
        }

        foreach ($entities as $entity) {
            foreach (['og_title_attribute', 'og_description_attribute'] as $sourceAttribute) {
                $sourceCode = trim((string)$entity->getData($sourceAttribute));
                if ($sourceCode !== '') {
                    $codes[] = $sourceCode;
                }
            }
        }

        return array_values(array_unique(array_filter($codes)));
    }
}
