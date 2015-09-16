<?php
/**
 * Simple module for updating system configuration data.
 *
 * @category    Pan
 * @package     Pan_Updater
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @author      Josh Johnson (August Ash)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// load app/etc/local.xml configuration
$config         = Mage::getBaseDir('etc') . '/local.xml';
$xml            = simplexml_load_file($config);
$connectionXml  = $xml->global->resources->default_setup->connection;


$dbHost     = (string) $connectionXml->host;
$dbUser     = (string) $connectionXml->username;
$dbPass     = (string) $connectionXml->password;
$mageDbName = (string) $connectionXml->dbname;
$wpDbName   = str_replace('magento', 'wordpress', $mageDbName);


/**
 * ----------------------------
 * Fishpig_Wordpress config
 * ----------------------------
 */
$installer->setConfigData('wordpress/module/enabled', 1);
// database (external db configuration)
$installer->setConfigData('wordpress/database/is_shared', 0);
$installer->setConfigData('wordpress/database/host', $dbHost);
$installer->setConfigData('wordpress/database/dbname', $wpDbName);
$installer->setConfigData('wordpress/database/username', $dbUser);
$installer->setConfigData('wordpress/database/password', $dbPass);

$installer->setConfigData('wordpress/database/charset', 'utf8');
$installer->setConfigData('wordpress/database/table_prefix', 'wp_');

// auto-login
$installer->setConfigData('wordpress/autologin/username', 'admin');

// integration
$installer->setConfigData('wordpress/integration/path', 'blog');



$installer->endSetup();
