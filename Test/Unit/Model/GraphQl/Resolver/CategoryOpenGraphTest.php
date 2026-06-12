<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\GraphQl\Resolver;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;
use Mosaicora\OpenGraph\Model\Data\OpenGraphMetadata;
use Mosaicora\OpenGraph\Model\GraphQl\MetadataFormatter;
use Mosaicora\OpenGraph\Model\GraphQl\OpenGraphAttributeCodes;
use Mosaicora\OpenGraph\Model\GraphQl\Resolver\CategoryOpenGraph;
use Mosaicora\OpenGraph\Model\MetadataProvider;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CategoryOpenGraphTest extends TestCase
{
    public function testBatchLoadsAttributesOnceAndResolvesEveryCategory(): void
    {
        $first = $this->category(11);
        $second = $this->category(12);
        $loadedFirst = $this->category(11);
        $loadedFirst->setData('og_image_mode', 'custom');
        $loadedSecond = $this->category(12);
        $loadedSecond->setData('og_image_mode', 'auto');

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())->method('setStoreId')->with(7)->willReturnSelf();
        $collection->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(['og_image_mode'])
            ->willReturnSelf();
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('entity_id', ['in' => [11, 12]])
            ->willReturnSelf();
        $collection->method('getIterator')->willReturn(new \ArrayIterator([$loadedFirst, $loadedSecond]));

        $factory = $this->createMock(CollectionFactory::class);
        $factory->expects($this->once())->method('create')->willReturn($collection);
        $codes = $this->createMock(OpenGraphAttributeCodes::class);
        $codes->expects($this->once())
            ->method('getCategoryCodes')
            ->with([$first, $second], 7)
            ->willReturn(['og_image_mode']);
        $provider = $this->createMock(MetadataProvider::class);
        $provider->expects($this->exactly(2))
            ->method('getCategory')
            ->willReturnCallback(
                function (Category $category): OpenGraphMetadata {
                    self::assertNotSame('', $category->getData('og_image_mode'));
                    return $this->metadata((string)$category->getId());
                }
            );

        $firstRequest = $this->request($first);
        $secondRequest = $this->request($second);
        $response = (new CategoryOpenGraph(
            $factory,
            $codes,
            $provider,
            new MetadataFormatter()
        ))->resolve(
            $this->context(),
            $this->createStub(Field::class),
            [$firstRequest, $secondRequest]
        );

        self::assertSame('11', $response->findResponseFor($firstRequest)['identifier']);
        self::assertSame('12', $response->findResponseFor($secondRequest)['identifier']);
    }

    private function category(int $id): Category
    {
        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $category->setId($id);

        return $category;
    }

    private function request(Category $category): BatchRequestItemInterface
    {
        $request = $this->createStub(BatchRequestItemInterface::class);
        $request->method('getValue')->willReturn(['model' => $category]);

        return $request;
    }

    private function context(): ContextInterface
    {
        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn(7);
        $extension = $this->contextExtension($store);
        $context = $this->createStub(ContextInterface::class);
        $context->method('getExtensionAttributes')->willReturn($extension);

        return $context;
    }

    private function contextExtension(StoreInterface $store): ContextExtensionInterface
    {
        return new class ($store) implements ContextExtensionInterface {
            public function __construct(
                private StoreInterface $store
            ) {
            }

            public function getStore(): StoreInterface
            {
                return $this->store;
            }

            public function setStore(StoreInterface $store): self
            {
                $this->store = $store;
                return $this;
            }

            public function getIsCustomer(): bool
            {
                return false;
            }

            public function setIsCustomer(bool $isCustomer): self
            {
                return $this;
            }

            public function getCustomerGroupId(): int
            {
                return 0;
            }

            public function setCustomerGroupId(int $customerGroupId): self
            {
                return $this;
            }
        };
    }

    private function metadata(string $identifier): OpenGraphMetadata
    {
        $metadata = new OpenGraphMetadata();
        $metadata->setPageType('category')
            ->setIdentifier($identifier)
            ->setStoreId(7)
            ->setEnabled(true)
            ->setTags([]);

        return $metadata;
    }
}
