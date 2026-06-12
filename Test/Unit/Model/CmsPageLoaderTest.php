<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Mosaicora\OpenGraph\Model\CmsPageLoader;
use PHPUnit\Framework\TestCase;

class CmsPageLoaderTest extends TestCase
{
    public function testCreatesFreshCollectionForEveryLoad(): void
    {
        $page = $this->createStub(Page::class);
        $page->method('getId')->willReturn(42);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->exactly(4))
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $collection->expects($this->exactly(2))
            ->method('addStoreFilter')
            ->with(3)
            ->willReturnSelf();
        $collection->expects($this->exactly(2))
            ->method('getFirstItem')
            ->willReturn($page);

        $factory = $this->createMock(CollectionFactory::class);
        $factory->expects($this->exactly(2))
            ->method('create')
            ->willReturn($collection);

        $loader = new CmsPageLoader($factory);

        self::assertSame($page, $loader->load('home', 3));
        self::assertSame($page, $loader->load('home', 3));
    }
}
