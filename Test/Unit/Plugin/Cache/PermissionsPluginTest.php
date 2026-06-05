<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Plugin\Cache;

use Magento\Backend\Block\Cache\Permissions;
use Magento\Framework\AuthorizationInterface;
use Mosaicora\OpenGraph\Plugin\Cache\PermissionsPlugin;
use PHPUnit\Framework\TestCase;

class PermissionsPluginTest extends TestCase
{
    public function testKeepsExistingAdditionalActionsAccess(): void
    {
        $authorization = $this->createMock(AuthorizationInterface::class);
        $authorization->expects($this->never())->method('isAllowed');

        self::assertTrue(
            (new PermissionsPlugin($authorization))->afterHasAccessToAdditionalActions(
                $this->createStub(Permissions::class),
                true
            )
        );
    }

    public function testAllowsAdditionalActionsForOpenGraphImagesPermission(): void
    {
        $authorization = $this->createMock(AuthorizationInterface::class);
        $authorization->expects($this->once())
            ->method('isAllowed')
            ->with('Mosaicora_OpenGraph::flush_open_graph_images')
            ->willReturn(true);

        self::assertTrue(
            (new PermissionsPlugin($authorization))->afterHasAccessToAdditionalActions(
                $this->createStub(Permissions::class),
                false
            )
        );
    }
}
