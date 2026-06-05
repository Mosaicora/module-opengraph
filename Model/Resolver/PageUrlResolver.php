<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Resolver;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Cms\Model\Page;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Model\Context\PageContext;

class PageUrlResolver
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly PageHelper $pageHelper
    ) {
    }

    public function resolve(PageContext $context): string
    {
        $entity = $context->getEntity();

        if ($context->getType() === PageContext::TYPE_PRODUCT && $entity instanceof Product) {
            return (string)$entity->getProductUrl();
        }

        if ($context->getType() === PageContext::TYPE_CATEGORY && $entity instanceof Category) {
            return (string)$entity->getUrl();
        }

        if ($context->getType() === PageContext::TYPE_CMS && $entity instanceof Page) {
            return (string)$this->pageHelper->getPageUrl($entity->getId());
        }

        if ($context->getType() === PageContext::TYPE_HOME) {
            return (string)$this->storeManager->getStore()->getBaseUrl();
        }

        return '';
    }
}
