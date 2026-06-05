<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\OptionSourceInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;

class ImageMode extends AbstractSource implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => ConfigProvider::MODE_AUTO, 'label' => __('Default')],
            ['value' => ConfigProvider::MODE_CUSTOM, 'label' => __('Custom Image')],
        ];
    }

    public function getAllOptions(): array
    {
        return $this->toOptionArray();
    }
}
