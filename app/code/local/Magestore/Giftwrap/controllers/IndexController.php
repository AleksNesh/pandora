<?php

class Magestore_Giftwrap_IndexController extends Mage_Core_Controller_Front_Action {

    protected $_totalRenderers;
    protected $_defaultRenderer = 'checkout/total_default';
    protected $_checkout = null;
    protected $_quote = null;
    protected $_totals;

    public function getGiftCardMaxLenghtAction() {
        $result = array();
        $giftcardId = $this->getRequest()->getParam('giftcard_id');
        $giftcard = Mage::getModel('giftwrap/giftcard')->load($giftcardId);
        $result['character'] = $giftcard->getCharacter();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function giftboxdetailsAction() {
        $form_html = $this->getLayout()
                ->createBlock('giftwrap/giftbox')
                ->setTemplate('giftwrap/giftboxcatalog.phtml')
                ->toHtml();
        $this->getResponse()->setBody($form_html);
    }

    public function savemessagesubmitAction() {
        $count = $this->getRequest()->getParam('count');
        for ($i = 0; $i < $count; $i++) {
            $all = '';
            $all = $this->getRequest()->getParam('a' . $i);
            $all = str_replace('%20', ' ', $all);
            $all = explode('_array_giftwrap_message_area_', $all);
            $itemId = (int) $all[1];
            $message = (string) $all[0];
            $message = urldecode($message);
            $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
            $error_message = '';
            try {
                $model = Mage::getModel('giftwrap/selection')->loadByQuoteId(
                        $quoteId, $itemId);
                if ($model) {
                    $style = Mage::getModel('giftwrap/giftwrap')->load(
                            $model->getStyleId());
                    if (strlen($message) > $style->getCharacter()) {
                        $mesSave = substr($message, 0, $style->getCharacter());
                        $result['character'] = 'Maximum of this style is ' .
                                $style->getCharacter() . ' !';
                    } else {
                        $mesSave = $message;
                    }
                    if ($message == 'Type your personal message here') {
                        $model->setMessage('')->save();
                    } else {
                        $model->setMessage($mesSave)->save();
                    }
                }
                $result['personal_message'] = $model->getMessage();
                $result['item_id'] = $itemId;
            } catch (Exception $e) {
                $error_message = Mage::helper('giftwrap')->__(
                        'There was an error when saving your giftwrap selection');
            }
        }
    }

    public function reloadstyleAction() {
        $itemId = (int) $this->getRequest()->getParam('item_id');
        $action = (string) $this->getRequest()->getParam('action');
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
        $error_message = '';
        if ($action == 'add') {
            try {
                $styles = $this->getStyleCollection();
                $data = array();
                $data['quote_id'] = $quoteId;
                $data['store_id'] = Mage::app()->getStore()->getId();
                $data['item_id'] = $itemId;
                $data['style_id'] = $styles[0]['id'];
                $data['giftwrap_message'] = '';
                Mage::getSingleton('giftwrap/selection')->setData($data)->save();
            } catch (Exception $e) {
                $error_message = Mage::helper('giftwrap')->__(
                        'There was an error when saving your giftwrap selection');
            }
        } else {
            try {
                Mage::getModel('giftwrap/selection')->loadByQuoteId($quoteId, $itemId)->delete();
            } catch (Exception $e) {
                $error_message = Mage::helper('giftwrap')->__(
                        'There was an error when deleting your giftwrap selection');
            }
        }
        $result = array();
        if ($error_message == '') {
            $result['items'] = $this->_getErrorQtyItems();
            $result['html'] = $this->_getStyleSelectionHtml();
            $this->getResponse()->setBody(Zend_Json::encode($result));
        } else {
            $result['error'] = $error_message;
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    protected function _getStyleSelectionHtml() {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('giftwrap_index_styleselection');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    public function choosestyleAction() {
        if (!Mage::helper('magenotification')->checkLicenseKey('Giftwrap')) {
            Mage::getSingleton('core/config')->saveConfig(
                    'giftwrap/general/active', 2);
            $error_message = Mage::helper('giftwrap')->__(
                    'The gift wrap feature was disabled.');
        } else {
            $itemId = (int) $this->getRequest()->getParam('itemId');
            $styleId = (string) $this->getRequest()->getParam('styleId');
            $result = array();
            $error_message = '';
            if ($styleId) {
                try {
                    $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
                    $selModel = Mage::getModel('giftwrap/selection')->loadByQuoteId(
                            $quoteId, $itemId);
                    $selModel->setStyleId($styleId)->save();
                    $style = Mage::getModel('giftwrap/giftwrap')->load($styleId);
                    if ($style->getPersonalMessage() == 0) {
                        if ($selModel) {
                            $selModel->setMessage('')->save();
                        }
                    } else {
                        if (strlen($selModel->getMessage()) >
                                $style->getCharacter()) {
                            $mesSave = substr($selModel->getMessage(), 0, $style->getCharacter());
                            $selModel->setMessage($mesSave)->save();
                            $result['character'] = 'Maximum of this style is ' .
                                    $style->getCharacter() . ' !';
                        }
                    }
                    $message = $selModel->getMessage();
                    $result['image'] = $style->getImage();
                    $messageHtml = '';
                    $result['flag'] = 0;
                    if ($style->getPersonalMessage()) {
                        if ($message != '') {
                            $messageHtml = $message;
                        } else {
                            $messageHtml = "Type your personal message here";
                        }
                    } else {
                        $result['flag'] = 1;
                    }
                    $result['personal_message'] = $messageHtml;
                    $result['price'] = Mage::helper('core')->currency(
                            $style->getPrice());
                    $result['item_id'] = $itemId;
                } catch (Exception $e) {
                    $error_message = Mage::helper('giftwrap')->__(
                            'There was an error when saving your giftwrap selection');
                }
            }
        }
        if ($error_message != '')
            $result['error'] = $error_message;
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function reloadTotalAction() {
        $this->getQuote()->collectTotals();
        $result = array();
        $footerHtml = $this->_getFooterHtml();
        $bodyHtml = $this->_getTotalHtml();
        $result['html'] = $footerHtml . $bodyHtml;
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    protected function _getFooterHtml() {
        $html = '';
        $area = 'footer';
        $html .= '<tfoot>';
        foreach ($this->getTotals() as $total) {
            if ($total->getArea() != $area && $area != - 1) {
                continue;
            }
            $html .= $this->renderTotal($total, $area);
        }
        $html .= '</tfoot>';
        return $html;
    }

    protected function _getTotalHtml() {
        $html = '';
        $html .= '<tbody>';
        $area = null;
        foreach ($this->getTotals() as $total) {
            if ($total->getArea() != $area && $area != - 1) {
                continue;
            }
            $html .= $this->renderTotal($total, $area);
        }
        $html .= '</tbody>';
        return $html;
    }

    public function getCheckout() {
        if (null === $this->_checkout) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

    public function getQuote() {
        if (null === $this->_quote) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }

    public function getTotalsCache() {
        if (empty($this->_totals)) {
            $this->_totals = $this->getQuote()->getTotals();
        }
        return $this->_totals;
    }

    public function getTotals() {
        return $this->getTotalsCache();
    }

    public function renderTotal($total, $area = null, $colspan = 1) {
        $code = $total->getCode();
        if ($total->getAs()) {
            $code = $total->getAs();
        }
        return $this->_getTotalRenderer($code)
                        ->setTotal($total)
                        ->setColspan($colspan)
                        ->setRenderingArea(is_null($area) ? - 1 : $area)
                        ->toHtml();
    }

    protected function _getTotalRenderer($code) {
        if (!isset($this->_totalRenderers[$code])) {
            $this->_totalRenderers[$code] = $this->_defaultRenderer;
            $config = Mage::getConfig()->getNode(
                    "global/sales/quote/totals/{$code}/renderer");
            if ($config)
                $this->_totalRenderers[$code] = (string) $config;
            $this->_totalRenderers[$code] = $this->getLayout()->createBlock(
                    $this->_totalRenderers[$code], "{$code}_total_renderer");
        }
        return $this->_totalRenderers[$code];
    }

    public function checkAction() {
        foreach ($this->getTotals() as $total) {
            echo $total->getTotal();
            echo $total->getValue();
            echo "<br />";
        }
    }

    public function getStyleCollection() {
        $collection = Mage::getModel('giftwrap/giftwrap')->getCollection()
                ->addFieldToFilter('store_id', Mage::app()->getStore(true)
                        ->getId())
                ->setOrder('sort_order', 'asc')
                ->load();
        $styles = array();
        foreach ($collection as $item) {
            $styles[] = array('id' => $item['giftwrap_id'],
                'title' => $item['title'], 'price' => $item['price'],
                'image' => $item['image']);
        }
        return $styles;
    }

    public function saveMessageAction() {
        $itemId = (int) $this->getRequest()->getParam('itemId');
        $message = (string) $this->getRequest()->getParam('message');
        $message = urldecode($message);
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
        $error_message = '';
        try {
            $model = Mage::getModel('giftwrap/selection')->loadByQuoteId(
                    $quoteId, $itemId);
            if ($model) {
                $style = Mage::getModel('giftwrap/giftwrap')->load(
                        $model->getStyleId());
                if (strlen($message) > $style->getCharacter()) {
                    $mesSave = substr($message, 0, $style->getCharacter());
                    $result['character'] = 'Maximum of this style is ' .
                            $style->getCharacter() . ' !';
                } else {
                    $mesSave = $message;
                }
                if ($message == 'Type your personal message here') {
                    $model->setMessage('')->save();
                } else {
                    $model->setMessage($mesSave)->save();
                }
            }
            $result['personal_message'] = $model->getMessage();
            $result['item_id'] = $itemId;
        } catch (Exception $e) {
            $error_message = Mage::helper('giftwrap')->__(
                    'There was an error when saving your giftwrap selection');
        }
        if ($error_message != '')
            $result['error'] = $error_message;
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function pagersAction() {
        $this->loadLayout();
        $this->getLayout()
                ->getBlock('head')
                ->setTitle(Mage::helper('giftwrap')->__('Available Pagers'));
        $this->renderLayout();
    }

    public function wrapAllAction() {
        $action = (string) $this->getRequest()->getParam('action');
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
        $itemId = 0;
        $error_message = '';
        if ($action == 'add') {
            try {
                $styles = $this->getStyleCollection();
                $selections = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId(
                        $quoteId);
                $data = array();
                $data['quote_id'] = $quoteId;
                $data['store_id'] = Mage::app()->getStore()->getId();
                $data['item_id'] = $itemId;
                $data['style_id'] = $styles[0]['id'];
                $data['giftwrap_message'] = '';
                if (count($selections)) {
                    foreach ($selections as $selection) {
                        Mage::getModel('giftwrap/selection')->load(
                                $selection['id'])->delete();
                    }
                }
                Mage::getModel('giftwrap/selection')->setData($data)->save();
            } catch (Exception $e) {
                $error_message = Mage::helper('giftwrap')->__(
                        'There was an error when saving your giftwrap selection');
            }
        } else {
            try {
                $selections = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId(
                        $quoteId);
                if (count($selections)) {
                    foreach ($selections as $selection) {
                        Mage::getModel('giftwrap/selection')->load(
                                $selection['id'])->delete();
                    }
                }
            } catch (Exception $e) {
                $error_message = Mage::helper('giftwrap')->__(
                        'There was an error when deleting your giftwrap selection');
            }
        }
        $result = array();
        if ($error_message == '') {
            $result['html'] = $this->_getStyleSelectionHtml();
            $result['items'] = $this->_getAvailableItems();
            $this->getResponse()->setBody(Zend_Json::encode($result));
        } else {
            $result['error'] = $error_message;
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    public function _getAvailableItems() {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $giftwrapItems = array();
        foreach ($quote->getAllVisibleItems() as $item) {
            if (Mage::helper('giftwrap')->isGiftwrap(
                            $item->getProduct()
                                    ->getId())) {
                $giftwrapItems[] = $item->getId();
            }
        }
        return $giftwrapItems;
    }

    public function _getErrorQtyItems() {
        $maxItems = Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $giftwrapItems = array();
        $selectionCollection = Mage::getModel("giftwrap/selection")->getCollection()->addFieldToFilter(
                "quote_id", $quote->getId());
        foreach ($quote->getAllVisibleItems() as $item) {
            if (Mage::helper('giftwrap')->isGiftwrap(
                            $item->getProduct()
                                    ->getId())) {
                $productId = $item->getId();
                if (count($selectionCollection)) {
                    foreach ($selectionCollection as $selection) {
                        if ($selection->getItemId() == $item->getId() &&
                                ($item->getQty() > $maxItems)) {
                            $giftwrapItems[] = $item->getId();
                        }
                    }
                }
            }
        }
        return $giftwrapItems;
    }

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function giftboxAction() {
        //Zend_Debug::dump(get_class_methods($this->getLayout()));die();
        $form_html = $this->getLayout()
                ->createBlock('giftwrap/giftbox_paper')
                ->setTemplate('giftwrap/giftbox/paper.phtml')
                ->toHtml();
        $this->getResponse()->setBody($form_html);
    }

    public function showdeleteboxAction() {
        //Zend_Debug::dump(get_class_methods($this->getLayout()));die();
        $form_html = $this->getLayout()
                ->createBlock('giftwrap/giftbox_deletebox')
                ->setTemplate('giftwrap/giftbox/deletebox.phtml')
                ->toHtml();
        $this->getResponse()->setBody($form_html);
    }

    public function savegiftboxAction() {
        $data = $this->getRequest()->getPost();

        if ((!isset($data['giftbox_paper'])) || (!$data['giftbox_paper'])) {
            $error = "Cannot save giftbox . Please select paper !";
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart/index');
            return;
        }

        if ((!isset($data['wrap_type'])) || (!$data['wrap_type'])) {
            $error = "Cannot save giftbox . Please select wrap type !";
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart/index');
            return;
        }

        $wraptype = $data['wrap_type'];

        $giftbox_items = array();

        if ($wraptype) {

            $model = Mage::getModel('giftwrap/selection');
            $model->setQuoteId($data['giftbox_quoteid']);
            if ($data['giftbox_id']) {
                $model = $model->load($data['giftbox_id']);
                //$model -> deleteAllItems();
                //$model -> delete();
            }
            if (isset($data['use_giftcard']) && $data['use_giftcard']) {
                if (!isset($data['giftbox_giftcard']) || (!$data['giftbox_giftcard'])) {
                    $data['giftbox_giftcard'] = null;
                }
                if (!isset($data['giftbox_message']) || (!$data['giftbox_message'])) {
                    $data['giftbox_message'] = '';
                }
            } else {
                $data['giftbox_giftcard'] = null;
                $data['giftbox_message'] = '';
            }
            //zend_debug::dump($model);die();
            $model->setGiftcardId($data['giftbox_giftcard']);
            $model->setMessage($data['giftbox_message']);
            $model->setStyleId($data['giftbox_paper']);
            if (Mage::helper('giftwrap')->getCalculateOnItems()) {
                $model->setData('calculate_by_item', 1);
            } else {
                $model->setData('calculate_by_item', 0);
            }
            $maxItems = Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');

            /*
              King_130619
             */
            // Zend_Debug::dump($wraptype);die();
            if ($wraptype == 1) {
                $itemId = $data['hidden_item'];
                // Zend_Debug::dump($itemId);die();
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $item = $quote->getItemById($itemId);
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                $itemCurrentQty = $item->getQty();

                try {
                    $model->setData('qty', 1);
                    $model->setData('type', 1);
                    $wrapall = $data['wrap'];

                    $model->setId(null);
                    //zend_debug::dump($model);die();
                    $model->save();

                    if (Mage::getSingleton('checkout/session')->getData('order_giftbox')) {
                        $orderGiftbox = Mage::getSingleton('checkout/session')->getData('order_giftbox') . ',' . $model->getId();
                    } else {
                        $orderGiftbox = $model->getId();
                    }
                    Mage::getSingleton('checkout/session')->setData('order_giftbox', $orderGiftbox);
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addError($e);
                }

                if ($data['giftbox_id'])
                    Mage::helper('giftwrap')->deleteItemOfSelection($data['giftbox_id']);

                //add option giftwrap to current item
                $addOption = true;
                $itemOptions = $item->getOptions();
                foreach ($itemOptions as $option) {
                    $oData = $option->getData();
                    if (!$item->getParentItemId()) {
                        if ($oData['code'] == 'option_giftwrap') {
                            $addOption = false;
                        }
                    }
                }
                if ($addOption) {
                    $item->addOption(new Varien_Object(
                            array(
                        'product' => $product,
                        'code' => 'option_ids',
                        'value' => 'giftwrap'
                            )
                    ));
                    $item->addOption(new Varien_Object(
                            array(
                        'product' => $product,
                        'code' => 'gifwrap',
                        'value' => $model->getId() . ',' . $data['giftbox_paper'] . ',' . $data['giftbox_giftcard'] . ',' . $data['giftbox_message'],
                            )
                    ));
                }
                //zend_debug::dump(count($item));die();
                //Add new product if item qty larger than item qty can wrap
                if ((int) $itemCurrentQty > (int) $maxItems) {
                    $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                    $newQty = $itemCurrentQty - $maxItems;
                    $requestInfo = $productOptions['info_buyRequest'];
                    $requestInfo['giftwrap_add'] = 'new';
                    $requestInfo['giftbox_paper'] = $data['giftbox_paper'];
                    $requestInfo['giftwrap_giftcard'] = $data['giftbox_giftcard'];
                    $requestInfo['giftbox_message'] = $data['giftbox_message'];
                    $requestInfo['item_product_id'] = $item->getProductId();
                    $newQty1 = $newQty;
                    for ($i = 0; (float) $i < ceil($newQty1 / $maxItems); $i++) {
                        if ($newQty > $maxItems) {
                            $requestInfo['qty'] = (int) $maxItems;
                            $newQty -= $maxItems;
                        } else {
                            $requestInfo['qty'] = $newQty;
                        }
                        $cart = Mage::getModel('checkout/cart');
                        try {
                            $cart->addProduct($product, $requestInfo);
                        } catch (Exception $e) {
                            
                        }
                    }
                    $qtyItem = $maxItems;
                } else {
                    $qtyItem = $itemCurrentQty;
                }
                $item->setQty($qtyItem);
                $item->save();
                //Add giftwrap items
                $itemModel = Mage::getModel('giftwrap/selectionitem');
                $itemModel->setData('item_id', $itemId);
                $itemModel->setData('selection_id', $model->getId());
                $itemModel->setData('qty', $qtyItem);
                if (Mage::helper('giftwrap')->getCalculateOnItems()) {
                    $itemModel->setData('calculate_on_item', 1);
                }
                try {
                    $itemModel->save();  
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }
                Mage::getSingleton('checkout/session')->addSuccess("Saved gift wrap successfully.");
            }
            if ($wraptype == 2) {
                //Zend_Debug::dump($data);die();
                if ((!isset($data['wrap'])) || (!is_array($data['wrap']))) {
                    $error = "Cannot save giftbox . Please select item !";
                    Mage::getSingleton('checkout/session')->addError($error);
                    $this->_redirect('checkout/cart/index');
                    return;
                }
                $model->setData('qty', 1);
                $model->setData('type', 2);
                $wrapall = $data['wrap'];
                //Zend_Debug::dump($wrapall);
                foreach ($wrapall as $itemId) {
                    if (!$itemId || $itemId == '0')
                        continue;
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    $item = $quote->getItemById($itemId);
                    if (floatval($data['qty_wrap_' . $itemId]) > floatval(Mage::helper('giftwrap')->getNumberOfItemsCanWraped($item, $data['giftbox_id']))) {
                        Mage::getSingleton('checkout/session')->addError('Number of items in giftbox is too large!');
                        $this->_redirect('checkout/cart');
                        return;
                    }
                }
                try {
                    $model->setId(null);
                    if (Mage::getStoreConfig('giftwrap/calculation/amount_on_number_items')) {
                        $model->setCalculateByItem('1');
                    } else {
                        $model->setCalculateByItem('0');
                    }
                    $model->save();
                    if (Mage::getSingleton('checkout/session')->getData('order_giftbox')) {
                        $orderGiftbox = Mage::getSingleton('checkout/session')->getData('order_giftbox') . ',' . $model->getId();
                    } else {
                        $orderGiftbox = $model->getId();
                    }
                    Mage::getSingleton('checkout/session')->setData('order_giftbox', $orderGiftbox);
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addError($e);
                }
                $itemSelectId = array();
                $itemSelectId[] = $data['hidden_item'];
                $wrapall = array_unique(array_merge($wrapall, $itemSelectId));
                if ($data['giftbox_id'])
                    Mage::helper('giftwrap')->deleteItemOfSelection($data['giftbox_id']);
                $remainingQty = $maxItems;

                foreach ($wrapall as $itemId) {
                    if (!$itemId || $itemId == '0')
                        continue;
                    $item = $quote->getItemById($itemId);
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    $itemCurrentQty = $item->getQty();
                    $itemQty = $data['qty_wrap_' . $itemId];
                    $newItemQty = 0;
                    if ($remainingQty > 0) {
                        if (floatval($itemQty) >= floatval($remainingQty)) {
                            $newItemQty = (int) $remainingQty;
                            $remainingQty = 0;
                        } else {
                            $newItemQty = (int) $itemQty;
                            $remainingQty -= (int) $itemQty;
                        }
                        //add option giftwrap to current item
                        $addOption = true;
                        $itemOptions = $item->getOptions();
                        foreach ($itemOptions as $option) {
                            $oData = $option->getData();
                            if (!$item->getParentItemId()) {
                                if ($oData['code'] == 'option_giftwrap') {
                                    $addOption = false;
                                }
                            }
                        }
                        if ($addOption) {
                            $item->addOption(new Varien_Object(
                                    array(
                                'product' => $product,
                                'code' => 'option_ids',
                                'value' => 'giftwrap'
                                    )
                            ));
                            $item->addOption(new Varien_Object(
                                    array(
                                'product' => $product,
                                'code' => 'option_giftwrap',
                                'value' => $model->getId() . ',' . $data['giftbox_paper'] . ',' . $data['giftbox_giftcard'] . ',' . $data['giftbox_message'],
                                    )
                            ));
                        }

                        //Add new product if item qty larger than item qty can wrap
                        if ((int) $itemCurrentQty > (int) $newItemQty) {
                            $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                            $newQty = $itemCurrentQty - $newItemQty;
                            $requestInfo = $productOptions['info_buyRequest'];
                            $requestInfo['giftwrap_add'] = '';
                            $requestInfo['qty'] = $newQty;
                            $cart = Mage::getModel('checkout/cart');
                            try {
                                $cart->addProduct($product, $requestInfo);
                                $cart->save();
                            } catch (Exception $e) {
                                
                            }
                        }
                        $qtyItem = $newItemQty;
                        $item->setQty($qtyItem);
                        $item->save();
                        $itemModel = Mage::getModel('giftwrap/selectionitem');
                        $itemModel->setData('item_id', $itemId);
                        $itemModel->setData('selection_id', $model->getId());
                        $itemModel->setData('qty', $qtyItem);
                        try {
                            $itemModel->save();
                        } catch (Exception $e) {
                            Mage::getSingleton('checkout/session')->addError($e->getMessage());
                        }
                    } 
//                    else {
//                        Mage::helper('giftwrap/cart')->addGiftwrapProduct($item,$model->getData(),$itemQty);
//                    }
                }
                Mage::getSingleton('checkout/session')->addSuccess("Saved gift wrap successfully.");
            }
            if ($data['giftbox_id']) {
                $model = Mage::getModel('giftwrap/selection')->load($data['giftbox_id']);
                $model->deleteAllItems();
                $model->delete();
            }
            /*
              King_130619_End
             */
            $this->_redirect('checkout/cart');
            return;
        }

        $model = Mage::getModel('giftwrap/selection');
        if ($data['giftbox_id']) {
            $model = $model->load($data['giftbox_id']);
        }
        if (isset($data['use_giftcard']) && $data['use_giftcard']) {
            if (!isset($data['giftbox_giftcard']) || (!$data['giftbox_giftcard'])) {
                $data['giftbox_giftcard'] = null;
            }
            if (!isset($data['giftbox_message']) || (!$data['giftbox_message'])) {
                $data['giftbox_message'] = '';
            }
        } else {
            $data['giftbox_giftcard'] = null;
            $data['giftbox_message'] = '';
        }
        $model->setGiftcardId($data['giftbox_giftcard']);
        $model->setMessage($data['giftbox_message']);
        $model->setStyleId($data['giftbox_paper']);
        $items = $data['giftbox_item'];
        $item = '';
        $number = 0;
        foreach ($items as $it) {
            $number+=(float) Mage::getModel('sales/quote_item')->load($it)->getQty();
            //$number=$data['item-qty-'.$it];
            $item .=$it . ',';
        }
        $maxItems = Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');
        if ($number > $maxItems) {
            Mage::getSingleton('checkout/session')->addError('There are too many items in one gift box. The maximum number allowed is '.$maxItems.'. Automatically added new gift box(es) to cart.');
            $this->_redirect('checkout/cart');
            return;
        } else {
            $item = trim($item, ',');
            $model->setItemId($item);
            $model->setQuoteId($data['giftbox_quoteid']);
            $model->save();
            $this->_redirect('checkout/cart');
        }
    }

    public function deletegiftboxAction() {
        $id = $this->getRequest()->getParam('id');
        $giftbox = Mage::getModel('giftwrap/selection')->load($id);
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $itemcollection = Mage::getModel('giftwrap/selectionitem')
                ->getCollection()
                ->addFieldToFilter('selection_id', $id);

        try {
            //remove item in gift box
            foreach ($itemcollection as $wrappedItem) {
                //delete options of item
                $itemId = $wrappedItem->getItemId();
                $item = $quote->getItemById($itemId);
                $wrappedItem->delete();
            }
            //remove giftbox
            $giftbox->delete();
            Mage::getSingleton('checkout/session')->addSuccess('Deleted gift wrap successfully.');
        } catch (Exception $e) {
            
        }
        $block = $this->getLayout()->createBlock('giftwrap/giftbox');
        $block->setTemplate('giftwrap/giftbox.phtml');

        $this->getResponse()->setBody($block->toHtml());
    }

    public function listgiftboxAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function testAction() {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $entity_type = Mage::getSingleton("eav/entity_type")->loadByCode("catalog_product");
        $entity_type_id = $entity_type->getId();
        $collection = Mage::getModel("eav/entity_attribute")
                ->getCollection()
                ->addFieldToFilter("entity_type_id", $entity_type_id)
                ->addFieldToFilter("attribute_code", "giftwrap");

        if (count($collection)) {
            $collection->getFirstItem()->delete();
        }

        if (!count($collection)) {
            $data = array(
                'group' => 'General',
                'type' => 'int',
                'input' => 'select',
                'label' => 'Wrappable',
                'backend' => '',
                'frontend' => '',
                'source' => 'giftwrap/attribute_wrappable',
                'visible' => 1,
                'required' => 1,
                'user_defined' => 1,
                'is_searchable' => 1,
                'is_filterable' => 0,
                'is_comparable' => 1,
                'is_visible_on_front' => 0,
                'is_visible_in_advanced_search' => 1,
                'used_for_sort_by' => 0,
                'used_in_product_listing' => 1,
                'used_for_price_rules' => 1,
                'is_used_for_promo_rules' => 1,
                'position' => 2,
                'unique' => 0,
                'is_configurable' => 1,
                'default' => 0,
                'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
            );

            $setup->addAttribute('catalog_product', 'giftwrap', $data);

            $entity_type_id = $setup->getEntityTypeId('catalog_product');
            $data['entity_type_id'] = $entity_type_id;
            $attribute = Mage::getModel("eav/entity_attribute")
                    ->setData($data)
                    ->setId($setup->getAttributeId('catalog_product', 'giftwrap'));
            $attribute->save();
        }
    }

}