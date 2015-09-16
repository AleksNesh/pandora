<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */
$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('frauddetection_stats')};
CREATE TABLE {$this->getTable('frauddetection_stats')} (
  `code` varchar(32) NOT NULL,
  `value` varchar(32) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
	INSERT INTO {$this->getTable('frauddetection_stats')} VALUES('remaining_maxmind_credits', '0')
");

$installer->endSetup();