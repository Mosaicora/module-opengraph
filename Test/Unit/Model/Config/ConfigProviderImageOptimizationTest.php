<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderImageOptimizationTest extends TestCase
{
    public function testImageOptimizationDefaults(): void
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('isSetFlag')->willReturn(true);
        $scopeConfig->method('getValue')->willReturn(null);

        $config = new ConfigProvider($scopeConfig);

        self::assertTrue($config->isImageOptimizationEnabled());
        self::assertSame(1200, $config->getOptimizedImageWidth());
        self::assertSame(630, $config->getOptimizedImageHeight());
        self::assertSame(ConfigProvider::RESIZE_MODE_SCALE, $config->getOptimizedImageResizeMode());
        self::assertSame([255, 255, 255], $config->getOptimizedImageBackgroundColor());
    }

    public function testUnknownResizeModeUsesCurrentDefault(): void
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(
            fn (string $path): ?string => $path === ConfigProvider::XML_PATH_IMAGE_OPTIMIZATION_RESIZE_MODE
                ? 'invalid'
                : null
        );

        $config = new ConfigProvider($scopeConfig);

        self::assertSame(ConfigProvider::RESIZE_MODE_SCALE, $config->getOptimizedImageResizeMode());
    }
}
