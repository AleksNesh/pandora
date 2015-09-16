<?php

$installer = $this;

$installer->startSetup();

$installer->run("
		

ALTER TABLE {$this->getTable('tinybrick_authorizenetcim_ccsave')} ADD `verification` int(4) NULL DEFAULT NULL;
		
				");
		
$installer->endSetup();