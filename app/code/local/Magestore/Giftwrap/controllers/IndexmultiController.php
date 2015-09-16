<?php

class Magestore_Giftwrap_IndexmultiController extends Mage_Core_Controller_Front_Action {

    protected $_totalRenderers;
    protected $_defaultRenderer = 'checkout/total_default';
    protected $_checkout = null;
    protected $_quote = null;
    protected $_totals;

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
                Mage::getModel('giftwrap/selection')->loadByQuoteId($quoteId,
                        $itemId)->delete();
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
                            $mesSave = substr($selModel->getMessage(), 0,
                                            $style->getCharacter());
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

    public function giftboxmultiAction() {
        //Zend_Debug::dump(get_class_methods($this->getLayout()));die();
        $address_id = $this->getRequest()->getParam('id');
        //   Zend_Debug::dump($address_id);die();
        $form_html = $this->getLayout()
                        ->createBlock('giftwrap/giftbox_papermulti')
                        ->setTemplate('giftwrap/giftbox/papermulti.phtml')
                        ->toHtml();
        $this->getResponse()->setBody($form_html);
    }

    public function savegiftboxAction() {
        $address_id = $this->getRequest()->getParam('address_id');
        Mage::register('address_id', $address_id);
		$addressQuote = Mage::getModel('sales/quote_address')->load($address_id);
		$customerAddressId = (int)$addressQuote->getCustomerAddressId();
        $data = $this->getRequest()->getPost();
        if ((!isset($data['giftbox_paper'])) || (!$data['giftbox_paper'])) {
            $error = "Cannot save giftbox . Please select paper !";
            Mage::getSingleton('core/session')->addError($error);
            $this->_redirect('giftwrap/multishipping/addgiftwrap',array('address_id'=>$address_id));
            return;
        }
        //Zend_Debug::dump($data);die();
        if ((!isset($data['wrap'])) || (!is_array($data['wrap']))) {
            $error = "Cannot save giftbox . Please select item !";
            Mage::getSingleton('core/session')->addError($error);
            $this->_redirect('giftwrap/multishipping/addgiftwrap',array('address_id'=>$address_id));
            return;
        }
        if ((!isset($data['wrap_type'])) || (!$data['wrap_type'])) {
            $error = "Cannot save giftbox . Please select wrap type !";
            Mage::getSingleton('core/session')->addError($error);
            $this->_redirect('giftwrap/multishipping/addgiftwrap',array('address_id'=>$address_id));
            return;
        }

        $wraptype = $data['wrap_type'];

        $giftbox_items = array();
        if ($wraptype && count($data['wrap']) > 0) {

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
            $model->setGiftcardId($data['giftbox_giftcard']);
            $model->setMessage($data['giftbox_message']);
            $model->setStyleId($data['giftbox_paper']);
            $maxItems = Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');


            if ($wraptype == 1) {
                $model->setData('qty', 1);
                $model->setData('type', 1);
                $wrapall = $data['wrap'];
                $number_items = 0;
                //Zend_Debug::dump($wrapall);die();
                foreach ($wrapall as $itemId) {
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    $item = $quote->getItemById($itemId);
                    if (floatval($data['qty_wrap_' . $itemId]) > floatval(Mage::helper('giftwrap')->getNumberOfItemsCanWraped($item, $data['giftbox_id']))) {
                        Mage::getSingleton('core/session')->addError('Number items in giftbox is larger!');
                        $this->_redirect('giftwrap/multishipping/addgiftwrap',array('address_id'=>$address_id));
                        return;
                    }
                    $number_items += $data['qty_wrap_' . $itemId];
                }
                if ($number_items > $maxItems) {
                    Mage::getSingleton('core/session')->addError('Number items in giftbox is too large!');

                    $this->_redirect('giftwrap/multishipping/addgiftwrap',array('address_id'=>$address_id));
                    return;
                }
                /* if($data['giftbox_id'])
                  $model->setId($data['giftbox_id']);
                  else */
                 if ($address_id) {
                        $model->setData('addressgift_id',$address_id);
						$model->setData('addresscustomer_id',$customerAddressId);
                    }
                $model->setId(null);
                $model->save();
                if ($data['giftbox_id'])
                    Mage::helper('giftwrap')->deleteItemOfSelection($data['giftbox_id']);
                foreach ($wrapall as $itemId) {
                    $itemModel = Mage::getModel('giftwrap/selectionitem');
                    $itemModel->setData('item_id', $itemId);
                    $itemModel->setData('selection_id', $model->getId());
                    $itemModel->setData('qty', $data['qty_wrap_' . $itemId]);
                    try {
                        $itemModel->save();
                    } catch (Exception $e) {
                        Mage::getSingleton('core/session')->addError($e->getMessage());
                    }
                }
            }
            if ($wraptype == 2) {
                $wrapone = $data['wrap'];
                foreach ($wrapone as $itId) {
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    $item = $quote->getItemById($itId);
                    if (floatval($data['qty_wrap_' . $itId]) > floatval(Mage::helper('giftwrap')->getNumberOfItemsCanWraped($item, $data['giftbox_id']))) {
                        Mage::getSingleton('core/session')->addError('Number items in giftbox is larger!');

                        $this->_redirect('giftwrap/multishipping/addgiftwrap',array('address_id'=>$address_id));
                        return;
                    }
                    $model->setData('qty', $data['qty_wrap_' . $itId]);
                    $model->setData('type', 2);
                    if ($address_id) {
                        $model->setData('addressgift_id',$address_id);
						$model->setData('addresscustomer_id',$customerAddressId);
                    }
                    /* if($data['giftbox_id'])
                      $model->setId($data['giftbox_id']);
                      else */
                    $model->setId(null);
                    $model->save();
                    if ($data['giftbox_id'])
                        Mage::helper('giftwrap')->deleteItemOfSelection($data['giftbox_id']);
                    $itemModel = Mage::getModel('giftwrap/selectionitem');
                    $itemModel->setData('item_id', $itId);
                    $itemModel->setData('selection_id', $model->getId());
                    $itemModel->setData('qty', 1);

                    try {
                        //Zend_Debug::dump($itemModel->getData());die();
                        $itemModel->save();
                    } catch (Exception $e) {
                        Mage::getSingleton('core/session')->addError($e->getMessage());
                    }
                }
            }
            if ($data['giftbox_id']) {
                $model = Mage::getModel('giftwrap/selection')->load($data['giftbox_id']);
                $model->deleteAllItems();
                $model->delete();
            }

            $this->_redirect('giftwrap/multishipping/addgiftwrap',array('address_id'=>$address_id));
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
            Mage::getSingleton('core/session')->addError('Number items in giftbox is too large!');
            $this->_redirect('giftwrap/multishipping/addgiftwrap',array('address_id'=>$address_id));
            return;
        } else {
            $item = trim($item, ',');
            $model->setItemId($item);
            $model->setQuoteId($data['giftbox_quoteid']);
            if ($address_id) {
                $model->setData('addressgift_id',$address_id);
            }
            $model->save();
            $this->_redirect('giftwrap/multishipping/addgiftwrap',array('address_id'=>$address_id), array('address_id' => $address_id));
        }
    }

    public function deletegiftboxAction() {
        $id = $this->getRequest()->getParam('id');
        $giftbox = Mage::getModel('giftwrap/selection')->load($id);
        try {
            $giftbox->delete();
        } catch (Exception $e) {

        }
        $block = $this->getLayout()->createBlock('giftwrap/giftboxmulti');
        $block->setTemplate('giftwrap/giftboxmulti.phtml');

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