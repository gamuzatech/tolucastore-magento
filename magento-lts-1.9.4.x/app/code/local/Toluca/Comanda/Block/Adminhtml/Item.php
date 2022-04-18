<?php
/**
 * @package     Toluca_Comanda
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_Comanda_Block_Adminhtml_Item extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct ()
	{
	    $this->_blockGroup = 'comanda';
	    $this->_controller = 'adminhtml_item';

	    $this->_headerText = Mage::helper ('comanda')->__('Items Manager');
        $this->_addButtonLabel = Mage::helper ('comanda')->__('Add New Item');

	    parent::__construct();

        $this->_removeButton ('add');

        $this->_addBackButton ();
	}

    public function getBackUrl()
    {
        return $this->getUrl('*/adminhtml_mesa/index');
    }
}

