# Release Process

## Prepare

1. Set the release version in `composer.json`.
2. Add a matching dated section to `CHANGELOG.md`.
3. Confirm `etc/module.xml` has no `setup_version`.
4. Run:

   ```bash
   composer validate --no-check-lock
   vendor/bin/phpcs --standard=[module path]/Mosaicora_OpenGraph/phpcs.xml [module path]/Mosaicora_OpenGraph
   vendor/bin/phpunit --configuration [module path]/Mosaicora_OpenGraph/phpunit.xml.dist
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   ```

5. Verify Swagger, REST, GraphQL, store scope, uploads, image resolution, and cold/warm full-page cache output.
6. Inspect `git archive` output for credentials, caches, generated files, and nested dependency trees.

## Publish

1. Merge the release commit to `master`.
2. Create and push an annotated tag matching the Composer version:

   ```bash
   git tag -a v1.0.0 -m "Release v1.0.0"
   git push origin v1.0.0
   ```

3. The release workflow validates the tag, Composer version, and changelog, then creates the GitHub release.
4. Confirm Packagist synchronized `mosaicora/module-opengraph`.
5. Install `mosaicora/module-opengraph:1.0.0` in a clean Magento project.

## Rollback

Do not move or overwrite a published tag. If publication is incorrect, remove the GitHub release, mark the Packagist version abandoned when appropriate, fix the issue, and publish a new patch version.
