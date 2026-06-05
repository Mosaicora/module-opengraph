<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\OptionSourceInterface;

class CmsField extends AbstractSource implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('-- Auto --')],
            ['value' => 'meta_title', 'label' => __('Meta Title (meta_title)')],
            ['value' => 'title', 'label' => __('Page Title (title)')],
            ['value' => 'content_heading', 'label' => __('Content Heading (content_heading)')],
            ['value' => 'meta_description', 'label' => __('Meta Description (meta_description)')],
            ['value' => 'content', 'label' => __('Content (content)')],
        ];
    }

    public function getAllOptions(): array
    {
        return $this->toOptionArray();
    }
}
