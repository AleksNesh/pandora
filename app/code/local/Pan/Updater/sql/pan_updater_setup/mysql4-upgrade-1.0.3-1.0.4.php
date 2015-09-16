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
 * Webtex Gift Card configuration
 * ----------------------------
 */
$installer->setConfigData('giftcards/default/show_as_payment_method', 1);
$installer->setConfigData('giftcards/default/show_in_shopping_cart', 1);
$installer->setConfigData('giftcards/default/show_mail_delivery_date_field', 1);


$installer->endSetup();
