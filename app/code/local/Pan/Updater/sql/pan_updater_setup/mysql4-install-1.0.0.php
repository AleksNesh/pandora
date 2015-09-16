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

/**
 * -----------------------------------------------------------------------------
 * GENERAL SETTINGS/CONFIGURATION FOR ASH MODULES
 * -----------------------------------------------------------------------------
 */
$installer->setConfigData('ash_jquery/cdn/enabled', 1);
$installer->setConfigData('ash_jquery/cdn/jquery_migrate_enabled', 1);
$installer->setConfigData('ash_jquery/cdn/jquery_enabled', 1);
$installer->setConfigData('ash_jquery/cdn/jqueryui_enabled', 1);

$installer->setConfigData('ash_jquery/version/jquery', '1.11.0');
$installer->setConfigData('ash_jquery/version/jquery_ui', '1.10.4');

