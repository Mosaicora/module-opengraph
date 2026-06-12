<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\GraphQl;

use Mosaicora\OpenGraph\Model\Data\OpenGraphMetadata;
use Mosaicora\OpenGraph\Model\Data\OpenGraphTag;
use Mosaicora\OpenGraph\Model\GraphQl\MetadataFormatter;
use Mosaicora\OpenGraph\Model\Resolver\TextSanitizer;
use PHPUnit\Framework\TestCase;

class MetadataFormatterTest extends TestCase
{
    public function testFormatsServiceContractAsGraphQlData(): void
    {
        $sanitizer = $this->createStub(TextSanitizer::class);
        $sanitizer->method('clean')->willReturn('Example & title');
        $tag = new OpenGraphTag($sanitizer);
        $tag->setName('og:title')->setContent('<strong>Example</strong> &amp; title');
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
                    ['name' => 'og:title', 'content' => 'Example & title'],
                ],
            ],
            (new MetadataFormatter())->format($metadata)
        );
    }
}
