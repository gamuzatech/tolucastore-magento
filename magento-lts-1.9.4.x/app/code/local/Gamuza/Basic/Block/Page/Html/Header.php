<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Html page block
 */
class Gamuza_Basic_Block_Page_Html_Header extends Mage_Page_Block_Html_Header
{
    private $_logo = null;

    public function _construct()
    {
        parent::_construct();

        $media = Mage::getBaseDir('media') . DS . 'store' . DS . 'info';
        $file  = Mage::getStoreConfig('general/store_information/logo');

        if (is_file($media . DS . $file))
        {
            $this->_logo = Mage::getBaseUrl('media') . 'store/info/' . $file;
        }
    }

    /**
     * @return string
     */
    public function getLogoSrc()
    {
        if (!empty($this->_logo))
        {
            return $this->_logo;
        }

        return parent::getLogoSrc();
    }

    /**
     * @return string
     */
    public function getLogoSrcSmall()
    {
        if (!empty($this->_logo))
        {
            return $this->_logo;
        }

        return parent::getLogoSrcSmall();
    }
}

