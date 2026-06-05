<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Builder;

use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Model\Builder\AbstractTagBuilder;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Resolver\ImageUrlResolver;
use Mosaicora\OpenGraph\Model\Resolver\OptimizedImage;
use Mosaicora\OpenGraph\Model\Resolver\PageUrlResolver;
use PHPUnit\Framework\TestCase;

class AbstractTagBuilderTest extends TestCase
{
    public function testImageTagsIncludeOptimizedDimensionsAndTwitterMirror(): void
    {
        $config = $this->createStub(ConfigProvider::class);
        $config->method('getSiteName')->willReturn('Mosaicora');
        $config->method('isTwitterEnabled')->willReturn(true);
        $config->method('getTwitterCard')->willReturn('summary_large_image');

        $builder = new class(
            $config,
            $this->createStub(ImageUrlResolver::class),
            $this->createStub(StoreManagerInterface::class),
            $this->createStub(PageUrlResolver::class)
        ) extends AbstractTagBuilder {
            /**
             * @return array<string, string>
             */
            public function buildForTest(OptimizedImage $image): array
            {
                return $this->withCommonTags(
                    $this->withImageTags(
                        [
                        'og:title' => 'Example title',
                        'og:description' => 'Example description',
                        ], $image
                    ),
                    'website',
                    'https://example.test/page',
                    1
                );
            }
        };

        $tags = $builder->buildForTest(
            new OptimizedImage(
                'https://example.test/media/mosaicora/opengraph/cache/1200x630/cover/hash.jpg',
                1200,
                630
            )
        );

        self::assertSame('https://example.test/media/mosaicora/opengraph/cache/1200x630/cover/hash.jpg', $tags['og:image']);
        self::assertSame('1200', $tags['og:image:width']);
        self::assertSame('630', $tags['og:image:height']);
        self::assertSame($tags['og:image'], $tags['twitter:image']);
        self::assertSame($tags['og:title'], $tags['twitter:title']);
        self::assertSame($tags['og:description'], $tags['twitter:description']);
        self::assertSame('summary_large_image', $tags['twitter:card']);

        $tagsWithoutImage = $builder->buildForTest(new OptimizedImage(''));
        self::assertArrayNotHasKey('og:image', $tagsWithoutImage);
        self::assertArrayNotHasKey('twitter:image', $tagsWithoutImage);
    }
}
