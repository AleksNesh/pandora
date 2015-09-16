<?php

/**
 * Simple module for custom override of Webtex_Giftcards module
 *
 * @category    Pan
 * @package     Pan_Giftcards
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pan_Giftcards_Helper_Data extends Webtex_Giftcards_Helper_Data
{
    const XML_PATH_DEFAULT_CURRENCY = 'giftcards/default/currency';

    public function getUsedGiftCards($id)
    {
        $orderModel = Mage::getModel('giftcards/order')->getCollection()->addFieldToFilter('id_order',$id);
        $storeId    = Mage::getModel('sales/order')->load($id)->getStoreId();

        $usedGiftcards = array();
        $html = '';
        if($orderModel->getData()){
            $html .= '<br /><div class="order-totalsx"><div><strong>'.$this->__('Used Gift Cards').':</strong></div><table width="100%">';
            $total = 0;
            foreach($orderModel as $order){
                $card = Mage::getModel('giftcards/giftcards')->load($order->getIdGiftcard());
                $html .= '<tr><td>' . $card->getCardCode() . '</td><td class="a-right">' . Mage::helper('core')->currency($order->getDiscounted(),true,false) . '</td></tr>';
                $total += $order->getDiscounted();
            }
            $html .= '<tfoot><tr><td>' . $this->__('Total Gift Cards Amount') .':</td><td class="a-right emph"><span class="price">' . Mage::helper('core')->currency($total,true,false) . '</span></td></tr><tfoot></table></div>';
        }
        return $html;
    }

    public function getDefaultCurrency()
    {
        return Mage::getStoreConfig(self::XML_PATH_DEFAULT_CURRENCY);
    }
}
