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
    Mage::getModel ('backup/observer')->scheduledBackup ();

    $point = date ('Y-m-d', strtotime ('-7 days'));

    foreach (Mage::getModel ('backup/fs_collection') as $fs)
    {
        $stamp = date ('Y-m-d', $fs->getTime ());

        if ($stamp < $point)
        {
            $backup = Mage::getModel ('backup/backup')->loadByTimeAndType ($fs->getTime (), $fs->getType ());

            if ($backup && $backup->getId ()) $backup->deleteFile();
        }
    }
}
catch (Exception $e)
{
    echo $e->getMessage() . PHP_EOL;

    exit(1);
}

