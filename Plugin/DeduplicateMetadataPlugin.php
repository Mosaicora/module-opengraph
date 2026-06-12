<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Plugin;

use Magento\Framework\App\Response\Http;
use Mosaicora\OpenGraph\Model\Applier\HeadMetadataDeduplicator;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;

class DeduplicateMetadataPlugin
{
    public function __construct(
        private readonly ConfigProvider $config,
        private readonly HeadMetadataDeduplicator $deduplicator
    ) {
    }

    /**
     * @param callable(string): Http $proceed
     */
    public function aroundAppendBody(Http $subject, callable $proceed, string $value): Http
    {
        if (!$this->config->isEnabled() || !$this->config->isRemoveCompetingTagsEnabled()) {
            return $proceed($value);
        }

        $contentType = $subject->getHeader('Content-Type');
        if ($contentType && stripos($contentType->getFieldValue(), 'html') === false) {
            return $proceed($value);
        }

        return $proceed($this->deduplicator->process($value));
    }
}
