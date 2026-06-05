<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\GraphQl\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Mosaicora\OpenGraph\Model\GraphQl\MetadataFormatter;
use Mosaicora\OpenGraph\Model\GraphQl\OpenGraphAttributeCodes;
use Mosaicora\OpenGraph\Model\MetadataProvider;

class ProductOpenGraph implements BatchResolverInterface
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
        $products = $this->getProducts($requests);
        $loadedProducts = $this->loadProducts($products, $storeId);
        $response = new BatchResponse();

        foreach ($requests as $request) {
            /** @var Product $product */
            $product = $request->getValue()['model'];
            if (isset($loadedProducts[(int)$product->getId()])) {
                $product->addData($loadedProducts[(int)$product->getId()]->getData());
            }
            $product->setData('store_id', $storeId);

            $response->addResponse(
                $request,
                $this->formatter->format($this->metadataProvider->getProduct($product, $storeId))
            );
        }

        return $response;
    }

    /**
     * @param BatchRequestItemInterface[] $requests
     * @return Product[]
     */
    private function getProducts(array $requests): array
    {
        $products = [];
        foreach ($requests as $request) {
            $product = $request->getValue()['model'] ?? null;
            if (!$product instanceof Product) {
                throw new LocalizedException(__('"model" value should contain a product.'));
            }
            $products[] = $product;
        }

        return $products;
    }

    /**
     * @param Product[] $products
     * @return array<int, Product>
     */
    private function loadProducts(array $products, int $storeId): array
    {
        $ids = array_map(static fn (Product $product): int => (int)$product->getId(), $products);
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addIdFilter(array_unique($ids));
        $collection->addAttributeToSelect($this->attributeCodes->getProductCodes($products, $storeId));

        $loaded = [];
        foreach ($collection as $product) {
            $loaded[(int)$product->getId()] = $product;
        }

        return $loaded;
    }
}
