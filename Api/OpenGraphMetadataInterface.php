<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Api;

use Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface as OpenGraphMetadataDataInterface;

interface OpenGraphMetadataInterface
{
    /**
     * Return Open Graph metadata for a product.
     *
     * @param string $sku Product SKU.
     * @return \Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface
     */
    public function getProduct(string $sku): OpenGraphMetadataDataInterface;

    /**
     * Return Open Graph metadata for a category.
     *
     * @param int $categoryId Category ID.
     * @return \Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface
     */
    public function getCategory(int $categoryId): OpenGraphMetadataDataInterface;

    /**
     * Return Open Graph metadata for a CMS page.
     *
     * @param string $identifier CMS page identifier.
     * @return \Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface
     */
    public function getCms(string $identifier): OpenGraphMetadataDataInterface;

    /**
     * Return Open Graph metadata for the store home page.
     *
     * @return \Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface
     */
    public function getHome(): OpenGraphMetadataDataInterface;
}
