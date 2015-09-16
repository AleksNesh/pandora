<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('giftwrap_selection')} ADD `calculate_by_item` tinyint(1) NOT NULL default '0' ;
");
        
$installer->endSetup(); 
