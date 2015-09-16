<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 */

/**
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 * @author     RocketWeb
 */

/**
 * @var $installer RocketWeb_GoogleBaseFeedGenerator_Model_Resource_Eav_Mysql4_Setup
 */
$installer = $this;
$installer->startSetup();

// Update migrated serialized configs to json format
$rows = $installer->getConnection()->fetchAll("SELECT * from `{$this->getTable('core_config_data')}`
                                               WHERE path IN ('rocketweb_googlebasefeedgenerator/columns/google_product_category_by_category',
                                                              'rocketweb_googlebasefeedgenerator/columns/product_type_by_category')");
foreach ($rows as $row) {

        $value = @unserialize($row['value']);
        if ($value && is_array($value)) {
            $value = json_encode($value);
            $value = preg_replace_callback('/\\\\u(\w{4})/', array(Mage::helper('googlebasefeedgenerator'), 'jsonUnescapedUnicodeCallback'), $value);
            $sql = "UPDATE `{$this->getTable('core_config_data')}` SET value = '". addcslashes($value, "'"). "' WHERE config_id = '{$row['config_id']}'";
            $this->run($sql);
        }
}

$installer->endSetup();