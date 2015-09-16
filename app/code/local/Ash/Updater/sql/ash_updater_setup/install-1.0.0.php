<?php
/**
 * Simple module for synchronizing system configuration and database changes.
 *
 * @category    Ash
 * @package     Ash_Updater
 * @copyright   Copyright (c) 2013 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

/**
 * Mark all notifications as "read"
 */
$installer->startSetup();
$installer->run("UPDATE `{$installer->getTable('adminnotification/inbox')}` SET `is_read` = 1;");
$installer->endSetup();
