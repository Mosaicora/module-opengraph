<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Resolver;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Resolver\DefaultAttributeMatcher;
use Mosaicora\OpenGraph\Model\Resolver\ImageUrlResolver;
use Mosaicora\OpenGraph\Model\Resolver\MediaPathNormalizer;
use Mosaicora\OpenGraph\Model\Resolver\OpenGraphImageOptimizer;
use Mosaicora\OpenGraph\Model\Resolver\OptimizedImage;
use PHPUnit\Framework\TestCase;

class ImageUrlResolverTest extends TestCase
{
    public function testProductOpenGraphImageRoleIsPreferred(): void
    {
        $config = $this->createStub(ConfigProvider::class);
        $matcher = $this->createStub(DefaultAttributeMatcher::class);
        $matcher->method('getCandidates')->willReturn(
            [
            'open_graph_image',
            'image',
            'small_image',
            ]
        );

        $product = $this->createStub(Product::class);
        $product->method('getData')->willReturnMap(
            [
            ['open_graph_image', null, '/o/g/custom.jpg'],
            ['image', null, '/i/m/base.jpg'],
            ['small_image', null, '/s/m/small.jpg'],
            ]
        );

        $store = $this->createStub(Store::class);
        $store->method('getBaseUrl')->willReturn('https://example.test/media/');

        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $optimizer = $this->createStub(OpenGraphImageOptimizer::class);
        $optimizer->method('optimize')->willReturnCallback(
            static fn (string $url): OptimizedImage => new OptimizedImage($url)
        );

        $resolver = new ImageUrlResolver($config, $matcher, $optimizer, new MediaPathNormalizer($storeManager));

        self::assertSame(
            'https://example.test/media/catalog/product/o/g/custom.jpg',
            $resolver->resolveProductImage($product)
        );
    }

    public function testCategoryCustomImageBeatsCategoryImageAndDefault(): void
    {
        $category = $this->createStub(Category::class);
        $category->method('getData')->willReturnMap(
            [
            ['og_image_mode', null, ConfigProvider::MODE_CUSTOM],
            ['og_image_custom', null, 'custom-category.jpg'],
            ['image', null, 'native-category.jpg'],
            ]
        );

        self::assertSame(
            'https://example.test/media/mosaicora/opengraph/custom-category.jpg',
            $this->createResolver()->resolveCategoryImage($category)
        );
    }

    public function testCategoryAutoImageUsesCategoryImageBeforeDefault(): void
    {
        $category = $this->createStub(Category::class);
        $category->method('getData')->willReturnMap(
            [
            ['og_image_mode', null, ConfigProvider::MODE_AUTO],
            ['og_image_custom', null, 'custom-category.jpg'],
            ['image', null, 'native-category.jpg'],
            ]
        );

        self::assertSame(
            'https://example.test/media/catalog/category/native-category.jpg',
            $this->createResolver()->resolveCategoryImage($category)
        );
    }

    public function testCmsCustomImageBeatsDefault(): void
    {
        $page = new DataObject(
            [
            'og_image_mode' => ConfigProvider::MODE_CUSTOM,
            'og_image_custom' => 'cms-page.jpg',
            ]
        );

        self::assertSame(
            'https://example.test/media/mosaicora/opengraph/cms-page.jpg',
            $this->createResolver()->resolveCmsImage($page)
        );
    }

    public function testCmsAutoImageUsesDefault(): void
    {
        $page = new DataObject(
            [
            'og_image_mode' => ConfigProvider::MODE_AUTO,
            'og_image_custom' => 'cms-page.jpg',
            ]
        );

        self::assertSame(
            'https://example.test/media/mosaicora/opengraph/default/default.jpg',
            $this->createResolver()->resolveCmsImage($page)
        );
    }

    public function testUnsafeRelativeImagePathIsRejected(): void
    {
        $page = new DataObject(
            [
            'og_image_mode' => ConfigProvider::MODE_CUSTOM,
            'og_image_custom' => '../secret.jpg',
            ]
        );

        self::assertSame(
            '',
            $this->createResolver()->resolveCmsImage($page)
        );
    }

    public function testEmptyCustomCategoryImageDoesNotFallBack(): void
    {
        $category = $this->createStub(Category::class);
        $category->method('getData')->willReturnMap(
            [
                ['og_image_mode', null, ConfigProvider::MODE_CUSTOM],
                ['og_image_custom', null, ''],
                ['image', null, 'native-category.jpg'],
            ]
        );

        self::assertSame('', $this->createResolver()->resolveCategoryImage($category));
    }

    public function testProductContinuesAfterMissingCandidate(): void
    {
        $config = $this->createStub(ConfigProvider::class);
        $matcher = $this->createStub(DefaultAttributeMatcher::class);
        $matcher->method('getCandidates')->willReturn(['open_graph_image', 'image']);

        $product = $this->createStub(Product::class);
        $product->method('getData')->willReturnMap(
            [
                ['open_graph_image', null, 'missing.jpg'],
                ['image', null, 'existing.jpg'],
            ]
        );

        $optimizer = $this->createMock(OpenGraphImageOptimizer::class);
        $optimizer->expects($this->exactly(2))
            ->method('optimize')
            ->willReturnCallback(
                static fn (string $url): OptimizedImage => str_ends_with($url, '/missing.jpg')
                    ? new OptimizedImage('')
                    : new OptimizedImage($url)
            );

        $resolver = new ImageUrlResolver(
            $config,
            $matcher,
            $optimizer,
            $this->createMediaPathNormalizer()
        );

        self::assertSame(
            'https://example.test/media/catalog/product/existing.jpg',
            $resolver->resolveProductImage($product)
        );
    }

    public function testExternalImageUrlIsKeptUnchanged(): void
    {
        $page = new DataObject(
            [
            'og_image_mode' => ConfigProvider::MODE_CUSTOM,
            'og_image_custom' => 'https://cdn.example.test/og/page.jpg',
            ]
        );

        self::assertSame(
            'https://cdn.example.test/og/page.jpg',
            $this->createResolver()->resolveCmsImage($page)
        );
    }

    private function createResolver(): ImageUrlResolver
    {
        $config = $this->createStub(ConfigProvider::class);
        $config->method('getDefaultImage')->willReturn('default.jpg');

        $optimizer = $this->createStub(OpenGraphImageOptimizer::class);
        $optimizer->method('optimize')->willReturnCallback(
            static fn (string $url): OptimizedImage => new OptimizedImage($url)
        );

        return new ImageUrlResolver(
            $config,
            $this->createStub(DefaultAttributeMatcher::class),
            $optimizer,
            $this->createMediaPathNormalizer()
        );
    }

    private function createMediaPathNormalizer(): MediaPathNormalizer
    {
        $store = $this->createStub(Store::class);
        $store->method('getBaseUrl')->willReturn('https://example.test/media/');

        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        return new MediaPathNormalizer($storeManager);
    }
}
