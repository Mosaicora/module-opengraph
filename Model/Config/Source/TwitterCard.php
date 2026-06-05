<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TwitterCard implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'summary_large_image', 'label' => __('Summary with Large Image')],
            ['value' => 'summary', 'label' => __('Summary')],
        ];
    }
}
