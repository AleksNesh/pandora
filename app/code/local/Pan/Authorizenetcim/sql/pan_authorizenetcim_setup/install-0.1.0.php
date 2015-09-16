<?php
/**
 * Extend/Override TinyBrick_Authorizenetcim module
 *
 * @category    Pan
 * @package     Pan_Authorizenetcim
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

// start transaction
$installer->startSetup();

/**
 * add db columns for AuthorizationCode and SplitTenderId
 */
try {
    $sqlStatement = <<<STATEMENT
ALTER TABLE `{$this->getTable('authorizenetcim/teoauths')}` ADD `cc_authorization_code` varchar(255) NULL DEFAULT NULL COMMENT 'Authorization Code - necessary for partial authorizations and capture only transactions';
ALTER TABLE `{$this->getTable('authorizenetcim/teoauths')}` ADD `cc_split_tender_id` varchar(255) NULL DEFAULT NULL COMMENT 'Split Tender ID - necessary for partial authorizations and capture only transactions';
ALTER TABLE `{$this->getTable('sales/quote_payment')}` ADD `cc_authorization_code` varchar(255) NULL DEFAULT NULL COMMENT 'Authorization Code - necessary for partial authorizations and capture only transactions';
ALTER TABLE `{$this->getTable('sales/quote_payment')}` ADD `cc_split_tender_id` varchar(255) NULL DEFAULT NULL COMMENT 'Split Tender ID - necessary for partial authorizations and capture only transactions';
ALTER TABLE `{$this->getTable('sales/order_payment')}` ADD `cc_authorization_code` varchar(255) NULL DEFAULT NULL COMMENT 'Authorization Code - necessary for partial authorizations and capture only transactions';
ALTER TABLE `{$this->getTable('sales/order_payment')}` ADD `cc_split_tender_id` varchar(255) NULL DEFAULT NULL COMMENT 'Split Tender ID - necessary for partial authorizations and capture only transactions';
STATEMENT;

$installer->run($sqlStatement);


} catch (Exception $e) {
    Mage::log('FROM ' . __CLASS__ . ' IN FILE ' . __FILE__);
    Mage::log($e->getMessage());
}


// end transaction
$installer->endSetup();
