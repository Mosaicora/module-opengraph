<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Data;

use Mosaicora\OpenGraph\Model\Data\OpenGraphTag;
use Mosaicora\OpenGraph\Model\Resolver\TextSanitizer;
use PHPUnit\Framework\TestCase;

class OpenGraphTagTest extends TestCase
{
    public function testContentIsSanitizedWhenStored(): void
    {
        $sanitizer = $this->createMock(TextSanitizer::class);
        $sanitizer->expects($this->once())
            ->method('clean')
            ->with('<strong>Hello</strong> &amp;   welcome')
            ->willReturn('Hello & welcome');

        $tag = new OpenGraphTag($sanitizer);
        $tag->setName('og:description')->setContent('<strong>Hello</strong> &amp;   welcome');

        self::assertSame('Hello & welcome', $tag->getContent());
    }
}
