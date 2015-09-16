<?php

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

$rows = $installer->getConnection()->query("
  UPDATE `{$this->getTable('core_config_data')}` SET path = 'rocketweb_googlebasefeedgenerator/apparel/attribute_overwrites'
  WHERE path = 'rocketweb_googlebasefeedgenerator/configurable_products/attribute_overwrites'
");

$installer->endSetup();