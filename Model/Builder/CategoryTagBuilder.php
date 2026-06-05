<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Builder;

use Magento\Catalog\Model\Category;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Api\TagBuilderInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Context\PageContext;
use Mosaicora\OpenGraph\Model\Resolver\ImageUrlResolver;
use Mosaicora\OpenGraph\Model\Resolver\PageUrlResolver;
use Mosaicora\OpenGraph\Model\Resolver\ValueResolver;

class CategoryTagBuilder extends AbstractTagBuilder implements TagBuilderInterface
{
    public function __construct(
        ConfigProvider $config,
        ImageUrlResolver $imageUrlResolver,
        StoreManagerInterface $storeManager,
        PageUrlResolver $pageUrlResolver,
        private readonly ValueResolver $valueResolver
    ) {
        parent::__construct($config, $imageUrlResolver, $storeManager, $pageUrlResolver);
    }

    public function supports(PageContext $context): bool
    {
        return $context->getType() === PageContext::TYPE_CATEGORY && $context->getEntity() instanceof Category;
    }

    public function build(PageContext $context): array
    {
        /** @var Category $category */
        $category = $context->getEntity();
        $storeId = $this->getStoreId();

        $title = $this->valueResolver->resolveText(
            $category,
            'category',
            'title',
            'og_title_mode',
            'og_title_attribute',
            'og_title_custom',
            $storeId,
            120
        ) ?: (string)$category->getName();

        $description = $this->valueResolver->resolveText(
            $category,
            'category',
            'description',
            'og_description_mode',
            'og_description_attribute',
            'og_description_custom',
            $storeId
        );

        return $this->withCommonTags(
            $this->withImageTags(
                [
                'og:title' => $title,
                'og:description' => $description,
                ],
                $this->imageUrlResolver->resolveCategoryImageData($category, $storeId)
            ),
            'website',
            $this->pageUrlResolver->resolve($context),
            $storeId
        );
    }
}
