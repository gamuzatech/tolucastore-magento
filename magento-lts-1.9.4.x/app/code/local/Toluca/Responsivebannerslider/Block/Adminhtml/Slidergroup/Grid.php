<?php
/*
 * @package     Toluca_Responsivebannerslider
 * @copyright   Copyright (c) 2023 Gamuza Technologies (https://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_Responsivebannerslider_Block_Adminhtml_Slidergroup_Grid
    extends Magebees_Responsivebannerslider_Block_Adminhtml_Slidergroup_Grid
{
    protected function _prepareColumns()
    {
        $result = parent::_prepareColumns();

        $this->addColumn(
            'status', array(
                'header'    => Mage::helper('responsivebannerslider')->__('Status'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => array(
                    1 => Mage::helper('responsivebannerslider')->__('Enabled'),
                    0 => Mage::helper('responsivebannerslider')->__('Disabled'),
                ),
            )
        );

        return $result;
    }

    public function getRowUrl($row)  
    {
        // nothing
    }
}

