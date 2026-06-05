<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Applier;

use Magento\Framework\View\Page\Config as PageConfig;

class MetaTagApplier
{
    public function __construct(
        private readonly PageConfig $pageConfig
    ) {
    }

    /**
     * @param array<string, string> $tags
     */
    public function apply(array $tags): void
    {
        foreach ($tags as $name => $content) {
            $this->pageConfig->setMetadata($name, $content);
        }
    }
}
