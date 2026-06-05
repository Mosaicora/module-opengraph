<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Resolver;

class OptimizedImage
{
    public function __construct(
        private readonly string $url,
        private readonly ?int $width = null,
        private readonly ?int $height = null
    ) {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }
}
