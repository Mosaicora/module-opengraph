<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Mosaicora\OpenGraph\Api\Data\OpenGraphMetadataInterface;
use Mosaicora\OpenGraph\Api\Data\OpenGraphTagInterface;
use Mosaicora\OpenGraph\Model\Builder\CompositeTagBuilder;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Context\PageContext;
use Mosaicora\OpenGraph\Model\Data\OpenGraphMetadata;
use Mosaicora\OpenGraph\Model\Data\OpenGraphTag;

class MetadataProvider
{
    private const XML_PATH_HOME_PAGE = 'web/default/cms_home_page';

    public function __construct(
        private readonly ConfigProvider $config,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly CmsPageLoader $cmsPageLoader,
        private readonly CompositeTagBuilder $tagBuilder
    ) {
    }

    public function getProduct(Product $product, int $storeId): OpenGraphMetadataInterface
    {
        return $this->build(
            PageContext::TYPE_PRODUCT,
            (string)$product->getSku(),
            new PageContext(PageContext::TYPE_PRODUCT, $product),
            $storeId
        );
    }

    public function getCategory(Category $category, int $storeId): OpenGraphMetadataInterface
    {
        return $this->build(
            PageContext::TYPE_CATEGORY,
            (string)$category->getId(),
            new PageContext(PageContext::TYPE_CATEGORY, $category),
            $storeId
        );
    }

    public function getCms(string $identifier, int $storeId): ?OpenGraphMetadataInterface
    {
        $page = $this->cmsPageLoader->load($identifier, $storeId);
        if ($page === null) {
            return null;
        }

        return $this->build(
            PageContext::TYPE_CMS,
            $identifier,
            new PageContext(PageContext::TYPE_CMS, $page),
            $storeId
        );
    }

    public function getHome(int $storeId): OpenGraphMetadataInterface
    {
        $identifier = $this->getHomeIdentifier($storeId);
        $page = $identifier !== '' ? $this->cmsPageLoader->load($identifier, $storeId) : null;

        return $this->build(
            PageContext::TYPE_HOME,
            $identifier,
            new PageContext(PageContext::TYPE_HOME, $page),
            $storeId
        );
    }

    public function getHomePage(int $storeId): ?Page
    {
        $identifier = $this->getHomeIdentifier($storeId);

        return $identifier !== '' ? $this->cmsPageLoader->load($identifier, $storeId) : null;
    }

    private function getHomeIdentifier(int $storeId): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_HOME_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    private function build(
        string $pageType,
        string $identifier,
        PageContext $context,
        int $storeId
    ): OpenGraphMetadataInterface {
        $enabled = $this->config->isEnabled($storeId);

        $metadata = new OpenGraphMetadata();
        $metadata->setPageType($pageType)
            ->setIdentifier($identifier)
            ->setStoreId($storeId)
            ->setEnabled($enabled)
            ->setTags($enabled ? $this->createTags($this->tagBuilder->build($context)) : []);

        return $metadata;
    }

    /**
     * @param array<string, string> $tags
     * @return OpenGraphTagInterface[]
     */
    private function createTags(array $tags): array
    {
        $tagItems = [];
        foreach ($tags as $name => $content) {
            $tag = new OpenGraphTag();
            $tagItems[] = $tag->setName((string)$name)->setContent((string)$content);
        }

        return $tagItems;
    }
}
