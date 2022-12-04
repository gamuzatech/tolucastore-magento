<img src="https://dl.dropboxusercontent.com/s/qi0b31um3y3nxo6/tolucastore-admin-panel.png" alt="TolucaStore Admin Panel"/>

<p align="center">
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
<a href="#contributors-"><img src="https://img.shields.io/badge/all_contributors-146-orange.svg?style=flat-square" alt="All Contributors"></a>
<!-- ALL-CONTRIBUTORS-BADGE:END -->
<a href="https://packagist.org/packages/openmage/magento-lts"><img src="https://poser.pugx.org/openmage/magento-lts/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/openmage/magento-lts"><img src="https://poser.pugx.org/openmage/magento-lts/license.svg" alt="License"></a>
<br />
<a href="https://github.com/openmage/magento-lts/actions/workflows/security-php.yml"><img src="https://github.com/openmage/magento-lts/actions/workflows/security-php.yml/badge.svg" alt="PHP Security workflow Badge" /></a>
<a href="https://github.com/openmage/magento-lts/actions/workflows/composer.yml"><img src="https://github.com/openmage/magento-lts/actions/workflows/composer.yml/badge.svg" alt="Composer workflow Badge" /></a>
<a href="https://github.com/openmage/magento-lts/actions/workflows/syntax-php.yml"><img src="https://github.com/openmage/magento-lts/actions/workflows/syntax-php.yml/badge.svg" alt="PHP Syntax Check workflow badge" /></a>
<a href="https://github.com/openmage/magento-lts/actions/workflows/syntax-xml.yml"><img src="https://github.com/openmage/magento-lts/actions/workflows/syntax-xml.yml/badge.svg" alt="XML Syntax Check workflow badge" /></a>
<a href="https://github.com/openmage/magento-lts/actions/workflows/phpcs.yml"><img src="https://github.com/openmage/magento-lts/actions/workflows/phpcs.yml/badge.svg" alt="PHPCS workflow badge" /></a>
<a href="https://github.com/openmage/magento-lts/actions/workflows/php-cs-fixer.yml"><img src="https://github.com/openmage/magento-lts/actions/workflows/php-cs-fixer.yml/badge.svg" alt="PHP-CS-Fixer workflow badge" /></a>
<a href="https://github.com/openmage/magento-lts/actions/workflows/phpstan.yml"><img src="https://github.com/openmage/magento-lts/actions/workflows/phpstan.yml/badge.svg" alt="PHPStan workflow badge" /></a>
<a href="https://github.com/openmage/magento-lts/actions/workflows/sonar.yml"><img src="https://github.com/openmage/magento-lts/actions/workflows/sonar.yml/badge.svg" alt="Sonar workflow badge" /></a>
<a href="https://github.com/openmage/magento-lts/actions/workflows/phpunit.yml"><img src="https://github.com/openmage/magento-lts/actions/workflows/phpunit.yml/badge.svg" alt="PHPUnit workflow badge" /></a>
</p>

# Magento - Long Term Support

This repository is the home of an **unofficial** community-driven project. It's goal is to be a dependable alternative
to the Magento CE official releases which integrates improvements directly from the community while maintaining a high
level of backwards compatibility to the official releases.

**Pull requests with unofficial bug fixes and security patches from the community are encouraged and welcome!**

### Versioning

