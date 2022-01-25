<?php
/**
 * @package     Gamuza_Store
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

class Gamuza_Basic_Block_Customer_Account_Navigation
    extends Mage_Customer_Block_Account_Navigation
{
    protected $_appendLinks = array ();

    public function removeLinkByName ($name)
    {
        unset ($this->_links [$name]);

        return $this;
    }

    public function appendLink ($name, $path, $label, $urlParams = array ())
    {
        $this->_appendLinks [$name] = new Varien_Object (array(
            'name'  => $name,
            'path'  => $path,
            'label' => $label,
            'url'   => $this->getUrl ($path, $urlParams),
        ));

        return $this;
    }

    public function getLinks ()
    {
        return array_merge ($this->_links, $this->_appendLinks);
    }
}

