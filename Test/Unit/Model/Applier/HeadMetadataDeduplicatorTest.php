<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Applier;

use Mosaicora\OpenGraph\Model\Applier\HeadMetadataDeduplicator;
use PHPUnit\Framework\TestCase;

class HeadMetadataDeduplicatorTest extends TestCase
{
    public function testReplacesCompetingTagsAndPreservesUnrelatedMetadata(): void
    {
        $html = <<<'HTML'
<!doctype html>
<html>
<head prefix="og: https://ogp.me/ns#">
<meta property="og:title" content="Core title" />
<META content='Extension title' NAME='OG:TITLE'>
<meta content="Core image" property=og:image>
<meta property="og:locale" content="en_US">
<meta name="twitter:title" content="Extension title">
<meta name="description" content="Search description">
</head>
<body>Page</body>
</html>
HTML;

        $deduplicator = new HeadMetadataDeduplicator();
        $canonical = $deduplicator->markCanonicalTags(
            <<<'HTML'
<meta property="og:title" content="Mosaicora &quot;title&quot;"/>
<meta property="og:image" content="https://example.test/image.jpg?a=1&amp;b=2"/>
<meta name="twitter:title" content="Mosaicora title"/>
HTML,
            [
                'og:title' => 'Mosaicora "title"',
                'og:image' => 'https://example.test/image.jpg?a=1&b=2',
                'twitter:title' => 'Mosaicora title',
            ]
        );
        $result = $deduplicator->process(str_replace('</head>', $canonical . '</head>', $html));

        self::assertSame(1, substr_count(strtolower($result), 'property="og:title"'));
        self::assertSame(1, substr_count(strtolower($result), 'property="og:image"'));
        self::assertSame(1, substr_count(strtolower($result), 'name="twitter:title"'));
        self::assertStringContainsString('content="Mosaicora &quot;title&quot;"', $result);
        self::assertStringContainsString('content="https://example.test/image.jpg?a=1&amp;b=2"', $result);
        self::assertStringContainsString('<meta property="og:locale" content="en_US">', $result);
        self::assertStringContainsString('<meta name="description" content="Search description">', $result);
        self::assertStringNotContainsString('Core title', $result);
        self::assertStringNotContainsString('Extension title', $result);
        self::assertStringNotContainsString('Core image', $result);
    }

    public function testLeavesHtmlUnchangedWithoutMarkersOrHead(): void
    {
        $deduplicator = new HeadMetadataDeduplicator();

        self::assertSame('<html><head></head></html>', $deduplicator->process('<html><head></head></html>'));
        self::assertSame('<div>Fragment</div>', $deduplicator->process('<div>Fragment</div>'));
    }

    public function testDoesNotRemoveTwitterTagsThatMosaicoraDidNotGenerate(): void
    {
        $deduplicator = new HeadMetadataDeduplicator();
        $canonical = $deduplicator->markCanonicalTags(
            '<meta property="og:title" content="Title"/>',
            ['og:title' => 'Title']
        );
        $html = '<html><head><meta name="twitter:image" content="existing">'
            . $canonical
            . '</head></html>';

        $result = $deduplicator->process($html);

        self::assertStringContainsString('<meta name="twitter:image" content="existing">', $result);
        self::assertStringContainsString('<meta property="og:title" content="Title"/>', $result);
        self::assertStringNotContainsString('data-mosaicora-opengraph', $result);
    }

    public function testMarksOnlyCanonicalTagNames(): void
    {
        $result = (new HeadMetadataDeduplicator())->markCanonicalTags(
            '<meta property="og:title" content="Title"/>'
            . '<meta name="description" content="Description"/>',
            ['og:title' => 'Title']
        );

        self::assertSame(1, substr_count($result, 'data-mosaicora-opengraph'));
        self::assertStringContainsString(
            '<meta name="description" content="Description"/>',
            $result
        );
    }
}
