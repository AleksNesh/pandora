<?php
    /**
    * @author Amasty Team
    * @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
    * @package Amasty_Ogrid
    */
    $installer = $this;
    $installer->startSetup();
    
    $orderItem = Mage::getModel("amogrid/order_item");
    $attributes = $orderItem->getMappedColumns();
    
    $orderItem->mapData(array(), array(), TRUE);
    $orderItem->mapData($attributes, array(), TRUE);
    
    $installer->endSetup();
?>