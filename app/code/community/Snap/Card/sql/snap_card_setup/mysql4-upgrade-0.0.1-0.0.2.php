<?php


/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->addAttribute('quote', 'snap_cards', array('type'=>'text'));
$installer->addAttribute('quote', 'snap_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('quote', 'base_snap_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('quote', 'snap_cards_amount_used', array('type'=>'decimal'));
$installer->addAttribute('quote', 'base_snap_cards_amount_used', array('type'=>'decimal'));

$installer->addAttribute('quote_address', 'snap_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('quote_address', 'base_snap_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('quote_address', 'snap_cards', array('type'=>'text'));
$installer->addAttribute('quote_address', 'used_snap_cards', array('type'=>'text'));

$installer->addAttribute('order', 'snap_cards', array('type'=>'text'));
$installer->addAttribute('order', 'base_snap_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('order', 'snap_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('order', 'base_snap_cards_invoiced', array('type'=>'decimal'));
$installer->addAttribute('order', 'snap_cards_invoiced', array('type'=>'decimal'));
$installer->addAttribute('order', 'base_snap_cards_refunded', array('type'=>'decimal'));
$installer->addAttribute('order', 'snap_cards_refunded', array('type'=>'decimal'));

$installer->addAttribute('invoice', 'base_snap_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('invoice', 'snap_cards_amount', array('type'=>'decimal'));

$installer->addAttribute('creditmemo', 'base_snap_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('creditmemo', 'snap_cards_amount', array('type'=>'decimal'));

$installer->getConnection()->changeColumn($this->getTable('sales_flat_quote'),
    'snap_cards', 'snap_cards',
    'text CHARACTER SET utf8'
);
$installer->getConnection()->changeColumn($this->getTable('sales_flat_quote_address'),
    'snap_cards', 'snap_cards',
    'text CHARACTER SET utf8'
);
$installer->getConnection()->changeColumn($this->getTable('sales_flat_quote_address'),
    'used_snap_cards', 'used_snap_cards',
    'text CHARACTER SET utf8'
);

$installer->endSetup();
