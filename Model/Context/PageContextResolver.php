<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Context;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;

class PageContextResolver
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly Registry $registry,
        private readonly CmsPageContextRegistry $cmsPageRegistry
    ) {
    }

    public function resolve(): ?PageContext
    {
        $product = $this->registry->registry('current_product');
        if ($product instanceof Product && $this->request->getFullActionName() === 'catalog_product_view') {
            return new PageContext(PageContext::TYPE_PRODUCT, $product);
        }

        $category = $this->registry->registry('current_category');
        if ($category instanceof Category && $this->request->getFullActionName() === 'catalog_category_view') {
            return new PageContext(PageContext::TYPE_CATEGORY, $category);
        }

        $cmsPage = $this->cmsPageRegistry->get();
        if ($cmsPage !== null) {
            $type = $this->request->getFullActionName() === 'cms_index_index'
                ? PageContext::TYPE_HOME
                : PageContext::TYPE_CMS;
            return new PageContext($type, $cmsPage);
        }

        if ($this->request->getFullActionName() === 'cms_index_index') {
            return new PageContext(PageContext::TYPE_HOME);
        }

        return null;
    }
}
