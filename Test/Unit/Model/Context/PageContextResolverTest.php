<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Context;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;
use Mosaicora\OpenGraph\Model\Context\CmsPageContextRegistry;
use Mosaicora\OpenGraph\Model\Context\PageContext;
use Mosaicora\OpenGraph\Model\Context\PageContextResolver;
use PHPUnit\Framework\TestCase;

class PageContextResolverTest extends TestCase
{
    public function testResolvesProductOnlyOnStorefrontProductView(): void
    {
        $product = $this->createStub(Product::class);
        $registry = $this->createStub(Registry::class);
        $registry->method('registry')->willReturnMap(
            [
                ['current_product', $product],
                ['current_category', null],
            ]
        );
        $request = $this->createStub(Http::class);
        $request->method('getFullActionName')->willReturn('catalog_product_view');

        $context = (new PageContextResolver(
            $request,
            $registry,
            $this->createStub(CmsPageContextRegistry::class)
        ))->resolve();

        self::assertInstanceOf(PageContext::class, $context);
        self::assertSame(PageContext::TYPE_PRODUCT, $context->getType());
        self::assertSame($product, $context->getEntity());
    }

    public function testIgnoresRegisteredProductOutsideStorefrontProductView(): void
    {
        $registry = $this->createStub(Registry::class);
        $registry->method('registry')->willReturnMap(
            [
                ['current_product', $this->createStub(Product::class)],
                ['current_category', null],
            ]
        );
        $request = $this->createStub(Http::class);
        $request->method('getFullActionName')->willReturn('catalog_product_edit');

        self::assertNull(
            (new PageContextResolver(
                $request,
                $registry,
                $this->createStub(CmsPageContextRegistry::class)
            ))->resolve()
        );
    }
}
