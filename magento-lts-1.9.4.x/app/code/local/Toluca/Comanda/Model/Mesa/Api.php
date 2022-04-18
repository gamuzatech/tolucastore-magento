<?php
/**
 * @package     Toluca_Comanda
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Mesa API
 */
class Toluca_Comanda_Model_Mesa_Api extends Mage_Api_Model_Resource_Abstract
{
    public function items ()
    {
        $result = array ();

        $collection = Mage::getModel ('comanda/mesa')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => '1'))
        ;

        $collection->getSelect ()
            ->joinLeft(
                array ('item' => Mage::getSingleton ('core/resource')->getTablename ('comanda/item')),
                'main_table.entity_id = item.mesa_id',
                array (
                    'items_qty'   => 'COUNT(item.qty)',
                    'items_total' => 'SUM(item.total)',
                )
            )
            ->where ('item.order_id IS NULL')
            ->group ('main_table.entity_id')
        ;

        foreach ($collection as $mesa)
        {
            $result [] = array(
                'entity_id'   => intval ($mesa->getId ()),
                'name'        => $mesa->getName (),
                'description' => $mesa->getDescription (),
                'is_active'   => boolval ($mesa->getIsActive ()),
                'status'      => $mesa->getStatus (),
                'items_qty'   => intval ($mesa->getItemsQty ()),
                'items_total' => floatval ($mesa->getItemsTotal ()),
                'created_at'  => $mesa->getCreatedAt (),
                'updated_at'  => $mesa->getUpdatedAt (),
            );
        }

        return $result;
    }
}

