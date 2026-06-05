<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Builder;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Model\Builder\ProductTagBuilder;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Context\PageContext;
use Mosaicora\OpenGraph\Model\Resolver\ImageUrlResolver;
use Mosaicora\OpenGraph\Model\Resolver\OptimizedImage;
use Mosaicora\OpenGraph\Model\Resolver\PageUrlResolver;
use Mosaicora\OpenGraph\Model\Resolver\ValueResolver;
use PHPUnit\Framework\TestCase;

class ProductTagBuilderTest extends TestCase
{
    public function testProductAvailabilityUsesCanonicalValue(): void
    {
        $product = $this->createStub(Product::class);
        $product->method('getName')->willReturn('Product name');
        $product->method('getFinalPrice')->willReturn(10.0);
        $product->method('isAvailable')->willReturn(true);
        $product->method('getProductUrl')->willReturn('https://example.test/product.html');

        $config = $this->createStub(ConfigProvider::class);
        $config->method('getSiteName')->willReturn('Mosaicora');
        $config->method('isTwitterEnabled')->willReturn(false);

        $imageUrlResolver = $this->createStub(ImageUrlResolver::class);
        $imageUrlResolver->method('resolveProductImageData')
            ->willReturn(new OptimizedImage('https://example.test/media/catalog/product/image.jpg', 1200, 630));

        $store = $this->createStub(Store::class);
        $store->method('getId')->willReturn(1);
        $store->method('getCurrentCurrencyCode')->willReturn('USD');

        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $valueResolver = $this->createStub(ValueResolver::class);
        $valueResolver->method('resolveText')->willReturnOnConsecutiveCalls('Product title', 'Product description');

        $priceCurrency = $this->createStub(PriceCurrencyInterface::class);
        $priceCurrency->method('round')->willReturn(10.0);

        $pageUrlResolver = $this->createStub(PageUrlResolver::class);
        $pageUrlResolver->method('resolve')->willReturn('https://example.test/product.html');

        $tags = (new ProductTagBuilder(
            $config,
            $imageUrlResolver,
            $storeManager,
            $pageUrlResolver,
            $valueResolver,
            $priceCurrency
        ))->build(new PageContext(PageContext::TYPE_PRODUCT, $product));

        self::assertSame('instock', $tags['product:availability']);
    }
}
