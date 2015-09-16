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
 * @copyright  Copyright (c) 2011 MageWorx (http://www.mageworx.com/)
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

/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('mageworx_orderspro_order_group')};
CREATE TABLE IF NOT EXISTS {$this->getTable('mageworx_orderspro_order_group')} ( 
  `order_group_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  PRIMARY KEY (`order_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='OrdersPro Group';


INSERT IGNORE INTO {$this->getTable('mageworx_orderspro_order_group')} (`order_group_id`, `title`) VALUES
(1, 'Archived'),
(2, 'Deleted');
  
-- DROP TABLE IF EXISTS {$this->getTable('mageworx_orderspro_upload_files')};
CREATE TABLE IF NOT EXISTS {$this->getTable('mageworx_orderspro_upload_files')} (
  `entity_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `history_id` int(10) unsigned NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  PRIMARY KEY (`entity_id`),
  UNIQUE KEY `IDX_HISTORY` (`history_id`),  
  CONSTRAINT `FK_ORDERSPRO_HISTORY_ID` FOREIGN KEY (`history_id`) REFERENCES `{$this->getTable('sales_flat_order_status_history')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='OrdersPro Upload Files';
");
  
  
//-- DROP TABLE IF EXISTS {$this->getTable('mageworx_orderspro_order_item_group')};
//CREATE TABLE IF NOT EXISTS {$this->getTable('mageworx_orderspro_order_item_group')} (
//  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
//  `order_id` int(10) unsigned NOT NULL,
//  `order_group_id` smallint(5) unsigned DEFAULT NULL,
//  PRIMARY KEY (`entity_id`),
//  UNIQUE KEY `IDX_ORDER` (`order_id`),
//  KEY `IDX_ORDER_GROUP` (`order_group_id`),
//  CONSTRAINT `FK_ORDERSPRO_ORDER_ID` FOREIGN KEY (`order_id`) REFERENCES `{$this->getTable('sales_flat_order')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
//  CONSTRAINT `FK_ORDERSPRO_ORDER_GROUP_ID` FOREIGN KEY (`order_group_id`) REFERENCES `{$this->getTable('mageworx_orderspro_order_group')}` (`order_group_id`) ON DELETE CASCADE ON UPDATE CASCADE
//) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='OrdersPro Order Item Group';

$installer->endSetup();