<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @category    Brim
 * @package     Brim_Groupedoptions
 * @copyright   Copyright (c) 2011-2012 Brim LLC. (http://www.brimllc.com)
 */
class Brim_Groupedoptions_Model_Product_Type_Grouped extends Mage_Catalog_Model_Product_Type_Grouped
{
    public function getAssociatedConfigurableProductHtml($view, $product){
        return $view->getLayout()->createBlock('catalog/product_view_type_configurable')
        ->setTemplate('groupedconfigured/product/view/type/groupedconfigured/configurable.phtml')
        ->setProduct($product)
        ->toHtml();
    }

	/**
	 * Overriding this method to remove the 'required option' filter. We do this because required options are filtered
	 * and we want to be able to associate simple products with required options to grouped products.
	 */
	public function getAssociatedProducts($product = null)
	{
		if (!$this->getProduct($product)->hasData($this->_keyAssociatedProducts)) {
			$associatedProducts = array();

			if (!Mage::app()->getStore()->isAdmin()) {
				$this->setSaleableStatus($product);
			}

			$collection = $this->getAssociatedProductCollection($product)
			->addAttributeToSelect('*')
            /* BEGIN Brim Grouped-Options Customizations */
			//->addFilterByRequiredOptions()
            /* END Brim Grouped-Options Customizations */
			->setPositionOrder()
			->addStoreFilter($this->getStoreFilter($product))
			->addAttributeToFilter('status', array('in' => $this->getStatusFilters($product)));

			foreach ($collection as $item) {
				$associatedProducts[] = $item;
			}

			$this->getProduct($product)->setData($this->_keyAssociatedProducts, $associatedProducts);
		}
		return $this->getProduct($product)->getData($this->_keyAssociatedProducts);
	}

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and add logic specific to Grouped product type.
     *
     * Implemented for Magento 1.5+ CE.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $product = $this->getProduct($product);
        $productsInfo = $buyRequest->getSuperGroup();
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        if (!$isStrictProcessMode || (!empty($productsInfo) && is_array($productsInfo))) {
            $products = array();
            $associatedProductsInfo = array();
            $associatedProducts = $this->getAssociatedProducts($product);
            if ($associatedProducts || !$isStrictProcessMode) {
                foreach ($associatedProducts as $subProduct) {
                    $subProductId = $subProduct->getId();
                    if(isset($productsInfo[$subProductId])) {
                        $qty = $productsInfo[$subProductId];
                        if (!empty($qty) && is_numeric($qty)) {

                            /* BEGIN Brim Grouped-Options Customizations */
                            if ($subProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                                $isConfigurable = true;

                                $configBuyRequest = clone $buyRequest;

                                $superAttr = $buyRequest->getSuperAttribute();
                                if (array_key_exists($subProductId, $superAttr)) {
                                    $configBuyRequest->setSuperAttribute($superAttr[$subProductId]);
                                }

                                $configBuyRequest->setQty($qty);

                                if (!($superOptions = $buyRequest->getOptions())) {
                                    $superOptions = $buyRequest->getSuperOptions();
                                }
                                if (is_array($superOptions) && array_key_exists($subProductId, $superOptions)) {
                                    $optionIds = join(',', array_keys($superOptions[$subProductId]));
                                    $subProduct->addCustomOption('option_ids', $optionIds);

                                    foreach($superOptions[$subProductId] as $id => $value) {
                                        if (is_array($value)) {
                                            $value = join(',', $value);
                                        }
                                        $subProduct->addCustomOption('option_' . $id, $value);
                                    }

                                    $configBuyRequest->setOptions($superOptions[$subProductId]);
                                }

                                $_result = $subProduct->getTypeInstance(true)
                                    ->_prepareProduct($configBuyRequest, $subProduct, $processMode);
                            } else {
                            /* END Brim Grouped-Options Customizations */
                                $isConfigurable = false;

                                $clonedBuyRequest   = clone $buyRequest;
                                $superOptions       = $buyRequest->getSuperOptions();

                                if($superOptions && isset($superOptions[$subProduct->getId()])){
                                    $clonedBuyRequest->setOptions($superOptions[$subProduct->getId()]);
                                }

                                if ($subProduct->getHasOptions() && count($subProduct->getOptions()) == 0) {
                                    foreach ($subProduct->getProductOptionsCollection() as $_option) {
                                        $subProduct->addOption($_option);
                                    }
                                }
                                $_result = $subProduct->getTypeInstance(true)
                                    ->_prepareProduct($clonedBuyRequest, $subProduct, $processMode);
                            }

                            if (is_string($_result) && !is_array($_result)) {
                                return $_result;
                            }

                            if (!isset($_result[0])) {
                                return Mage::helper('checkout')->__('Cannot process the item.');
                            }

                            /* BEGIN Brim Grouped-Options Customizations */
                            if ($isConfigurable) {
                                foreach ($_result as $item) {
                                    $products[] = $item;
                                }
                            } else {
                            /* END Brim Grouped-Options Customizations */

                                if ($isStrictProcessMode) {
                                    $_result[0]->setCartQty($qty);
                                    $_result[0]->addCustomOption('product_type', self::TYPE_CODE, $product);

                                    $newBuyRequest = array(
                                        'super_product_config' => array(
                                            'product_type'  => self::TYPE_CODE,
                                            'product_id'    => $product->getId()
                                        )
                                    );

                                    if (isset($clonedBuyRequest)) {
                                        if ($clonedBuyRequest->getOptions()) {
                                            $newBuyRequest['options'] = $clonedBuyRequest->getOptions();
                                        }
                                        if ($clonedBuyRequest->getQty()) {
                                            $newBuyRequest['qty'] = $clonedBuyRequest->getQty();
                                        }
                                    }

                                    $_result[0]->addCustomOption('info_buyRequest', serialize($newBuyRequest));
                                    $products[] = $_result[0];
                                } else {
                                    $associatedProductsInfo[] = array($subProductId => $qty);
                                    $product->addCustomOption('associated_product_' . $subProductId, $qty);
                                }
                            }
                        }
                    }
                }
            }

            if (!$isStrictProcessMode || count($associatedProductsInfo)) {
                $product->addCustomOption('product_type', self::TYPE_CODE, $product);
                $product->addCustomOption('info_buyRequest',serialize($buyRequest));

                $products[] = $product;
            }

            if (count($products)) {
                return $products;
            }
        }

        return Mage::helper('catalog')->__('Please specify the quantity of product(s).');
    }


    /**
     * Implement for compatibility with Magento pre 1.5.0 CE.
     *
     * @param Varien_Object $buyRequest
     * @param null $product
     * @return array|string
     */
    public function prepareForCart(Varien_Object $buyRequest, $product = null)
    {
        $product = $this->getProduct($product);
        $productsInfo = $buyRequest->getSuperGroup();
        if (!empty($productsInfo) && is_array($productsInfo)) {
            $products = array();
            $associatedProducts = $this->getAssociatedProducts($product);
            if ($associatedProducts) {
                foreach ($associatedProducts as $subProduct) {
                    if(isset($productsInfo[$subProduct->getId()])) {
                        $qty = $productsInfo[$subProduct->getId()];
                        if (!empty($qty) && is_numeric($qty)) {


                            /* BEGIN Brim Grouped-Options Customizations */
                            if ($subProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                                $isConfigurable = true;

                                $configBuyRequest = clone $buyRequest;
                                $superAttr = $buyRequest->getSuperAttribute();
                                $configBuyRequest->setSuperAttribute($superAttr[$subProduct->getId()]);
                                $configBuyRequest->setQty($qty);

                                $_result = $subProduct->getTypeInstance(true)
                                    ->prepareForCart($configBuyRequest, $subProduct);
                            } else {
                            /* END Brim Grouped-Options Customizations */
                                $isConfigurable = false;

                                $clonedBuyRequest   = clone $buyRequest;
                                $superOptions       = $buyRequest->getSuperOptions();

                                if($superOptions && isset($superOptions[$subProduct->getId()])){
                                    $clonedBuyRequest->setOptions($superOptions[$subProduct->getId()]);
                                }

                                if ($subProduct->getHasOptions() && count($subProduct->getOptions()) == 0) {
                                    foreach ($subProduct->getProductOptionsCollection() as $_option) {
                                        $subProduct->addOption($_option);
                                    }
                                }
                                $_result = $subProduct->getTypeInstance(true)
                                    ->prepareForCart($clonedBuyRequest, $subProduct);
                            }

                            if (is_string($_result) && !is_array($_result)) {
                                return $_result;
                            }

                            if (!isset($_result[0])) {
                                return Mage::helper('checkout')->__('Cannot add the item to shopping cart.');
                            }

                            /* BEGIN Brim Grouped-Options Customizations */
                            if ($isConfigurable) {
                                foreach ($_result as $item) {
                                    $products[] = $item;
                                }
                            } else {
                            /* END Brim Grouped-Options Customizations */

                                $_result[0]->setCartQty($qty);
                                $_result[0]->addCustomOption('product_type', self::TYPE_CODE, $product);

                                $newBuyRequest = array(
                                    'super_product_config' => array(
                                        'product_type'  => self::TYPE_CODE,
                                        'product_id'    => $product->getId()
                                    )
                                );

                                if (isset($clonedBuyRequest)) {
                                    if ($clonedBuyRequest->getOptions()) {
                                        $newBuyRequest['options'] = $clonedBuyRequest->getOptions();
                                    }
                                    if ($clonedBuyRequest->getQty()) {
                                        $newBuyRequest['qty'] = $clonedBuyRequest->getQty();
                                    }
                                }

                                $_result[0]->addCustomOption('info_buyRequest', serialize($newBuyRequest));
                                $products[] = $_result[0];
                            }
                        }
                    }
                }
            }
            if (count($products)) {
                return $products;
            }
        }
        return Mage::helper('catalog')->__('Please specify the quantity of product(s).');
    }
}