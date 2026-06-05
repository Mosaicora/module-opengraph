<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Builder;

use Mosaicora\OpenGraph\Api\TagBuilderInterface;
use Mosaicora\OpenGraph\Model\Builder\CompositeTagBuilder;
use Mosaicora\OpenGraph\Model\Context\PageContext;
use PHPUnit\Framework\TestCase;

class CompositeTagBuilderTest extends TestCase
{
    public function testNormalizesTagsForFrontendAndApiConsumers(): void
    {
        $context = new PageContext(PageContext::TYPE_HOME);
        $builder = $this->createMock(TagBuilderInterface::class);
        $builder->method('supports')->with($context)->willReturn(true);
        $builder->method('build')->with($context)->willReturn(
            [
                ' og:title ' => ' Example title ',
                'og:description' => ' ',
                '' => 'Ignored',
            ]
        );

        self::assertSame(
            ['og:title' => 'Example title'],
            (new CompositeTagBuilder([$builder]))->build($context)
        );
    }
}
