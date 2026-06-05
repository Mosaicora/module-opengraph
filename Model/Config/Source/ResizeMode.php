<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;

class ResizeMode implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => ConfigProvider::RESIZE_MODE_COVER, 'label' => __('Cover (crop to fit)')],
            ['value' => ConfigProvider::RESIZE_MODE_SCALE, 'label' => __('Scale (fit and pad)')],
        ];
    }
}
