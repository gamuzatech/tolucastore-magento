<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Block_Adminhtml_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct ()
    {
        $this->_controller = 'adminhtml_product';
        $this->_blockGroup = 'mercadolivre';
        $this->_headerText = Mage::helper ('mercadolivre')->__('Products Manager');

        parent::__construct ();

        $this->_removeButton ('add');
    }
}

