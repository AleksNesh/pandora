<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */

 
// not doing this anymore because sales/order is no longer an EAV entity as of Magento 1.4.1
// load id for order entity
/*$read = Mage::getSingleton('core/resource')->getConnection('core_read');
$eid = $read->fetchRow("select entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code = 'order_payment'");
$order_payment_type_id = $eid['entity_type_id'];

$eid = $read->fetchRow("select entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code = 'order'");
$order_type_id = $eid['entity_type_id'];*/

$installer = $this;
$installer->startSetup();
/*
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute($order_payment_type_id, 'maxmind_response', array(
	'type' => 'text',
	'input' => 'text',
	'label' => 'MaxMind Response',
	'global' => 1,
	'visible' => 1,
	'required' => 0,
	'user_defined' => 0,
	'default' => '0',
	'visible_on_front' => 0,
));
$setup->addAttribute($order_type_id, 'fraud_score', array(
	'type' => 'int',
	'input' => 'int',
	'label' => 'Fraud Detection Score',
	'global' => 1,
	'visible' => 1,
	'required' => 0,
	'user_defined' => 0,
	'default' => '0',
	'visible_on_front' => 0,
));*/

$installer->endSetup();


 
