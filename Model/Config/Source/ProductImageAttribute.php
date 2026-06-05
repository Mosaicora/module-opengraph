<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class ProductImageAttribute extends AbstractAttributeSource
{
    public function __construct(
        private readonly CollectionFactory $collectionFactory
    ) {
    }

    protected function getAttributeCollection(): iterable
    {
        return $this->collectionFactory->create()->setOrder('frontend_label', 'ASC');
    }

    protected function isAllowedInput(string $frontendInput): bool
    {
        return $frontendInput === 'media_image';
    }
}
