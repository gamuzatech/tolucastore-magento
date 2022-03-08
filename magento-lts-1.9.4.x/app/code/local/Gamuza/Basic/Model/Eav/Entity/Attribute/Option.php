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

/**
 * Emtity attribute option model
 */
class Gamuza_Basic_Model_Eav_Entity_Attribute_Option
    extends Mage_Eav_Model_Entity_Attribute_Option
{
    protected function _afterLoad ()
    {
        parent::_afterLoad ();

        $item = $this->_getStoreValue ();

        if ($item && $item->getId ())
        {
            $this->setStoreId ($item->getStoreId ());
            $this->setvalue ($item->getValue ());
        }
    }

    protected function _afterSave ()
    {
        parent::_afterSave ();

        $item = $this->_getStoreValue ()
            ->setOptionId ($this->getId ())
            ->setStoreId ($this->getStoreId ())
            ->setValue ($this->getValue ())
            ->save ()
        ;

        return $this;
    }

    protected function _afterDelete ()
    {
        parent::_afterDelete ();

        $collection = Mage::getModel ('basic/eav_entity_attribute_option_value')->getCollection ()
            ->addFieldToFilter ('option_id', array ('eq' => $this->getId ()))
        ;

        foreach ($collection as $value)
        {
            $value->delete ();
        }
    }

    private function _getStoreValue ()
    {
        $collection = Mage::getModel ('basic/eav_entity_attribute_option_value')->getCollection ()
            ->addFieldToFilter ('option_id', array ('eq' => $this->getId ()))
            ->addFieldToFilter ('store_id', array ('eq' => Mage_Core_Model_App::ADMIN_STORE_ID))
        ;

        return $collection->getFirstItem ();
    }
}

