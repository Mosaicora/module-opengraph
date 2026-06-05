<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Config\Source;

use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Config\Source\ResizeMode;
use PHPUnit\Framework\TestCase;

class ResizeModeTest extends TestCase
{
    public function testContainsSupportedResizeModes(): void
    {
        self::assertSame(
            [
                ConfigProvider::RESIZE_MODE_COVER,
                ConfigProvider::RESIZE_MODE_SCALE,
            ],
            array_column((new ResizeMode())->toOptionArray(), 'value')
        );
    }
}
