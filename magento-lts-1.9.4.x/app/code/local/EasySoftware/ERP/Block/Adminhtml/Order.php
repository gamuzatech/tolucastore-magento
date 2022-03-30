<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct ()
	{
	    $this->_blockGroup = 'erp';
	    $this->_controller = 'adminhtml_order';

	    $this->_headerText = Mage::helper ('erp')->__('Orders Manager');

	    parent::__construct();

        $this->_removeButton ('add');
	}
}

