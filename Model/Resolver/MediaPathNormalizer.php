<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Resolver;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class MediaPathNormalizer
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function normalizeMediaPath(mixed $value, string $basePath = ''): string
    {
        if (is_array($value)) {
            return '';
        }

        $value = trim((string)$value);
        if ($value === '' || $value === 'no_selection') {
            return '';
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            $path = $this->getLocalMediaPathFromUrl($value);
            if ($path === null) {
                return '';
            }

            $value = $path;
        }

        $path = $this->sanitizeRelativePath($value);
        if ($path === '') {
            return '';
        }

        foreach (['pub/media/', 'media/'] as $mediaPrefix) {
            if (str_starts_with($path, $mediaPrefix)) {
                $path = substr($path, strlen($mediaPrefix));
                break;
            }
        }

        if ($basePath !== '' && !str_starts_with($path, trim($basePath, '/') . '/')) {
            $path = trim($basePath, '/') . '/' . $path;
        }

        return $this->sanitizeRelativePath($path);
    }

    public function normalizeImageUrl(mixed $value, string $basePath = ''): string
    {
        if (is_array($value)) {
            return '';
        }

        $value = trim((string)$value);
        if ($value === '' || $value === 'no_selection') {
            return '';
        }

        if (preg_match('#^https?://#i', $value) === 1 && $this->getLocalMediaPathFromUrl($value) === null) {
            return $this->isValidRemoteUrl($value) ? $value : '';
        }

        $path = $this->normalizeMediaPath($value, $basePath);
        if ($path === '') {
            return '';
        }

        return rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/') . '/' . $path;
    }

    public function getMediaRelativePath(string $url): ?string
    {
        if (preg_match('#^https?://#i', $url) === 1) {
            $path = $this->getLocalMediaPathFromUrl($url);
            return $path === null ? null : $this->normalizeMediaPath($path);
        }

        $path = $this->normalizeMediaPath($url);
        return $path !== '' ? $path : null;
    }

    private function getLocalMediaPathFromUrl(string $url): ?string
    {
        if (!$this->isValidRemoteUrl($url)) {
            return null;
        }

        $mediaBaseUrl = rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/') . '/';
        if (!str_starts_with($url, $mediaBaseUrl)) {
            return null;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $path = (string)(parse_url($url, PHP_URL_PATH) ?: '');
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $mediaBasePath = (string)(parse_url($mediaBaseUrl, PHP_URL_PATH) ?: '');

        if ($mediaBasePath !== '' && str_starts_with($path, $mediaBasePath)) {
            return ltrim(substr($path, strlen($mediaBasePath)), '/');
        }

        return ltrim(substr($url, strlen($mediaBaseUrl)), '/');
    }

    private function isValidRemoteUrl(string $url): bool
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        return in_array($scheme, ['http', 'https'], true)
            && !empty($parts['host'])
            && empty($parts['user'])
            && empty($parts['pass'])
            && !$this->containsUnsafeSegments((string)($parts['path'] ?? ''));
    }

    private function sanitizeRelativePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        $path = ltrim($path, '/');
        $path = preg_split('/[?#]/', $path, 2)[0] ?? '';
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $path = rawurldecode($path);

        if ($path === '' || str_starts_with($path, '/') || $this->containsUnsafeSegments($path)) {
            return '';
        }

        return $path;
    }

    private function containsUnsafeSegments(string $path): bool
    {
        foreach (explode('/', str_replace('\\', '/', $path)) as $index => $segment) {
            if ($segment === '' && $index === 0) {
                continue;
            }

            if ($segment === '..' || $segment === '') {
                return true;
            }
        }

        return false;
    }
}
