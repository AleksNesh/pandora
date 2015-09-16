<?php
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer->startSetup();
$installer->setConfigData('shoppersettings/social/tweets_num', '2');
$installer->setConfigData('shoppersettings/social/consumerkey', '');
$installer->setConfigData('shoppersettings/social/consumersecret', '');
$installer->setConfigData('shoppersettings/social/accesstoken', '');
$installer->setConfigData('shoppersettings/social/accesstokensecret', '');
$installer->setConfigData('shoppersettings/design/prev_next', '0');
$installer->endSetup();