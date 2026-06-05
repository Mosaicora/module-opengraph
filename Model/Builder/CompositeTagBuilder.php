<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Builder;

use Mosaicora\OpenGraph\Api\TagBuilderInterface;
use Mosaicora\OpenGraph\Model\Context\PageContext;

class CompositeTagBuilder
{
    /**
     * @param TagBuilderInterface[] $builders
     */
    public function __construct(
        private readonly array $builders = []
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function build(PageContext $context): array
    {
        foreach ($this->builders as $builder) {
            if ($builder->supports($context)) {
                return $this->normalize($builder->build($context));
            }
        }

        return [];
    }

    /**
     * @param array<string, string> $tags
     * @return array<string, string>
     */
    private function normalize(array $tags): array
    {
        $normalized = [];

        foreach ($tags as $name => $content) {
            $name = trim((string)$name);
            $content = trim((string)$content);

            if ($name !== '' && $content !== '') {
                $normalized[$name] = $content;
            }
        }

        return $normalized;
    }
}
