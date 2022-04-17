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
            ->addFieldToFilter ('is_active', array ('eq' => true))
        ;

        foreach ($collection as $mesa)
        {
            $result = array(
                'entity_id'   => intval ($mesa->getId ()),
                'name'        => $mesa->getName (),
                'description' => $mesa->getDescription () ?: null,
                'is_active'   => boolval ($mesa->getIsActive ()),
                'status'      => $mesa->getStatus (),
                'created_at'  => $mesa->getCreatedAt (),
                'updated_at'  => $mesa->getUpdatedAt (),
            );
        }

        return $result;
    }
}

