<?php

$installer = $this;

$installer->startSetup();

$installer->run("
		
ALTER TABLE `{$installer->getTable('authorizenetcim/authorizenetcim')}` ADD `store_id` int(2) NOT NULL DEFAULT '0';

				");
		
$installer->endSetup();
