<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Applier;

use Mosaicora\OpenGraph\Model\Applier\AppliedTagRegistry;
use PHPUnit\Framework\TestCase;

class AppliedTagRegistryTest extends TestCase
{
    public function testStoresAppliedTags(): void
    {
        $registry = new AppliedTagRegistry();
        $tags = ['og:title' => 'Mosaicora title'];

        $registry->set($tags);

        self::assertSame($tags, $registry->get());
    }

    public function testClearsTagsAfterRequest(): void
    {
        $registry = new AppliedTagRegistry();
        $registry->set(['og:title' => 'Mosaicora title']);

        $registry->_resetState();

        self::assertSame([], $registry->get());
    }
}
