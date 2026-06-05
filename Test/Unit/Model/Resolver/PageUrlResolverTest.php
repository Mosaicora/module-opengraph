<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Resolver;

use Magento\Cms\Helper\Page as PageHelper;
use Magento\Cms\Model\Page;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Model\Context\PageContext;
use Mosaicora\OpenGraph\Model\Resolver\PageUrlResolver;
use PHPUnit\Framework\TestCase;

class PageUrlResolverTest extends TestCase
{
    public function testCmsPageUsesStorefrontCmsUrl(): void
    {
        $page = $this->createStub(Page::class);
        $page->method('getId')->willReturn(12);

        $pageHelper = $this->createMock(PageHelper::class);
        $pageHelper->expects($this->once())
            ->method('getPageUrl')
            ->with(12)
            ->willReturn('https://example.test/about-us');

        $resolver = new PageUrlResolver(
            $this->createStub(StoreManagerInterface::class),
            $pageHelper
        );

        self::assertSame(
            'https://example.test/about-us',
            $resolver->resolve(new PageContext(PageContext::TYPE_CMS, $page))
        );
    }

    public function testHomeUsesCurrentStoreBaseUrl(): void
    {
        $store = $this->createStub(Store::class);
        $store->method('getBaseUrl')->willReturn('https://example.test/');
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $resolver = new PageUrlResolver($storeManager, $this->createStub(PageHelper::class));

        self::assertSame(
            'https://example.test/',
            $resolver->resolve(new PageContext(PageContext::TYPE_HOME))
        );
    }
}
