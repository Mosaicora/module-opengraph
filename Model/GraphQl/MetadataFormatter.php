<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\GraphQl;

use Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface;
use Mosaicora\OpenGraph\Api\Data\OpenGraphTagInterface;

class MetadataFormatter
{
    /**
     * @return array<string, mixed>
     */
    public function format(OpenGraphMetadataInterface $metadata): array
    {
        return [
            'page_type' => $metadata->getPageType(),
            'identifier' => $metadata->getIdentifier(),
            'store_id' => $metadata->getStoreId(),
            'enabled' => $metadata->getEnabled(),
            'tags' => array_map(
                static fn (OpenGraphTagInterface $tag): array => [
                    'name' => $tag->getName(),
                    'content' => $tag->getContent(),
                ],
                $metadata->getTags()
            ),
        ];
    }
}
