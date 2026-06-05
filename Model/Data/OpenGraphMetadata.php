<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Data;

use Magento\Framework\DataObject;
use Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface;

class OpenGraphMetadata extends DataObject implements OpenGraphMetadataInterface
{
    public function getPageType(): string
    {
        return (string)$this->getData(self::PAGE_TYPE);
    }

    public function setPageType(string $pageType): OpenGraphMetadataInterface
    {
        return $this->setData(self::PAGE_TYPE, $pageType);
    }

    public function getIdentifier(): string
    {
        return (string)$this->getData(self::IDENTIFIER);
    }

    public function setIdentifier(string $identifier): OpenGraphMetadataInterface
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    public function getStoreId(): int
    {
        return (int)$this->getData(self::STORE_ID);
    }

    public function setStoreId(int $storeId): OpenGraphMetadataInterface
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function getEnabled(): bool
    {
        return (bool)$this->getData(self::ENABLED);
    }

    public function setEnabled(bool $enabled): OpenGraphMetadataInterface
    {
        return $this->setData(self::ENABLED, $enabled);
    }

    public function getTags(): array
    {
        $tags = $this->getData(self::TAGS);

        return is_array($tags) ? $tags : [];
    }

    public function setTags(array $tags): OpenGraphMetadataInterface
    {
        return $this->setData(self::TAGS, $tags);
    }
}
