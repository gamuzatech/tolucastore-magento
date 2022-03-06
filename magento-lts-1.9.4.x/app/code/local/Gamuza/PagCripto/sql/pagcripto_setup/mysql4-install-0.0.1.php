<?php
/**
 * @package     Gamuza_PagCripto
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
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
 * These files are distributed with gamuza_pagcripto-magento at http://github.com/gamuzatech/.
 */

$installer = new Mage_Sales_Model_Resource_Setup ('pagcripto_setup');
$installer->startSetup ();

$entities = array(
    'sales/quote_payment',
    'sales/order_payment',
);

foreach ($entities as $entity)
{
    $table = $installer->getTable ($entity);

    $installer->getConnection ()
        ->addColumn ($table, Gamuza_PagCripto_Helper_Data::PAYMENT_ATTRIBUTE_PAGCRIPTO_CURRENCY, array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'PagCripto Currency',
        ));
    $installer->getConnection ()
        ->addColumn ($table, Gamuza_PagCripto_Helper_Data::PAYMENT_ATTRIBUTE_PAGCRIPTO_ADDRESS, array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'PagCripto Address',
        ));
    $installer->getConnection ()
        ->addColumn ($table, Gamuza_PagCripto_Helper_Data::PAYMENT_ATTRIBUTE_PAGCRIPTO_STATUS, array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'PagCripto Status',
        ));
    $installer->getConnection ()
        ->addColumn ($table, Gamuza_PagCripto_Helper_Data::PAYMENT_ATTRIBUTE_PAGCRIPTO_CONFIRMATIONS, array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'PagCripto Confirmations',
        ));
    $installer->getConnection ()
        ->addColumn ($table, Gamuza_PagCripto_Helper_Data::PAYMENT_ATTRIBUTE_PAGCRIPTO_RECEIVED_AMOUNT, array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '16,8',
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'PagCripto Received Amount',
        ));
    $installer->getConnection ()
        ->addColumn ($table, Gamuza_PagCripto_Helper_Data::PAYMENT_ATTRIBUTE_PAGCRIPTO_PAYMENT_REQUEST, array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'PagCripto Payment Request',
        ));
}

$entities = array(
    'sales/quote_payment',
    'sales/order_payment',
);

foreach ($entities as $entity)
{
    $table = $installer->getTable ($entity);

    $installer->getConnection ()
        ->addColumn ($table, Gamuza_PagCripto_Helper_Data::PAYMENT_ATTRIBUTE_PAGCRIPTO_AMOUNT, array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '16,8',
            'unsigned' => true,
            'nullable' => true,
            'comment'  => 'PagCripto Amount',
        ));
}

$installer->endSetup ();

