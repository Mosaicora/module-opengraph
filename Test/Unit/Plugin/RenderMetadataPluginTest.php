<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Plugin;

use Magento\Framework\View\Page\Config\Renderer;
use Mosaicora\OpenGraph\Model\Applier\AppliedTagRegistry;
use Mosaicora\OpenGraph\Model\Applier\MetaTagApplier;
use Mosaicora\OpenGraph\Model\Builder\CompositeTagBuilder;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Context\PageContext;
use Mosaicora\OpenGraph\Model\Context\PageContextResolver;
use Mosaicora\OpenGraph\Plugin\RenderMetadataPlugin;
use PHPUnit\Framework\TestCase;

class RenderMetadataPluginTest extends TestCase
{
    public function testDoesNothingBeforeRenderMetadataWhenDisabled(): void
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
            $this->createStub(AppliedTagRegistry::class)
        );

        $plugin->beforeRenderMetadata($this->createStub(Renderer::class));
    }

    public function testAppliesOpenGraphTagsBeforeRenderingMetadata(): void
    {
        $config = $this->createStub(ConfigProvider::class);
        $config->method('isEnabled')->willReturn(true);

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

        $tagRegistry = $this->createMock(AppliedTagRegistry::class);
        $tagRegistry->expects($this->once())
            ->method('set')
            ->with($tags);

        $plugin = new RenderMetadataPlugin($config, $contextResolver, $tagBuilder, $tagApplier, $tagRegistry);

        $plugin->beforeRenderMetadata($this->createStub(Renderer::class));
    }

    public function testConvertsProductMetadataNameToProperty(): void
    {
        $plugin = new RenderMetadataPlugin(
            $this->createStub(ConfigProvider::class),
            $this->createStub(PageContextResolver::class),
            $this->createStub(CompositeTagBuilder::class),
            $this->createStub(MetaTagApplier::class),
            $this->createStub(AppliedTagRegistry::class)
        );

        self::assertSame(
            '<meta property="product:price:amount" content="10"/>',
            $plugin->afterRenderMetadata(
                $this->createStub(Renderer::class),
                '<meta name="product:price:amount" content="10"/>'
            )
        );
    }
}
