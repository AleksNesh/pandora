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
 * Queldorei Shopper Theme Settings
 * (retired labels)
 * ----------------------------
 */
$installer->setConfigData('shoppersettings/labels/retired_label', 1);
$installer->setConfigData('shoppersettings/labels/retired_label_position', 'top-left');


$installer->endSetup();
