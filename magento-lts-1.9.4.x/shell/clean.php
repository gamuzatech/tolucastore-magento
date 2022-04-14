<?php

// Change current directory to the directory of current script
chdir(dirname(__FILE__));

require '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';

if (!Mage::isInstalled())
{
    echo "Application is not installed yet, please complete install wizard first.";

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
    // Mage::app()->cleanAllSessions();

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

    $write->delete(Mage::getSingleton('core/resource')->getTableName('api/session'));
    $write->delete(Mage::getSingleton('core/resource')->getTableName('core/session'));

    Mage::getModel ('basic/observer')->cleanExpiredQuotes ();
    Mage::getModel ('bot/observer')->cleanExpiredChats ();
}
catch (Exception $e)
{
    echo $e->getMessage() . PHP_EOL;

    exit(1);
}

