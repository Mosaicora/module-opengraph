<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Image;

class DefaultImage extends Image
{
    protected function _getAllowedExtensions(): array
    {
        return ['jpg', 'jpeg', 'gif', 'png', 'webp'];
    }
}
