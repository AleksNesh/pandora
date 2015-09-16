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

/**
 * -----------------------------------
 * Xtento Grid Actions settings
 * -----------------------------------
 */

$baseUrl = Mage::helper('pan_updater')->getConfig(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL);

switch ($baseUrl) {
    // local development
    case 'http://pan.dev/':
       $serialNum = '5ff3539eb5170a50fa7df9d64c08fe116808f25e';
       break;
    // staging server
    case 'http://staging.pandoramoa.com/':
       $serialNum = '1e39cbbc788ccef9400c672970b7050ed2cbd951';
       break;
    // production
    default:
        $serialNum = '8480fed3f58452754bf4119d21fc005b9089369c';
        break;
}

$installer->setConfigData('gridactions/general/serial', $serialNum);
$installer->setConfigData('gridactions/general/enabled', 1);



$installer->endSetup();
