<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Resolver;

use Magento\Framework\DataObject;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Resolver\DefaultAttributeMatcher;
use Mosaicora\OpenGraph\Model\Resolver\TextSanitizer;
use Mosaicora\OpenGraph\Model\Resolver\ValueResolver;
use PHPUnit\Framework\TestCase;

class ValueResolverTest extends TestCase
{
    public function testCustomValueWins(): void
    {
        $sanitizer = $this->createStub(TextSanitizer::class);
        $sanitizer->method('clean')->willReturn('Custom title');
        $resolver = new ValueResolver($this->createStub(DefaultAttributeMatcher::class), $sanitizer);
        $entity = new DataObject(
            [
            'og_title_mode' => ConfigProvider::MODE_CUSTOM,
            'og_title_custom' => '<strong>Custom</strong> title',
            'name' => 'Name',
            ]
        );

        self::assertSame(
            'Custom title',
            $resolver->resolveText($entity, 'product', 'title', 'og_title_mode', 'og_title_attribute', 'og_title_custom')
        );
    }

    public function testAttributeValueWinsWhenModeIsAttribute(): void
    {
        $sanitizer = $this->createStub(TextSanitizer::class);
        $sanitizer->method('clean')->willReturn('Product Name');
        $resolver = new ValueResolver($this->createStub(DefaultAttributeMatcher::class), $sanitizer);
        $entity = new DataObject(
            [
            'og_title_mode' => ConfigProvider::MODE_ATTRIBUTE,
            'og_title_attribute' => 'name',
            'name' => 'Product Name',
            'meta_title' => 'Meta Title',
            ]
        );

        self::assertSame(
            'Product Name',
            $resolver->resolveText($entity, 'product', 'title', 'og_title_mode', 'og_title_attribute', 'og_title_custom')
        );
    }

    public function testAutoUsesDefaultMatcher(): void
    {
        $matcher = $this->createMock(DefaultAttributeMatcher::class);
        $matcher->expects($this->once())
            ->method('resolveFirstValue')
            ->with($this->isInstanceOf(DataObject::class), 'product', 'title', null)
            ->willReturn('Matched title');

        $sanitizer = $this->createStub(TextSanitizer::class);
        $sanitizer->method('clean')->willReturn('Matched title');
        $resolver = new ValueResolver($matcher, $sanitizer);

        self::assertSame(
            'Matched title',
            $resolver->resolveText(new DataObject(), 'product', 'title', 'og_title_mode', 'og_title_attribute', 'og_title_custom')
        );
    }
}
