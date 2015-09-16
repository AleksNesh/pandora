<?php
/**
 * Open Commerce LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Commerce LLC Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.opencommercellc.com/license/commercial-license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@opencommercellc.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future.
 *
 * @category   OpenCommerce
 * @package    OpenCommerce_OrderEdit
 * @copyright  Copyright (c) 2013 Open Commerce LLC
 * @license    http://store.opencommercellc.com/license/commercial-license
 */
class TinyBrick_OrderEdit_Model_Adminhtml_Config_Config extends Mage_Core_Model_Config_Data
{
    
        public function toOptionArray()
    {
        $result = array(); 
        if(Mage::helper('orderedit')->_isRegistered() == 1){
            $result[] = array(
                    'label' => "Enabled",
                    'value' => "1"
                );
        }else{
            $result[] = array(
                    'label' => "Disabled",
                    'value' => "0"
                );
        }
        return $result;
    }
    
}
