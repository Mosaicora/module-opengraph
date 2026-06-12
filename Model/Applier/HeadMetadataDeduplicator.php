<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Applier;

use Magento\Framework\Escaper;

class HeadMetadataDeduplicator
{
    public function __construct(
        private readonly Escaper $escaper
    ) {
    }

    /**
     * @param array<string, string> $tags
     */
    public function process(string $html, array $tags): string
    {
        if ($tags === [] || stripos($html, '<head') === false) {
            return $html;
        }

        $targets = array_fill_keys(array_map('strtolower', array_keys($tags)), true);

        $result = preg_replace_callback(
            '/<head\b[^>]*>.*?<\/head\s*>/is',
            function (array $headMatch) use ($targets, $tags): string {
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

                $metadata = $this->render($tags);

                return (string)preg_replace(
                    '/<\/head\s*>$/i',
                    $metadata . '</head>',
                    $head,
                    1
                );
            },
            $html,
            1
        );

        return $result ?? $html;
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

    /**
     * @param array<string, string> $tags
     */
    private function render(array $tags): string
    {
        $result = '';

        foreach ($tags as $name => $content) {
            $attribute = str_starts_with(strtolower($name), 'twitter:') ? 'name' : 'property';
            $result .= sprintf(
                '<meta %s="%s" content="%s"/>' . "\n",
                $attribute,
                $this->escaper->escapeHtmlAttr($name, false),
                $this->escaper->escapeHtmlAttr($content, false)
            );
        }

        return $result;
    }
}
