<?php
/**
 * Ash Up Extension
 *
 * Management interface for keeping Ash core extensions updated.
 *
 * @category    Ash
 * @package     Ash_Up
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;

/**
 * @var $model Ash_Up_Model_Extension
 */
$model = Mage::getModel('ash_up/extension');

// Setup data row
$dataRow = array(
    'extension_name' => 'Ash_Up',
    'download_uri'   => 'https://github.com/augustash/ash_up.git',
    'installed_flag' => 1,
);

// Generate data
$model->setData($dataRow)->setOrigData()->save();
