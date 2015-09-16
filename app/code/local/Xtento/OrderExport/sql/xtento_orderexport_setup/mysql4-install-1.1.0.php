<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS `" . $this->getTable('xtento_orderexport_destination') . "` (
  `destination_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `port` int(5) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `timeout` int(5) NOT NULL DEFAULT '15',
  `path` varchar(255) NOT NULL,
  `ftp_type` enum('','ftp','ftps') NOT NULL,
  `ftp_pasv` int(1) NOT NULL,
  `email_sender` varchar(255) NOT NULL COMMENT 'E-Mail Destination',
  `email_recipient` varchar(255) NOT NULL COMMENT 'E-Mail Destination',
  `email_subject` varchar(255) NOT NULL COMMENT 'E-Mail Destination',
  `email_body` text NOT NULL COMMENT 'E-Mail Destination',
  `custom_class` varchar(255) NOT NULL,
  `custom_function` varchar(255) NOT NULL,
  `do_retry` int(1) NOT NULL DEFAULT '1',
  `last_result` int(1) NOT NULL,
  `last_result_message` text NOT NULL,
  `last_modification` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`destination_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `" . $this->getTable('xtento_orderexport_log') . "` (
  `log_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `profile_id` int(9) NOT NULL,
  `files` text NOT NULL,
  `destination_ids` text NOT NULL,
  `export_type` int(9) NOT NULL,
  `export_event` varchar(255) NOT NULL,
  `records_exported` int(9) NOT NULL,
  `result` int(1) NOT NULL,
  `result_message` text NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `" . $this->getTable('xtento_orderexport_profile') . "` (
  `profile_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `entity` varchar(255) NOT NULL,
  `enabled` int(1) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `destination_ids` varchar(255) NOT NULL,
  `last_execution` datetime DEFAULT NULL,
  `last_modification` datetime DEFAULT NULL,
  `conditions_serialized` text NOT NULL,
  `store_ids` text NOT NULL,
  `export_fields` text NOT NULL,
  `customer_groups` varchar(255) NOT NULL DEFAULT '',
  `export_filter_new_only` int(1) NOT NULL,
  `export_filter_datefrom` date DEFAULT NULL,
  `export_filter_dateto` date DEFAULT NULL,
  `export_filter_status` varchar(255) NOT NULL,
  `export_action_change_status` varchar(255) NOT NULL,
  `save_files_manual_export` int(1) NOT NULL DEFAULT '1',
  `save_files_local_copy` int(1) NOT NULL DEFAULT '1',
  `event_observers` varchar(255) NOT NULL,
  `cronjob_enabled` int(1) NOT NULL DEFAULT '0',
  `cronjob_frequency` varchar(255) NOT NULL,
  `cronjob_custom_frequency` varchar(255) NOT NULL,
  `xsl_template` mediumtext NOT NULL,
  `test_id` int(11) NOT NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `" . $this->getTable('xtento_orderexport_profile_history') . "` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL,
  `log_id` int(11) NOT NULL,
  `entity` varchar(255) NOT NULL COMMENT 'Export Entity',
  `entity_id` int(11) NOT NULL,
  `exported_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`history_id`),
  KEY `ENTITY_ID` (`entity`,`entity_id`),
  KEY `PROFILE_ID` (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Export history of objects exported for profile';
    
");

$installer->endSetup();