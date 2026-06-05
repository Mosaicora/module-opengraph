<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Config\Source;

use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Config\Source\ImageMode;
use PHPUnit\Framework\TestCase;

class ImageModeTest extends TestCase
{
    public function testDoesNotExposeAttributeMode(): void
    {
        $options = (new ImageMode())->toOptionArray();
        $values = array_column($options, 'value');
        $labels = array_map(static fn (mixed $label): string => (string)$label, array_column($options, 'label'));

        self::assertSame([ConfigProvider::MODE_AUTO, ConfigProvider::MODE_CUSTOM], $values);
        self::assertNotContains(ConfigProvider::MODE_ATTRIBUTE, $values);
        self::assertSame(['Default', 'Custom Image'], $labels);
    }
}
