<img src="https://dl.dropboxusercontent.com/s/qi0b31um3y3nxo6/tolucastore-admin-panel.png" alt="TolucaStore Admin Panel"/>

## Table of features

- APP
- Chat Bot
- PDV
- PagCripto
- PicPay
- PagSeguro (Cartão e Boleto)
- OpenPix
- Correios

---

<p align="center">
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
<a href="#contributors-"><img src="https://img.shields.io/badge/all_contributors-151-orange.svg" alt="All Contributors"></a>
<!-- ALL-CONTRIBUTORS-BADGE:END -->
<a href="https://packagist.org/packages/openmage/magento-lts"><img src="https://poser.pugx.org/openmage/magento-lts/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/openmage/magento-lts"><img src="https://poser.pugx.org/openmage/magento-lts/license.svg" alt="License"></a>
<a href="https://github.com/openmage/magento-lts/actions/workflows/security-php.yml"><img src="https://github.com/openmage/magento-lts/actions/workflows/security-php.yml/badge.svg" alt="PHP Security workflow Badge" /></a>
<a href="https://github.com/OpenMage/magento-lts/actions/workflows/workflow.yml"><img src="https://github.com/OpenMage/magento-lts/actions/workflows/workflow.yml/badge.svg" alt="CI workflow Badge" /></a>
</p>

# Magento - Long Term Support

This repository is the home of an **unofficial** community-driven project. It's goal is to be a dependable alternative
to the Magento CE official releases which integrates improvements directly from the community while maintaining a high
level of backwards compatibility to the official releases.

**Pull requests with bug fixes and security patches from the community are encouraged and welcome!**

---

## Table of contents

