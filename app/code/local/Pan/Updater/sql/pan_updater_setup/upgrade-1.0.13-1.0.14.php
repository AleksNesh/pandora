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
 * Shopper Theme Settings - Navigation
 * -----------------------------------
 */
$installer->setConfigData('shoppersettings/navigation/use_navigation', 0);

$installer->endSetup();
