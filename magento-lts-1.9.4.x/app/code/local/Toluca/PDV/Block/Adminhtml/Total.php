<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2023 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Block_Adminhtml_Total extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct ()
	{
	    $this->_blockGroup = 'pdv';
	    $this->_controller = 'adminhtml_total';

	    $this->_headerText = Mage::helper ('pdv')->__('Totals Manager');

	    parent::__construct();

        $this->_removeButton ('add');
	}
}

