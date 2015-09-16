<?php
/**
 * Disabled step 2
 *
 * @category    Alpine
 * @package     Alpine_Giftwrap
 * @copyright   Copyright (c) 2015 Alpine Consulting, Inc
 * @author      dmitry.ilin@alpineinc.com
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = Mage::getModel('core/config');
$config->saveConfig('giftwrap/general/use_card', 0);

$installer->endSetup();