<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2021 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Order model
 */
class Gamuza_Basic_Model_Sales_Order extends Mage_Sales_Model_Order
{
    /**
     * Order statuses
     */
    const STATUS_PENDING   = 'pending';
    const STATUS_PAID      = 'paid';
    const STATUS_PREPARING = 'preparing';
    const STATUS_SHIPPED   = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_REFUNDED  = 'refunded';
    const STATUS_CANCELED  = 'canceled';
}

