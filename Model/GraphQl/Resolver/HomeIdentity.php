<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\GraphQl\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

class HomeIdentity implements IdentityInterface
{
    public function getIdentities(array $resolvedData): array
    {
        $identities = $resolvedData['_cache_identities'] ?? [];

        return is_array($identities) ? array_values(array_unique($identities)) : [];
    }
}
