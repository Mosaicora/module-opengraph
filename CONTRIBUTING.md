# Contributing to Mosaicora Open Graph

Thanks for taking the time to contribute.

## What to contribute

- Bug fixes
- Documentation improvements
- Test coverage
- Compatibility fixes for supported Magento / PHP versions

## Local setup

This module is a Magento 2 package. In a full project checkout, install dependencies and run the normal Magento setup flow for your environment.

Typical commands:

```bash
bin/magento setup:upgrade
bin/magento cache:clean
```

If your parent project provides a PHPUnit setup, run the unit tests before opening a pull request.

## Coding expectations

- Keep changes focused and small.
- Prefer type-safe PHP and strict typing, matching the existing codebase.
- Follow the current Magento module structure and naming conventions.
- Add or update tests when behavior changes.
- Do not commit generated artifacts unless they are intentionally part of the change.

## Tests

This module includes unit tests under `Test/Unit/`.

Please run the relevant module tests in the parent Magento project before submitting changes. If you add new behavior, include a regression test for it.

## Pull requests

- Describe the change and why it is needed.
- Call out any configuration or upgrade impact.
- Mention test coverage.
- Include screenshots only when the change affects admin UI or rendered markup.

## Reporting issues

If you find a bug, please include:

- Magento version
- PHP version
- Module version or commit hash
- Steps to reproduce
- Expected vs actual behavior

