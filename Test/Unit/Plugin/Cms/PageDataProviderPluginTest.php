<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Plugin\Cms;

use Mosaicora\OpenGraph\Model\Resolver\MediaPathNormalizer;
use Mosaicora\OpenGraph\Plugin\Cms\PageDataProviderPlugin;
use PHPUnit\Framework\TestCase;

class PageDataProviderPluginTest extends TestCase
{
    public function testAfterGetDataFormatsStoredImageForUploader(): void
    {
        $mediaPathNormalizer = $this->createMock(MediaPathNormalizer::class);
        $mediaPathNormalizer->expects($this->once())
            ->method('normalizeImageUrl')
            ->with('mosaicora/opengraph/example.jpg')
            ->willReturn('https://example.test/media/mosaicora/opengraph/example.jpg');

        $plugin = new PageDataProviderPlugin($mediaPathNormalizer);

        $result = $plugin->afterGetData(new \stdClass(), [
            7 => [
                'title' => 'About us',
                'og_image_custom' => 'mosaicora/opengraph/example.jpg',
            ],
        ]);

        self::assertSame(
            [[
                'name' => 'example.jpg',
                'url' => 'https://example.test/media/mosaicora/opengraph/example.jpg',
            ]],
            $result[7]['og_image_custom']
        );
    }

    public function testAfterGetDataLeavesEmptyOrUnsupportedValuesUntouched(): void
    {
        $mediaPathNormalizer = $this->createMock(MediaPathNormalizer::class);
        $mediaPathNormalizer->expects($this->never())->method('normalizeImageUrl');

        $plugin = new PageDataProviderPlugin($mediaPathNormalizer);

        $input = [
            1 => ['og_image_custom' => ''],
            2 => ['og_image_custom' => null],
            3 => ['og_image_custom' => [['name' => 'existing.jpg']]],
        ];

        self::assertSame($input, $plugin->afterGetData(new \stdClass(), $input));
    }
}
