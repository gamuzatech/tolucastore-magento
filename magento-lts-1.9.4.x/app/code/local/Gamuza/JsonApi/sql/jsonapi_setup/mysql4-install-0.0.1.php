<?php
/*
 * Gamuza JSON API - Fast API for magento platform.
 * Copyright (c) 2016 Gamuza Technologies (http://www.gamuza.com.br/)
 * Author: Eneias Ramos de Melo <eneias@gamuza.com.br>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA 02110-1301, USA.
 */

/*
 * See the AUTHORS file for a list of people on the Gamuza Team.
 * See the ChangeLog files for a list of changes.
 * These files are distributed with gamuza_jsonapi-magento at http://github.com/gamuzabrasil/.
 */

$installer = $this;
$installer->startSetup();

$coreConfig = Mage::getModel ('core/config');

$coreConfig->saveConfig ('api/config/charset',             'UTF-8');
$coreConfig->saveConfig ('api/config/session_timeout',     '86400');
$coreConfig->saveConfig ('api/config/compliance_wsi',      '1');
$coreConfig->saveConfig ('api/config/wsdl_cache_enabled',  '1');
$coreConfig->saveConfig ('api/json/enabled',               '1');
$coreConfig->saveConfig ('api/json/cache_enabled',         '0');
$coreConfig->saveConfig ('api/json/cache_lifetime',        '3600');
$coreConfig->saveConfig ('api/json/map_enabled',           '0');

$installer->endSetup();

