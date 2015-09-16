<?php

/*
 * 	Magestore
 * 	Version 0.1.0 : 0.1.4 create by Neo
 * 	Version 0.2.0 by magento 1.4.1 : Kend fix
 */
?>
<?php

class Magestore_Giftwrap_Helper_Data extends Mage_Core_Helper_Abstract {
    /*
     * 	Return boolean : giftwrap is enable or disable
     */

    // Start HoaNTT 26.09.14
     public function getAllPapers() {
        $papers = Mage::getModel('giftwrap/giftwrap')
                ->getCollection()
                ->addFieldToFilter(
                        'store_id', Mage::app()->getStore()->getId())
                ->addFieldToFilter('status', 1)
        ;
        $list = array();
        foreach ($papers as $paper) {
            $list[] = $paper;
        }
        return $list;
    }
    // End HoaNTT
    
    public function enableGiftwrap() {
        $quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
        if (Mage::getStoreConfig('giftwrap/general/active')) {
            if (count(Mage::getModel('giftwrap/giftwrap')->getCollection()
                                    ->addFieldToFilter('status', 1)
                                    ->addFieldToFilter('store_id', Mage::app()->getStore(true)
                                            ->getId())) == 0) {
                Mage::getModel('giftwrap/selection')->deleteSelectionByQuoteId($quoteId);
                return false;
            } else {
                return true;
            }
        } else {
            Mage::getModel('giftwrap/selection')->deleteSelectionByQuoteId($quoteId);
            return false;
        }
    }

    /*
     * 	return array : array ids of product enable giftwrap in shopping cart
     */

    public function arrayItems() {
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
        $session = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId(
                $quoteId);
        $productIds = array();
        if ($session) {
            foreach ($session as $value) {
                $productIds[] = $value['itemId'];
            }
        }
        return $productIds;
    }

    /*
     * 	return boolean : show checkbox selection or not
     */

