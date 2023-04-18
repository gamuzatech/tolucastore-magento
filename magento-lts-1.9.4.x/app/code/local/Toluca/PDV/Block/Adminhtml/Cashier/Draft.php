<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2023 Gamuza Technologies (https://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Block_Adminhtml_Cashier_Draft extends Mage_Adminhtml_Block_Template
{
    private $_wordwrap = 48; // default

    public function __construct ()
    {
        parent::__construct ();

        $this->_wordwrap = intval (Mage::getStoreConfig ('sales/order_draft/word_wrap'));
    }

    public function getWordWrap ()
    {
        return $this->_wordwrap;
    }

    public function getLineSeparator ($title = null)
    {
        $result = str_repeat ('-', $this->_wordwrap);

        if (empty ($title))
        {
            return $result;
        }

        $title = sprintf (' %s ', $title);

        $position = strlen ($result) / 2 - strlen ($title) / 2;

        for ($i = 0; $i < strlen ($title); $i ++)
        {
            $result [$i + $position] = $title [$i];
        }

        return $result;
    }
}

