<?php
/**
 * @package     Toluca_Comanda
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

$installer = $this;
$installer->startSetup ();

function addComandaItemTable ($installer, $model, $comment)
{
    $table = $installer->getTable ($model);

    $sqlBlock = <<< SQLBLOCK
CREATE TABLE IF NOT EXISTS {$table} (
    entity_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Entity ID',
    PRIMARY KEY(entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='{$comment}';
SQLBLOCK;

    $installer->run ($sqlBlock);

    $installer->getConnection ()
        ->addColumn ($table, 'mesa_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Mesa ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'order_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => true,
            'comment'  => 'Order ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'order_increment_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => true,
            'comment'  => 'Order Increment ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'product_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Product ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'sku', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'Sku',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'name', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'Name',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'price', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '12,4',
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Price',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'qty', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Qty',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'total', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '12,4',
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Total',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'options', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'comment'  => 'Options',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'additional_options', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'comment'  => 'Additional Options',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'super_attribute', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'comment'  => 'Super Attribute',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'bundle_option', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'comment'  => 'Bundle Option',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'created_at', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'nullable' => false,
            'comment'  => 'Created At',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'updated_at', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'nullable' => true,
            'comment'  => 'Updated At',
        ));
}

addComandaItemTable ($installer, Toluca_Comanda_Helper_Data::ITEM_TABLE, 'Toluca Comanda Item');

$installer->endSetup ();

