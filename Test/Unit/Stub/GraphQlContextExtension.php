<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Stub;

use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Store\Api\Data\StoreInterface;

class GraphQlContextExtension implements ContextExtensionInterface
{
    public function __construct(
        private StoreInterface $store
    ) {
    }

    public function getStore()
    {
        return $this->store;
    }

    public function setStore($store)
    {
        $this->store = $store;
        return $this;
    }

    public function getIsCustomer()
    {
        return false;
    }

    public function setIsCustomer($isCustomer)
    {
        return $this;
    }

    public function getCustomerGroupId()
    {
        return 0;
    }

    public function setCustomerGroupId($customerGroupId)
    {
        return $this;
    }
}
