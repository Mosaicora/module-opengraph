<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Resolver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Resolver\DefaultAttributeMatcher;
use PHPUnit\Framework\TestCase;

class DefaultAttributeMatcherTest extends TestCase
{
    public function testUsesConfiguredAttributeBeforeBuiltInFallbacks(): void
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnMap(
            [
            ['mosaicora_opengraph/product/title_attribute', 'store', null, 'custom_title'],
            ]
        );

        $matcher = new DefaultAttributeMatcher(new ConfigProvider($scopeConfig));
        $product = new DataObject(
            [
            'custom_title' => 'Custom title',
            'meta_title' => 'Meta title',
            'name' => 'Name',
            ]
        );

        self::assertSame('Custom title', $matcher->resolveFirstValue($product, 'product', 'title'));
    }

    public function testFallsBackToMatchingAttributesWhenConfigIsUnset(): void
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn(null);

        $matcher = new DefaultAttributeMatcher(new ConfigProvider($scopeConfig));
        $product = new DataObject(
            [
            'meta_description' => '',
            'short_description' => 'Short description',
            'description' => 'Long description',
            ]
        );

        self::assertSame('Short description', $matcher->resolveFirstValue($product, 'product', 'description'));
    }
}
