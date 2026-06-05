<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Cms;

use Magento\Catalog\Model\ImageUploader;
use Magento\Cms\Model\Page;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Mosaicora\OpenGraph\Model\Resolver\MediaPathNormalizer;

class OpenGraphImageProcessor implements ObserverInterface
{
    public function __construct(
        private readonly ImageUploader $imageUploader,
        private readonly MediaPathNormalizer $mediaPathNormalizer
    ) {
    }

    public function execute(Observer $observer): void
    {
        $page = $observer->getEvent()->getData('page');
        if (!$page instanceof Page) {
            return;
        }

        $page->setData('og_image_custom', $this->normalizeImageValue($page->getData('og_image_custom')));
    }

    public function normalizeImageValue(mixed $value): ?string
    {
        if (!is_array($value)) {
            $value = trim((string)$value);
            if ($value === '') {
                return null;
            }

            $path = $this->normalizeMediaPath($value);
            return $path !== '' ? $path : null;
        }

        if ($value === []) {
            return null;
        }

        $image = reset($value);
        if (!is_array($image)) {
            return null;
        }

        $name = trim((string)($image['name'] ?? ''));
        if (isset($image['tmp_name'])) {
            if ($name === '') {
                throw new LocalizedException(__('The uploaded image has no file name.'));
            }

            return $this->imageUploader->moveFileFromTmp($name, true);
        }

        $url = (string)($image['url'] ?? '');
        if ($url !== '') {
            $path = $this->normalizeMediaPath($url);
            return $path !== '' ? $path : null;
        }

        if ($name === '') {
            return null;
        }

        $path = $this->normalizeMediaPath($name);
        return $path !== '' ? $path : null;
    }

    private function normalizeMediaPath(string $value): string
    {
        return $this->mediaPathNormalizer->normalizeMediaPath($value);
    }
}
