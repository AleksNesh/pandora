<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */

class MageWorx_OrdersPro_Model_System_Config_Source_Orders_Status
{
    public function toOptionArray($isMultiselect=true) {
        //Mage::getResourceModel('sales/order_status_collection')->joinStates()->toOptionArray();
        $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        $options = array();
        foreach ($statuses as $code=>$label) {
            $options[] = array('value'=>$code, 'label'=>$label);
        }
        return $options;        
    }
}