<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\GraphQl\Resolver;

use Mosaicora\OpenGraph\Model\GraphQl\Resolver\HomeIdentity;
use PHPUnit\Framework\TestCase;

class HomeIdentityTest extends TestCase
{
    public function testReturnsUniqueInternalCacheIdentities(): void
    {
        self::assertSame(
            ['CONFIG', 'cms_p_2'],
            (new HomeIdentity())->getIdentities(
                ['_cache_identities' => ['CONFIG', 'cms_p_2', 'CONFIG']]
            )
        );
    }
}
