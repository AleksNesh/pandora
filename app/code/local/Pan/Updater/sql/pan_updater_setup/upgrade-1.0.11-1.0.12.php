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
 * Enable/disable slideshows
 * ----------------------------
 */

// enable Ash_SlideshowExtended settings
$installer->setConfigData('ash_slideshowextended/general/enabled', 1);
$installer->setConfigData('ash_slideshowextended/general/show', 'home');

// disable Queldorei Shopper Slideshow settings
$installer->setConfigData('shopperslideshow/config/enabled', 0);


$installer->endSetup();
