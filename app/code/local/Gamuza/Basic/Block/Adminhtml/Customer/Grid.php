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

class Gamuza_Basic_Block_Adminhtml_Customer_Grid
    extends Mage_Adminhtml_Block_Customer_Grid
{
    protected $_isExport = true;

    protected function _prepareCollection ()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->addAttributeToSelect ('taxvat')
            ->addAttributeToSelect ('dob')
            ->addAttributeToSelect ('gender')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_fax', 'customer_address/fax', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $result = parent::_prepareColumns();

        $this->removeColumn ('Telephone');
        $this->removeColumn ('billing_country_id');

        $this->addColumnAfter('billing_city', array(
            'header'    => Mage::helper('customer')->__('City'),
            'width'     => '150',
            'index'     => 'billing_city',
        ), 'billing_postcode');

        $this->addColumnAfter('billing_fax', array(
            'header'    => Mage::helper('customer')->__('Fax'),
            'width'     => '100',
            'index'     => 'billing_fax',
        ), 'email');

        $this->addColumnAfter ('taxvat', array(
            'header'    => Mage::helper ('customer')->__('Taxvat'),
            'width'     => '100',
            'index'     => 'taxvat',
        ), 'billing_fax');

        $this->addColumnAfter ('dob', array(
            'header'    => Mage::helper ('customer')->__('Date of Birth'),
            'width'     => '100',
            'index'     => 'dob',
            'type'      => 'date',
            'time'      => false,
            'format'    => Mage::app ()->getLocale ()->getDateFormat (Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'filter_index' => 'dob',
            'filter_condition_callback' => array ($this, '_dobFilterConditionCallback'),
        ), 'taxvat');

        $this->addColumnAfter ('gender', array(
            'header'    => Mage::helper ('customer')->__('Gender'),
            'width'     => '100',
            'index'     => 'gender',
            'type'      => 'options',
            'options'   => Mage::getModel ('basic/adminhtml_system_config_source_customer_gender')->toOptionArray (),
        ), 'dob');

        $this->sortColumnsByOrder();

        return $result;
    }

    protected function _dobFilterConditionCallback ($collection, $column)
    {
        $value = $column->getFilter ()->getValue ();

        if (!empty ($value))
        {
            $from = $value ['from'];
            $to   = $value ['to'];

            if (isset ($from) && $from instanceof Zend_Date)
            {
                $this->getCollection ()->addAttributeToFilter ($column->getFilterIndex (), array ('gteq' => $from->get('YYYY-MM-dd')));
            }

            if (isset ($to) && $to instanceof Zend_Date)
            {
                $this->getCollection ()->addAttributeToFilter ($column->getFilterIndex (), array ('lteq' => $to->get('YYYY-MM-dd')));
            }
        }

        return $this;
    }
}

