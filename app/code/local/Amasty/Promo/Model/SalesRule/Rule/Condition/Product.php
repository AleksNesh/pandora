<?php
/**
 * @copyright   Copyright (c) 2009-14 Amasty
 */
class Amasty_Promo_Model_SalesRule_Rule_Condition_Product extends Mage_SalesRule_Model_Rule_Condition_Product
{

    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['quote_item_sku'] = Mage::helper('salesrule')->__('Custom Options');
    }

    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $product = false;
        if ($object->getProduct() instanceof Mage_Catalog_Model_Product) {
            $product = $object->getProduct();
        } 
        else {
            $product = Mage::getModel('catalog/product')
                ->load($object->getProductId());
        }
        
        $product->setQuoteItemSku($object->getSku());
        
        //$newObject = new Varien_Object();
        $object->setProduct($product);
        
        return parent::validate($object);
    }
}
