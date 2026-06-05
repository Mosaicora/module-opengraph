<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Applier;

use Magento\Framework\View\Page\Config as PageConfig;
use Mosaicora\OpenGraph\Model\Applier\MetaTagApplier;
use PHPUnit\Framework\TestCase;

class MetaTagApplierTest extends TestCase
{
    public function testAppliesNormalizedTags(): void
    {
        $pageConfig = $this->createMock(PageConfig::class);
        $pageConfig->expects($this->once())
            ->method('setMetadata')
            ->with('og:title', 'Title');

        (new MetaTagApplier($pageConfig))->apply(
            [
            'og:title' => 'Title',
            ]
        );
    }
}
