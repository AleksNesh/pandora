<?php

//

class Magestore_Giftwrap_Model_Sales_Order_Invoice_Total_Giftwrap extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

//Trungha: calculate giftwrap amount to invoice
    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        $invoice->setGiftwrapAmount(0);
        $totalAmount = 0;
        $giftwrapPrice = 0;
        $giftcardPrice = 0;
        $giftbox = array();
        $parentItemChildQty = array();
        $order = $invoice->getOrder();
        $tax_info = $order->getFullTaxInfo();
        $rate = $tax_info[0]['percent'];
        //lay ra cac giftbox co trong order
        foreach ($invoice->getAllItems() as $item) {
            $orderItemModel = Mage::getResourceModel('sales/order_item_collection');
            $orderItem = $orderItemModel->addFieldToFilter('item_id', $item->getOrderItemId())->getFirstItem();
            if (!$orderItem->getParentItemId() && $item->getQty() > 0) {
                $quoteItemId = $orderItem->getQuoteItemId();
                $selectionItem = Mage::getResourceModel('giftwrap/selectionitem_collection')->addFieldToFilter('item_id', $quoteItemId)->getFirstItem();
                $selection = Mage::getModel('giftwrap/selection')->load($selectionItem->getSelectionId());
                if ($selection && !$selection->getIsInvoiced()) {
                    if (!in_array($selection->getId(), $giftbox)) {
                        $giftwrapPrice = 0;
                        $giftcardPrice = 0;
                        $totalQtyInBox = 0;
                        $giftbox[] = $selection->getId();
                        $giftwrapPrice += (float) Mage::getModel('giftwrap/giftwrap')->load($selection->getStyleId())
                                        ->getPrice();
                        if ($selection->getGiftcardId()) {
                            $giftcardPrice += (float) Mage::getModel('giftwrap/giftcard')->load($selection->getGiftcardId())
                                            ->getPrice();
                        }
                        if ($selection->getCalculateByItem()) {
                            $selectionItems = Mage::getResourceModel('giftwrap/selectionitem_collection')->addFieldToFilter('selection_id', $selection->getId());
                            foreach ($selectionItems as $sItem) {
                                $totalQtyInBox += $sItem->getQty();
                            }
                            $totalAmount += $totalQtyInBox * ($giftwrapPrice + $giftcardPrice);
                        } else {
                            $totalAmount += ($giftwrapPrice + $giftcardPrice);
                        }
                    }
                }
            } else {
                $parentItemChildQty[$orderItem->getParentItemId()] += $item->getQty();
            }
        }
        if (count($parentItemChildQty) > 0) {
            foreach ($parentItemChildQty as $orderItemId => $qty) {
                if ($qty == 0) {
                    $quoteItemId = Mage::getModel('sales/order_item')->load($orderItemId)->getQuoteItemId();
                    $selectionItem = Mage::getResourceModel('giftwrap/selectionitem_collection')->addFieldToFilter('item_id', $quoteItemId)->getFirstItem();
                    $selection = Mage::getModel('giftwrap/selection')->load($selectionItem->getSelectionId());
                    $giftwrapPrice = 0;
                    $giftcardPrice = 0;
                    $totalQtyInBox = 0;
                    if (($key = array_search($selection->getId(), $giftbox)) !== false) {
                        unset($giftbox[$key]);

                        $giftwrapPrice += (float) Mage::getModel('giftwrap/giftwrap')->load($selection->getStyleId())
                                        ->getPrice();
                        if ($selection->getGiftcardId()) {
                            $giftcardPrice += (float) Mage::getModel('giftwrap/giftcard')->load($selection->getGiftcardId())
                                            ->getPrice();
                        }
                        if ($selection->getCalculateByItem()) {
                            $selectionItems = Mage::getResourceModel('giftwrap/selectionitem_collection')->addFieldToFilter('selection_id', $selection->getId());
                            foreach ($selectionItems as $sItem) {
                                $totalQtyInBox += $sItem->getQty();
                            }
                            $totalAmount -= $totalQtyInBox * ($giftwrapPrice + $giftcardPrice);
                        } else {
                            $totalAmount -= ($giftwrapPrice + $giftcardPrice);
                        }
                    }
                }
            }
        }
        $giftbox = array_filter($giftbox);
        $lastItem = $invoice->getItemsCollection()->getLastItem();
        if($lastItem->getOrderItem()->getParentItemId()){
            $lastId = $lastItem->getOrderItem()->getParentItemId();
        } else {
            $lastId = $lastItem->getOrderItem()->getId();
        }
        Mage::getSingleton('adminhtml/session')->setSubtotalGiftwrap($totalAmount);
        Mage::getSingleton('adminhtml/session')->setGiftbox($giftbox);
        Mage::getSingleton('adminhtml/session')->setInvoiceLastId($lastId);
        $orderGiftwrapAmount = $totalAmount;
        if (Mage::getStoreConfig('giftwrap/calculation/tax', Mage::app()->getStore(true)->getId())) {
            if ($rate) {
                $orderGiftwrapTax = $totalAmount * $rate;
            }
        }
        if ($orderGiftwrapAmount || $orderGiftwrapAmount == 0) {
            $invoice->setGiftwrapAmount($orderGiftwrapAmount);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $orderGiftwrapAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $orderGiftwrapAmount);
            if ($orderGiftwrapTax) {
                $invoice->setGiftwrapTax($orderGiftwrapTax);
                $invoice->setGrandTotal($invoice->getGrandTotal() + $orderGiftwrapTax);
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $orderGiftwrapTax);
            }
        }
        return $this;
    }

}
