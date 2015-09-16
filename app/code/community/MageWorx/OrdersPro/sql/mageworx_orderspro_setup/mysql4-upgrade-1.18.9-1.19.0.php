<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */

$installer = $this;
$installer->startSetup();

$pathLike = 'mageworx_sales/orderspro/%';
$configCollection = Mage::getModel('core/config_data')->getCollection();
$configCollection->getSelect()->where('path like ?', $pathLike);

foreach ($configCollection as $conf) {
    $path = $conf->getPath();
    $path = str_replace('orderspro', 'general', $path);
    $path = str_replace('mageworx_sales', 'mageworx_orderspro', $path);
    $conf->setPath($path)->save();
}

$salesInstaller = new Mage_Sales_Model_Resource_Setup('core_setup');
$salesInstaller->addAttribute(
    'quote_item',
    'orderspro_is_temporary',
    array(
        'type' => 'int',
        'nullable' => true,
        'grid' => false,
    )
);
$salesInstaller->endSetup();

if ($installer->tableExists('orderspro_order_group')) {
    $installer->run("RENAME TABLE orderspro_order_group TO {$this->getTable('mageworx_orderspro_order_group')};");
}

if ($installer->tableExists('orderspro_upload_files')) {
    $installer->run("RENAME TABLE orderspro_upload_files TO {$this->getTable('mageworx_orderspro_upload_files')};");
}

$installer->endSetup();