Though Magento does __not__ follow [Semantic Versioning](http://semver.org/) we aim to provide a workable system for
dependency definition. Each Magento `1.<minor>.<revision>` release will get its own branch (named `1.<minor>.<revision>.x`)
that will be independently maintained with upstream patches and community bug fixes for as long as it makes sense
to do so (based on available resources). For example, Magento version `1.9.4.5` was merged into the `1.9.4.x` branch.

## Requirements

- PHP 7.3+ (PHP 8.0 is supported)
- MySQL 5.6+ (8.0+ recommended) or MariaDB

__Please be aware that although OpenMage is compatible that one or more extensions may not be__

### Optional

- Redis 5+ (6.x recommended, latest verified compatible 6.0.7 with 20.x)

### PHP 7.2+
If using php 7.2+ then `mcrypt` needs to be disabled in `php.ini` or pecl to fallback on `mcryptcompat` and `phpseclib`. `mcrypt` is deprecated from 7.2+ onwards.

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
git pull origin main
git remote add upstream https://github.com/OpenMage/magento-lts
git pull upstream 1.9.4.x
git add -A && git commit
```

[More Information](http://openmage.github.io/magento-lts/install.html)

## Secure your installation

Don't use common paths like /admin for OpenMage Backend URL. Don't use the path in _robots.txt_ and keep it secret. You can change it from Backend (System / Configuration / Admin / Admin Base Url) or by editing _app/etc/local.xml_:

```xml
<config>
    <admin>
        <routers>
            <adminhtml>
                <args>
                    <frontName><![CDATA[admin]]></frontName>
                </args>
            </adminhtml>
        </routers>
    </admin>
</config>
```

Don't use common file names like api.php for OpenMage API URLs to prevent attacks. Don't use the new file name in _robots.txt_ and keep it secret with your partners. After renaming the file you must update the webserver configuration as follows:

* Apache .htaccess: `RewriteRule ^api/rest api.php?type=rest [QSA,L]`
* Nginx: `rewrite ^/api/(\w+).*$ /api.php?type=$1 last;`

## Changes

Most important changes will be listed here, all other changes since `19.4.0` can be found in
[release](https://github.com/OpenMage/magento-lts/releases) notes.

### Between Magento 1.9.4.5 and OpenMage 19.x

- bug fixes and PHP 7.x, 8.0 and 8.1 compatibility
- added config cache for system.xml [#1916](https://github.com/OpenMage/magento-lts/pull/1916)
- search for "NULL" in backend grids [#1203](https://github.com/OpenMage/magento-lts/pull/1203)
- removed modules `Mage_Compiler`, `Mage_GoogleBase`, `Mage_Xmlconnect`, `Phoenix_Moneybookers`

### Between OpenMage 19.4.18 / 20.0.16 and 19.4.19 / 20.0.17

- PHP extension `intl` is required

### Between OpenMage 19.x and 20.x

Do not use 20.x.x if you need IE support.

- removed IE conditional comments, IE styles, IE scripts and IE eot files [#1073](https://github.com/OpenMage/magento-lts/pull/1073)
- removed frontend default themes (default, modern, iphone, german, french, blank, blue) [#1600](https://github.com/OpenMage/magento-lts/pull/1600)
- fixed incorrect datetime in customer block (`$useTimezone` parameter) [#1525](https://github.com/OpenMage/magento-lts/pull/1525)
- added redis as a valid option for `global/session_save` [#1513](https://github.com/OpenMage/magento-lts/pull/1513)
- reduce needless saves by avoiding setting `_hasDataChanges` flag [#2066](https://github.com/OpenMage/magento-lts/pull/2066)
- removed support for `global/sales/old_fields_map` defined in XML [#921](https://github.com/OpenMage/magento-lts/pull/921)
- removed module `Mage_PageCache` [#2258](https://github.com/OpenMage/magento-lts/pull/2258)
- removed lib/flex containing unused ActionScript "file uploader" files [#2271](https://github.com/OpenMage/magento-lts/pull/2271)
- enabled website level config cache [#2355](https://github.com/OpenMage/magento-lts/pull/2355)

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

[Full list of events](docs/EVENTS.md)

### Changes to SOAP/WSDL

Since `19.4.17`/`20.0.15` we changed the `targetNamespace` of all the WSDL files (used in the API modules), from `Magento` to `OpenMage`.
If your custom modules extends OpenMage's APIs with a custom WSDL file and there are some hardcoded `targetNamespace="urn:Magento"` strings, your APIs may stop working.

Please replace all occurrences of 

```
targetNamespace="urn:Magento"
```
with
```
targetNamespace="urn:OpenMage"
```
or alternatively 
```
targetNamespace="urn:{{var wsdl.name}}"
```
 to avoid any problem.

To find which files need the modification you can run this command from the root directory of your project.
```
grep -rn 'urn:Magento' --include \*.xml
```

## Development Environment with ddev

- Install [ddev](https://ddev.com/get-started/)
- Clone the repository as described in installation ([Using Git](https://github.com/OpenMage/magento-lts#using-git))
- Create a ddev config, defaults should be good for you
  ```bash
  $ ddev config
  ```
- Open `.ddev/config.yaml` and change the php version to your needs
- Download and start the containers
  ```bash
  $ ddev start
  ```
- Open your site in browser
  ```bash
  $ ddev launch
  ```

## PhpStorm Factory Helper

This repo includes class maps for the core Magento files in `.phpstorm.meta.php`.
To add class maps for installed extensions, you have to install [N98-magerun](https://github.com/netz98/n98-magerun)
and run command:

```
n98-magerun.phar dev:ide:phpstorm:meta
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


