# Changelog

All notable changes to this project will be documented in this file.

The project follows a simple, human-readable release log.

## [1.0.3] - 2026-06-13

- Restored Magento 2.4.6 compatibility by removing dependencies on framework APIs introduced in later releases.
- Made social metadata deduplication stateless and safe for long-running application workers.
- Reused loaded CMS home pages in GraphQL resolution and preserved cache identities across supported versions.

## [1.0.2] - 2026-06-12

- Added an optional, store-scoped setting that removes competing social metadata tags and keeps Mosaicora's values.
- Stripped HTML markup from resolved Open Graph and Twitter Card text values.

## [1.0.1] - 2026-06-07

- Fixed custom Open Graph images not being saved on categories.

## [1.0.0] - 2026-06-06

- Open Graph metadata support for product, category, CMS, and home pages.
- Optional Twitter Card metadata support.
- Store-scoped admin configuration for metadata sources and default images.
- Product, category, and CMS entity-level Open Graph fields.
- Strict custom-image resolution and cached local image optimization.
- Anonymous REST and native GraphQL metadata APIs.
- Swagger-compatible service contracts and store-aware canonical URLs.
- Admin upload endpoints and generated image cache management.

[1.0.0]: https://github.com/Mosaicora/module-opengraph/releases/tag/v1.0.0
[1.0.1]: https://github.com/Mosaicora/module-opengraph/releases/tag/v1.0.1
[1.0.2]: https://github.com/Mosaicora/module-opengraph/releases/tag/v1.0.2
[1.0.3]: https://github.com/Mosaicora/module-opengraph/releases/tag/v1.0.3
