<img src="https://dl.dropboxusercontent.com/s/qi0b31um3y3nxo6/tolucastore-admin-panel.png" alt="TolucaStore Admin Panel"/>

<p align="center">
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
<a href="#contributors-"><img src="https://img.shields.io/badge/all_contributors-146-orange.svg?style=flat-square" alt="All Contributors"></a>
<!-- ALL-CONTRIBUTORS-BADGE:END -->
<a href="https://packagist.org/packages/openmage/magento-lts"><img src="https://poser.pugx.org/openmage/magento-lts/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/openmage/magento-lts"><img src="https://poser.pugx.org/openmage/magento-lts/license.svg" alt="License"></a>
<br />
<img src="https://github.com/openmage/magento-lts/actions/workflows/php.yml/badge.svg" alt="PHP workflow Badge" />
<img src="https://github.com/openmage/magento-lts/actions/workflows/sonar.yml/badge.svg" alt="Sonar workflow badge" />
<img src="https://github.com/openmage/magento-lts/actions/workflows/static-code-analyses.yml/badge.svg" alt="Static Code Analyses workflow badge" />
<img src="https://github.com/openmage/magento-lts/actions/workflows/unit-tests.yml/badge.svg" alt="Unit Tests workflow badge" />
</p>

# Magento - Long Term Support

This repository is the home of an **unofficial** community-driven project. It's goal is to be a dependable alternative
to the Magento CE official releases which integrates improvements directly from the community while maintaining a high
level of backwards compatibility to the official releases.

**Pull requests with unofficial bug fixes and security patches from the community are encouraged and welcome!**

Though Magento does not follow [Semantic Versioning](http://semver.org/) we aim to provide a workable system for
dependency definition. Each Magento `1.<minor>.<revision>` release will get its own branch (named `1.<minor>.<revision>.x`)
that will be independently maintained with upstream patches and community bug fixes for as long as it makes sense
to do so (based on available resources). For example, Magento version `1.9.3.4` was merged into the `1.9.3.x` branch.

Note, the branches older than `1.9.4.x` and that were created before this strategy came into practice are **not maintained**.

## Requirements

- PHP 7.0+ (PHP 7.3 with OpenSSL extension strongly recommended and verified compatible) (PHP 7.4 and 8.0 are supported)
- MySQL 5.6+ (8.0+ recommended)
- (optional) Redis 5+ (6.x recommended, latest verified compatible 6.0.7 with 20.x)

- PHP 7.4 and 8.0 are supported
- Please be aware that although OpenMage is compatible that 1 or more extensions may not be

Installation on PHP 7.2.33 (7.2.x), MySQL 5.7.31-34 (5.7.x) Percona Server and Redis 6.x should work fine and confirmed by users.

If using php 7.2+ then mcrypt needs to be disabled in php.ini or pecl to fallback on mcryptcompat and phpseclib. mcrypt is deprecated from 7.2+ onwards.

## Installation

### Using Composer

Download the latest archive and extract it, clone the repo, or add a composer dependency to your existing project like so:

```bash
composer require "openmage/magento-lts":"^19.4.0"
```

To get the latest changes use:

```bash
composer require "openmage/magento-lts":"dev-main"
```

<small>Note: `dev-main` is just an alias for current `1.9.4.x` branch and may change</small>

### Using Git

If you want to contribute to the project:

```bash
git init
git remote add origin https://github.com/<YOUR GIT USERNAME>/magento-lts
git pull origin master
git remote add upstream https://github.com/OpenMage/magento-lts
git pull upstream 1.9.4.x
git add -A && git commit
```

[More Information](http://openmage.github.io/magento-lts/install.html)

## Changes

Most important changes will be listed here, all other changes since `19.4.0` can be found in
[release](https://github.com/OpenMage/magento-lts/releases) notes.

### Between Magento 1.9.4.5 and OpenMage 19.x

- bug fixes and PHP 7.x and 8.0 compatibility
- added config cache for system.xml #1916

### Between OpenMage 19.x and 20.x

Do not use 20.x.x if you need IE support.

- removed IE conditional comments, IE styles, IE scripts and IE eot files #1073
- removed frontend default themes (default, modern, iphone, german, french, blank, blue) #1600
- fixed incorrect datetime in customer block (`$useTimezone` parameter) #1525
- add redis as a valid option for `global/session_save` #1513
- possibility to disable global search in backend #1532

For full list of changes, you can [compare tags](https://github.com/OpenMage/magento-lts/compare/1.9.4.x...20.0).

### New Config Options

- `admin/design/use_legacy_theme`
- `admin/global_search/enable`
- `admin/emails/admin_notification_email_template`
- `catalog/product_image/progressive_threshold`
- `catalog/search/search_separator`
- `dev/log/max_level`
- `newsletter/security/enable_form_key`
- `sitemap/category/lastmod`
- `sitemap/page/lastmod`
- `sitemap/product/lastmod`

### New Events

- `adminhtml_block_widget_form_init_form_values_after`
- `adminhtml_block_widget_tabs_html_before`
- `adminhtml_sales_order_create_save_before`
- `checkout_cart_product_add_before`
- `sitemap_cms_pages_generating_before`
- `sitemap_urlset_generating_before`

[Full list of events](EVENTS.md)

### Removed Modules

- `Mage_Compiler`
- `Mage_GoogleBase`
- `Mage_Xmlconnect`
- `Phoenix_Moneybookers`

## Development Environment with ddev

- Install [ddev](https://ddev.com/get-started/)
- Clone the repository as described in Installation -> Using Git
- Create a ddev config using ```$ ddev config``` the defaults should be good for you
- Open .ddev/config.yaml and change the php version to 7.2
- Type ```$ ddev start``` to download and start the containers
- Navigate to https://magento-lts.ddev.site
- When you are done you can stop the test system by typing ```$ ddev stop```

### PhpStorm Factory Helper

This repo includes class maps for the core Magento files in `.phpstorm.meta.php`.
To add class maps for installed extensions, you have to install [N98-magerun](https://github.com/netz98/n98-magerun)
and run command:

```
n98-magerun dev:ide:phpstorm:meta
```

You can add additional meta files in this directory to cover your own project files. See
[PhpStorm advanced metadata](https://www.jetbrains.com/help/phpstorm/ide-advanced-metadata.html)
for more information.

## Public Communication

* [Discord](https://discord.gg/EV8aNbU) (maintained by Flyingmana)

## Maintainers

* [Lee Saferite](https://github.com/LeeSaferite)
* [David Robinson](https://github.com/drobinson)
* [Daniel Fahlke aka Flyingmana](https://github.com/Flyingmana)
* [Tymoteusz Motylewski](https://github.com/tmotyl)
* [Sven Reichel](https://github.com/sreichel)

## License

- [OSL v3.0](http://opensource.org/licenses/OSL-3.0)
- [AFL v3.0](http://opensource.org/licenses/AFL-3.0)


