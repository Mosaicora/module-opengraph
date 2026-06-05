<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Builder;

use Magento\Cms\Model\Page;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Api\TagBuilderInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Context\PageContext;
use Mosaicora\OpenGraph\Model\Resolver\ImageUrlResolver;
use Mosaicora\OpenGraph\Model\Resolver\PageUrlResolver;
use Mosaicora\OpenGraph\Model\Resolver\ValueResolver;

class HomeTagBuilder extends AbstractTagBuilder implements TagBuilderInterface
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
        return $context->getType() === PageContext::TYPE_HOME;
    }

    public function build(PageContext $context): array
    {
        $page = $context->getEntity();
        $storeId = $this->getStoreId();
        $title = $this->config->getSiteName($storeId);
        $description = '';
        $image = $this->imageUrlResolver->resolveDefaultImageData($storeId);

        if ($page instanceof Page) {
            $title = $this->valueResolver->resolveText(
                $page,
                'cms',
                'title',
                'og_title_mode',
                'og_title_attribute',
                'og_title_custom',
                $storeId,
                120
            ) ?: $title;
            $description = $this->valueResolver->resolveText(
                $page,
                'cms',
                'description',
                'og_description_mode',
                'og_description_attribute',
                'og_description_custom',
                $storeId
            );
            $image = $this->imageUrlResolver->resolveCmsImageData($page, $storeId);
        }

        return $this->withCommonTags(
            $this->withImageTags(
                [
                'og:title' => $title,
                'og:description' => $description,
                ],
                $image
            ),
            'website',
            $this->pageUrlResolver->resolve($context),
            $storeId
        );
    }
}
