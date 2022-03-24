<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

$installer = $this;
$installer->startSetup ();

function addMercadoLivreProductsTable ($installer, $model, $description)
{
    $table = $installer->getTable ($model);

    $sqlBlock = <<< SQLBLOCK
CREATE TABLE IF NOT EXISTS {$table}
(
    entity_id int(11) unsigned NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='{$description}';
SQLBLOCK;

    $installer->run ($sqlBlock);

    $installer->getConnection ()
        ->addColumn ($table, 'product_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Product ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'external_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => true,
            'comment'  => 'External ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'seller_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => true,
            'comment'  => 'Seller ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'category_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => true,
            'comment'  => 'Category ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'category_name', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => true,
            'comment'  => 'Category Name',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'status', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment' => 'Status'
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'message', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'comment' => 'Message'
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'updated_at', array(
            'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'nullable' => false,
            'comment'  => 'Updated At',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'synced_at', array(
            'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'nullable' => true,
            'comment'  => 'Synced At'
        ));
}

addMercadoLivreProductsTable ($installer, Gamuza_MercadoLivre_Helper_Data::PRODUCT_TABLE, 'Gamuza MercadoLivre Product');

$installer->endSetup ();

