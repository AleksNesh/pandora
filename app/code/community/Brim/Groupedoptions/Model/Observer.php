<?php
/**
 * Brim LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brim LLC Commercial Extension License
 * that is bundled with this package in the file Brim-LLC-Magento-License.pdf.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.brimllc.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@brimllc.com so we can send you a copy immediately.
 *
 * @category   Brim
 * @package    Brim_Groupedoptions
 * @copyright  Copyright (c) 2011 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */
 
class Brim_Groupedoptions_Model_Observer {

    /**
     * Fixes parent products associations.  Adding multiple configurable products to the cart
     *
     * observers: sales_quote_product_add_after
     *
     * @param $observer
     */
    public function fixConfigurableParentProductIds($observer) {

        $items      = $observer->getItems();

        $itemMap    = array();
        foreach($items as $item) {
            $itemMap[$item->getProductId()] = $item;
        }

        foreach($items as $item) {
            $product = $item->getProduct();

            if(!$item->getId() && $item->getParentItem()) {
                if ($product->getParentProductId() != $item->getParentItem()->getProduct()->getId()) {
                    //  The wrong parent item is set.
                    if (array_key_exists($product->getParentProductId(), $itemMap)) {
                        $item->setParentItem($itemMap[$product->getParentProductId()]);
                    }
                }
            }
        }
    }
}