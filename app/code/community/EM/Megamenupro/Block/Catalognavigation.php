<?php
class EM_Megamenupro_Block_Catalognavigation extends Mage_Catalog_Block_Navigation implements Mage_Widget_Block_Interface {
	protected $time_cache	=	60;

	public function cleanCache()
    {
        Mage::app()->cleanCache('catalog_category');
    }

	public function _toHtml(){
		$this->setTemplate('em_megamenupro/catalognavigation.phtml');
		return parent::_toHtml();
	}

	public function renderCategoriesMenuHtml($catId = 0, $level = 0, $outermostItemClass = '', $childrenWrapClass = '') {
		if (!$catId) 
			return parent::renderCategoriesMenuHtml($level, $outermostItemClass, $childrenWrapClass);
		else {
			$catId	=	str_replace("category/","",$catId);
			$storeId = Mage::app()->getStore()->getId();
			$lib_multicache	=	Mage::helper('megamenupro/multicache');
			$html	=	$lib_multicache->get('navigation_'.$storeId.'_'.$catId.'_'.$level);
			if(!$html){
				$this->cleanCache();
				if (Mage::helper('catalog/category_flat')->isEnabled())
					$categories = Mage::getModel('megamenupro/megamenupro')->getCategories($catId);
				else 
					$categories = Mage::getModel('catalog/category')->getCategories($catId);

				$activeCategories = array();
				foreach ($categories as $child) {
					if ($child->getIsActive()) {
						$activeCategories[] = $child;
					}
				}
				$activeCategoriesCount = count($activeCategories);
				$hasActiveCategoriesCount = ($activeCategoriesCount > 0);

				if (!$hasActiveCategoriesCount) {
					return '';
				}
			
				$html = '';
				$j = 0;
				foreach ($activeCategories as $category) {
					$html .= $this->_renderCategoryMenuItemHtml(
						$category,
						$level,
						($j == $activeCategoriesCount - 1),
						($j == 0),
						true,
						$outermostItemClass,
						$childrenWrapClass,
						true
					);
					$j++;
				}
				
				$html	=	base64_encode($html);
				$this->time_cache	=	Mage::getStoreConfig('megamenupro/cache_time');
				$lib_multicache	=	Mage::helper('megamenupro/multicache');
				$lib_multicache->set('navigation_'.$storeId.'_'.$catId.'_'.$level,$html,$this->time_cache*60);
			}
			return base64_decode($html);
		}
	}

}