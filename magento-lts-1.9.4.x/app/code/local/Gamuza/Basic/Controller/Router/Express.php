<?php

class Gamuza_Basic_Controller_Router_Express extends Mage_Core_Controller_Varien_Router_Standard
{
    public function match(Zend_Controller_Request_Http $request)
    {
        $request->setModuleName('express')
            ->setControllerName('express')
            ->setActionName('view');

        return true;
    }
}

