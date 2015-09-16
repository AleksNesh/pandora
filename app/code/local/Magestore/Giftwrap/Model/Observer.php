<?php

class Magestore_Giftwrap_Model_Observer {

    public function addOrder($observer) {
        $orderIds = array();
        $orderIds = $observer->getOrder_ids();
        $quoteId = Mage::getModel('sales/order')->load($orderIds[0])->getQuoteId();
        $selection = Mage::getModel('giftwrap/selection')->getCollection()
                ->addFieldToFilter('quote_id', $quoteId)
                ->addAttributeToSort('addressgift_id', 'ASC');
        foreach ($orderIds as $index => $value) {
            $selection[$index]->setData('order_id', $value)->save();
        }
    }

    public function processDataBefore($observer) {
        $request = $observer->getRequestModel();
        $post = $request->getPost();
        $items = $post['item'];
        $installer = Mage::getModel('core/resource_setup');
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $readConnection = $resource->getConnection('core_read');
        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        
        foreach ($items as $itemId => $data) {
            $item = $quote->getItemById($itemId);
            if (isset($data['action']) && $data['action'] == 'remove') {
                $selectionItem = Mage::getModel('giftwrap/selectionitem')
                        ->getCollection()
                        ->addFieldToFilter('item_id', $itemId)
                        ->getFirstItem();
                if ($selectionItem->getId()) {
                    $query = 'SELECT SUM(qty) as `total_qty` FROM ' . $installer->getTable('giftwrap/selectionitem') . ' WHERE `selection_id` = ' . $selectionItem->getSelectionId();
                    $result = $readConnection->fetchAll($query);
                    $remainQty = (int) $result['total_qty'] - $item->getQty();
                    if($remainQty <= 0){
                        $selection = Mage::getModel('giftwrap/selection')->load($selectionItem->getSelectionId());
                        $selection->delete();
                    }
                }
            }
        }
    }

    // Giftwarp filter King_211112
    public function afterSaveOrder($observer) {
        $order = $observer->getOrder();
        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        $giftwrapAmount = $quote->getAdminGiftwrapAmount();
        if ($giftwrapAmount)
            $order->setGiftwrapAmount($giftwrapAmount);
    }

    /**
     * 	Hai.Ta 6.6.2013
     * */
    public function saveItemsBeforeReorder($observer) {
        $order_id = Mage::app()->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($order_id);
        $quote_idOld = $order->getQuoteId();
        Mage::getSingleton('core/session')->setQuoteOldId($quote_idOld);

        $selection = Mage::getModel('giftwrap/selection')->getCollection()
                ->addFieldToFilter('quote_id', $quote_idOld);
        $dataSelection = $selection->getData();

        if (count($dataSelection)) {
            $quote_idCurrent = Mage::getSingleton('checkout/session')->getQuote()->getId();
            if (!$quote_idCurrent) {
                $quote_idCurrent = Mage::getModel('sales/quote')->getCollection()->getLastItem()->getId() + 1;
            }

            foreach ($dataSelection as $data) {
                $model = Mage::getModel('giftwrap/selection');
                $model->setStyleId($data['style_id']);
                $model->setMessage($data['message']);
                $model->setGiftcardId($data['giftcard_id']);
                $model->setQty($data['qty']);
                $model->setCalculateByItem($data['calculate_by_item']);
                $model->setType($data['type']);
                $model->setAddressgiftId($data['addressgift_id']);
                $model->setAddresscustomerId($data['addresscustomer_id']);
                $model->setQuoteId($quote_idCurrent);
                $model->save();
                // Mage::getSingleton('core/session')->setCurrentSelectionId($model->getId());
                $this->saveItemsSelection($data['id'], $model->getId());
            }
            Mage::getSingleton('core/session')->setItemsWrap(true);
        } else {
            Mage::getSingleton('core/session')->setItemsWrap(false);
        }

        return;
    }

    public function saveItemsSelection($selectionIdOld, $selectionIdNew) {
        $itemsSelection = Mage::getModel('giftwrap/selectionitem')->getCollection()
                ->addFieldToFilter('selection_id', $selectionIdOld);
        // $idItems  = array();
        foreach ($itemsSelection as $item) {
            $model = Mage::getModel('giftwrap/selectionitem');
            $model->setSelectionId($selectionIdNew);
            $model->setItemId(-1);
            $model->setCheckReorder($item->getItemId());
            $model->setQty($item->getQty());
            $model->save();
            // $idItems[] = $model->getId();
        }
    }

    public function saveSessionBeforeReorder($observer) {
        Mage::getSingleton('adminhtml/session')->setCheckReorder(true);
        Mage::getSingleton('adminhtml/session')->setIdOrder(Mage::app()->getRequest()->getParam('order_id'));
        return;
    }

