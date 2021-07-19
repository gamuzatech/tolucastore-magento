<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2016 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA 02110-1301, USA.
 */

/**
 * See the AUTHORS file for a list of people on the Gamuza Team.
 * See the ChangeLog files for a list of changes.
 * These files are distributed with gamuza_basic-magento at http://github.com/gamuzatech/.
 */

class Gamuza_Basic_Block_Adminhtml_Customer_Edit_Tabs
    extends Mage_Adminhtml_Block_Customer_Edit_Tabs
{
    protected function _beforeToHtml()
    {
/*
        if (Mage::registry('current_customer')->getId()) {
            $this->addTab('view', array(
                'label'     => Mage::helper('customer')->__('Customer View'),
                'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_view')->toHtml(),
                'active'    => true
            ));
        }
*/
        $this->addTab('account', array(
            'label'     => Mage::helper('customer')->__('Account Information'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_account')->initForm()->toHtml(),
            'active'    => Mage::registry('current_customer')->getId() ? false : true
        ));

        $this->addTab('addresses', array(
            'label'     => Mage::helper('customer')->__('Addresses'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_addresses')->initForm()->toHtml(),
        ));

        // load: Orders, Shopping Cart - with ajax

        if (Mage::registry('current_customer')->getId())
        {
            if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view'))
            {
                $this->addTab('orders', array(
                    'label'     => Mage::helper('customer')->__('Orders'),
                    'class'     => 'ajax',
                    'url'       => $this->getUrl('*/*/orders', array('_current' => true)),
                 ));
            }

            $this->addTab('cart', array(
                'label'     => Mage::helper('customer')->__('Shopping Cart'),
                'class'     => 'ajax',
                'url'       => $this->getUrl('*/*/carts', array('_current' => true)),
            ));
        }

        $this->removeTab('agreements');

        $this->_updateActiveTab();

        Varien_Profiler::stop('customer/tabs');

        return Mage_Adminhtml_Block_Widget_Tabs::_beforeToHtml();
    }
}

