<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Cache;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class OpenGraphImages
{
    public const CACHE_PATH = 'mosaicora/opengraph/cache';

    public function __construct(
        private readonly Filesystem $filesystem
    ) {
    }

    public function clean(): void
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($mediaDirectory->isExist(self::CACHE_PATH)) {
            $mediaDirectory->delete(self::CACHE_PATH);
        }
    }
}