- [Requirements](#requirements)
  - [Optional](#optional)
- [Installation](#installation)
  - [Composer](#composer)
  - [Git](#git)
- [Secure your installation](#secure-your-installation)
  - [Apache .htaccess](#apache-htaccess)
  - [Nginx](#nginx)
- [Changes](#changes)
  - [Between Magento 1.9.4.5 and OpenMage 19.x](#between-magento-1945-and-openmage-19x)
  - [Between OpenMage 19.4.18 / 20.0.16 and 19.4.19 / 20.0.17](#between-openmage-19418--20016-and-19419--20017)
  - [Since OpenMage 19.5.0 / 20.1.0](#since-openmage-1950--2010)
  - [New Config Options](#new-config-options)
  - [New Events](#new-events)
  - [Changes to SOAP/WSDL](#changes-to-soapwsdl)
- [Development Environment with ddev](#development-environment-with-ddev)
- [Development with PHP 8.1](#development-with-php-81)
- [PhpStorm Factory Helper](#phpstorm-factory-helper)
- [Versioning](#versioning)
- [Public Communication](#public-communication)
- [Maintainers](#maintainers)
- [License](#license)
- [Contributors](#contributors-)

## Requirements

- PHP 7.3+ (PHP 8.0 is supported, PHP 8.1 is work in progress)
- MySQL 5.6+ (8.0+ recommended) or MariaDB


- PHP extension `intl` <small>since 1.9.4.19 & 20.0.17</small>
- Command `patch` 2.7+ (or `gpatch` on MacOS/HomeBrew) <small>since 1.9.5.0 & 20.1.0</small>

__Please be aware that although OpenMage is compatible that one or more extensions may not be.__

### Optional

- Redis 5+ (6.x recommended, latest verified compatible 6.0.7 with 20.x)

## Installation

### Composer

Download the latest archive and extract it, clone the repo, or add a composer dependency to your existing project like so:

```bash
composer require "openmage/magento-lts":"^19.5.0"
```

To get the latest changes use:

```bash
composer require "openmage/magento-lts":"dev-main"
```

<small>Note: `dev-main` is just an alias for current `1.9.4.x` branch and may change</small>

### Git

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

### Apache .htaccess
```
RewriteRule ^api/rest api.php?type=rest [QSA,L]
```

### Nginx
```
rewrite ^/api/(\w+).*$ /api.php?type=$1 last;`
```

## Changes

Most important changes will be listed here, all other changes since `19.4.0` can be found in
[release](https://github.com/OpenMage/magento-lts/releases) notes.

### Between Magento 1.9.4.5 and OpenMage 19.x

- bug fixes and PHP 7.x, 8.0 and 8.1 compatibility
- added config cache for system.xml ([#1916](https://github.com/OpenMage/magento-lts/pull/1916))
- search for "NULL" in backend grids ([#1203](https://github.com/OpenMage/magento-lts/pull/1203))
- removed `lib/flex` containing unused ActionScript "file uploader" files ([#2271](https://github.com/OpenMage/magento-lts/pull/2271))
- Mage_Catalog_Model_Resource_Abstract::getAttributeRawValue() now returns `'0'` instead of `false` if the value stored in the database is `0` ([#572](https://github.com/OpenMage/magento-lts/pull/572))
- removed modules:
  - `Mage_Backup` ([#2811](https://github.com/OpenMage/magento-lts/pull/2811))
  - `Mage_Compiler`
  - `Mage_GoogleBase`
  - `Mage_PageCache` ([#2258](https://github.com/OpenMage/magento-lts/pull/2258))
  - `Mage_Xmlconnect`
  - `Phoenix_Moneybookers`

_If you rely on those modules you can reinstall them with composer:_
- `Mage_Backup`: `composer require openmage/module-mage-backup`
- `Mage_PageCache`: `composer require openmage/module-mage-pagecache`

### Between OpenMage 19.4.18 / 20.0.16 and 19.4.19 / 20.0.17

- PHP extension `intl` is required

### Between OpenMage 19.x and 20.x

Do not use 20.x.x if you need IE support.

- removed IE conditional comments, IE styles, IE scripts and IE eot files ([#1073](https://github.com/OpenMage/magento-lts/pull/1073))
- removed frontend default themes (default, modern, iphone, german, french, blank, blue) ([#1600](https://github.com/OpenMage/magento-lts/pull/1600))
- fixed incorrect datetime in customer block (`$useTimezone` parameter) ([#1525](https://github.com/OpenMage/magento-lts/pull/1525))
- added redis as a valid option for `global/session_save` ([#1513](https://github.com/OpenMage/magento-lts/pull/1513))
- reduce needless saves by avoiding setting `_hasDataChanges` flag ([#2066](https://github.com/OpenMage/magento-lts/pull/2066))
- removed support for `global/sales/old_fields_map` defined in XML ([#921](https://github.com/OpenMage/magento-lts/pull/921))
- enabled website level config cache ([#2355](https://github.com/OpenMage/magento-lts/pull/2355))
- make overrides of Mage_Core_Model_Resource_Db_Abstract::delete respect parent api ([#1257](https://github.com/OpenMage/magento-lts/pull/1257))

For full list of changes, you can [compare tags](https://github.com/OpenMage/magento-lts/compare/1.9.4.x...20.0).

### Since OpenMage 19.5.0 / 20.1.0

Most of the 3rd party libraries/modules that were bundled in our repository were removed and migrated to composer dependencies.
This allows for better maintenance and upgradability.

Specifically:
- `phpseclib`, `mcrypt_compat`, `Cm_RedisSession`, `Cm_Cache_Backend_Redis`, `Pelago_Emogrifier` ([#2411](https://github.com/OpenMage/magento-lts/pull/2411))
- Zend Framework 1 ([#2827](https://github.com/OpenMage/magento-lts/pull/2827))

If your project uses OpenMage through composer then all dependencies will be managed automatically.  
If you just extracted the release zip/tarball in your project's main folder then be sure to:
- remove the old copy of aforementioned libraries from your project, you can do that with this command:
  ```bash
  rm -rf app/code/core/Zend lib/Cm lib/Credis lib/mcryptcompat lib/Pelago lib/phpseclib lib/Zend
  ```

- download the new release zip file that is named `openmage-VERSIONNUMBER.zip`, this one is built to contain the `vendor`
  folder generated by composer, with all the dependencies in it
- extract the zip file in your project's repository as you always did

We also decided to remove our Zend_DB patches (that were stored in `app/code/core/Zend`) because they were very old and
not compatible with the new implementations made by ZF1-Future, which is much more advanced and feature rich.
This may generate a problem with `Zend_Db_Select' statements that do not use 'Zend_Db_Expr' to quote expressions.
If you see SQL errors after upgrading please remember to check for this specific issue in your code.

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
```bash
grep -rn 'urn:Magento' --include \*.xml
```

## Development Environment with DDEV

- Install [ddev](https://ddev.com/get-started/)
- Clone the repository as described in installation ([Git](#git))
- Create a ddev config, defaults should be good for you
  ```bash
  ddev config
  ```
- Open `.ddev/config.yaml` and change the php version to your needs
- Download and start the containers
  ```bash
  ddev start
  ```
- Open your site in browser
  ```bash
  ddev launch
  ```

## Development with PHP 8.1

Deprecation errors are supressed by default.

If you want to work on PHP 8.1 support, set environment variable `DEV_PHP_STRICT` to `1`, to show all errors.  

## PhpStorm Factory Helper

This repo includes class maps for the core Magento files in `.phpstorm.meta.php`.
To add class maps for installed extensions, you have to install [N98-magerun](https://github.com/netz98/n98-magerun)
and run command:

```bash
n98-magerun.phar dev:ide:phpstorm:meta
```

You can add additional meta files in this directory to cover your own project files. See
[PhpStorm advanced metadata](https://www.jetbrains.com/help/phpstorm/ide-advanced-metadata.html)
for more information.

## Versioning

Though Magento does __not__ follow [Semantic Versioning](http://semver.org/) we aim to provide a workable system for
dependency definition.

## Public Communication

* [Toluca Store](https://t.me/tolucastore) (Telegram)
* [Gamuza Technologies](https://t.me/gamuzatech) (Telegram)

## Developers

* [Enéias Ramos de Melo](https://github.com/eneiasramos)

## Maintainers

* [Daniel Fahlke](https://github.com/Flyingmana)
* [David Robinson](https://github.com/drobinson)
* [Fabrizio Balliano](https://github.com/fballiano)
* [Lee Saferite](https://github.com/LeeSaferite)
* [Mohamed Elidrissi](https://github.com/elidrissidev)
* [Ng Kiat Siong](https://github.com/kiatng)
* [Sven Reichel](https://github.com/sreichel)
* [Tymoteusz Motylewski](https://github.com/tmotyl)

## License

- [OSL v3.0](http://opensource.org/licenses/OSL-3.0)
- [AFL v3.0](http://opensource.org/licenses/AFL-3.0)


