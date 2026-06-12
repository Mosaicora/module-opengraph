<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Mosaicora\OpenGraph\Model\Builder\CompositeTagBuilder;
use Mosaicora\OpenGraph\Model\CmsPageLoader;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Context\PageContext;
use Mosaicora\OpenGraph\Model\Data\OpenGraphTag;
use Mosaicora\OpenGraph\Model\Data\OpenGraphTagFactory;
use Mosaicora\OpenGraph\Model\MetadataProvider;
use Mosaicora\OpenGraph\Model\Resolver\TextSanitizer;
use PHPUnit\Framework\TestCase;

class MetadataProviderTest extends TestCase
{
    public function testBuildsProductMetadataFromSharedTagBuilder(): void
    {
        $product = $this->createStub(Product::class);
        $product->method('getSku')->willReturn('shirt-blue');
        $config = $this->createMock(ConfigProvider::class);
        $config->method('isEnabled')->with(7)->willReturn(true);
        $tagBuilder = $this->createMock(CompositeTagBuilder::class);
        $tagBuilder->expects($this->once())
            ->method('build')
            ->with($this->callback(
                static fn (PageContext $context): bool => $context->getType() === PageContext::TYPE_PRODUCT
                    && $context->getEntity() === $product
            ))
            ->willReturn(['og:title' => '<strong>Example</strong> &amp; title']);

        $metadata = $this->createProvider($config, $tagBuilder)->getProduct($product, 7);

        self::assertSame('product', $metadata->getPageType());
        self::assertSame('shirt-blue', $metadata->getIdentifier());
        self::assertSame('og:title', $metadata->getTags()[0]->getName());
        self::assertSame('Example & title', $metadata->getTags()[0]->getContent());
    }

    public function testDisabledModuleReturnsEmptyTagsWithoutBuilding(): void
    {
        $product = $this->createStub(Product::class);
        $product->method('getSku')->willReturn('shirt-blue');
        $config = $this->createMock(ConfigProvider::class);
        $config->method('isEnabled')->with(7)->willReturn(false);
        $tagBuilder = $this->createMock(CompositeTagBuilder::class);
        $tagBuilder->expects($this->never())->method('build');

        $metadata = $this->createProvider($config, $tagBuilder)->getProduct($product, 7);

        self::assertFalse($metadata->getEnabled());
        self::assertSame([], $metadata->getTags());
    }

    private function createProvider(
        ConfigProvider $config,
        CompositeTagBuilder $tagBuilder
    ): MetadataProvider {
        $sanitizer = $this->createStub(TextSanitizer::class);
        $sanitizer->method('clean')->willReturn('Example & title');
        $tagFactory = $this->createStub(OpenGraphTagFactory::class);
        $tagFactory->method('create')->willReturnCallback(
            static fn (): OpenGraphTag => new OpenGraphTag($sanitizer)
        );

        return new MetadataProvider(
            $config,
            $this->createStub(ScopeConfigInterface::class),
            $this->createStub(CmsPageLoader::class),
            $tagBuilder,
            $tagFactory
        );
    }
}
