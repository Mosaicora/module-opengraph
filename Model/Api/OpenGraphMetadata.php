<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Api;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface as OpenGraphMetadataDataInterface;
use Mosaicora\OpenGraph\Api\OpenGraphMetadataInterface;
use Mosaicora\OpenGraph\Model\MetadataProvider;

class OpenGraphMetadata implements OpenGraphMetadataInterface
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly MetadataProvider $metadataProvider
    ) {
    }

    public function getProduct(string $sku): OpenGraphMetadataDataInterface
    {
        $storeId = $this->getStoreId();
        $product = $this->productRepository->get($sku, false, $storeId);

        if ((int)$product->getStatus() !== Status::STATUS_ENABLED) {
            throw NoSuchEntityException::singleField('sku', $sku);
        }

        return $this->metadataProvider->getProduct($product, $storeId);
    }

    public function getCategory(int $categoryId): OpenGraphMetadataDataInterface
    {
        $storeId = $this->getStoreId();
        $category = $this->categoryRepository->get($categoryId, $storeId);

        if (!$category->getIsActive()) {
            throw NoSuchEntityException::singleField('categoryId', (string)$categoryId);
        }

        return $this->metadataProvider->getCategory($category, $storeId);
    }

    public function getCms(string $identifier): OpenGraphMetadataDataInterface
    {
        $storeId = $this->getStoreId();
        $metadata = $this->metadataProvider->getCms($identifier, $storeId);
        if ($metadata === null) {
            throw NoSuchEntityException::singleField('identifier', $identifier);
        }

        return $metadata;
    }

    public function getHome(): OpenGraphMetadataDataInterface
    {
        return $this->metadataProvider->getHome($this->getStoreId());
    }

    private function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }
}
