<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

class CmsPageLoader implements ResetAfterRequestInterface
{
    /**
     * @var array<string, Page|null>
     */
    private array $pages = [];

    public function __construct(
        private readonly PageCollectionFactory $pageCollectionFactory
    ) {
    }

    public function load(string $identifier, int $storeId): ?Page
    {
        $cacheKey = $storeId . ':' . $identifier;
        if (array_key_exists($cacheKey, $this->pages)) {
            return $this->pages[$cacheKey];
        }

        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToFilter('identifier', $identifier);
        $collection->addFieldToFilter('is_active', 1);
        $collection->addStoreFilter($storeId);
        $page = $collection->getFirstItem();

        $this->pages[$cacheKey] = $page instanceof Page && $page->getId() ? $page : null;

        return $this->pages[$cacheKey];
    }

    public function _resetState(): void
    {
        $this->pages = [];
    }
}
