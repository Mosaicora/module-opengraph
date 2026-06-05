<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Observer;

use Magento\Cms\Model\Page;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mosaicora\OpenGraph\Model\Context\CmsPageContextRegistry;

class CaptureCmsPageObserver implements ObserverInterface
{
    public function __construct(
        private readonly CmsPageContextRegistry $cmsPageRegistry
    ) {
    }

    public function execute(Observer $observer): void
    {
        $page = $observer->getEvent()->getData('page');
        if ($page instanceof Page) {
            $this->cmsPageRegistry->set($page);
        }
    }
}
