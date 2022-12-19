<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ITEM_TABLE = 'toluca_pdv_item';
    const USER_TABLE = 'toluca_pdv_user';

    const ORDER_ATTRIBUTE_IS_PDV = 'is_pdv';
    const ORDER_ATTRIBUTE_PDV_ID = 'pdv_id';

    const STATUS_CLOSED = 0;
    const STATUS_OPENED = 1;

    const XML_PATH_PDV_PAYMENT_METHOD_CASHONDELIVERY = 'pdv/payment_method/cashondelivery';
}

