<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Plugin;

use Magento\Framework\View\Page\Config\Renderer;
use Mosaicora\OpenGraph\Model\Applier\HeadMetadataDeduplicator;
use Mosaicora\OpenGraph\Model\Applier\MetaTagApplier;
use Mosaicora\OpenGraph\Model\Builder\CompositeTagBuilder;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Context\PageContext;
use Mosaicora\OpenGraph\Model\Context\PageContextResolver;
use Mosaicora\OpenGraph\Plugin\RenderMetadataPlugin;
use PHPUnit\Framework\TestCase;

class RenderMetadataPluginTest extends TestCase
{
    public function testRendersMetadataWithoutBuildingTagsWhenDisabled(): void
    {
        $config = $this->createStub(ConfigProvider::class);
        $config->method('isEnabled')->willReturn(false);

        $contextResolver = $this->createMock(PageContextResolver::class);
        $contextResolver->expects($this->never())->method('resolve');

        $plugin = new RenderMetadataPlugin(
            $config,
            $contextResolver,
            $this->createStub(CompositeTagBuilder::class),
            $this->createStub(MetaTagApplier::class),
            $this->createStub(HeadMetadataDeduplicator::class)
        );

        self::assertSame(
            '<meta name="description" content="Description"/>',
            $plugin->aroundRenderMetadata(
                $this->createStub(Renderer::class),
                static fn (): string => '<meta name="description" content="Description"/>'
            )
        );
    }

    public function testAppliesAndMarksOpenGraphTagsWhenDeduplicationIsEnabled(): void
    {
        $config = $this->createStub(ConfigProvider::class);
        $config->method('isEnabled')->willReturn(true);
        $config->method('isRemoveCompetingTagsEnabled')->willReturn(true);

        $context = new PageContext(PageContext::TYPE_HOME);
        $tags = ['og:image' => 'https://example.test/image.jpg'];

        $contextResolver = $this->createStub(PageContextResolver::class);
        $contextResolver->method('resolve')->willReturn($context);

        $tagBuilder = $this->createMock(CompositeTagBuilder::class);
        $tagBuilder->expects($this->once())
            ->method('build')
            ->with($context)
            ->willReturn($tags);

        $tagApplier = $this->createMock(MetaTagApplier::class);
        $tagApplier->expects($this->once())
            ->method('apply')
            ->with($tags);

        $deduplicator = $this->createMock(HeadMetadataDeduplicator::class);
        $deduplicator->expects($this->once())
            ->method('markCanonicalTags')
            ->with('<meta property="og:image" content="image.jpg"/>', $tags)
            ->willReturn('<meta property="og:image" content="image.jpg" data-mosaicora-opengraph="1"/>');

        $plugin = new RenderMetadataPlugin($config, $contextResolver, $tagBuilder, $tagApplier, $deduplicator);

        self::assertStringContainsString(
            'data-mosaicora-opengraph="1"',
            $plugin->aroundRenderMetadata(
                $this->createStub(Renderer::class),
                static fn (): string => '<meta property="og:image" content="image.jpg"/>'
            )
        );
    }

    public function testDoesNotMarkTagsWhenDeduplicationIsDisabled(): void
    {
        $config = $this->createStub(ConfigProvider::class);
        $config->method('isEnabled')->willReturn(true);
        $config->method('isRemoveCompetingTagsEnabled')->willReturn(false);

        $contextResolver = $this->createStub(PageContextResolver::class);
        $contextResolver->method('resolve')->willReturn(new PageContext(PageContext::TYPE_HOME));
        $tagBuilder = $this->createStub(CompositeTagBuilder::class);
        $tagBuilder->method('build')->willReturn(['og:title' => 'Title']);

        $deduplicator = $this->createMock(HeadMetadataDeduplicator::class);
        $deduplicator->expects($this->never())->method('markCanonicalTags');

        $plugin = new RenderMetadataPlugin(
            $config,
            $contextResolver,
            $tagBuilder,
            $this->createStub(MetaTagApplier::class),
            $deduplicator
        );

        self::assertSame(
            '<meta property="og:title" content="Title"/>',
            $plugin->aroundRenderMetadata(
                $this->createStub(Renderer::class),
                static fn (): string => '<meta property="og:title" content="Title"/>'
            )
        );
    }

    public function testConvertsProductMetadataNameToProperty(): void
    {
        $plugin = new RenderMetadataPlugin(
            $this->createStub(ConfigProvider::class),
            $this->createStub(PageContextResolver::class),
            $this->createStub(CompositeTagBuilder::class),
            $this->createStub(MetaTagApplier::class),
            $this->createStub(HeadMetadataDeduplicator::class)
        );

        self::assertSame(
            '<meta property="product:price:amount" content="10"/>',
            $plugin->aroundRenderMetadata(
                $this->createStub(Renderer::class),
                static fn (): string => '<meta name="product:price:amount" content="10"/>'
            )
        );
    }
}
