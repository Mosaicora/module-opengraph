# Mosaicora Open Graph

Open Graph and Twitter Card metadata module for Magento 2, Mage-OS, and Adobe Commerce storefronts.

`mosaicora/module-opengraph` gives product, category, CMS, and home pages the metadata that social platforms need to generate clean share previews. It works out of the box with sensible defaults, while still giving store teams per-store control over titles, descriptions, images, and generated social-preview image sizes.

## Live Demo

A Magento demo shop configured with this module is available at <https://demo-shop-magento.mosaicora.io/>.

Use the free Mosaicora Open Graph Checker at <https://mosaicora.io/tools/open-graph-checker> to inspect the shop's Open Graph tags and preview how its pages will appear when shared. Try these ready-made examples:

- [Preview the Bags category](https://mosaicora.io/tools/open-graph-checker?url=https%3A%2F%2Fdemo-shop-magento.mosaicora.io%2Fgear%2Fbags.html)
- [Preview the Joust Duffle Bag](https://mosaicora.io/tools/open-graph-checker?url=https%3A%2F%2Fdemo-shop-magento.mosaicora.io%2Fjoust-duffle-bag.html)
- [Preview the Voyage Yoga Bag](https://mosaicora.io/tools/open-graph-checker?url=https%3A%2F%2Fdemo-shop-magento.mosaicora.io%2Fvoyage-yoga-bag.html)

## Why Use It

- Adds Open Graph metadata across common storefront page types.
- Mirrors Open Graph values into optional Twitter Card tags.
- Lets admins choose title, description, and image sources without template changes.
- Supports custom Open Graph images for categories and CMS pages.
- Generates cached `1200x630` social-preview images from local Magento media assets by default.
- Provides an admin cache action for flushing generated Open Graph images.

## Supported Pages

The module resolves the active storefront context and applies metadata during layout generation.

- Product pages use the current product.
- Category pages use the current category on `catalog_category_view`.
- CMS pages use the active CMS page captured during render.
- The home page uses the current CMS home page when available, otherwise it falls back to the configured site name and default image.

## Requirements

- PHP `~8.1` to `~8.5`
- Magento 2, Mage-OS, or Adobe Commerce with the Catalog, CMS, Store, Web API, and GraphQL modules enabled.
- Composer credentials for `repo.magento.com` when installing Magento packages directly.

## Installation

Install the package through Composer:

```bash
composer require mosaicora/module-opengraph
```

Run the normal Magento upgrade and cache workflow:

```bash
bin/magento module:enable Mosaicora_OpenGraph
bin/magento setup:upgrade
bin/magento cache:flush config layout block_html eav full_page config_webservice
```

For production-mode deployments, also regenerate dependency injection and static content according to the store's deployment process:

```bash
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

Install dependencies from the Magento project root. Do not run `composer install` inside a module directory mounted or symlinked into Magento because a nested `vendor/` tree causes duplicate class loading during compilation.

## Configuration

The module adds a configuration section at:

`Stores > Configuration > Mosaicora > Open Graph`

Configuration is store-scoped where Magento allows it, so each store view can use its own site name, default image, metadata source attributes, and image optimization settings.

### General

- `Enable Open Graph Tags`
- `Site Name`
- `Default Image`
- `Enable Twitter Card Tags`
- `Twitter Card Type`
- `Remove Competing Social Tags`

`Remove Competing Social Tags` is disabled by default. When enabled, Mosaicora removes duplicate Open Graph,
product, and enabled Twitter Card tags for metadata keys that this module generates, so Mosaicora's values win.

### Image Optimization

- `Serve Optimized OG Image`
- `Width`
- `Height`
- `Resize Mode`
- `Background Color`

Image optimization is enabled by default and uses `1200x630`, the common Open Graph share-card aspect ratio. Local Magento media images can be converted into cached social-preview images. External image URLs are left unchanged.

### Content Defaults

- Product title, description, and image attribute sources.
- Category title and description attribute sources.
- CMS title and description field sources.

Individual products, categories, and CMS pages can also provide Open Graph-specific values when the module attributes or fields are available.

## Metadata Output

The module can output:

- `og:type`
- `og:url`
- `og:site_name`
- `og:title`
- `og:description`
- `og:image`
- `og:image:width`
- `og:image:height`
- `twitter:card`
- `twitter:title`
- `twitter:description`
- `twitter:image`

Product pages may also output:

- `product:price:amount`
- `product:price:currency`
- `product:availability` with `instock` or `oos`

Twitter tags are copied from the resolved Open Graph values when Twitter Card output is enabled.

### Duplicate Social Tags

Magento, themes, and third-party extensions may emit their own Open Graph or Twitter Card tags. The preferred
setup is to disable that output in the other module or theme so only one metadata provider remains active.

If the competing output cannot be disabled, enable `Remove Competing Social Tags` under
`Stores > Configuration > Mosaicora > Open Graph > General`. The switch is off by default for compatibility.
When enabled, it removes competing tags only for metadata keys generated by Mosaicora and keeps unrelated tags.

## Metadata API

The module exposes anonymous read-only metadata endpoints intended for preview tooling and integrations:

- `GET /V1/mosaicora/opengraph/product/:sku`
- `GET /V1/mosaicora/opengraph/category/:categoryId`
- `GET /V1/mosaicora/opengraph/cms/:identifier`
- `GET /V1/mosaicora/opengraph/home`

These endpoints return the same resolved tags that frontend pages use. They do not expose private catalog data, but they are intentionally public.

Send Magento's standard `Store` request header to resolve metadata for a specific store view.

### GraphQL

GraphQL exposes the same metadata on Magento's native storefront types:

- `ProductInterface.open_graph`
- `CategoryInterface.open_graph`
- `CmsPage.open_graph`
- `StoreConfig.home_open_graph`

The `tags` field uses the same flexible `name` and `content` list returned by REST, including Open Graph, Twitter Card, and product metadata.

```graphql
query ProductOpenGraph($sku: String!) {
  products(filter: { sku: { eq: $sku } }) {
    items {
      sku
      open_graph {
        page_type
        identifier
        store_id
        enabled
        tags {
          name
          content
        }
      }
    }
  }
}
```

```graphql
query CategoryOpenGraph($uid: String!) {
  categories(filters: { category_uid: { eq: $uid } }) {
    items {
      uid
      open_graph {
        enabled
        tags {
          name
          content
        }
      }
    }
  }
}
```

```graphql
query PageOpenGraph($identifier: String!) {
  cmsPage(identifier: $identifier) {
    identifier
    open_graph {
      enabled
      tags {
        name
        content
      }
    }
  }

  storeConfig {
    home_open_graph {
      enabled
      tags {
        name
        content
      }
    }
  }
}
```

Disabled configuration returns `enabled: false` and an empty `tags` list. REST and GraphQL use the same normalized tag builders and storefront canonical URLs.

## Default Resolution

Out of the box, the module uses these fallbacks:

- Product title: `meta_title`, then `name`
- Product description: `meta_description`, then `short_description`, then `description`
- Product image: `open_graph_image`, then `image`, then `small_image`
- Category title: `meta_title`, then `name`
- Category description: `meta_description`, then `description`
- Category image: `image`
- CMS title: `meta_title`, then `title`
- CMS description: `meta_description`, then `content`
- Home page title: configured site name
- Home page image: configured default image

Text values are sanitized before output and truncated when needed.

## Images

Product images are resolved from the configured product image attribute and then through the default fallback list until a usable image is found.

Category images can use the category image automatically or a custom `og_image_custom` category attribute.

CMS pages can use the configured default image or a custom uploaded image stored in `og_image_custom`.

Only normalized paths under Magento media are used for local image optimization. Remote URLs are emitted unchanged and are never used for filesystem reads or generated cache files.

When image optimization is enabled, generated images are cached in:

`pub/media/mosaicora/opengraph/cache`

The cache key accounts for the source file, source modification time, target dimensions, and resize mode. Authorized admin users can clear generated Open Graph images from the Magento cache management screen.

## Admin Fields

The installation patches add these product attributes:

- `open_graph_image`
- `og_title_mode`
- `og_title_attribute`
- `og_title_custom`
- `og_description_mode`
- `og_description_attribute`
- `og_description_custom`

The installation patches add these category attributes:

- `og_title_mode`
- `og_title_attribute`
- `og_title_custom`
- `og_description_mode`
- `og_description_attribute`
- `og_description_custom`
- `og_image_mode`
- `og_image_custom`

CMS pages receive matching Open Graph fields through the admin UI component.

## Admin Permissions

The module adds permissions for configuration access and generated image cache management:

- `Mosaicora_OpenGraph::config`
- `Mosaicora_OpenGraph::flush_open_graph_images`

## Development

When the module is mounted in Magento, run checks with the parent dependency tree:

```bash
vendor/bin/phpcs --standard=[module path]/Mosaicora_OpenGraph/phpcs.xml [module path]/Mosaicora_OpenGraph
vendor/bin/phpunit --configuration [module path]/Mosaicora_OpenGraph/phpunit.xml.dist
```

For a standalone clone outside Magento, configure `auth.json` for `repo.magento.com`, run `composer install`, then use `composer phpcs` and `composer test`.

CI validates the package on PHP 8.1 and PHP 8.5. Pull requests from forks do not receive Magento repository credentials; maintainers rerun reviewed changes from a trusted repository branch.

## Project Links

- [Contributing](./CONTRIBUTING.md)
- [Security](./SECURITY.md)
- [Changelog](./CHANGELOG.md)
- [License](./LICENSE)

## License

GPL-3.0-or-later

## Maintainer

Built and maintained by [Mosaicora.io](https://mosaicora.io).

Mosaicora automatically creates stunning Open Graph images from your web pages using beautiful, customizable templates.
No design tools or uploads needed.
