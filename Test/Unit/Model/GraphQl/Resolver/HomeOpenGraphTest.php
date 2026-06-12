<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\GraphQl\Resolver;

use Magento\Cms\Model\Page;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;
use Mosaicora\OpenGraph\Model\Data\OpenGraphMetadata;
use Mosaicora\OpenGraph\Model\GraphQl\MetadataFormatter;
use Mosaicora\OpenGraph\Model\GraphQl\Resolver\HomeOpenGraph;
use Mosaicora\OpenGraph\Model\MetadataProvider;
use Mosaicora\OpenGraph\Test\Unit\Stub\GraphQlContextExtension;
use PHPUnit\Framework\TestCase;

class HomeOpenGraphTest extends TestCase
{
    public function testUsesLoadedPageForMetadataAndCacheIdentities(): void
    {
        $page = $this->createStub(Page::class);
        $page->method('getId')->willReturn(42);
        $metadata = new OpenGraphMetadata();
        $metadata->setPageType('home')
            ->setIdentifier('home')
            ->setStoreId(7)
            ->setEnabled(true)
            ->setTags([]);

        $provider = $this->createMock(MetadataProvider::class);
        $provider->expects($this->once())->method('getHomePage')->with(7)->willReturn($page);
        $provider->expects($this->once())
            ->method('getHomeWithPage')
            ->with(7, $page)
            ->willReturn($metadata);

        $result = (new HomeOpenGraph($provider, new MetadataFormatter()))->resolve(
            $this->createStub(Field::class),
            $this->context(),
            $this->createStub(ResolveInfo::class)
        );

        self::assertContains(Page::CACHE_TAG . '_42', $result['_cache_identities']);
    }

    private function context(): ContextInterface
    {
        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn(7);
        $context = $this->createStub(ContextInterface::class);
        $context->method('getExtensionAttributes')->willReturn(new GraphQlContextExtension($store));

        return $context;
    }
}
