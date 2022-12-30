<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

$installer = $this;
$installer->startSetup ();

function addPDVHistoryTable ($installer, $model, $comment)
{
    $table = $installer->getTable ($model);

    $sqlBlock = <<< SQLBLOCK
CREATE TABLE IF NOT EXISTS {$table}
(
    entity_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='{$comment}';
SQLBLOCK;

    $installer->run ($sqlBlock);

    $installer->getConnection ()
        ->addColumn ($table, 'cashier_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Cashier ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'operator_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Operator ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'customer_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Customer ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'type_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Type ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'order_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Order ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'order_increment_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'Order Increment ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'payment_method', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Payment Method',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'amount', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '12,4',
            'nullable' => false,
            'comment'  => 'Amount',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'message', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => false,
            'comment'  => 'Message',
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

addPDVHistoryTable ($installer, Toluca_PDV_Helper_Data::HISTORY_TABLE, 'Toluca PDV History');

$installer->endSetup ();