    public function saveAdminItemsReorder($observer) {
        if (Mage::getSingleton('adminhtml/session')->getCheckReorder()) {
            $order_id = Mage::getSingleton('adminhtml/session')->getIdOrder();

            $order = Mage::getModel('sales/order')->load($order_id);
            $quote_idOld = $order->getQuoteId();
            Mage::getSingleton('core/session')->setQuoteOldId($quote_idOld);

            $selection = Mage::getModel('giftwrap/selection')->getCollection()
                    ->addFieldToFilter('quote_id', $quote_idOld);
            $dataSelection = $selection->getData();

            if (count($dataSelection)) {

                $quote_idCurrent = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getId();
                // Zend_debug::dump($quote_idCurrent);die();
                if (!$quote_idCurrent) {
                    $quote_idCurrent = Mage::getModel('sales/quote')->getCollection()->getLastItem()->getId() + 1;
                }

                foreach ($dataSelection as $data) {
                    $model = Mage::getModel('giftwrap/selection');
                    $model->setStyleId($data['style_id']);
                    $model->setMessage($data['message']);
                    $model->setGiftcardId($data['giftcard_id']);
                    $model->setQty($data['qty']);
                    $model->setCalculateByItem($data['calculate_by_item']);
                    $model->setType($data['type']);
                    $model->setAddressgiftId($data['addressgift_id']);
                    $model->setAddresscustomerId($data['addresscustomer_id']);
                    $model->setQuoteId($quote_idCurrent);
                    $model->save();
                    // Mage::getSingleton('core/session')->setCurrentSelectionId($model->getId());
                    $this->saveItemsSelection($data['id'], $model->getId());
                }

                $quoteItems = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getAllItems();
                Mage::helper('giftwrap')->saveIdItemQuote($quoteItems, 2);
            } else {
                // nothing
            }
        }
        Mage::getSingleton('adminhtml/session')->setCheckReorder(false);
        return;
    }

    public function updateItemOptionsBefore($observer) {
        //save item to session
        $action = $observer->getControllerAction();
        $itemid = $action->getRequest()->getParam('id');
        //check to add new product and giftbox if product update is larger than the max qty
        $session = $this->_getSession();
        $quote = $this->_getQuote();
        $item = $quote->getItemById($itemid);
        $session->setData('update_item_old_item_qty', $item->getQty());
    }

