<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\GraphQl\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;
use Mosaicora\OpenGraph\Model\Data\OpenGraphMetadata;
use Mosaicora\OpenGraph\Model\GraphQl\MetadataFormatter;
use Mosaicora\OpenGraph\Model\GraphQl\Resolver\CmsOpenGraph;
use Mosaicora\OpenGraph\Model\MetadataProvider;
use PHPUnit\Framework\TestCase;

class CmsOpenGraphTest extends TestCase
{
    public function testResolvesCmsMetadataByParentIdentifierAndStore(): void
    {
        $metadata = new OpenGraphMetadata();
        $metadata->setPageType('cms')
            ->setIdentifier('about-us')
            ->setStoreId(7)
            ->setEnabled(true)
            ->setTags([]);
        $provider = $this->createMock(MetadataProvider::class);
        $provider->expects($this->once())->method('getCms')->with('about-us', 7)->willReturn($metadata);

        $result = (new CmsOpenGraph($provider, new MetadataFormatter()))->resolve(
            $this->createStub(Field::class),
            $this->context(),
            $this->createStub(ResolveInfo::class),
            ['identifier' => 'about-us']
        );

        self::assertSame('about-us', $result['identifier']);
        self::assertSame(7, $result['store_id']);
    }

    private function context(): ContextInterface
    {
        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn(7);
        $extension = new class ($store) implements ContextExtensionInterface {
            public function __construct(
                private readonly StoreInterface $store
            ) {
            }

            public function getStore(): StoreInterface
            {
                return $this->store;
            }
        };
        $context = $this->createStub(ContextInterface::class);
        $context->method('getExtensionAttributes')->willReturn($extension);

        return $context;
    }
}
