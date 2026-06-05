<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Context;

use Magento\Cms\Model\Page;

class CmsPageContextRegistry
{
    private ?Page $page = null;

    public function set(Page $page): void
    {
        $this->page = $page;
    }

    public function get(): ?Page
    {
        return $this->page;
    }
}
