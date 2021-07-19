<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2018 Gamuza Technologies (http://www.gamuza.com.br/)
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

class Gamuza_Basic_Block_Adminhtml_Widget_Grid_Column_Renderer_Image
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function _getValue (Varien_Object $row)
    {
        $index = $this->getColumn ()->getIndex ();

        if (!$row->getData ($index) || !strcmp ($row->getData ($index), 'no_selection'))
        {
            return null;
        }

        $mediaUrl = Mage::app ()->getStore (Mage_Core_Model_App::ADMIN_STORE_ID)->getBaseUrl (Mage_Core_Model_Store::URL_TYPE_MEDIA, false);

        $mediaDir = $this->getColumn ()->getMedia ();

        $result = sprintf ("<img src='%s' width='75' />", sprintf ("%s%s%s", $mediaUrl, $mediaDir, $row->getData ($index)));

        return $result;
    }
}

