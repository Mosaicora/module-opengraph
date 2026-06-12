<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Configuration;

use PHPUnit\Framework\TestCase;

class FrontendDiTest extends TestCase
{
    public function testRendererPluginIsRegisteredOnlyInFrontendArea(): void
    {
        $moduleRoot = dirname(__DIR__, 3);
        $globalDi = (string)file_get_contents($moduleRoot . '/etc/di.xml');
        $frontendDi = (string)file_get_contents($moduleRoot . '/etc/frontend/di.xml');

        self::assertStringNotContainsString('mosaicora_opengraph_render_metadata', $globalDi);
        self::assertStringContainsString('mosaicora_opengraph_render_metadata', $frontendDi);
        self::assertStringNotContainsString('mosaicora_opengraph_deduplicate_metadata', $globalDi);
        self::assertStringContainsString('mosaicora_opengraph_deduplicate_metadata', $frontendDi);
    }
}
