<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

$installer = $this;
$installer->startSetup ();

function addPDVCashierTable ($installer, $model, $comment)
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
        ->addColumn ($table, 'operator_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Operator ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'total_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Total ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'code', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'Code',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'name', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'Name',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'is_active', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Is Active',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'status', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Status',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'open_amount', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '12,4',
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Open Amount',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'reinforce_amount', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '12,4',
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Reinforce Amount',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'bleed_amount', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '12,4',
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Bleed Amount',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'money_amount', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '12,4',
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Money Amount',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'change_amount', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '12,4',
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Change Amount',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'close_amount', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '12,4',
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Close Amount',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'opened_at', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'nullable' => true,
            'comment'  => 'Opened At',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'closed_at', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'nullable' => true,
            'comment'  => 'Closed At',
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

addPDVCashierTable ($installer, Toluca_PDV_Helper_Data::CASHIER_TABLE, 'Toluca PDV Cashier');

$installer->endSetup ();

