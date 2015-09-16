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
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;

/* @var $installer Mage_Sales_Model_Entity_Setup */

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('upslabelpickup')};
CREATE TABLE {$this->getTable('upslabelpickup')} (
  `pickup_id` int(11) unsigned NOT NULL auto_increment,
  `RatePickupIndicator` char(1) NOT NULL default 'N',
  `CloseTime` varchar(50) NOT NULL default '',
  `ReadyTime` varchar(50) NOT NULL default '',
  `PickupDateYear` char(4) NOT NULL default '',
  `PickupDateMonth` char(2) NOT NULL default '',
  `PickupDateDay` char(2) NOT NULL default '',
  `AlternateAddressIndicator` char(1) NOT NULL default 'N',
  `ServiceCode` varchar(5) NOT NULL default '',
  `Quantity` int(11) NOT NULL default 0,
  `DestinationCountryCode` char(2) NOT NULL default '',
  `ContainerCode` varchar(50) NOT NULL default '',
  `Weight` varchar(50) NOT NULL default '',
  `UnitOfMeasurement` varchar(5) NOT NULL default '',
  `OverweightIndicator` char(1) NOT NULL default 'N',
  `PaymentMethod` varchar(5) NOT NULL default '',
  `SpecialInstruction` text,
  `ReferenceNumber` text,
  `Notification` tinyint(1) NOT NULL default 0,
  `ConfirmationEmailAddress` text,
  `UndeliverableEmailAddress` text,
  `ShipFrom` text,
  `pickup_request` text,
  `pickup_response` text,
  `pickup_cancel` text,
  `pickup_cancel_request` text,
  `status` varchar(255) NOT NULL default '',
  `price` varchar(255) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`pickup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

