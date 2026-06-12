<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Plugin;

use Laminas\Http\Header\ContentType;
use Magento\Framework\App\Response\Http;
use Mosaicora\OpenGraph\Model\Applier\AppliedTagRegistry;
use Mosaicora\OpenGraph\Model\Applier\HeadMetadataDeduplicator;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Plugin\DeduplicateMetadataPlugin;
use PHPUnit\Framework\TestCase;

class DeduplicateMetadataPluginTest extends TestCase
{
    public function testLeavesResponseUnchangedWhenSwitchIsDisabled(): void
    {
        $config = $this->createStub(ConfigProvider::class);
        $config->method('isEnabled')->willReturn(true);
        $config->method('isRemoveCompetingTagsEnabled')->willReturn(false);

        $deduplicator = $this->createMock(HeadMetadataDeduplicator::class);
        $deduplicator->expects($this->never())->method('process');

        $value = '<html><head></head></html>';
        $response = $this->createStub(Http::class);
        $appended = null;

        self::assertSame(
            $response,
            (new DeduplicateMetadataPlugin(
                $config,
                $this->createStub(AppliedTagRegistry::class),
                $deduplicator
            ))->aroundAppendBody(
                $response,
                static function (string $body) use ($response, &$appended): Http {
                    $appended = $body;
                    return $response;
                },
                $value
            )
        );
        self::assertSame($value, $appended);
    }

    public function testProcessesHtmlWithAppliedTags(): void
    {
        $config = $this->enabledConfig();
        $tags = ['og:title' => 'Mosaicora'];

        $registry = $this->createStub(AppliedTagRegistry::class);
        $registry->method('get')->willReturn($tags);

        $deduplicator = $this->createMock(HeadMetadataDeduplicator::class);
        $deduplicator->expects($this->once())
            ->method('process')
            ->with('<html><head></head></html>', $tags)
            ->willReturn('<html><head>deduplicated</head></html>');

        $response = $this->createStub(Http::class);
        $appended = null;

        self::assertSame(
            $response,
            (new DeduplicateMetadataPlugin($config, $registry, $deduplicator))->aroundAppendBody(
                $response,
                static function (string $body) use ($response, &$appended): Http {
                    $appended = $body;
                    return $response;
                },
                '<html><head></head></html>'
            )
        );
        self::assertSame('<html><head>deduplicated</head></html>', $appended);
    }

    public function testLeavesNonHtmlResponseUnchanged(): void
    {
        $deduplicator = $this->createMock(HeadMetadataDeduplicator::class);
        $deduplicator->expects($this->never())->method('process');

        $response = $this->createMock(Http::class);
        $response->method('getHeader')->with('Content-Type')->willReturn(new ContentType('application/json'));

        $value = '{"ok":true}';
        $appended = null;

        self::assertSame(
            $response,
            (new DeduplicateMetadataPlugin(
                $this->enabledConfig(),
                $this->createStub(AppliedTagRegistry::class),
                $deduplicator
            ))->aroundAppendBody(
                $response,
                static function (string $body) use ($response, &$appended): Http {
                    $appended = $body;
                    return $response;
                },
                $value
            )
        );
        self::assertSame($value, $appended);
    }

    private function enabledConfig(): ConfigProvider
    {
        $config = $this->createStub(ConfigProvider::class);
        $config->method('isEnabled')->willReturn(true);
        $config->method('isRemoveCompetingTagsEnabled')->willReturn(true);

        return $config;
    }
}
