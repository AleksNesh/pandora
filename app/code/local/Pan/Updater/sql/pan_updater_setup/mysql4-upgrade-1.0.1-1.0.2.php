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
 * Taxes - Tax Classes
 * ----------------------------
 */
$installer->setConfigData('taxes/classes/shipping_tax_class', 2); # Taxable Goods

/**
 * ----------------------------
 * General - Store Information
 * ----------------------------
 */
$installer->setConfigData('general/store_information/phone', '952-252-1495');
$installer->setConfigData('general/store_information/address', "Mall of America\n127 North Garden\nBloomington, MN 55425");

$installer->endSetup();
