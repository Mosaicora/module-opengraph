<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Applier;

class HeadMetadataDeduplicator
{
    private const MARKER_ATTRIBUTE = 'data-mosaicora-opengraph';

    /**
     * @param array<string, string> $tags
     */
    public function markCanonicalTags(string $metadata, array $tags): string
    {
        if ($tags === []) {
            return $metadata;
        }

        $targets = array_fill_keys(array_map('strtolower', array_keys($tags)), true);
        $result = preg_replace_callback(
            '/<meta\b[^>]*>/is',
            function (array $match) use ($targets): string {
                $key = $this->getMetadataKey($match[0]);
                if ($key === null || !isset($targets[strtolower($key)])) {
                    return $match[0];
                }

                return (string)preg_replace(
                    '/\s*(\/?>)$/',
                    ' ' . self::MARKER_ATTRIBUTE . '="1"$1',
                    $match[0],
                    1
                );
            },
            $metadata
        );

        return $result ?? $metadata;
    }

    public function process(string $html): string
    {
        if (stripos($html, '<head') === false || stripos($html, self::MARKER_ATTRIBUTE) === false) {
            return $html;
        }

        $result = preg_replace_callback(
            '/<head\b[^>]*>.*?<\/head\s*>/is',
            function (array $headMatch): string {
                $canonicalTags = $this->getCanonicalTags($headMatch[0]);
                if ($canonicalTags === []) {
                    return $headMatch[0];
                }

                $targets = array_fill_keys(array_keys($canonicalTags), true);
                $head = preg_replace_callback(
                    '/<meta\b[^>]*>/is',
                    function (array $metaMatch) use ($targets): string {
                        $key = $this->getMetadataKey($metaMatch[0]);

                        return $key !== null && isset($targets[strtolower($key)]) ? '' : $metaMatch[0];
                    },
                    $headMatch[0]
                );

                if ($head === null) {
                    return $headMatch[0];
                }

                return (string)preg_replace(
                    '/<\/head\s*>$/i',
                    implode('', $canonicalTags) . '</head>',
                    $head,
                    1
                );
            },
            $html,
            1
        );

        return $result ?? $html;
    }

    /**
     * @return array<string, string>
     */
    private function getCanonicalTags(string $head): array
    {
        $canonicalTags = [];
        preg_match_all('/<meta\b[^>]*>/is', $head, $matches);

        foreach ($matches[0] as $tag) {
            if (preg_match('/\s+' . self::MARKER_ATTRIBUTE . '\s*=\s*(["\'])1\1/i', $tag) !== 1) {
                continue;
            }

            $key = $this->getMetadataKey($tag);
            if ($key === null) {
                continue;
            }

            $canonicalTags[strtolower($key)] = (string)preg_replace(
                '/\s+' . self::MARKER_ATTRIBUTE . '\s*=\s*(["\'])1\1/i',
                '',
                $tag,
                1
            ) . "\n";
        }

        return $canonicalTags;
    }

    private function getMetadataKey(string $tag): ?string
    {
        if (preg_match('/\b(?:property|name)\s*=\s*(["\'])(.*?)\1/is', $tag, $match) === 1) {
            return trim($match[2]);
        }

        if (preg_match('/\b(?:property|name)\s*=\s*([^\s>\/]+)/i', $tag, $match) === 1) {
            return trim($match[1]);
        }

        return null;
    }
}
