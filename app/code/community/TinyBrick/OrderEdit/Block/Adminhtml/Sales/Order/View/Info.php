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
class TinyBrick_OrderEdit_Block_Adminhtml_Sales_Order_View_Info extends Mage_Adminhtml_Block_Sales_Order_View_Info {

    /**
     * Translates the block into HTML view
     * @return object $html
     */
    protected function _toHtml() {
        $str = Mage::app()->getFrontController()->getRequest()->getPathInfo();
        if (strpos($str, '/sales_order/view/')) {
            $this->setTemplate('orderedit/sales/order/view/edit.phtml');
        }
        if (!$this->getTemplate()) {
            return '';
        }
        $html = $this->renderView();
        return $html;
    }

    /**
     * Gets a list of countries from the database
     * @return object
     */
    public function getCountryList() {
        return Mage::getResourceModel('directory/country_collection')
                        ->addFieldToFilter('country_id', array('in' => explode(",", Mage::getStoreConfig('general/country/allow'))))
                        ->toOptionArray();
    }

    /**
     * Gets a list of the states from the database based on a country ID
     * @param int $countryID Id of the country you need the states for
     * @return object $states 
     */
    public function getStateList($countryID) {
        $states = Mage::getResourceModel('directory/region_collection')
                ->addFieldToFilter('country_id', array('in' => explode(",", Mage::getStoreConfig('general/country/allow'))))
                ->addCountryFilter($countryID)
                ->setOrder('country_id', 'DESC')
                ->setOrder('default_name', 'ASC')
                ->load();
        $states = $states->getData();
        return $states;
    }

    public function getItemsHtml() {
        return $this->getChildHtml('order_items');
    }

    /**
     * Checks to see if you have capabilities of editing the order
     * @param string $status Order Status
     * @return boolean
     */
    public function canEditOrder($status) {
        if (!Mage::getStoreConfig('toe/orderedit/active')) {
            return false;
        }
        $configStatus = Mage::getStoreConfig('toe/orderedit/statuses');
        $arrStatus = explode(",", $configStatus);
        if (in_array($status, $arrStatus)) {
            return true;
        }
        return false;
    }

}