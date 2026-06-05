<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Plugin\Category;

use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Model\Category\DataProvider;

class DataProviderMetaPlugin
{
    public function __construct(
        private readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * Restore Open Graph category UI metadata after EAV metadata is merged.
     *
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    public function afterPrepareMeta(DataProvider $subject, array $result): array
    {
        if (!isset($result['open_graph']['children']) || !is_array($result['open_graph']['children'])) {
            return $result;
        }

        foreach ($result['open_graph']['children'] as $field => &$metadata) {
            if (!is_array($metadata)) {
                continue;
            }

            $metadata['arguments']['data']['config']['dataScope'] = $field;
            $metadata['arguments']['data']['config']['source'] = 'category';
        }
        unset($metadata);

        $result['open_graph']['children'] = array_replace_recursive(
            $result['open_graph']['children'],
            $this->getOpenGraphMetaOverrides()
        );

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function getOpenGraphMetaOverrides(): array
    {
        $overrides['og_image_mode']['arguments']['data']['config'] = [
            'switcherConfig' => [
                'enabled' => true,
                'rules' => [
                    [
                        'value' => 'auto',
                        'actions' => [
                            [
                                'target' => 'category_form.category_form.open_graph.og_image_custom',
                                'callback' => 'hide',
                            ],
                        ],
                    ],
                    [
                        'value' => 'custom',
                        'actions' => [
                            [
                                'target' => 'category_form.category_form.open_graph.og_image_custom',
                                'callback' => 'show',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $overrides['og_image_custom']['arguments']['data']['config'] = [
            'formElement' => 'imageUploader',
            'elementTmpl' => 'ui/form/element/uploader/image',
            'previewTmpl' => 'Magento_Catalog/image-preview',
            'uploaderConfig' => [
                'url' => $this->urlBuilder->getUrl('mosaicora_opengraph/category_image/upload'),
            ],
            'openDialogTitle' => 'Media Gallery',
            'initialMediaGalleryOpenSubpath' => 'mosaicora/opengraph',
            'allowedExtensions' => 'jpg jpeg gif png webp',
            'maxFileSize' => 4194304,
            'visible' => true,
            'required' => false,
        ];

        return $overrides;
    }
}
