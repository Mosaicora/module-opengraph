<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Plugin;

use Magento\Framework\View\Page\Config\Renderer;
use Mosaicora\OpenGraph\Model\Applier\HeadMetadataDeduplicator;
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
        private readonly HeadMetadataDeduplicator $deduplicator
    ) {
    }

    /**
     * @param callable(): string $proceed
     */
    public function aroundRenderMetadata(Renderer $subject, callable $proceed): string
    {
        $tags = [];
        if ($this->config->isEnabled()) {
            $context = $this->contextResolver->resolve();
            if ($context !== null) {
                $tags = $this->tagBuilder->build($context);
                $this->tagApplier->apply($tags);
            }
        }

        $result = str_replace('<meta name="product:', '<meta property="product:', $proceed());
        if ($tags === [] || !$this->config->isRemoveCompetingTagsEnabled()) {
            return $result;
        }

        return $this->deduplicator->markCanonicalTags($result, $tags);
    }
}
