<?php

// Change current directory to the directory of current script
chdir(dirname(__FILE__));

require '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';

if (!Mage::isInstalled())
{
    echo "Application is not installed yet, please complete install wizard first." . PHP_EOL;

    exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

try
{
    Mage::app('admin')->setUseSessionInUrl(false);
}
catch (Exception $e)
{
    echo $e->getMessage() . PHP_EOL;

    exit;
}

umask(0);

try
{
    Mage::app()->cleanAllSessions();

    $dir = Mage::app()->getConfig()->getOptions()->getSessionDir();

    $dh  = scandir($dir);

    foreach ($dh as $file)
    {
        if (strpos ($file, 'sess_') !== false)
        {
            unlink ($dir . DS . $file);
        }
    }

    $write = Mage::getSingleton('core/resource')->getConnection('core_write');

    $write->delete(Mage::getSingleton('core/resource')->getTableName('core_session'));

    if (!strcmp(php_sapi_name(), 'cli'))
    {
        $user = Mage::getModel('admin/user')->loadByUsername('admin');

        if ($user && $user->getId())
        {
            $session = Mage::getSingleton('admin/session');

            $session->renewSession();

            if (Mage::getSingleton('adminhtml/url')->useSecretKey())
            {
                Mage::getSingleton('adminhtml/url')->renewSecretUrls();
            }

            $session->setIsFirstPageAfterLogin(true);
            $session->setUser($user);
            $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());

            Mage::dispatchEvent('admin_session_user_login_success', array('user' => $user));
        }
        else
        {
            Mage::throwException(Mage::helper('adminhtml')->__('User not found.'));
        }
    }
}
catch (Exception $e)
{
    echo $e->getMessage() . PHP_EOL;

    exit(1);
}

