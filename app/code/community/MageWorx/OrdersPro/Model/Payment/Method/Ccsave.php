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

class MageWorx_OrdersPro_Model_Payment_Method_Ccsave extends MageWorx_OrdersPro_Model_Payment_Method_Ccsave_Abstract
{
    public function validate() {        
        if (Mage::app()->getRequest()->getControllerName()=='orderspro_order_edit') {
            $paymentData = Mage::app()->getRequest()->getPost('payment');
            // if method=='ccsave' - must be card number to validate payment data 
            if ($paymentData && isset($paymentData['method']) && $paymentData['method']=='ccsave' && !isset($paymentData['cc_number'])) {
                return $this;
            }
        }        
        return parent::validate();
    }
}
