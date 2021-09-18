<?php
/**
 * @package     Gamuza_Mobile
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
 * These files are distributed with gamuza_mobile-magento at http://github.com/gamuzatech/.
 */

$installer = new Mage_Sales_Model_Resource_Setup ('mobile_setup');
$installer->startSetup ();

/**
 * Quote & Order
 */
$entities = array(
    'quote',
    'order',
);

$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'usigned'  => true,
    'nullable' => false,
    'visible'  => true,
    'required' => false,
);

foreach ($entities as $entity)
{
    $installer->addAttribute ($entity, Gamuza_Mobile_Helper_Data::ORDER_ATTRIBUTE_IS_APP, $options);
}

/**
 * Order Table
 */
$installer->getConnection ()->addColumn (
    $installer->getTable ('sales/order'),
    Gamuza_Mobile_Helper_Data::ORDER_ATTRIBUTE_CUSTOMER_STARS,
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'length'   => 1,
        'unsigned' => true,
        'nullable' => true,
        'comment'  => 'Customer Stars',
    )
);

/**
 * Order Grid
 */
$installer->getConnection ()->addColumn(
    $installer->getTable ('sales/order_grid'),
    'customer_stars',
    'tinyint(1) UNSIGNED DEFAULT NULL'
);

$this->getConnection ()->addKey(
    $this->getTable ('sales/order_grid'),
    'customer_stars',
    'customer_stars'
);

$select = $this->getConnection ()->select ();

$select->join(
    array ('order' => $this->getTable ('sales/order')),
    'order.entity_id = grid.entity_id',
    array ('customer_stars')
);

$this->getConnection()->query(
    $select->crossUpdateFromSelect(
        array ('grid' => $this->getTable ('sales/order_grid'))
    )
);

/**
 * Order Status History
 */
$installer->getConnection ()->addColumn(
    $installer->getTable ('sales/order_status_history'),
    'is_customer_rating',
    'tinyint(1) UNSIGNED DEFAULT NULL'
);

$installer->endSetup ();

