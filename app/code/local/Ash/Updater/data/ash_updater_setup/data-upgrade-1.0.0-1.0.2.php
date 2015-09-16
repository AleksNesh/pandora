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
 * General -> General
 */
$installer->setConfigData('general/store_information/merchant_country', 'US');

/**
 * General -> Design
 */
$installer->setConfigData('design/theme/default', 'blank'); // for rapid prototyping
$installer->setConfigData('design/header/welcome', 'Welcome!');
$installer->setConfigData('design/header/logo_alt', '');
$installer->setConfigData('design/head/title', 'Online Store');
$installer->setConfigData('design/head/default_description', '');
$installer->setConfigData('design/head/default_keywords', '');

/**
 * Catalog -> Catalog
 */
$installer->setConfigData('catalog/search/search_type', 3);

/**
 * Customer -> Customer Configuration
 */
$installer->setConfigData('customer/captcha/enable', 1);

/**
 * Customer -> Persistent Shopping Cart
 */
$installer->setConfigData('persistent/options/enable', 1);

/**
 * Advanced -> System
 */
$installer->setConfigData('system/backup/enabled', 1);
$installer->setConfigData('system/backup/type', 'db');
$installer->setConfigData('system/backup/time', '03,00,00');

/**
 * Advanced -> Developer
 */
$installer->setConfigData('dev/js/merge_files', 1);
$installer->setConfigData('dev/css/merge_css_files', 1);
