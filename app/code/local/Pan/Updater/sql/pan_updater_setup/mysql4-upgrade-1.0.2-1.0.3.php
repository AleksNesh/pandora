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
 * ----------------------------
 * TinyBrick Authorize.Net CIM configuration
 * ----------------------------
 */
$installer->setConfigData('payment/authorizenetcim/active', 1);
$installer->setConfigData('payment/authorizenetcim/test_mode', 1);
$installer->setConfigData('payment/authorizenetcim/cctypes', 'AE,VI,MC,DI');
$installer->setConfigData('payment/authorizenetcim/useccv', 1);


$installer->endSetup();
