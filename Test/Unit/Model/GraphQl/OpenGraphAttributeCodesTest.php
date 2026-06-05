<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\GraphQl;

use Magento\Framework\DataObject;
use Mosaicora\OpenGraph\Model\GraphQl\OpenGraphAttributeCodes;
use Mosaicora\OpenGraph\Model\Resolver\DefaultAttributeMatcher;
use PHPUnit\Framework\TestCase;

class OpenGraphAttributeCodesTest extends TestCase
{
    public function testIncludesConfiguredDefaultsAndPerEntitySources(): void
    {
        $product = new DataObject();
        $product->setData('og_title_attribute', 'custom_heading');
        $product->setData('og_description_attribute', 'marketing_copy');
        $matcher = $this->createStub(DefaultAttributeMatcher::class);
        $matcher->method('getCandidates')->willReturnMap(
            [
                ['product', 'title', 7, ['meta_title', 'name']],
                ['product', 'description', 7, ['meta_description', 'description']],
                ['product', 'image', 7, ['open_graph_image', 'image']],
            ]
        );

        $codes = (new OpenGraphAttributeCodes($matcher))->getProductCodes([$product], 7);

        self::assertContains('custom_heading', $codes);
        self::assertContains('marketing_copy', $codes);
        self::assertContains('open_graph_image', $codes);
        self::assertSame($codes, array_values(array_unique($codes)));
    }
}
