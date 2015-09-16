<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
$this->startSetup();

$this->run("

ALTER TABLE  `{$this->getTable('amtable/rate')}` 
ADD `cost_weight` DECIMAL( 12, 2 ) NOT NULL DEFAULT '0.00' AFTER `cost_product` 

");

$this->endSetup(); 