<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\GraphQl\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;
use Mosaicora\OpenGraph\Model\Data\OpenGraphMetadata;
use Mosaicora\OpenGraph\Model\GraphQl\MetadataFormatter;
use Mosaicora\OpenGraph\Model\GraphQl\OpenGraphAttributeCodes;
use Mosaicora\OpenGraph\Model\GraphQl\Resolver\ProductOpenGraph;
use Mosaicora\OpenGraph\Model\MetadataProvider;
use Mosaicora\OpenGraph\Test\Unit\Stub\GraphQlContextExtension;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ProductOpenGraphTest extends TestCase
{
    public function testBatchLoadsAttributesOnceAndResolvesEveryProduct(): void
    {
        $first = $this->product(1, 'first');
        $second = $this->product(2, 'second');
        $loadedFirst = $this->product(1, 'first');
        $loadedFirst->setData('og_title_custom', 'First title');
        $loadedSecond = $this->product(2, 'second');
        $loadedSecond->setData('og_title_custom', 'Second title');

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())->method('setStoreId')->with(7)->willReturnSelf();
        $collection->expects($this->once())->method('addIdFilter')->with([1, 2])->willReturnSelf();
        $collection->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(['og_title_custom'])
            ->willReturnSelf();
        $collection->method('getIterator')->willReturn(new \ArrayIterator([$loadedFirst, $loadedSecond]));

        $collectionFactory = $this->createMock(CollectionFactory::class);
        $collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $attributeCodes = $this->createMock(OpenGraphAttributeCodes::class);
        $attributeCodes->expects($this->once())
            ->method('getProductCodes')
            ->with([$first, $second], 7)
            ->willReturn(['og_title_custom']);
        $provider = $this->createMock(MetadataProvider::class);
        $provider->expects($this->exactly(2))
            ->method('getProduct')
            ->willReturnCallback(
                function (Product $product, int $storeId): OpenGraphMetadata {
                    self::assertSame(7, $storeId);
                    self::assertNotSame('', $product->getData('og_title_custom'));
                    return $this->metadata((string)$product->getData('sku'));
                }
            );

        $firstRequest = $this->request($first);
        $secondRequest = $this->request($second);
        $response = (new ProductOpenGraph(
            $collectionFactory,
            $attributeCodes,
            $provider,
            new MetadataFormatter()
        ))->resolve(
            $this->context(),
            $this->createStub(Field::class),
            [$firstRequest, $secondRequest]
        );

        self::assertSame('first', $response->findResponseFor($firstRequest)['identifier']);
        self::assertSame('second', $response->findResponseFor($secondRequest)['identifier']);
    }

    private function product(int $id, string $sku): Product
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $product->setId($id);
        $product->setSku($sku);

        return $product;
    }

    private function request(Product $product): BatchRequestItemInterface
    {
        $request = $this->createStub(BatchRequestItemInterface::class);
        $request->method('getValue')->willReturn(['model' => $product]);

        return $request;
    }

    private function context(): ContextInterface
    {
        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn(7);
        $context = $this->createStub(ContextInterface::class);
        $context->method('getExtensionAttributes')->willReturn(new GraphQlContextExtension($store));

        return $context;
    }

    private function metadata(string $identifier): OpenGraphMetadata
    {
        $metadata = new OpenGraphMetadata();
        $metadata->setPageType('product')
            ->setIdentifier($identifier)
            ->setStoreId(7)
            ->setEnabled(true)
            ->setTags([]);

        return $metadata;
    }
}
