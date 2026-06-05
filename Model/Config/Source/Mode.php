<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\OptionSourceInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;

class Mode extends AbstractSource implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => ConfigProvider::MODE_AUTO, 'label' => __('Auto')],
            ['value' => ConfigProvider::MODE_ATTRIBUTE, 'label' => __('Attribute')],
            ['value' => ConfigProvider::MODE_CUSTOM, 'label' => __('Custom')],
        ];
    }

    public function getAllOptions(): array
    {
        return $this->toOptionArray();
    }
}
