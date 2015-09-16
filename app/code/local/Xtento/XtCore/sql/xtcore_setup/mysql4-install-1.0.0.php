<?php
$this->startSetup();

//Mage::getConfig()->saveConfig('xtcore/adminnotification/installation_date', time());
$installationDate = Mage::getStoreConfig('xtcore/adminnotification/installation_date');
if (!$installationDate) {
    Mage::getModel('core/config_data')
        ->setScope('default')
        ->setPath('xtcore/adminnotification/installation_date')
        ->setValue(time())
        ->save();
}

$this->endSetup();