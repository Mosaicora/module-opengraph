<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Api\Data;

interface OpenGraphMetadataInterface
{
    public const PAGE_TYPE = 'page_type';
    public const IDENTIFIER = 'identifier';
    public const STORE_ID = 'store_id';
    public const ENABLED = 'enabled';
    public const TAGS = 'tags';

    /**
     * @return string
     */
    public function getPageType(): string;

    /**
     * @param string $pageType
     * @return \Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface
     */
    public function setPageType(string $pageType): self;

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @param string $identifier
     * @return \Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface
     */
    public function setIdentifier(string $identifier): self;

    /**
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @param int $storeId
     * @return \Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface
     */
    public function setStoreId(int $storeId): self;

    /**
     * @return bool
     */
    public function getEnabled(): bool;

    /**
     * @param bool $enabled
     * @return \Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface
     */
    public function setEnabled(bool $enabled): self;

    /**
     * @return \Mosaicora\OpenGraph\Api\Data\OpenGraphTagInterface[]
     */
    public function getTags(): array;

    /**
     * @param \Mosaicora\OpenGraph\Api\Data\OpenGraphTagInterface[] $tags
     * @return \Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface
     */
    public function setTags(array $tags): self;
}
