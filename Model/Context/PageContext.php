<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Context;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Cms\Model\Page;

class PageContext
{
    public const TYPE_HOME = 'home';
    public const TYPE_CMS = 'cms';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_PRODUCT = 'product';

    public function __construct(
        private readonly string $type,
        private readonly Product|Category|Page|null $entity = null
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEntity(): Product|Category|Page|null
    {
        return $this->entity;
    }
}
