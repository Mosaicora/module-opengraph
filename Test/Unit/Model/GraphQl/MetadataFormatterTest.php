<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\GraphQl;

use Mosaicora\OpenGraph\Model\Data\OpenGraphMetadata;
use Mosaicora\OpenGraph\Model\Data\OpenGraphTag;
use Mosaicora\OpenGraph\Model\GraphQl\MetadataFormatter;
use PHPUnit\Framework\TestCase;

class MetadataFormatterTest extends TestCase
{
    public function testFormatsServiceContractAsGraphQlData(): void
    {
        $tag = new OpenGraphTag();
        $tag->setName('og:title')->setContent('Example');
        $metadata = new OpenGraphMetadata();
        $metadata->setPageType('product')
            ->setIdentifier('shirt-blue')
            ->setStoreId(7)
            ->setEnabled(true)
            ->setTags([$tag]);

        self::assertSame(
            [
                'page_type' => 'product',
                'identifier' => 'shirt-blue',
                'store_id' => 7,
                'enabled' => true,
                'tags' => [
                    ['name' => 'og:title', 'content' => 'Example'],
                ],
            ],
            (new MetadataFormatter())->format($metadata)
        );
    }
}
