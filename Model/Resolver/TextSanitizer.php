<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Resolver;

class TextSanitizer
{
    public function clean(mixed $value, int $maxLength = 300): string
    {
        if (is_array($value)) {
            return '';
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $text = html_entity_decode(
            strip_tags((string)$value),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
        $text = preg_replace('/\s+/u', ' ', $text) ?: '';
        $text = trim($text);

        if ($text === '' || $maxLength <= 0 || mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $maxLength - 3)) . '...';
    }
}
