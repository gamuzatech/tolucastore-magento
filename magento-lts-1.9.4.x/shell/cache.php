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
    $cacheTypes = Mage::helper('core')->getCacheTypes();

    if ($argc != 1)
    {
        array_shift($argv);

        $argv = array_flip($argv);
    }

    foreach($cacheTypes as $type => $value)
    {
        $cacheTypes[$type] = 1;

        if (!array_key_exists($type, $argv))
        {
            continue; // skip
        }

        Mage::app()->getCacheInstance()->cleanType($type);

        Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
    }

    Mage::app()->saveUseCache($cacheTypes);

    if ($argc != 1) exit(0);

    Mage::app()->cleanCache();

    Mage::dispatchEvent('adminhtml_cache_flush_system');

    Mage::app()->getCacheInstance()->flush();

    Mage::dispatchEvent('adminhtml_cache_flush_all');
}
catch (Exception $e)
{
    echo $e->getMessage() . PHP_EOL;

    exit(1);
}

