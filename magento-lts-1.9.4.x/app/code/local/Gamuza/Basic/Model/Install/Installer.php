<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Installer model
 */
class Gamuza_Basic_Model_Install_Installer extends Mage_Install_Model_Installer
{
    public function finish ()
    {
        parent::finish ();

        self::applyAllSecurityUpdates ();
    }

    public static function applyAllSecurityUpdates ()
    {
        $adminUser = Mage::getModel ('admin/user')->loadByUsername ('admin');

        if ($adminUser && $adminUser->getId ())
        {
            $adminUser->getResource ()->save ($adminUser->setIsSystem (true));
        }

        $adminUser = Mage::getModel ('admin/user')->loadByUsername (Gamuza_Basic_Helper_Data::DEFAULT_ADMIN_USER);

        if ($adminUser && $adminUser->getId ())
        {
            $adminUser->getResource()->save($adminUser->setIsSystem(true));
        }

        $apiUser = Mage::getModel ('api/user')->loadByUsername (Gamuza_Basic_Helper_Data::DEFAULT_API_USER);

        if ($apiUser && $apiUser->getId ())
        {
            $apiUser->getResource()->save($apiUser->setIsSystem(true));
        }

        Mage::dispatchEvent('basic_install_installer_finish_after');
    }
}

