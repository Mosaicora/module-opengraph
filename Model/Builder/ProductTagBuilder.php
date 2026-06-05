<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Builder;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Api\TagBuilderInterface;
use Mosaicora\OpenGraph\Model\Config\ConfigProvider;
use Mosaicora\OpenGraph\Model\Context\PageContext;
use Mosaicora\OpenGraph\Model\Resolver\ImageUrlResolver;
use Mosaicora\OpenGraph\Model\Resolver\PageUrlResolver;
use Mosaicora\OpenGraph\Model\Resolver\ValueResolver;

class ProductTagBuilder extends AbstractTagBuilder implements TagBuilderInterface
{
    public function __construct(
        ConfigProvider $config,
        ImageUrlResolver $imageUrlResolver,
        StoreManagerInterface $storeManager,
        PageUrlResolver $pageUrlResolver,
        private readonly ValueResolver $valueResolver,
        private readonly PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($config, $imageUrlResolver, $storeManager, $pageUrlResolver);
    }

    public function supports(PageContext $context): bool
    {
        return $context->getType() === PageContext::TYPE_PRODUCT && $context->getEntity() instanceof Product;
    }

    public function build(PageContext $context): array
    {
        /** @var Product $product */
        $product = $context->getEntity();
        $storeId = $this->getStoreId();

        $title = $this->valueResolver->resolveText(
            $product,
            'product',
            'title',
            'og_title_mode',
            'og_title_attribute',
            'og_title_custom',
            $storeId,
            120
        ) ?: (string)$product->getName();

        $description = $this->valueResolver->resolveText(
            $product,
            'product',
            'description',
            'og_description_mode',
            'og_description_attribute',
            'og_description_custom',
            $storeId
        );

        $tags = $this->withImageTags(
            [
            'og:title' => $title,
            'og:description' => $description,
            ],
            $this->imageUrlResolver->resolveProductImageData($product, $storeId)
        );

        $price = (float)$product->getFinalPrice();
        if ($price > 0) {
            $tags['product:price:amount'] = (string)$this->priceCurrency->round($price);
            $tags['product:price:currency'] = (string)$this->storeManager->getStore()->getCurrentCurrencyCode();
        }

        $tags['product:availability'] = $product->isAvailable() ? 'instock' : 'oos';

        return $this->withCommonTags($tags, 'product', $this->pageUrlResolver->resolve($context), $storeId);
    }
}
