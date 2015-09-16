<?php
/**
* @copyright Amasty.
*/
$this->startSetup();

$this->run("
ALTER TABLE `{$this->getTable('amshiprestriction/rule')}`  ADD `for_admin` TINYINT NOT NULL AFTER `is_active`;
"); 

$this->endSetup();