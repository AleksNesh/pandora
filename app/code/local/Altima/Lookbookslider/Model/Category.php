<?php
/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 */
class Altima_Lookbookslider_Model_Category extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('lookbookslider/category');
    }    
    
    public function toGridArray($slider_id)
    {
        $_collection = $this->getCollection()->addFieldToFilter('lookbookslider_id', $slider_id); 

        $_result = array();
        foreach ($_collection as $item) {
            $page = Mage::getSingleton('catalog/category')->load($item->getData('category_id'));
            $_result[$page->getId()] = $page->getName();
        }
        return $_result;
    }
}
