<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2018 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
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

/**
 * See the AUTHORS file for a list of people on the Gamuza Team.
 * See the ChangeLog files for a list of changes.
 * These files are distributed with gamuza_basic-magento at http://github.com/gamuzatech/.
 */

/**
 * admin product edit tabs
 */
class Gamuza_Basic_Block_Adminhtml_Catalog_Product_Edit_Tabs
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    const CATALOG_PRODUCT_GROUP_ID_9  = 'group_9';
    const CATALOG_PRODUCT_GROUP_ID_11 = 'group_11';
    const CATALOG_PRODUCT_GROUP_ID_12 = 'group_12';
    const CATALOG_PRODUCT_GROUP_ID_17 = 'group_17';

    protected function _prepareLayout ()
    {
        $result = parent::_prepareLayout ();

        $this->removeTab (self::CATALOG_PRODUCT_GROUP_ID_9);
        $this->removeTab (self::CATALOG_PRODUCT_GROUP_ID_11);
        $this->removeTab (self::CATALOG_PRODUCT_GROUP_ID_12);
        $this->removeTab (self::CATALOG_PRODUCT_GROUP_ID_17);

        $this->removeTab ('related');
        $this->removeTab ('upsell');
        $this->removeTab ('crosssell');

        return $result;
    }
}

