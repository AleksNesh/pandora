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

// begin a transaction
$installer->startSetup();

// Add This CMS Static Block
$addThisContent = 'TODO: CMS BLOCK shopper_product_addthis';

try {
    $cmsBlock = Mage::getModel('cms/block')->load('shopper_product_addthis');
    if ($cmsBlock->isObjectNew()) {
        $cmsBlock->setIdentifier('shopper_product_addthis')
                 ->setStores(array(0))
                 ->setIsActive(true)
                 ->setTitle('AddThis Links');
    }
    $cmsBlock->setContent($addThisContent)->save();
} catch (Exception $e) {
    Mage::log('FROM ' . __FILE__ . ' LINE ' . __LINE__);
    Mage::log($e->getMessage());
}


// end transaction
$installer->endSetup();
