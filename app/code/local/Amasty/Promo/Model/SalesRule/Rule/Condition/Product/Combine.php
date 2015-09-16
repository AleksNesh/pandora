<?php
/**
 * @copyright   Copyright (c) 2009-14 Amasty
 */
class Amasty_Promo_Model_SalesRule_Rule_Condition_Product_Combine extends Mage_SalesRule_Model_Rule_Condition_Product_Combine
{
    public function validate(Varien_Object $object)
    {
        // for optimization if we no conditions
        if (!$this->getConditions()) {
            return true;
        }
        
        $origProduct  = null;
        if ($object->getHasChildren() && $object->getProductType() == 'configurable'){
            //remember original product
            $origProduct = $object->getProduct();


            $origSku     = $object->getSku();
            foreach ($object->getChildren() as $child) { 
                // only one itereation.
                $categoryIds = array_merge($child->getProduct()->getCategoryIds(),$origProduct->getCategoryIds());
                $categoryIds = array_unique($categoryIds);
                $object->setProduct($child->getProduct());
                $object->setSku($child->getSku());
                $object->getProduct()->setCategoryIds($categoryIds);
            }
        }
        $result = @Mage_Rule_Model_Condition_Combine::validate($object);
        if ($origProduct){
            // restore original product
            $object->setProduct($origProduct);    
            $object->setSku($origSku);    
        }        

        return $result;       
    }

}
