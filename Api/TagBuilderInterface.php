<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Api;

use Mosaicora\OpenGraph\Model\Context\PageContext;

interface TagBuilderInterface
{
    public function supports(PageContext $context): bool;

    /**
     * @return array<string, string>
     */
    public function build(PageContext $context): array;
}
