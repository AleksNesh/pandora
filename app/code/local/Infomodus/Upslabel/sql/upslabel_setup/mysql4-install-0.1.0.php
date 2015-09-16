<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('upslabel')};
CREATE TABLE {$this->getTable('upslabel')} (
  `upslabel_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `order_id` int(11) NOT NULL default 0,
  `trackingnumber` varchar(255) NOT NULL default '',
  `shipmentidentificationnumber` varchar(255) NOT NULL default '',
  `shipmentdigest` text,
  `labelname` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`upslabel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 