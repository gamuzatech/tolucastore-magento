<?php
/**
 * @package     Gamuza_PagCripto
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
 * These files are distributed with gamuza_PagCripto-magento at http://github.com/gamuzatech/.
 */

class Gamuza_PagCripto_Model_Cron_Abstract
{
    public function __construct ()
    {
        Mage::app ()->getTranslator ()->init (Mage_Core_Model_App_Area::AREA_ADMINHTML, true);

        if (!$this->isCli ()) $this->message ('<pre>');

        $this->message ('+ Begin : ' . strftime ('%c'));

        static::_construct ();
    }

    public function _construct () {}

    public function __destruct ()
    {
        $this->message ('= End : ' . strftime ('%c'));

        if (!$this->isCli ()) $this->message ('</pre>');
    }

    protected function getHelper ()
    {
        return Mage::helper ('pagcripto');
    }

    protected function getStoreConfig ($key)
    {
        return $this->getHelper ()->getStoreConfig ($key);
    }

    protected function isCli ()
    {
        if (!strcmp (php_sapi_name (), 'cli') && empty ($_SERVER ['REMOTE_ADDR']))
        {
            return true;
        }
    }

    protected function message ($text)
    {
        Mage::log ($text, null, Gamuza_PagCripto_Helper_Data::LOG, false);
    }
}

