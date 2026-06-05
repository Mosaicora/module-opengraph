<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Api;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Model\Api\OpenGraphMetadata;
use Mosaicora\OpenGraph\Model\Data\OpenGraphMetadata as MetadataData;
use Mosaicora\OpenGraph\Model\MetadataProvider;
use PHPUnit\Framework\TestCase;

class OpenGraphMetadataTest extends TestCase
{
    public function testProductDelegatesLoadedEntityToSharedProvider(): void
    {
        $product = $this->createStub(Product::class);
        $product->method('getStatus')->willReturn(Status::STATUS_ENABLED);
        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->expects($this->once())->method('get')->with('shirt-blue', false, 7)->willReturn($product);
        $metadata = $this->metadata('product', 'shirt-blue');
        $provider = $this->createMock(MetadataProvider::class);
        $provider->expects($this->once())->method('getProduct')->with($product, 7)->willReturn($metadata);

        self::assertSame(
            $metadata,
            $this->createService($provider, productRepository: $repository)->getProduct('shirt-blue')
        );
    }

    public function testCategoryDelegatesLoadedEntityToSharedProvider(): void
    {
        $category = $this->createStub(Category::class);
        $category->method('getIsActive')->willReturn(true);
        $repository = $this->createMock(CategoryRepositoryInterface::class);
        $repository->expects($this->once())->method('get')->with(42, 7)->willReturn($category);
        $metadata = $this->metadata('category', '42');
        $provider = $this->createMock(MetadataProvider::class);
        $provider->expects($this->once())->method('getCategory')->with($category, 7)->willReturn($metadata);

        self::assertSame(
            $metadata,
            $this->createService($provider, categoryRepository: $repository)->getCategory(42)
        );
    }

    public function testCmsDelegatesIdentifierToSharedProvider(): void
    {
        $metadata = $this->metadata('cms', 'about-us');
        $provider = $this->createMock(MetadataProvider::class);
        $provider->expects($this->once())->method('getCms')->with('about-us', 7)->willReturn($metadata);

        self::assertSame($metadata, $this->createService($provider)->getCms('about-us'));
    }

    public function testCmsThrowsWhenProviderCannotLoadPage(): void
    {
        $provider = $this->createStub(MetadataProvider::class);
        $provider->method('getCms')->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->createService($provider)->getCms('missing');
    }

    public function testHomeDelegatesCurrentStoreToSharedProvider(): void
    {
        $metadata = $this->metadata('home', 'home');
        $provider = $this->createMock(MetadataProvider::class);
        $provider->expects($this->once())->method('getHome')->with(7)->willReturn($metadata);

        self::assertSame($metadata, $this->createService($provider)->getHome());
    }

    private function createService(
        MetadataProvider $provider,
        ?ProductRepositoryInterface $productRepository = null,
        ?CategoryRepositoryInterface $categoryRepository = null
    ): OpenGraphMetadata {
        $store = $this->createStub(Store::class);
        $store->method('getId')->willReturn(7);
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        return new OpenGraphMetadata(
            $storeManager,
            $productRepository ?? $this->createStub(ProductRepositoryInterface::class),
            $categoryRepository ?? $this->createStub(CategoryRepositoryInterface::class),
            $provider
        );
    }

    private function metadata(string $pageType, string $identifier): MetadataData
    {
        $metadata = new MetadataData();
        $metadata->setPageType($pageType)
            ->setIdentifier($identifier)
            ->setStoreId(7)
            ->setEnabled(true)
            ->setTags([]);

        return $metadata;
    }
}
