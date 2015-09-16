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
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */

class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Changed_Coupons extends Mage_Adminhtml_Block_Widget //MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Coupons
{
    protected $_template = 'mageworx/orderspro/changed/coupons.phtml';

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_coupons');
        $this->setTemplate('mageworx/orderspro/changed/coupons.phtml');
    }

    public function getCouponCode()
    {
        return $this->getQuote()->getCouponCode();
    }
}