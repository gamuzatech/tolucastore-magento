<?php
/**
 * @package     Toluca_Express
 * @copyright   Copyright (c) 2023 Gamuza Technologies (https://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_Express_Controller_Router_Express
    extends Mage_Core_Controller_Varien_Router_Standard
{
    public function match (Zend_Controller_Request_Http $request)
    {
        if (strpos ($request->getPathInfo (), '/express') !== false)
        {
            $request->setModuleName ('express')
                ->setControllerName ('index')
                ->setActionName ('view');

            return true;
        }
    }
}

