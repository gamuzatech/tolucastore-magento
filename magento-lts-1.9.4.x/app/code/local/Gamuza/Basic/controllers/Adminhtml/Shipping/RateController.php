<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
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

class Gamuza_Basic_Adminhtml_Shipping_RateController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed ()
    {
        return Mage::getSingleton ('admin/session')->isAllowed ('admin/shipping/rates');
    }

    protected function _initAction ()
    {
        $this->loadLayout ()->_setActiveMenu ('shipping/rates')
            ->_addBreadcrumb(
                Mage::helper ('adminhtml')->__('Shipping Rates Manager'),
                Mage::helper ('adminhtml')->__('Shipping Rates Manager')
            )
        ;

        return $this;
    }

    public function indexAction ()
    {
        $this->_title ($this->__('Shipping'));
        $this->_title ($this->__('Manage Shipping Rates'));

        $this->_initAction ();

        $this->renderLayout ();
    }

    /**
     * Export shipping rates grid to CSV format
     */
    public function exportCsvAction ()
    {
        $fileName   = 'shippingrates.csv';

        $content    = $this->getLayout ()
            ->createBlock ('basic/adminhtml_shipping_rate_grid')
            ->getCsvFile ()
        ;

        $this->_prepareDownloadResponse ($fileName, $content);
    }
}

