<?php
    /**
    * @author Amasty Team
    * @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
    * @package Amasty_Ogrid
    */
$this->startSetup();

$this->run("

ALTER TABLE `{$this->getTable('amogrid/order_item_product')}`
ENGINE = INNODB;    

");

$this->endSetup(); 