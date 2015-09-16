<?php
$this->startSetup();

$this->run("
CREATE TABLE IF NOT EXISTS `" . $this->getTable('xtcore_config_data') . "` (
  `config_id` int(10) unsigned NOT NULL auto_increment,
  `path` varchar(255) NOT NULL default 'general',
  `value` text NOT NULL,
  PRIMARY KEY  (`config_id`),
  UNIQUE KEY `config_scope` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();