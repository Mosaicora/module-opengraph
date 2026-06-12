<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;

class CmsPageLoader
{
    public function __construct(
        private readonly PageCollectionFactory $pageCollectionFactory
    ) {
    }

    public function load(string $identifier, int $storeId): ?Page
    {
        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToFilter('identifier', $identifier);
        $collection->addFieldToFilter('is_active', 1);
        $collection->addStoreFilter($storeId);
        $page = $collection->getFirstItem();

        return $page instanceof Page && $page->getId() ? $page : null;
    }
}
