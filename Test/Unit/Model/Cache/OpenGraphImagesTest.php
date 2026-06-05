<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Cache;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Mosaicora\OpenGraph\Model\Cache\OpenGraphImages;
use PHPUnit\Framework\TestCase;

class OpenGraphImagesTest extends TestCase
{
    public function testCleanDeletesOnlyOpenGraphImagesCacheDirectory(): void
    {
        $mediaDirectory = $this->createMock(WriteInterface::class);
        $mediaDirectory->expects($this->once())
            ->method('isExist')
            ->with(OpenGraphImages::CACHE_PATH)
            ->willReturn(true);
        $mediaDirectory->expects($this->once())
            ->method('delete')
            ->with(OpenGraphImages::CACHE_PATH);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectory);

        (new OpenGraphImages($filesystem))->clean();
    }

    public function testCleanNoOpsWhenCacheDirectoryDoesNotExist(): void
    {
        $mediaDirectory = $this->createMock(WriteInterface::class);
        $mediaDirectory->expects($this->once())
            ->method('isExist')
            ->with(OpenGraphImages::CACHE_PATH)
            ->willReturn(false);
        $mediaDirectory->expects($this->never())->method('delete');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectory);

        (new OpenGraphImages($filesystem))->clean();
    }
}
