<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Configuration;

use PHPUnit\Framework\TestCase;

class GraphQlSchemaTest extends TestCase
{
    public function testDeclaresNativeEntityAndHomeFields(): void
    {
        $schema = (string)file_get_contents(dirname(__DIR__, 3) . '/etc/schema.graphqls');

        self::assertStringContainsString('interface ProductInterface', $schema);
        self::assertStringContainsString('interface CategoryInterface', $schema);
        self::assertStringContainsString('type CmsPage', $schema);
        self::assertStringContainsString('open_graph: MosaicoraOpenGraphMetadata!', $schema);
        self::assertStringContainsString('home_open_graph: MosaicoraOpenGraphMetadata!', $schema);
        self::assertStringContainsString('tags: [MosaicoraOpenGraphTag!]!', $schema);
    }
}
