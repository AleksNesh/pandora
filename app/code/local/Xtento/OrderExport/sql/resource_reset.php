<?php

/**
 * In case you're experiencing problems with the SQL installation script not running or failing, please copy this file into the root directory of Magento and run it ONCE using your browser.
 * It will try to delete itself after execution.
 *
 * Copyright XTENTO GmbH & Co. KG
 */

require_once 'app/Mage.php';
Mage::app();
Mage::register('isSecureArea', true);

function deleteResourceEntry($resourceCode)
{
    if (empty($resourceCode)) {
        echo "No resource specified. Script failed.<br>";
        die();
    }
    $resource = Mage::getSingleton('core/resource');
    $tableName = $resource->getTableName('core/resource');
    $resource->getConnection('core_write')->delete($tableName, array('code = ?' => $resourceCode));
    echo "Resource entry reset.<br>";
}

echo "Trying to delete resource entry..<br>";
deleteResourceEntry('xtento_orderexport_setup');
echo "Done.<br>";

if (@unlink(__FILE__)) {
    echo "Script removed itself.<br>";
} else {
    echo "Script could not remove itself automatically. Please remove the script from the Magento root directory and do not run it again.<br>";
}