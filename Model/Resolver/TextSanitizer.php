<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Resolver;

use Magento\Framework\Filter\FilterManager;

class TextSanitizer
{
    public function __construct(
        private readonly FilterManager $filterManager
    ) {
    }

    public function clean(mixed $value, int $maxLength = 300): string
    {
        if (is_array($value)) {
            return '';
        }

        $text = $this->filterManager->removeTags((string)$value);
        $text = preg_replace('/\s+/u', ' ', $text) ?: '';
        $text = trim($text);

        if ($text === '' || $maxLength <= 0 || mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $maxLength - 3)) . '...';
    }
}
