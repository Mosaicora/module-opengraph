<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mosaicora\OpenGraph\Model\Category\Attribute\Backend\OpenGraphImage;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Config\Source\CategoryTextAttribute;
use Mosaicora\OpenGraph\Model\Config\Source\ImageMode;
use Mosaicora\OpenGraph\Model\Config\Source\Mode;

class AddCategoryOpenGraphAttributes implements DataPatchInterface
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

        foreach ($this->getAttributes() as $code => $config) {
            $eavSetup->addAttribute(Category::ENTITY, $code, $config);
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

    private function getAttributes(): array
    {
        $base = [
            'required' => false,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'group' => 'Open Graph',
        ];

        return [
            'og_title_mode' => $base + [
                'type' => 'varchar',
                'label' => 'Title Source',
                'input' => 'select',
                'source' => Mode::class,
                'default' => ConfigProvider::MODE_AUTO,
                'sort_order' => 10,
            ],
            'og_title_attribute' => $base + [
                'type' => 'varchar',
                'label' => 'Title Attribute',
                'input' => 'select',
                'source' => CategoryTextAttribute::class,
                'sort_order' => 20,
            ],
            'og_title_custom' => $base + [
                'type' => 'varchar',
                'label' => 'Custom Title',
                'input' => 'text',
                'sort_order' => 30,
            ],
            'og_description_mode' => $base + [
                'type' => 'varchar',
                'label' => 'Description Source',
                'input' => 'select',
                'source' => Mode::class,
                'default' => ConfigProvider::MODE_AUTO,
                'sort_order' => 40,
            ],
            'og_description_attribute' => $base + [
                'type' => 'varchar',
                'label' => 'Description Attribute',
                'input' => 'select',
                'source' => CategoryTextAttribute::class,
                'sort_order' => 50,
            ],
            'og_description_custom' => $base + [
                'type' => 'text',
                'label' => 'Custom Description',
                'input' => 'textarea',
                'sort_order' => 60,
            ],
            'og_image_mode' => $base + [
                'type' => 'varchar',
                'label' => 'Image Source',
                'input' => 'select',
                'source' => ImageMode::class,
                'default' => ConfigProvider::MODE_AUTO,
                'sort_order' => 70,
            ],
            'og_image_custom' => $base + [
                'type' => 'varchar',
                'label' => 'Open Graph Image',
                'input' => 'image',
                'backend' => OpenGraphImage::class,
                'sort_order' => 90,
            ],
        ];
    }
}
