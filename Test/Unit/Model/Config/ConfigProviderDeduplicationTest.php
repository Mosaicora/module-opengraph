<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderDeduplicationTest extends TestCase
{
    public function testReadsStoreScopedDeduplicationFlag(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(ConfigProvider::XML_PATH_REMOVE_COMPETING_TAGS, ScopeInterface::SCOPE_STORE, 7)
            ->willReturn(true);

        self::assertTrue((new ConfigProvider($scopeConfig))->isRemoveCompetingTagsEnabled(7));
    }

    public function testDeduplicationIsDisabledByDefault(): void
    {
        $config = simplexml_load_file(dirname(__DIR__, 4) . '/etc/config.xml');

        self::assertNotFalse($config);
        self::assertSame(
            '0',
            (string)$config->default->mosaicora_opengraph->general->remove_competing_tags
        );
    }
}
