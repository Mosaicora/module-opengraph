<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Frontend\Image as ImageFrontend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Config\Source\Mode;
use Mosaicora\OpenGraph\Model\Config\Source\ProductTextAttribute;

class AddProductOpenGraphAttributes implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            'open_graph_image',
            [
            'type' => 'varchar',
            'label' => 'Open Graph Image',
            'input' => 'media_image',
            'frontend' => ImageFrontend::class,
            'required' => false,
            'sort_order' => 35,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'used_in_product_listing' => false,
            'visible' => true,
            ]
        );

        foreach ($this->getTextOverrideAttributes() as $code => $config) {
            $eavSetup->addAttribute(Product::ENTITY, $code, $config);
        }

        $attributeSetId = (int)$eavSetup->getDefaultAttributeSetId(Product::ENTITY);
        $imageGroupId = (int)$eavSetup->getAttributeGroupByCode(
            Product::ENTITY,
            $attributeSetId,
            'image-management',
            'attribute_group_id'
        );

        if ($imageGroupId > 0) {
            $eavSetup->addAttributeToGroup(Product::ENTITY, $attributeSetId, $imageGroupId, 'open_graph_image');
        }

        $this->moduleDataSetup->getConnection()->endSetup();
        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    private function getTextOverrideAttributes(): array
    {
        $base = [
            'required' => false,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'user_defined' => false,
            'group' => 'Search Engine Optimization',
        ];

        return [
            'og_title_mode' => $base + [
                'type' => 'varchar',
                'label' => 'Open Graph Title Source',
                'input' => 'select',
                'source' => Mode::class,
                'default' => ConfigProvider::MODE_AUTO,
                'sort_order' => 170,
            ],
            'og_title_attribute' => $base + [
                'type' => 'varchar',
                'label' => 'Open Graph Title Attribute',
                'input' => 'select',
                'source' => ProductTextAttribute::class,
                'sort_order' => 171,
            ],
            'og_title_custom' => $base + [
                'type' => 'varchar',
                'label' => 'Open Graph Custom Title',
                'input' => 'text',
                'sort_order' => 172,
            ],
            'og_description_mode' => $base + [
                'type' => 'varchar',
                'label' => 'Open Graph Description Source',
                'input' => 'select',
                'source' => Mode::class,
                'default' => ConfigProvider::MODE_AUTO,
                'sort_order' => 173,
            ],
            'og_description_attribute' => $base + [
                'type' => 'varchar',
                'label' => 'Open Graph Description Attribute',
                'input' => 'select',
                'source' => ProductTextAttribute::class,
                'sort_order' => 174,
            ],
            'og_description_custom' => $base + [
                'type' => 'text',
                'label' => 'Open Graph Custom Description',
                'input' => 'textarea',
                'sort_order' => 175,
            ],
        ];
    }
}
