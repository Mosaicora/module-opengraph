<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Resolver;

use Magento\Framework\Filter\FilterManager;
use Mosaicora\OpenGraph\Model\Resolver\TextSanitizer;
use PHPUnit\Framework\TestCase;

class TextSanitizerTest extends TestCase
{
    public function testArrayReturnsEmptyStringWithoutFiltering(): void
    {
        $filterManager = new RecordingFilterManager('unused');

        self::assertSame('', (new TextSanitizer($filterManager))->clean(['content']));
        self::assertSame([], $filterManager->getCalls());
    }

    public function testDelegatesTagRemovalAndNormalizesWhitespace(): void
    {
        $filterManager = new RecordingFilterManager("  Example &\n\t title  ");
        $sanitizer = new TextSanitizer($filterManager);

        self::assertSame(
            'Example & title',
            $sanitizer->clean('<strong>Example</strong> &amp; title')
        );
        self::assertSame(
            [['removeTags', '<strong>Example</strong> &amp; title']],
            $filterManager->getCalls()
        );
    }

    public function testValueWithinLimitRemainsUnchanged(): void
    {
        $sanitizer = new TextSanitizer(new RecordingFilterManager('Short title'));

        self::assertSame('Short title', $sanitizer->clean('Short title', 20));
    }

    public function testLongMultibyteValueIsTruncatedSafely(): void
    {
        $sanitizer = new TextSanitizer(new RecordingFilterManager('éééééé'));

        self::assertSame('éé...', $sanitizer->clean('éééééé', 5));
    }

    public function testNonPositiveLimitPreservesNormalizedValue(): void
    {
        $sanitizer = new TextSanitizer(new RecordingFilterManager('Complete value'));

        self::assertSame('Complete value', $sanitizer->clean('Complete value', 0));
        self::assertSame('Complete value', $sanitizer->clean('Complete value', -1));
    }
}

class RecordingFilterManager extends FilterManager
{
    /**
     * @var array<int, array{string, string}>
     */
    private array $calls = [];

    public function __construct(
        private readonly string $result
    ) {
    }

    public function __call($filterAlias, array $arguments = []): string
    {
        $this->calls[] = [(string)$filterAlias, (string)($arguments[0] ?? '')];

        return $this->result;
    }

    /**
     * @return array<int, array{string, string}>
     */
    public function getCalls(): array
    {
        return $this->calls;
    }
}
