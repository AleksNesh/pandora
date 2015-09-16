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
class TinyBrick_OrderEdit_Model_Order_Address_Total_Grand extends TinyBrick_OrderEdit_Model_Order_Address_Total_Abstract
{
    /**
     * Fetches the grand total
     * @param TinyBrick_OrderEdit_Model_Order_Address $address
     * @return TinyBrick_OrderEdit_Model_Order_Address_Total_Grand 
     */
    public function fetch(TinyBrick_OrderEdit_Model_Order_Address $address)
    {
        $address->addTotal(array(
            'code'=>$this->getCode(),
            'title'=>Mage::helper('sales')->__('Grand Total'),
            'value'=>$address->getGrandTotal(),
            'area'=>'footer',
        ));
        return $this;
    }
}