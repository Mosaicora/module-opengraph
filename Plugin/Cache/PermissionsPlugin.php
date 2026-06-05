<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Plugin\Cache;

use Magento\Backend\Block\Cache\Permissions;
use Magento\Framework\AuthorizationInterface;

class PermissionsPlugin
{
    public function __construct(
        private readonly AuthorizationInterface $authorization
    ) {
    }

    public function afterHasAccessToAdditionalActions(Permissions $subject, bool $result): bool
    {
        return $result || $this->authorization->isAllowed('Mosaicora_OpenGraph::flush_open_graph_images');
    }
}
