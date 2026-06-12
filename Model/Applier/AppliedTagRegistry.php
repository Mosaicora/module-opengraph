<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Applier;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

class AppliedTagRegistry implements ResetAfterRequestInterface
{
    /**
     * @var array<string, string>
     */
    private array $tags = [];

    /**
     * @param array<string, string> $tags
     */
    public function set(array $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @return array<string, string>
     */
    public function get(): array
    {
        return $this->tags;
    }

    public function _resetState(): void
    {
        $this->tags = [];
    }
}
