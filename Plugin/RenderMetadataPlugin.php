<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Plugin;

use Magento\Framework\View\Page\Config\Renderer;
use Mosaicora\OpenGraph\Model\Applier\AppliedTagRegistry;
use Mosaicora\OpenGraph\Model\Applier\MetaTagApplier;
use Mosaicora\OpenGraph\Model\Builder\CompositeTagBuilder;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Context\PageContextResolver;

class RenderMetadataPlugin
{
    public function __construct(
        private readonly ConfigProvider $config,
        private readonly PageContextResolver $contextResolver,
        private readonly CompositeTagBuilder $tagBuilder,
        private readonly MetaTagApplier $tagApplier,
        private readonly AppliedTagRegistry $tagRegistry
    ) {
    }

    public function beforeRenderMetadata(Renderer $subject): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $context = $this->contextResolver->resolve();
        if ($context === null) {
            return;
        }

        $tags = $this->tagBuilder->build($context);
        $this->tagApplier->apply($tags);
        $this->tagRegistry->set($tags);
    }

    public function afterRenderMetadata(Renderer $subject, string $result): string
    {
        return str_replace('<meta name="product:', '<meta property="product:', $result);
    }
}
