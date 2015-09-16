<?php
/**
 * Brands block
 *
 */
class Queldorei_ShopperSettings_Block_Brands extends Mage_Core_Block_Template
{
	public function getBrands()
	{
		if ( !$this->isBrandsEnabled() ) {
			return ;
		}
		$isAllBrands = Mage::getStoreConfig('shopperbrands/main/brands', Mage::app()->getStore()->getId());
		if ( $isAllBrands ) {
			$brandsList = $this->getAllBrands();
		} else {
			$brandsList = $this->getBrandsWithProducts();
		}
		//add image / url to brands
		$brandDir = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'wysiwyg/queldorei/shopper/brands/';
		$brandExt = Mage::getStoreConfig('shopperbrands/main/image', Mage::app()->getStore()->getId());
		$brands = array();
		foreach ($brandsList as $b) {
			$brands[] = array(
				'name' => $b,
				'image' => $brandDir . str_replace(' ', '_', strtolower($b)) . '.' . $brandExt,
				'url' => Mage::getUrl() . 'catalogsearch/result/?q=' . urlencode($b),
			);
		}
		return $brands;
	}

	private function getBrandAttribute()
	{
		return Mage::getStoreConfig('shopperbrands/main/attribute', Mage::app()->getStore()->getId());
	}

	private function isBrandsEnabled()
	{
		$request = Mage::app()->getFrontController()->getRequest();
		$status = false;
		if ( Mage::getStoreConfig('shopperbrands/main/status', Mage::app()->getStore()->getId()) ) {
			$status = true;
			if ( Mage::getStoreConfig('shopperbrands/main/pages', Mage::app()->getStore()->getId()) == 1) {
				$status = false;
				if ($request->getModuleName() == 'cms' && $request->getControllerName() == 'index' && $request->getActionName() == 'index') {
					$status = true;
				}
			}
		}
		return $status;
	}

	private function getAllBrands()
	{
		$result = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $this->getBrandAttribute())
			->getSource()
			->getAllOptions(0, 1);
		$brands = array();
		foreach ($result as $b) {
			$brands[] = $b['label'];
		}
		return $brands;
	}

	private function getBrandsWithProducts()
	{
		$attribute = $this->getBrandAttribute();
		$collection = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToSelect($attribute)
			->addAttributeToFilter($attribute, array('neq' => ''))
			->addAttributeToFilter($attribute, array('notnull' => true));
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
		$brands = array_unique($collection->getColumnValues($attribute));
		return Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attribute)
			->getSource()
			->getOptionText(implode(',', $brands));
	}

}