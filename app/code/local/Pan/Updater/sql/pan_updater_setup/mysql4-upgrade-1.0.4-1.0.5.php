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
 * Change theme to 'Shopper'
 * ----------------------------
 */
$installer->setConfigData('design/package/name', 'shopper');
$installer->setConfigData('design/theme/default', 'pan');


$installer->endSetup();
