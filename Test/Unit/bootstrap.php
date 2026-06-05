<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\TestFramework\Unit\Autoloader\ExtensionAttributesGenerator;
use Magento\Framework\TestFramework\Unit\Autoloader\ExtensionAttributesInterfaceGenerator;
use Magento\Framework\TestFramework\Unit\Autoloader\FactoryGenerator;
use Magento\Framework\TestFramework\Unit\Autoloader\GeneratedClassesAutoloader;

$moduleRoot = dirname(__DIR__, 2);
$magentoRoot = dirname($moduleRoot, 2);
$magentoBootstrap = $magentoRoot . '/dev/tests/unit/framework/bootstrap.php';
$testsTempDir = $moduleRoot . '/.phpunit.cache/tmp';

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', $testsTempDir);
}

$generatedCodeDir = TESTS_TEMP_DIR . '/generated/code';
if (!is_dir($generatedCodeDir) && !mkdir($generatedCodeDir, 0775, true) && !is_dir($generatedCodeDir)) {
    throw new RuntimeException(sprintf('Unable to create PHPUnit generated-code directory "%s".', $generatedCodeDir));
}

if (is_file($magentoBootstrap)) {
    require_once $magentoBootstrap;
    return;
}

require_once $moduleRoot . '/vendor/autoload.php';

$generatorIo = new Io(
    new File(),
    TESTS_TEMP_DIR . '/' . DirectoryList::getDefaultConfig()[DirectoryList::GENERATED_CODE][DirectoryList::PATH]
);
$generators = [
    new ExtensionAttributesGenerator(),
    new ExtensionAttributesInterfaceGenerator(),
    new FactoryGenerator(),
];

$proxyGenerator = Magento\Framework\TestFramework\Unit\Autoloader\ProxyGenerator::class;
if (class_exists($proxyGenerator)) {
    $generators[] = new $proxyGenerator();
}

$generatedCodeAutoloader = new GeneratedClassesAutoloader($generators, $generatorIo);
spl_autoload_register([$generatedCodeAutoloader, 'load']);
