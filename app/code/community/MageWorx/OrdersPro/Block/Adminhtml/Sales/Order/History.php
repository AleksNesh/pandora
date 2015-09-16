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
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */
class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_History extends Mage_Adminhtml_Block_Sales_Order_View_History
{
    protected function _prepareLayout()
    {
        $onclick = "submitHistoryAndReload($('order_history_block').parentNode, '" . $this->getSubmitUrl() . "')";

        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('sales')->__('Submit Comment'),
                'class' => 'save',
                'onclick' => $onclick
            ));
        $this->setChild('submit_button', $button);
        return $this;
    }

    public function getStatuses()
    {
        return $this->getOrder()->getConfig()->getStatuses();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('mageworxadmin/adminhtml_orderspro_history/addComment', array('order_id' => $this->getOrder()->getId()));
    }

    public function getSubmitEditUrl()
    {
        return $this->getUrl('mageworxadmin/adminhtml_orderspro_history/saveEditComment', array('order_id' => $this->getOrder()->getId()));
    }
}   