    public function updateItemOptions($observer) {
        //declare session and quote
        $session = $this->_getSession();
        $quote = $this->_getQuote();
        //get data from sesstion
        $oldItemQty = $session->getData('update_item_old_item_qty');
        $updatedItem = $observer->getQuoteItem();
        //declare other variables
        $maxQtyPerBox = Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');
        $qtyUpdate = Mage::app()->getRequest()->getParam('qty');
        $productId = Mage::app()->getRequest()->getParam('product');
        //true qty of current product in cart array
        $current_item_true_qty = array();
        //get selection item and giftbox
        $selectionItem = Mage::getModel('giftwrap/selectionitem')->getCollection()
                ->addFieldToFilter('item_id', Mage::app()->getRequest()->getParam('id'))
                ->getFirstItem();
        if ($qtyUpdate <= 0) {
            $selectionItem->delete();
            return;
        }
        $selection = Mage::getModel('giftwrap/selection')->load($selectionItem->getSelectionId());
        //total product in cart
        //declare product options
        $productOptions = $updatedItem->getProduct()->getTypeInstance(true)->getOrderOptions($updatedItem->getProduct());
        $requestInfo = $productOptions['info_buyRequest'];
//        Zend_Debug::Dump($productOptions);
//        die();
        if ($selectionItem->getId()) {
            //check if updated item have gift box
            $updatedSelectionItemCollection = Mage::getModel('giftwrap/selectionitem')->getCollection()
                    ->addFieldToFilter('item_id', $updatedItem->getId());
            if (!$updatedSelectionItemCollection->getSize()) {
                $selectionItem->setItemId($updatedItem->getId())->save();
            }
            $totalQtyInbox = 0;
            $itemsInBox = Mage::getModel('giftwrap/selectionitem')->getCollection()
                    ->addFieldToFilter('selection_id', $selection->getId());
            foreach ($itemsInBox as $iteminbox) {
                $totalQtyInbox += $iteminbox->getQty();
            }
            $itemid = $updatedItem->getId();
            $remainingSlot = (int) $maxQtyPerBox - $totalQtyInbox + (int) $selectionItem->getQty();
            if ((int) $updatedItem->getQty() > (int) $remainingSlot) {
                $itemNeedToAddQty = (int) $updatedItem->getQty() - (int) $remainingSlot;
                $current_item_true_qty[$itemid] = $remainingSlot;
                try {
                    $selectionItem->setQty($remainingSlot)->save();
                } catch (Exception $e) {
                    
                }
            } else {
                try {
                    $selectionItem->setQty($updatedItem->getQty())->save();
                } catch (Exception $e) {
                    
                }
            }

            if ((int) $itemNeedToAddQty > 0) {
                $itemUpdateQty1 = $itemNeedToAddQty;
                //prepare options for product
                $requestInfo = $productOptions['info_buyRequest'];
                $requestInfo['giftwrap_add'] = 'update_item';
                $requestInfo['giftbox_paper'] = $selection->getStyleId();
                $requestInfo['giftwrap_giftcard'] = $selection->getGiftcardId();
                $requestInfo['giftbox_message'] = $selection->getMessage();
                $requestInfo['item_product_id'] = $productId;
                for ($i = 0; (float) $i < ceil($itemUpdateQty1 / $maxQtyPerBox); $i++) {
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $session->setData('giftbox_update_box', 1);
                    $session->setData('giftbox_update_product', $productId);
                    if ($itemNeedToAddQty > $maxQtyPerBox) {
                        $requestInfo['qty'] = (int) $maxQtyPerBox;
                        $itemNeedToAddQty -= $maxQtyPerBox;
                    } else {
                        $requestInfo['qty'] = $itemNeedToAddQty;
                    }
                    $cart = Mage::getModel('checkout/cart');
                    try {
                        $cart->addProduct($product, $requestInfo)->save();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
        $session->setData('current_item_true_qty', $current_item_true_qty);
        $session->setData('update_item_old_item', null);
        $session->setData('update_item_options_buyrequest', null);
    }

    //start loki
    public function clearGiftwrap($observer) {
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('update_cart_action') == 'empty_cart') {
            $_cartQuote = Mage::getSingleton('checkout/session')->getQuote();
            $quoteId = $_cartQuote->getId();
            $selections = Mage::getModel('giftwrap/selection')->getCollection()
                    ->addFieldToFilter('quote_id', $quoteId);
            foreach ($selections as $key) {
                $key->delete();
            }
        } else {
            $maxQtyPerBox = Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');
            //check to add new product and giftbox if product update is larger than the max qty
            $cartData = $action->getRequest()->getParam('cart');
            $session = $this->_getSession();
            $quote = $this->_getQuote();
            $current_item_true_qty = array();
            foreach ($cartData as $itemid => $data) {
                $item = $quote->getItemById($itemid);
                if ($data['qty'] > 0 && $item) {
                    $productId = $item->getProductId();
                    $selectionItem = Mage::getModel('giftwrap/selectionitem')->getCollection()
                            ->addFieldToFilter('item_id', $itemid)
                            ->getFirstItem();
                    $selection = Mage::getModel('giftwrap/selection')->load($selectionItem->getSelectionId());
                    if ($selectionItem->getId()) {
                        //total items in box
                        $totalQtyInbox = 0;
                        $itemsInBox = Mage::getModel('giftwrap/selectionitem')->getCollection()
                                ->addFieldToFilter('selection_id', $selection->getId());
                        foreach ($itemsInBox as $iteminbox) {
                            $totalQtyInbox += $iteminbox->getQty();
                        }
                        $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                        if ($data['qty'] != 0) {
                            $itemAddNewQty = (int) $data['qty'] + $totalQtyInbox - (int) $maxQtyPerBox - (int) $item->getQty();
                        }
//                      //add new product with same option to cart - Marko
                        if ((int) $itemAddNewQty > 0) {
                            $newItemQty = (int) $maxQtyPerBox - $totalQtyInbox + (int) $selectionItem->getQty();
                            $current_item_true_qty[$item->getId()] = $newItemQty;
                            $selectionItem->setQty($newItemQty)->save();
                            $itemUpdateQty1 = $itemAddNewQty;
                            //prepare options for product
                            $requestInfo = $productOptions['info_buyRequest'];
                            $requestInfo['giftwrap_add'] = 'update_item';
                            $requestInfo['giftbox_paper'] = $selection->getStyleId();
                            $requestInfo['giftwrap_giftcard'] = $selection->getGiftcardId();
                            $requestInfo['giftbox_message'] = $selection->getMessage();
                            $requestInfo['item_product_id'] = $productId;
                            for ($i = 0; (float) $i < ceil($itemUpdateQty1 / $maxQtyPerBox); $i++) {
                                $product = Mage::getModel('catalog/product')->load($productId);
                                $session->setData('giftbox_update_box', 1);
                                $session->setData('giftbox_update_product', $productId);
                                if ($itemAddNewQty > $maxQtyPerBox) {
                                    $requestInfo['qty'] = (int) $maxQtyPerBox;
                                    $itemAddNewQty -= $maxQtyPerBox;
                                } else {
                                    $requestInfo['qty'] = $itemAddNewQty;
                                }
                                $cart = Mage::getModel('checkout/cart');
                                try {
                                    $cart->addProduct($product, $requestInfo)->save();
                                } catch (Exception $e) {
                                    
                                }
                            }
                        } else {
                            $selectionItem->setQty($data['qty'])->save();
                        }
                    }
                } else {
                    $selectionItem = Mage::getModel('giftwrap/selectionitem')->getCollection()
                            ->addFieldToFilter('item_id', $itemid)
                            ->getFirstItem();
                    $selectionItem->delete();
                }
            }
            $session->setData('current_item_true_qty', $current_item_true_qty);
        }
        return $this;
    }

    public function deleteproduct($observer) {
        //get name product
        $id = $observer->getEvent()->getControllerAction()->getRequest()->getParam('id');
        $selectionItem = Mage::getModel('giftwrap/selectionitem')->getCollection()
                ->addFieldToFilter('item_id', $id)
                ->getFirstItem();
        $selectionItem->delete();
        //get selection id
        $selectionId = $selectionItem->getSelectionId();
        $selection = Mage::getModel('giftwrap/selection')->load($selectionId);
        $numberItems = count($selection->getItemCollection());
        if ($numberItems < 1) {
            $selection->delete();
        }
    }

    //end loki
    //event checkout_cart_product_add_after action - Marko
    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getSession() {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getQuote() {
        return $this->_getCart()->getQuote();
    }

//check and add product if product add to cart over the maximum per box - Marko
    public function addActionPredispatch($observer) {
        Mage::getSingleton('core/session')->setData('multi', 0);
        $cart = Mage::getModel('checkout/cart');
        $session = $this->_getSession();
        $maxQtyPerBox = (int) Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');
        $requestInfo = Mage::app()->getRequest()->getParams();
        $product = Mage::getModel('catalog/product')->load($requestInfo['product']);
        $productQty = (int) $requestInfo['qty'];
        if ($requestInfo['giftwrap_add'] && $requestInfo['giftwrap_add'] == 'new') {
            if ($requestInfo['giftbox_paper']) {
                if ($productQty > $maxQtyPerBox) {
                    Mage::getSingleton('checkout/session')->addNotice('There are too many items in one gift box. The maximum number allowed is '.$maxQtyPerBox.'. Automatically added new gift box(es) to cart.');
                    $itemUpdateQty = $productQty - $maxQtyPerBox;
                    $itemUpdateQty1 = $itemUpdateQty;
                    for ($i = 0; (float) $i < ceil($itemUpdateQty1 / $maxQtyPerBox); $i++) {
                        if ($itemUpdateQty > $maxQtyPerBox) {
                            $requestInfo['qty'] = (int) $maxQtyPerBox;
                            $itemUpdateQty -= $maxQtyPerBox;
                        } else {
                            $session->setData('giftbox_add_box_last', 1);
                            $requestInfo['qty'] = $itemUpdateQty;
                        }
                        $cart = Mage::getModel('checkout/cart');
                        try {
                            $cart->addProduct($product, $requestInfo);
                        } catch (Exception $e) {
                            
                        }
                    }
                }
            }
        }
        if ($requestInfo['giftwrap_add'] && $requestInfo['giftwrap_add'] == 'exist') {
            if ($requestInfo['existing_giftbox']) {
                $giftboxId = $requestInfo ['existing_giftbox'];
                $giftbox = Mage::getModel('giftwrap/selection')->load($giftboxId);
                $items = $giftbox->getItemCollection();
                //reset qty if item qty > max qty per box
                $currentNumberItem = 0;
                foreach ($items as $item) {
                    $currentNumberItem += (int) $item->getQty();
                }
                if (($currentNumberItem + (int) $requestInfo['qty']) > (int) $maxQtyPerBox) {
                    Mage::getSingleton('checkout/session')->addNotice('There are too many items in one gift box. The maximum number allowed is '.$maxQtyPerBox.'. Automatically added new gift box(es) to cart.');
                    //save infomation to session
                    $remainingQty = $maxQtyPerBox - $currentNumberItem;
                    $session->setData('giftwrap_existing_legalqty', $remainingQty);
                    $itemQtyToAdd = $productQty - $remainingQty;
                    $requestInfo['giftwrap_add'] = 'new';
                    $requestInfo['giftbox_paper'] = $giftbox->getStyleId();
                    $requestInfo['giftwrap_giftcard'] = $giftbox->getGiftcardId();
                    $requestInfo['giftbox_message'] = $giftbox->getMessage();
                    $product = Mage::getModel('catalog/product')->load($requestInfo['product']);
                    if ($itemQtyToAdd > $maxQtyPerBox) {
                        $itemUpdateQty1 = $itemQtyToAdd;
                        for ($i = 0; (float) $i < ceil($itemUpdateQty1 / $maxQtyPerBox); $i++) {
                            if ($remainingQty > $maxQtyPerBox) {
                                $requestInfo['qty'] = (int) $maxQtyPerBox;
                                $remainingQty -= $maxQtyPerBox;
                            } else {
                                $requestInfo['qty'] = $remainingQty;
                            }
                            $cart = Mage::getModel('checkout/cart');
                            try {
                                $cart->addProduct($product, $requestInfo);
                            } catch (Exception $e) {
                                
                            }
                        }
                    } else {
                        $requestInfo['qty'] = $itemQtyToAdd;
                        $cart = Mage::getModel('checkout/cart');
                        try {
                            $cart->addProduct($product, $requestInfo);
                        } catch (Exception $e) {
                            
                        }
                    }
                } else {
                    $session->setData('giftwrap_existing_legalqty', $requestInfo['qty']);
                }
            }
        }
    }

    //check and add product if product add to cart over the maximum per box - Marko
    //add gift box item - Marko
    public function productAddAfter($observer) {
        //declare variables
        $session = $this->_getSession();
        $items = $observer->getItems();
        $params = Mage::app()->getRequest()->getParams();
        $buyrequest = $session->getData('giftwrap_buyrequest');
        $session->setData('giftwrap_buyrequest', null);
        $item_qty = $params['qty'];
        try {
            $session->getQuote()->save();
        } catch (Exception $e) {
            
        }
        $addExist = $session->getData('add_out_of_exist');

        foreach ($items as $item) {
            if ($item->getProductId() == $params['product']) {
                $item_id = $item->getId();
            }

            if ($item->getProductId() == $buyrequest->getItemProductId()) {
                $item_id = $item->getId();
            }

            if ($session->getData('giftbox_update_product') && $session->getData('giftbox_update_product') == $item->getProductId()) {
                $item_id = $item->getId();
            }
        }
        if ($session->getData('giftbox_update_box')) {
            if ($session->getData('giftbox_id')) {
                $giftboxId = $session->getData('giftbox_id');
            }
            if ($session->getData('giftbox_item_qty')) {
                $item_qty = $session->getData('giftbox_item_qty');
            }
            $itemModel = Mage::getModel('giftwrap/selectionitem');
            $itemModel->setData('item_id', $item_id);
            $itemModel->setData('selection_id', $giftboxId);
            $itemModel->setData('qty', $item_qty);
            try {
                $itemModel->save();
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addError($e->getMessage());
            }
            //reset session data giftbox id
            $session->setData('giftbox_id', null);
            $session->setData('giftbox_item_qty', null);
            $session->setData('giftbox_update_box', null);
            $session->setData('giftbox_update_product', null);
        }

        if ($session->getData('unwrap_remaining_product')) {
            $session->setData('unwrap_remaining_product', null);
            return $this;
        }

        if ($buyrequest->getGiftwrapAdd()) {
            $addGiftWrap = $buyrequest->getGiftwrapAdd();
        } else {
            return $this;
        }

        $maxQtyPerBox = Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');

        //add item to gift box - Marko
        if ($addGiftWrap == 'new' || $addExist) {
            $session->setData('add_out_of_exist', null);
            if (!isset($params['giftbox_paper'])) {
                return $this;
            } else {
                $giftboxpaper = $params['giftbox_paper'];
            }
            if (isset($params['giftwrap_giftcard'])) {
                $giftboxcard = $params['giftwrap_giftcard'];
            }
            if (isset($params['giftbox_message'])) {
                $cardmessage = $params['giftbox_message'];
                if (strlen(trim($cardmessage)) == 0) {
                    $cardmessage = null;
                }
            }
            if ($session->getData('item_add_new_qty')) {
                $item_qty = $session->getData('item_add_new_qty');
                $session->setData('item_add_new_qty', null);
            }
            if ($giftboxpaper && $giftboxpaper != 0) {
                //Add giftwrap items
                if ($item_qty > $maxQtyPerBox) {
                    $item_qty = $maxQtyPerBox;
                }
                if ($session->getData('giftbox_id')) {
                    $giftboxId = $session->getData('giftbox_id');
                }
                if ($giftboxId) {
                    $itemModel = Mage::getModel('giftwrap/selectionitem');
                    $itemModel->setData('item_id', $item_id);
                    $itemModel->setData('selection_id', $giftboxId);
                    $itemModel->setData('qty', $item_qty);
                    try {
                        $itemModel->save();
                    } catch (Exception $e) {
                        Mage::getSingleton('checkout/session')->addError($e->getMessage());
                    }
                }
                //reset session data giftbox id
                $session->setData('giftbox_id', null);
                //save data to session
                $giftWrapSessionData = $session->getData('giftwrap');
                if (count($giftWrapSessionData) == 0) {
                    $giftWrapSessionData = array();
                }
                if (!$addExist) {
                    $giftWrapSessionData[$params['product']] = array(
                        'giftwrap_add' => $addGiftWrap,
                        'giftbox_paper' => $giftboxpaper,
                        'giftbox_card' => $giftboxcard,
                        'giftbox_cardmessage' => $cardmessage
                    );
                    $session->setData('giftwrap', $giftWrapSessionData);
                }
            }
        }

        //add item to existing giftbox
        if ($addGiftWrap == 'exist' && !$addExist) {
            if ($params['existing_giftbox']) {
                $giftboxId = $params['existing_giftbox'];
                //Add giftwrap items
                if ($session->getData('giftwrap_existing_legalqty') && (int) $session->getData('giftwrap_existing_legalqty') > 0) {
                    $item_qty = $session->getData('giftwrap_existing_legalqty');
                    $itemCollection = Mage::getModel('giftwrap/selectionitem')->getCollection()
                            ->addFieldToFilter('item_id', $item_id);
                    if (count($itemCollection)) {
                        $selectionItem = $itemCollection->getFirstItem();
                        $newQty = (int) $selectionItem->getQty() + (int) $session->getData('giftwrap_existing_legalqty');
                        $selectionItem->setQty($newQty)->save();
                    } else {
                        $itemModel = Mage::getModel('giftwrap/selectionitem');
                        $itemModel->setData('item_id', $item_id);
                        $itemModel->setData('selection_id', $giftboxId);
                        $itemModel->setData('qty', $item_qty);
                        try {
                            $itemModel->save();
                        } catch (Exception $e) {
                            Mage::getSingleton('checkout/session')->addError($e->getMessage());
                        }
                    }
                    $session->setData('giftwrap_existing_legalqty', null);
                }

                //save infomation to session
                $giftWrapSessionData[$params ['product']] = array(
                    'giftwrap_add' => $addGiftWrap,
                    'giftbox_id' => $giftboxId,
                );
                $session->setData('giftwrap', $giftWrapSessionData);
            }
        }
    }

//end add giftbox item
//prepare options for product in cart - Marko
    public function catalogProductTypePrepareFullOptions($observer) {
        if (!Mage::getStoreConfig('giftwrap/general/active')) {
            return $this;
        }
        $session = $this->_getSession();
        $quote = $this->_getQuote();
        $maxQtyPerBox = Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');
        $buyrequest = $observer->getBuyRequest();
        //save buy request to session
        $session->setData('giftwrap_buyrequest', $buyrequest);
        $transport = $observer->getTransport();
        $product = $observer->getProduct();
        $params = Mage::app()->getRequest()->getParams();
        $action = Mage::app()->getRequest()->getActionName();
        $validateItem = false;
        if ($action == 'savegiftbox') {
            $validateItem = true;
        }
        if ($product->getId() == $params['product']) {
            if ($action == 'add' || $action == 'exist' || $action = 'savegiftbox') {
                $validateItem = true;
            }
        }
        if ($buyrequest->getGiftwrapAdd() && $buyrequest->getGiftwrapAdd() == 'new' && $validateItem) {
            $itemQty = $buyrequest->getQty();
            if ($itemQty > $maxQtyPerBox) {
                $buyrequest->setQty($maxQtyPerBox);
            }
            if ($buyrequest->getGiftboxPaper()) {
                if (!$buyrequest->getGiftwrapGiftcard()) {
                    $message = '';
                } else {
                    $message = $buyrequest->getGiftboxMessage();
                    if (strlen(trim($message)) == 0) {
                        $message = '';
                    }
                }

                //save wrap paper
                $model = Mage::getModel('giftwrap/selection');
                $model->setQuoteId($quote->getId());
                $model->setGiftcardId($buyrequest->getGiftwrapGiftcard());
                $model->setMessage($message);
                $model->setStyleId($buyrequest->getGiftboxPaper());
                $model->setData('qty', 1);
                $model->setData('type', 1);
                if (Mage::getStoreConfig('giftwrap/calculation/amount_on_number_items')) {
                    $model->setCalculateByItem('1');
                } else {
                    $model->setCalculateByItem('0');
                }
                try {
                    $model->save();
                    if (Mage::getSingleton('checkout/session')->getData('order_giftbox')) {
                        $orderGiftbox = Mage::getSingleton('checkout/session')->getData('order_giftbox') . ',' . $model->getId();
                    } else {
                        $orderGiftbox = $model->getId();
                    }
                    Mage::getSingleton('checkout/session')->setData('order_giftbox', $orderGiftbox);
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }
                //check if quote does not save
                $not_save_quote = array();
                if (!$quote->getId()) {
                    $not_save_quote[] = $model->getId();
                    $session->setData('not_save_quote', $not_save_quote);
                }
                //set wrap paper id to session
                if ($params['giftwrap_add'] == 'exist') {
                    $session->setData('add_out_of_exist', 1);
                }
                $session->setData('giftbox_id', $model->getId());
                $session->setData('item_add_new_qty', $itemQty);
                //add item option
                $transport->options ['giftwrap'
                        ] = $model->getId() . ',' . $buyrequest->getGiftboxPaper() . ',' . $buyrequest->getGiftwrapGiftcard() . ',' . $buyrequest->getGiftboxMessage();
                //reset qty if item qty > max qty per box
            }
        }
        if ($buyrequest->getGiftwrapAdd() && $buyrequest->getGiftwrapAdd() == 'exist' && $validateItem) {
            if ($buyrequest->getExistingGiftbox()) {
                $giftboxId = $buyrequest->getExistingGiftbox();
                $giftbox = Mage::getModel('giftwrap/selection')->load($giftboxId);
                //add item option
                $transport->options['giftwrap'] = $giftboxId . ',' . $giftbox->getStyleId() . ',' . $giftbox->getGiftcardId() . ',' . $giftbox->getMessage();
                $items = $giftbox->getItemCollection();
                //reset qty if item qty > max qty per box
                $currentNumberItem = 0;
                foreach ($items as $item) {
                    $currentNumberItem += $item->getQty();
                }
                if (($currentNumberItem + $buyrequest->getQty()) > $maxQtyPerBox) {
                    //reset qty if item qty > max qty per box
                    $remainingQty = $maxQtyPerBox - $currentNumberItem;
                    $buyrequest->setQty($remainingQty);
                }
            }
        }
        if ($session->getData('giftbox_update_box') && $buyrequest->getGiftwrapAdd() && $buyrequest->getGiftwrapAdd() == 'update_item' && $buyrequest->getItemProductId() == $product->getId()) {
            $itemQty = $buyrequest->getQty();
            if ($buyrequest->getGiftboxPaper()) {
                if (!$buyrequest->getGiftwrapGiftcard()) {
                    $message = '';
                } else {
                    $message = $buyrequest->getGiftboxMessage();
                    if (strlen(trim($message)) == 0) {
                        $message = '';
                    }
                }
                //save wrap paper
                $model = Mage::getModel('giftwrap/selection');
                $model->setQuoteId($quote->getId());
                $model->setGiftcardId($buyrequest->getGiftwrapGiftcard());
                $model->setMessage($message);
                $model->setStyleId($buyrequest->getGiftboxPaper());
                $model->setData('qty', 1);
                $model->setData('type', 1);
                if (Mage::getStoreConfig('giftwrap/calculation/amount_on_number_items')) {
                    $model->setCalculateByItem('1');
                } else {
                    $model->setCalculateByItem('0');
                }
                try {
                    $model->save();
                    if (Mage::getSingleton('checkout/session')->getData('order_giftbox')) {
                        $orderGiftbox = Mage::getSingleton('checkout/session')->getData('order_giftbox') . ',' . $model->getId();
                    } else {
                        $orderGiftbox = $model->getId();
                    }
                    Mage::getSingleton('checkout/session')->setData('order_giftbox', $orderGiftbox);
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }
                //set wrap paper id to session
                $session->setData('giftbox_id', $model->getId());

                $session->setData('giftbox_item_qty', $itemQty);
                //add item option
                $transport->options['giftwrap'
                        ] = $model->getId() . ',' . $buyrequest
                                ->getGiftboxPaper() . ',' . $buyrequest->getGiftwrapGiftcard() . ',' . $buyrequest->getGiftboxMessage();
                //Zend_Debug::Dump($transport);die();
                //reset qty if item qty > max qty per box
            }
        }
    }

    public function catalogProductTypePrepareLiteOptions($observer) {
        if (!Mage::getStoreConfig('giftwrap/general/active')) {
            return $this;
        }
        $session = $this->_getSession();
        $quote = $this->_getQuote();
        $maxQtyPerBox = Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');
        $buyrequest = $observer->getBuyRequest();
        $transport = $observer->getTransport();
        $product = $observer->getProduct();
        $params = Mage::app()->getRequest()->getParams();
        $action = Mage::app()->getRequest()->getActionName();
        $validateItem = false;
        if ($product->getId() == $params ['product']) {
            if ($action == 'add') {
                $validateItem = true;
            }
            if ($action == 'exist') {
                $validateItem = true;
            }
        }
        if ($buyrequest->getGiftwrapAdd() && $buyrequest->getGiftwrapAdd() == 'new' && $validateItem) {
            $itemQty = $buyrequest->getQty();
            if ($buyrequest->getGiftboxPaper()) {
                if (!$buyrequest->getGiftwrapGiftcard()) {
                    $message = '';
                } else {
                    $message = $buyrequest->getGiftboxMessage();
                    if (strlen(trim($message)) == 0) {
                        $message = '';
                    }
                }
//save wrap paper
                $model = Mage::getModel('giftwrap/selection');
                $model->setQuoteId($quote->getId());
                $model->setGiftcardId($buyrequest->getGiftwrapGiftcard());
                $model->setMessage($message);
                $model->setStyleId($buyrequest->getGiftboxPaper());
                $model->setData('qty', 1);
                $model->setData('type', 1);
                if (Mage::getStoreConfig('giftwrap/calculation/amount_on_number_items')) {
                    $model->setCalculateByItem('1');
                } else {
                    $model->setCalculateByItem('0');
                }
                try {
                    $model->save();
                    if (Mage::getSingleton('checkout/session')->getData('order_giftbox')) {
                        $orderGiftbox = Mage::getSingleton('checkout/session')->getData('order_giftbox') . ',' . $model->getId();
                    } else {
                        $orderGiftbox = $model->getId();
                    }
                    Mage::getSingleton('checkout/session')->setData('order_giftbox', $orderGiftbox);
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }
//set wrap paper id to session
                $session->setData('giftbox_id', $model->getId());
//add item option
                $transport->options ['giftwrap'
                        ] = $model->getId() . ',' . $buyrequest->getGiftboxPaper() . ',' . $buyrequest->getGiftwrapGiftcard() . ',' . $buyrequest->getGiftboxMessage();
//reset qty if item qty > max qty per box
                if ($itemQty > $maxQtyPerBox) {
                    $buyrequest->setQty($maxQtyPerBox);
                }
            }
        }
        if ($buyrequest->getGiftwrapAdd() && $buyrequest->getGiftwrapAdd() == 'exist' && $validateItem) {
            if ($buyrequest->getExistingGiftbox()) {
                $giftboxId = $buyrequest->getExistingGiftbox();
                $giftbox = Mage::getModel('giftwrap/selection')->load($giftboxId);
//add item option
                $transport->options['giftwrap'] = $giftboxId . ',' . $giftbox->getStyleId() . ',' . $giftbox->getGiftcardId() . ',' . $giftbox->getMessage();
                $items = $giftbox->getItemCollection();
//reset qty if item qty > max qty per box
                $currentNumberItem = 0;
                foreach ($items as $item) {
                    $currentNumberItem += $item->getQty();
                }
                if (($currentNumberItem + $buyrequest->getQty()) > $maxQtyPerBox) {
//reset qty if item qty > max qty per box
                    $remainingQty = $maxQtyPerBox - $currentNumberItem;
                    $buyrequest->setQty($remainingQty);
                }
            }
        }
        if ($session->getData('giftbox_update_box') && $buyrequest->getGiftwrapAdd() && $buyrequest->getGiftwrapAdd() == 'update_item' && $buyrequest->getItemProductId() == $product->getId()) {
            $itemQty = $buyrequest->getQty();
            if ($buyrequest->getGiftboxPaper()) {
                if (!$buyrequest->getGiftwrapGiftcard()) {
                    $message = '';
                } else {
                    $message = $buyrequest->getGiftboxMessage();
                    if (strlen(trim($message)) == 0) {
                        $message = '';
                    }
                }
//save wrap paper
                $model = Mage::getModel('giftwrap/selection');
                $model->setQuoteId($quote->getId());
                $model->setGiftcardId($buyrequest->getGiftwrapGiftcard());
                $model->setMessage($message);
                $model->setStyleId($buyrequest->getGiftboxPaper());
                $model->setData('qty', 1);
                $model->setData('type', 1);
                if (Mage::getStoreConfig('giftwrap/calculation/amount_on_number_items')) {
                    $model->setCalculateByItem('1');
                } else {
                    $model->setCalculateByItem('0');
                }
                try {
                    $model->save();
                    if (Mage::getSingleton('checkout/session')->getData('order_giftbox')) {
                        $orderGiftbox = Mage::getSingleton('checkout/session')->getData('order_giftbox') . ',' . $model->getId();
                    } else {
                        $orderGiftbox = $model->getId();
                    }
                    Mage::getSingleton('checkout/session')->setData('order_giftbox', $orderGiftbox);
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }
                //set wrap paper id to session
                $session->setData('giftbox_id', $model->getId());

                $session->setData('giftbox_item_qty', $itemQty);
                //add item option
                $transport->options['giftwrap'
                        ] = $model->getId() . ',' . $buyrequest->getGiftboxPaper() . ',' .
                        $buyrequest->getGiftwrapGiftcard() . ',' . $buyrequest->getGiftboxMessage();
                //reset qty if item qty > max qty per box
            }
        }
    }

    //end of set options for item - Marko
    //TrungHa: set giftwrap used after invoice
    public function setItemGiftWrapUsed($observer) {
        $invoiceId = $observer->getInvoice()->getId();
        $giftboxes = Mage::getSingleton('adminhtml/session')->getData('giftbox');
        if ($giftboxes) {
            foreach ($giftboxes as $giftbox) {
                Mage::getModel('giftwrap/selection')->
                        load($giftbox)
                        ->setIsInvoiced('1')
                        ->setInvoiceId($invoiceId)
                        ->save();
            }
        }
        Mage::getSingleton('adminhtml/session')->setData('giftbox', null);
    }

    public function refundSaveAfter($observer) {
        $creditmemoId = $observer->getCreditmemo()->getId();
        $giftboxes = Mage::getSingleton('adminhtml/session')->getData('giftbox_refund');
        if ($giftboxes) {
            foreach ($giftboxes as $giftbox) {
                Mage::getModel('giftwrap/selection')->load($giftbox)
                        ->setIsRefunded('1')
                        ->setCreditmemoId($creditmemoId)
                        ->save();
            }
        }
        Mage::getSingleton('adminhtml/session')->setData('giftbox_refund', null);
        //end TrungHa
    }

}