    public function disableCheckGift() {
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
        $giftWrapOrder = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId(
                $quoteId);
        if ($giftWrapOrder) {
            foreach ($giftWrapOrder as $gift) {
                if ($gift['itemId'] == 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /*
     * 	$productId : int
     * 	return boolean : product enable giftwrap or not
     */

    public function isGiftwrap($productId = null) {
        if (!$productId instanceof Mage_Catalog_Model_Product) {
            $product = Mage::getModel('catalog/product')->load($productId);
        } else {
            $product = $productId;
        }

        if ((int) $product->getGiftwrap() == Magestore_Giftwrap_Model_Giftwrap::STATUS_ENABLED) {
            return true;
        }
        return false;
    }

    /*
     * 	return boolean : module is disable or enable wrapall
     */

    public function giftwrapAll() {
        return Mage::getStoreConfig('giftwrap/general/all_item', $this->getStoreId());
    }

    /*
     * 	return string : image of Giftwrap module
     */

    public function getGiftwrapIcon() {
        return Mage::getStoreConfig('giftwrap/style/icon_image');
    }

    /*
     * 	return boolean : show icon giftwrap module on product page or not
     */

    public function showIcon() {
        return Mage::getStoreConfig('giftwrap/general/show_icon', $this->getStoreId());
    }

    /*
     * 	return boolean : return false if items > maximum items
     */

    public function enableWrapall() {
        $maxItems = Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
        $items = Mage::getSingleton('checkout/cart')->getItems();
        $itemsOnCart = 0;
        $selectionCollection = Mage::getModel("giftwrap/selection")->getCollection()->addFieldToFilter(
                "quote_id", $quoteId);
        foreach ($items as $item) {
            $productId = $item->getProductId();
            if (Mage::getModel('catalog/product')->load($productId)->getGiftwrap()) {
                $itemsOnCart += $item->getQty();
            }
            if (count($selectionCollection)) {
                foreach ($selectionCollection as $selection) {
                    if ($selection->getItemId() == $item->getId() &&
                            ($item->getQty() > $maxItems)) {
                        $selection->delete();
                    }
                }
            }
        }
        if ($itemsOnCart > $maxItems) {
            //Mage::getModel('giftwrap/selection')->getCollection()->loadByQuoteId($quoteId);
            Mage::getModel('giftwrap/selection')->deleteSelection();
            return false;
        }
        return true;
    }

    /*
      this function check the mixmum number of Item when giftwrap all
     */

    public function checkMaximumGiftwrapItems() {
//        $maxItems = Mage::getStoreConfig(
//                        'giftwrap/general/maximum_items_wrapall');
//        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
//        $items = Mage::getSingleton('checkout/cart')->getItems();
//        $itemsOnCart = 0;
//        foreach ($items as $item) {
//            $productId = $item->getProductId();
//            if (Mage::getModel('catalog/product')->load($productId)->getGiftwrap()) {
//                $itemsOnCart += $item->getQty();
//            }
//        }
//        if ($itemsOnCart > $maxItems) {
//            Mage::getModel('giftwrap/selection')->loadByQuoteId($quoteId, 0)->delete();
//            return false;
//        }
//        return true;
    }

    public function get_wrap_all_message() {
        $maxItems = Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');
        $message = Mage::getStoreConfig('giftwrap/message/can_not_wrap_all');
        return $this->__($message) . ": " . $maxItems;
    }

    /*
     * 	return boolean : if Config on adminhtml is Yes -> return true
     */

    public function getCalculateOnItems() {
        return Mage::getStoreConfig('giftwrap/calculation/amount_on_number_items');
    }

    /*
     * 	return int : return value of config on adminhtml
     */

    public function getDecreasePrice() {
        $decrease = Mage::getStoreConfig(
                        'giftwrap/general/decrease_price_wrapall');
        if (is_numeric($decrease)) {
            return $decrease;
        } else {
            return 0;
        }
    }

    /*
     * 	return boolean : return value of config on adminhtml
     */

    public function getPersonalMessageTurnedOff() {
        return Mage::getStoreConfig(
                        'giftwrap/message/personal_message_disable_msg');
    }

    /*
     * 	update of version 0.2.0
     */
    /*
     * 	return float : return  giftwrap amount
     */

    public function giftwrapAmount_2() {
        $amountPrice = 0;
        $giftwrap_items = Mage::getModel('giftwrap/selection')->getSelection();
        $decrease = $this->getDecreasePrice();
        if (is_array($giftwrap_items)) {
            foreach ($giftwrap_items as $value) {
                $giftwrap = Mage::getModel('giftwrap/giftwrap')->load(
                        $value['styleId']);
                $amountPrice += (float) $this->subGiftwrapTotal(
                                $giftwrap->getPrice(), $value['itemId']);
            }
        }
        $items = Mage::getSingleton('checkout/cart')->getItems();
        $countProductGiftwrap = 0;
        foreach ($items as $item) {
            $productId = $item->getProductId();
            if (Mage::getModel('catalog/product')->load($productId)->getGiftwrap()) {
                $countProductGiftwrap++;
            }
        }
        $itemIds = array();
        if (is_array($giftwrap_items)) {
            foreach ($giftwrap_items as $value) {
                $itemIds[] = $value['itemId'];
            }
        }
        if ($decrease != 0 && in_array(0, $itemIds) && $countProductGiftwrap >= 2) {
            $amountPrice = $amountPrice - $amountPrice * $decrease / 100;
        }
        return $amountPrice;
    }

    public function giftwrapAmount($storeId = null, $address_id = null, $customer_address = null) {

        $amount = 0;
        $quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
        $items = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId, $storeId, $address_id, $customer_address);
        if ($this->getCalculateOnItems()) {
            if (is_array($items)) {
                foreach ($items as $item) {
                    $giftbox = Mage::getModel('giftwrap/selection')->load($item['id']);

                    $style = Mage::getModel('giftwrap/giftwrap')->load($item['styleId']);
                    $giftcard = Mage::getModel('giftwrap/giftcard')->load($item['giftcardId']);
                    $number = 0;
                    $its = $giftbox->getItemCollection();

                    foreach ($its as $it) {
                        $number += $it->getQty();
                    }
                    $amount += floatval($number) * floatval($giftbox->getQty()) * (floatval($style->getPrice()) + floatval($giftcard->getPrice()));
                }
            }
        } else {
            if (is_array($items)) {
                foreach ($items as $item) {
                    $giftbox = Mage::getModel('giftwrap/selection')->load($item['id']);
                    $style = Mage::getModel('giftwrap/giftwrap')->load(
                            $item['styleId']);
                    $giftcard = Mage::getModel('giftwrap/giftcard')->load(
                            $item['giftcardId']);
                    $amount += floatval($giftbox->getQty()) * (floatval($style->getPrice()) +
                            floatval($giftcard->getPrice()));
                }
            }
        }

        return $amount;
    }

    /*
     * 	@param1 : price ( float)
     * 	@param2 : id (int)
     * 	return float : return  sub total giftwrap amount
     */

    public function subGiftwrapTotal($price, $id) {
        $items = Mage::getSingleton('checkout/cart')->getItems();
        $amount = 0;
        if ($this->getCalculateOnItems()) {
            if ($id == 0) {
                $itemsOnCart = 0;
                foreach ($items as $item) {
                    $productId = $item->getProductId();
                    if (Mage::getModel('catalog/product')->load($productId)->getGiftwrap()) {
                        $itemsOnCart += $item->getQty();
                    }
                }
                $amount = $price * $itemsOnCart;
            } else {
                foreach ($items as $item) {
                    $productId = $item->getProductId();
                    if ($productId == $id) {
                        $amount = $price * ($item->getQty());
                    }
                }
            }
        } else {
            if ($id == 0) {
                $itemsOnCart = 0;
                foreach ($items as $item) {
                    $productId = $item->getProductId();
                    if (Mage::getModel('catalog/product')->load($productId)->getGiftwrap()) {
                        $itemsOnCart++;
                    }
                }
                $amount = $price; //*$itemsOnCart;
            } else {
                $amount = $price;
            }
        }
        return $amount;
    }

    public function init($productfile) {
        $this->_productfile = $productfile;
        return $this;
    }

    public function getListStore() {
        $list = array();
        $storeCollection = Mage::getModel('core/store')->getCollection();
        //$list[''] = 'Select stote';
        foreach ($storeCollection as $store) {
            $list[$store->getId()] = $store->getName();
        }
        return $list;
    }

    public function getStoreOption() {
        $options = array();
        $list = $this->getListStore();
        if (count($list))
            foreach ($list as $key => $value)
                $options[] = array('label' => $value, 'value' => $key);
        return $options;
    }

    public function getAllItems() {
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
        $items = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId(
                $quoteId);
        return $items;
    }

    public function getOptionList($item) {
        /* $options = array();
          if ($optionIds = $item->getOptionByCode('option_ids')) {
          $options = array();
          foreach (explode(',', $optionIds->getValue()) as $optionId) {
          $product=Mage::getModel('catalog/product')->load($item->getProductId());
          if ($option = $product->getOptionById($optionId)) {

          $quoteItemOption = $item->getOptionByCode('option_' . $option->getId());

          $group = $option->groupFactory($option->getType())
          ->setOption($option)
          ->setQuoteItemOption($quoteItemOption);

          $options[] = array(
          'label' => $option->getTitle(),
          'value' => $group->getFormattedOptionValue($quoteItemOption->getValue()),
          'print_value' => $group->getPrintableOptionValue($quoteItemOption->getValue()),
          'option_id' => $option->getId(),
          'option_type' => $option->getType(),
          'custom_view' => $group->isCustomizedView()
          );
          }
          }
          }
          if ($addOptions = $item->getOptionByCode('additional_options')) {
          $options = array_merge($options, unserialize($addOptions->getValue()));
          }
          return $options; */
        $optionIds = array();
        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        $item->setProduct($product);
        try {
            if ($item->getData('product_type') == 'configurable') {
                $options = Mage::helper('catalog/product_configuration')->getConfigurableOptions($item);
                return $options;
            } else if ($item->getData('product_type') == 'simple') {
                $options = Mage::helper('catalog/product_configuration')->getCustomOptions($item);
                return $options;
            } else if ($item->getData('product_type') == 'bundle') {
                $options = Mage::helper('bundle/catalog_product_configuration')->getOptions($item);
                return $options;
            }
        } catch (Exception $e) {
            return array();
        }
    }

    public function getSwitchedTemplate($file) {
        //$package = 'pinky';
        $package = Mage::getStoreConfig('giftwrap/general/giftwrap_template');
        $styledir = 'style';
        $filename = substr($file, strrpos($file, DS) + 1);
        $offsetdir = str_replace('giftwrap' . DS, '', substr($file, strrpos($file, 'giftwrap' . DS)));
        $basedir = substr($file, 0, strrpos($file, 'giftwrap' . DS)) . 'giftwrap';
        $switchedfile = $basedir . DS . $styledir . DS . $package . DS . $offsetdir;
        //Zend_Debug::dump($switchedfile);die();
        return $switchedfile;
    }

    public function getSwitchedSkinFile() {
        $package = Mage::getStoreConfig('giftwrap/general/giftwrap_template');
        $styledir = 'css/style';
        $cssfile = $styledir . '/' . $package . '/' . 'giftwrap.css';
        return $cssfile;
    }

    public function getNumberOfItemsCanWraped($item, $selection_id = null) {
        $quoteId = $item->getQuoteId();
        $maxItems = Mage::getStoreConfig('giftwrap/calculation/maximum_items_wrapall');
        $qty = floatval($item->getQty());
        $selectionCollection = Mage::getModel('giftwrap/selection')
                ->getCollection()
                ->addFieldToFilter('quote_id', $quoteId)
        ;
        foreach ($selectionCollection as $selection) {
            $selectionItemCollection = Mage::getModel('giftwrap/selectionitem')
                    ->getCollection()
                    ->addFieldToFilter('selection_id', $selection->getId())
                    ->addFieldToFilter('item_id', $item->getId())
            ;
            foreach ($selectionItemCollection as $selectionItem) {
                $qty = floatval($qty) - floatval($selection->getQty()) * floatval($selectionItem->getQty());
            }
        }
        if ($selection_id) {
            $selection = Mage::getModel('giftwrap/selection')->load($selection_id);
            $selectionItem = Mage::getModel('giftwrap/selectionitem')->loadBySelectionAndItem($selection_id, $item
                            ->getId());
            return $qty + floatval($selectionItem->getQty()) * floatval($selection->getQty());
        }
        return $qty;
    }

    //delete selection item of selection
    public function deleteItemOfSelection($selectionId) {
        $selectionItems = Mage::getModel('giftwrap/selectionitem')
                ->getCollection()
                ->addFieldToFilter('selection_id', $selectionId)
        ;
        if (count($selectionItems)) {
            foreach ($selectionItems as $selectionItem) {
                $selectionItem->delete();
            }
        }
    }

    public function getAdminAllItems() {
        $quoteId = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getId();
        $items = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId(
                $quoteId);
        return $items;
    }

    // Giftwarp filter King_211112
    public function giftwrapAmountAdmin($storeId = null, $address_id = null, $customer_address = null) {

        $amount = 0;
        $quoteId = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getId();
        $items = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId, $storeId, $address_id, $customer_address);
        if ($this->getCalculateOnItems()) {
            if (is_array($items)) {
                foreach ($items as $item) {
                    $giftbox = Mage::getModel('giftwrap/selection')->load($item['id']);
                    
                    $style = Mage::getModel('giftwrap/giftwrap')->load($item['styleId']);
                    $giftcard = Mage::getModel('giftwrap/giftcard')->load($item['giftcardId']);
                    $number = 0;
                    $its = $giftbox->getItemCollection();
                    
                    foreach ($its as $it) {
                        $itemId = $it->getItemId();
                        //$orderItem = Mage::getModel('sales/quote_item')->load($itemId);
                        $number += $it->getQty();
                    }
                    $amount += floatval($number) * floatval($giftbox->getQty()) * (floatval($style->getPrice()) + floatval($giftcard->getPrice()));
                }
            }
        } else {
            if (is_array($items)) {
                foreach ($items as $item) {
                    $giftbox = Mage::getModel('giftwrap/selection')->load($item['id']);
                    $style = Mage::getModel('giftwrap/giftwrap')->load(
                            $item['styleId']);
                    $giftcard = Mage::getModel('giftwrap/giftcard')->load(
                            $item['giftcardId']);
                    $amount += floatval($giftbox->getQty()) * (floatval($style->getPrice()) +
                            floatval($giftcard->getPrice()));
                }
            }
        }

        return $amount;
    }

    /* King Update_UE 130610 */

    public function getStoreId() {
        return Mage::app()->getStore()->getId();
    }

    public function checkUseCard() {
         $gifcards = Mage::getModel('giftwrap/giftcard')->getCollection()
                ->addFieldToFilter(
                        'store_id', Mage::app()->getStore()
                        ->getId())
                ->addFieldToFilter('status', 1);
         
        if(count($gifcards) > 0 && Mage::getStoreConfig('giftwrap/general/use_card', $this->getStoreId())){
            return true;
        }else{
            return false;
        }
    }

    public function useConfirmDelete() {
        return Mage::getStoreConfig('giftwrap/general/confirm_delete', $this->getStoreId());
    }

    public function getNoteConfig($note, $store = null) {
        return Mage::getStoreConfig('giftwrap/note/' . $note, $store);
    }

    /**
     * Hai.Ta 6.6.2013
     * */
    public function saveIdItemQuote($itemQuote, $kt) {
        $ItemIdsCurrent = $this->getAllIdCurrentItems($itemQuote);
        asort($ItemIdsCurrent);
        $ItemdIdsPrevious = null;
        // Zend_debug::dump($ItemIdsCurrent);
        $model = Mage::getModel('giftwrap/selectionitem')->getCollection()
                ->addFieldToFilter('item_id', -1);

        if ($model->getSize()) {
            $ItemdIdsPrevious = $this->getAllIdPreviousItems($kt);
            asort($ItemdIdsPrevious);
            // Zend_debug::dump($ItemdIdsPrevious);die();
            foreach ($model as $item) {
                $position = array_search($item->getCheckReorder(), $ItemdIdsPrevious);
                $item->setData('item_id', $ItemIdsCurrent[$position]);
            }
            $model->save();
        }
        Mage::getSingleton('core/session')->setQuoteOldId(null);
        $this->deleteItemNotUse();
        return;
    }

    /**
     * 	return array id of item in current quote
     * */
    public function getAllIdCurrentItems($itemQuote) {
        $arrIdItemCurrent = array();

        foreach ($itemQuote as $item) {
            $arrIdItemCurrent[] = $item->getId();
        }

        return $arrIdItemCurrent;
    }

    // nothing
    public function test($collection) {
        $arr = array();
        foreach ($collection as $item) {
            $arr[] = $item->getCheckReorder();
        }
        return $arr;
    }

    /**
     * 	return array id of item in previous quote
     * */
    public function getAllIdPreviousItems($kt) {
        $arrIdItemOld = array();

        $quoteOldId = Mage::getSingleton('core/session')->getQuoteOldId();
        if ($kt == 2) {
            $quoteOld = Mage::getModel('sales/quote')
                            ->setStoreId(Mage::getSingleton('adminhtml/session_quote')->getStoreId())
                            ->load($quoteOldId)->getAllItems();
        } else {
            $quoteOld = Mage::getModel('sales/quote')->load($quoteOldId)->getAllItems();
        }

        foreach ($quoteOld as $item) {
            $arrIdItemOld[] = $item->getId();
        }
        // Zend_debug::dump($arrIdItemOld);die();
        return $arrIdItemOld;
    }

    public function deleteItemNotUse() {
        $model = Mage::getModel('giftwrap/selectionitem')->getCollection()
                ->addFieldToFilter('item_id', -1);
        $arrSelectId = array();
        if ($model->getSize()) {
            foreach ($model as $item) {
                $arrSelectId[] = $item->getSelectionId();
                $item->delete();
            }
        }
        $model->save();
        $this->deleteSelectionNotUse($arrSelectId);
    }

    public function deleteSelectionNotUse($array) {
        foreach ($array as $item) {
            $model = Mage::getModel('giftwrap/selection')->load($item);
            $model->delete();
            $model->save();
        }
    }

    //end Hai.Ta
}