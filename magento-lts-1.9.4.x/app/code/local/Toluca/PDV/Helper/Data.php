<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ITEM_TABLE    = 'toluca_pdv_item';
    const OPERATOR_TABLE = 'toluca_pdv_operator';
    const HISTORY_TABLE = 'toluca_pdv_history';

    const ORDER_ATTRIBUTE_IS_PDV = 'is_pdv';
    const ORDER_ATTRIBUTE_PDV_ID = 'pdv_id';
    const ORDER_ATTRIBUTE_OPERATOR_ID = 'operator_id';

    const ITEM_STATUS_CLOSED = 0;
    const ITEM_STATUS_OPENED = 1;

    const HISTORY_TYPE_OPEN      = 'open';
    const HISTORY_TYPE_REINFORCE = 'reinforce';
    const HISTORY_TYPE_BLEED     = 'bleed';
    const HISTORY_TYPE_MONEY     = 'money';
    const HISTORY_TYPE_CHANGE    = 'change';
    const HISTORY_TYPE_ORDER     = 'order';
    const HISTORY_TYPE_CLOSE     = 'close';

    const XML_PATH_PDV_PAYMENT_METHOD_CASHONDELIVERY = 'pdv/payment_method/cashondelivery';

    public function getTotals (Mage_Adminhtml_Block_Widget_Grid $grid)
    {
        $fieldsTotals = $grid->_fieldsTotals;

        foreach ($grid->getCollection () as $item)
        {
            foreach ($fieldsTotals as $id => $value)
            {
                $fieldsTotals [$id] += floatval ($item->getData ($id));
            }
        }

        $object = new Varien_Object ();
        $object->addData ($fieldsTotals);

        return $object;
    }
}

