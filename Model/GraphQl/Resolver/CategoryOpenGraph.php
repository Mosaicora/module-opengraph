<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\GraphQl\Resolver;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Mosaicora\OpenGraph\Model\GraphQl\MetadataFormatter;
use Mosaicora\OpenGraph\Model\GraphQl\OpenGraphAttributeCodes;
use Mosaicora\OpenGraph\Model\MetadataProvider;

class CategoryOpenGraph implements BatchResolverInterface
{
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly OpenGraphAttributeCodes $attributeCodes,
        private readonly MetadataProvider $metadataProvider,
        private readonly MetadataFormatter $formatter
    ) {
    }

    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $categories = $this->getCategories($requests);
        $loadedCategories = $this->loadCategories($categories, $storeId);
        $response = new BatchResponse();

        foreach ($requests as $request) {
            /** @var Category $category */
            $category = $request->getValue()['model'];
            if (isset($loadedCategories[(int)$category->getId()])) {
                $category->addData($loadedCategories[(int)$category->getId()]->getData());
            }
            $category->setData('store_id', $storeId);

            $response->addResponse(
                $request,
                $this->formatter->format($this->metadataProvider->getCategory($category, $storeId))
            );
        }

        return $response;
    }

    /**
     * @param BatchRequestItemInterface[] $requests
     * @return Category[]
     */
    private function getCategories(array $requests): array
    {
        $categories = [];
        foreach ($requests as $request) {
            $category = $request->getValue()['model'] ?? null;
            if (!$category instanceof Category) {
                throw new LocalizedException(__('"model" value should contain a category.'));
            }
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * @param Category[] $categories
     * @return array<int, Category>
     */
    private function loadCategories(array $categories, int $storeId): array
    {
        $ids = array_map(static fn (Category $category): int => (int)$category->getId(), $categories);
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect($this->attributeCodes->getCategoryCodes($categories, $storeId));
        $collection->addFieldToFilter('entity_id', ['in' => array_unique($ids)]);

        $loaded = [];
        foreach ($collection as $category) {
            $loaded[(int)$category->getId()] = $category;
        }

        return $loaded;
    }
}
