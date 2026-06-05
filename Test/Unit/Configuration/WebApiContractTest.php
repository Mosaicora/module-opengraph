<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Configuration;

use Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface as OpenGraphMetadataDataInterface;
use Mosaicora\OpenGraph\Api\Data\OpenGraphTagInterface;
use Mosaicora\OpenGraph\Api\OpenGraphMetadataInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class WebApiContractTest extends TestCase
{
    #[DataProvider('webApiInterfaceProvider')]
    public function testEveryWebApiMethodDeclaresReturnType(string $interface): void
    {
        $service = new ReflectionClass($interface);

        foreach ($service->getMethods() as $method) {
            $docComment = (string)$method->getDocComment();

            self::assertStringContainsString(
                '@return ',
                $docComment,
                sprintf('%s must declare its Web API return type.', $method->getName())
            );
            self::assertSame(
                $method->getNumberOfParameters(),
                substr_count($docComment, '@param '),
                sprintf('%s must document every Web API parameter.', $method->getName())
            );
            $this->assertParametersAreDocumentedInOrder($method, $docComment);
        }
    }

    private function assertParametersAreDocumentedInOrder(ReflectionMethod $method, string $docComment): void
    {
        $lastPosition = -1;
        foreach ($method->getParameters() as $parameter) {
            $position = strpos($docComment, '$' . $parameter->getName());

            self::assertNotFalse(
                $position,
                sprintf('%s must document $%s.', $method->getName(), $parameter->getName())
            );
            self::assertGreaterThan(
                $lastPosition,
                $position,
                sprintf('%s parameters must follow the method signature order.', $method->getName())
            );
            $lastPosition = $position;
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function webApiInterfaceProvider(): array
    {
        return [
            'service' => [OpenGraphMetadataInterface::class],
            'metadata DTO' => [OpenGraphMetadataDataInterface::class],
            'tag DTO' => [OpenGraphTagInterface::class],
        ];
    }
}
