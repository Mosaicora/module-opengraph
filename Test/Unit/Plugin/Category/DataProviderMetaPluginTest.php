<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Plugin\Category;

use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Model\Category\DataProvider;
use Mosaicora\OpenGraph\Plugin\Category\DataProviderMetaPlugin;
use PHPUnit\Framework\TestCase;

class DataProviderMetaPluginTest extends TestCase
{
    public function testAfterPrepareMetaRestoresCategoryOpenGraphUiConfig(): void
    {
        $urlBuilder = $this->createMock(UrlInterface::class);
        $urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('mosaicora_opengraph/category_image/upload')
            ->willReturn('https://example.test/admin/mosaicora_opengraph/category_image/upload/');

        $dataProvider = $this->createStub(DataProvider::class);

        $result = (new DataProviderMetaPlugin($urlBuilder))->afterPrepareMeta(
            $dataProvider,
            [
                'open_graph' => [
                    'children' => [
                        'og_image_mode' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => 'select',
                                    ],
                                ],
                            ],
                        ],
                        'og_image_custom' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => 'image',
                                    ],
                                ],
                            ],
                        ],
                        'og_title_mode' => [],
                    ],
                ],
            ]
        );

        $imageModeConfig = $result['open_graph']['children']['og_image_mode']['arguments']['data']['config'];
        $imageConfig = $result['open_graph']['children']['og_image_custom']['arguments']['data']['config'];

        self::assertSame('og_image_mode', $imageModeConfig['dataScope']);
        self::assertSame('category', $imageModeConfig['source']);
        self::assertTrue($imageModeConfig['switcherConfig']['enabled']);
        self::assertSame('custom', $imageModeConfig['switcherConfig']['rules'][1]['value']);

        self::assertSame('imageUploader', $imageConfig['formElement']);
        self::assertSame('string', $imageConfig['dataType']);
        self::assertSame('og_image_custom', $imageConfig['dataScope']);
        self::assertSame('category', $imageConfig['source']);
        self::assertSame(
            'https://example.test/admin/mosaicora_opengraph/category_image/upload/',
            $imageConfig['uploaderConfig']['url']
        );
        self::assertSame(
            'og_title_mode',
            $result['open_graph']['children']['og_title_mode']['arguments']['data']['config']['dataScope']
        );
    }
}
