<?php
/**
 * Brim LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brim LLC Commercial Extension License
 * that is bundled with this package in the file license.pdf.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.brimllc.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@brimllc.com so we can send you a copy immediately.
 *
 * @category   Brim
 * @package    Brim_Groupedoptions
 * @copyright  Copyright (c) 2011-2012 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */
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
class Brim_Groupedoptions_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Get product options
	 *
	 * @return array
	 */
	public function getOptions($product)
	{
		$options = array();
		foreach($product->getProductOptionsCollection() as $option){
			$option->setProduct($product);
            $options[] = $option;
		}
			
		return $options;
	}

	/**
	 * Returns html for an option
	 *
	 * NOTE: currently, this method supports only select
	 * and field inputs (per requirements).
	 */
	public function getOptionHtml($option, $layout){

		$type = Mage::getSingleton('catalog/product_option')->getGroupByType($option->getType());
		
		if($type == 'text'){
			return sprintf("<input type=\"text\" id=\"%s\" name=\"%s\" class=\"%s\" />",
			$option->getTitle().'-'.$option->getProduct()->getId(),
			'super_options['.$option->getProduct()->getId().']['.$option->getId().']',
			'product-custom-option product-monogram-option product-custom-option-'.$option->getProduct()->getId());
		}else{
			$input = $layout->createBlock('core/html_select')
			->setData(array(
                    'id' => $option->getTitle().'-'.$option->getProduct()->getId(),
                    'class' => 'product-custom-option product-monogram-option product-custom-option-'.$option->getProduct()->getId(),
					'name' => 'super_options['.$option->getProduct()->getId().']['.$option->getId().']'))
			->addOption('', '- Select '.Mage::helper('brim_monogram')->getOptionTitle($option).' -');

			// load options
			foreach ($option->getValues() as $value) {
				$input->addOption($value->getOptionTypeId(), $value->getTitle());
			}
				
			return $input->toHtml();
		}

		return null;
	}

	/**
	 * Gets a products configuration in JSON.
     *
     * @param $product Mage_Catalog_Model_Product
     *
     * @return string
	 */
	public function getProductViewJsonConfig($product) {
        return Mage::getSingleton('groupedoptions/block_container_product_view')->setProduct($product)->getJsonConfig();
	}

	/**
	 * Gets a products options configuration in JSON.
     *
     * @param $product Mage_Catalog_Model_Product
     *
     * @return string
	 */
	public function getOptionsJsonConfig($product) {
        return Mage::getSingleton('groupedoptions/block_container_product_view_options')->setProduct($product)->getJsonConfig();
	}

	/**
	 * This function returns true if the product contains custom options
     *
     * @param $product Mage_Catalog_Model_Product
     *
     * @return boolean
	 */
	public function hasCustomOptions($product){

		// if grouped product, check each associated product for options
		if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED){
			$associatedProducts = $product->getTypeInstance(true)
			->getAssociatedProducts($product);

			foreach($associatedProducts as $associatedProduct){
				if(count($associatedProduct->getProductOptionsCollection()) > 0){
					return true;
				}
			}
		}

		// return true if product contains custom options
		return count($product->getProductOptionsCollection()) > 0;
	}

	/**
	 * Since it's a bad practice to overwrite core magento abstract classes via
	 * copying the source in the local folder structure, this method contains the
	 * same code found in Mage_Catalog_Model_Product_Type_Abstract.getSku; however,
	 * it also includes logic to allow certain options to be disregarding from
	 * sku concatination.
	 *
	 * NOTE: Since we're providing the same logic here that is in the base abstract
	 * product type class, it is very important to check this logic during future
	 * magento upgrades to ensure nothing gets broken.
	 *
	 * @param Mage_Catalog_Model_Product $product
     * @return string
	 */
	public function getProductSku($product){
		$excludeSkus = explode(',', Mage::getConfig()->getNode(self::XML_PATH_EXCLUDE_OPTIONS_SKU));
		$skuDelimiter = '-';
		$sku = $product->getData('sku');
		if ($optionIds = $product->getCustomOption('option_ids')) {
			foreach (explode(',', $optionIds->getValue()) as $optionId) {
				if ($option = $product->getOptionById($optionId)) {

					// pass over options with a title contained in the config
					if(in_array($option->getTitle(), $excludeSkus)){
						continue;
					}

					$quoteItemOption = $product->getCustomOption('option_'.$optionId);

					$group = $option->groupFactory($option->getType())
					->setOption($option)->setListener(new Varien_Object());

					if ($optionSku = $group->getOptionSku($quoteItemOption->getValue(), $skuDelimiter)) {
						$sku .= $skuDelimiter . $optionSku;
					}

					if ($group->getListener()->getHasError()) {
						$product
						->setHasError(true)
						->setMessage(
						$group->getListener()->getMessage()
						);
					}

				}
			}
		}
		return $sku;
	}

    /**
     * Returns a unique attribute id based on product and attribute ids.
     *
     * @param $attribute
     * @return string
     */
    public function getHtmlConfigAttributeId($attribute) {

        $productId          = (string)$attribute->getProductId();
        $len                = strlen($productId);
        $encodedProductId   = '';

        // translate the product id to a letter string.  Allows unique element ids w/o breaking existing javscript.
        // Magento Product.Config object uses the /[a-z]*/ pattern to remove everything but the attribute id.
        for ($i = 0; $i < $len; $i++) {
            $encodedProductId .= chr(substr($productId, $i, 1)+97);
        }

        return 'attribute' . $encodedProductId . $attribute->getAttributeId();
    }

    /**
     * Resolves to the custom options template.
     *
     * @return string
     */
    public function includeCustomOptionsTemplate() {

        /** @var Brim_Groupedoptions_Block_Product_View_Options $goBlock */
        $goBlock = Mage::app()->getLayout()->getBlock('groupedoptions');

        $templateName = Mage::getDesign()->getTemplateFilename($goBlock->getSimpleTemplate());

        return $templateName;
    }

    /**
     * Compat. method for magento 1.4.0.x
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isProductAvailable(Mage_Catalog_Model_Product $product) {
        return (method_exists($product, 'isAvailable') ? $product->isAvailable() : $product->isSaleable());
    }

    /**
     * Compat method for Magento 1.4.0.x
     *
     * Magento 1.4.0.x does not have the check so we default to true if the methods does not exist.
     *
     * @param Mage_Catalog_Block_Product_View_Type_Grouped $block
     * @param Mage_Catalog_Model_Product $product
     */
    public function getCanShowProductPrice(Mage_Catalog_Block_Product_View_Type_Grouped $block,
                                           Mage_Catalog_Model_Product $product) {
        if (method_exists($block, 'getCanShowProductPrice')) {
            $canShow = $block->getCanShowProductPrice($product);
        } else {
            $canShow = true;
        }

        return $canShow;
    }
}