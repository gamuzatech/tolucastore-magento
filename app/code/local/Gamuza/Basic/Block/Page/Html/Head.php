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

/**
 * Html page block
 */
class Gamuza_Basic_Block_Page_Html_Head extends Mage_Page_Block_Html_Head
{
    /**
     * Getter for path to Favicon
     *
     * @return string
     */
    public function getFaviconFile()
    {
        return $this->getSkinUrl('favicon.png');
    }

    /**
     * Retrieve url of skins file
     *
     * @param   string $file path to file in skin
     * @param   array $params
     * @return  string
     */
    public function getSkinUrl($file = null, array $params = array())
    {
        $file = str_replace('favicon.ico', 'favicon.png', $file);

        return parent::getSkinUrl($file, $params);
    }

    /**
     * Add HEAD Item 'BEFORE'
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @return Mage_Page_Block_Html_Head
     */
    public function prependItem ($type, $name, $params = null, $if = null, $cond = null)
    {
        if ($type === 'skin_css' && empty ($params)) $params = 'media="all"';
        
        $_item ["$type/$name"] = array
        (
            'type'   => $type,
            'name'   => $name,
            'params' => $params,
            'if'     => $if,
            'cond'   => $cond,
        );
        
        $this->_data ['items'] = array_merge ($_item, $this->_data ['items']);
        
        return $this;
    }

    /**
     * Add HEAD Item 'AFTER'
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @return Mage_Page_Block_Html_Head
     */
    public function appendItem ($type, $name, $params = null, $if = null, $cond = null)
    {
        if ($type === 'skin_css' && empty ($params)) $params = 'media="all"';
        
        $_item ["$type/$name"] = array
        (
            'type'   => $type,
            'name'   => $name,
            'params' => $params,
            'if'     => $if,
            'cond'   => $cond,
        );
        
        $this->_data ['items'] = array_merge ($this->_data ['items'], $_item);
        
        return $this;
    }
}

