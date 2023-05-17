<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2023 Gamuza Technologies (https://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Customer model
 */
class Gamuza_Basic_Model_Customer_Customer
    extends Mage_Customer_Model_Customer
{
    /**#@+
     * Codes of exceptions related to customer model
     */
    public const EXCEPTION_CELLPHONE_EXISTS = 6;
}

