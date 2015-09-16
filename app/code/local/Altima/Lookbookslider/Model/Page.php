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
class Altima_Lookbookslider_Model_Page extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('lookbookslider/page');
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $_collection = Mage::getSingleton('cms/page')->getCollection()
                ->addFieldToFilter('is_active', 1);

        $_result = array();
        foreach ($_collection as $item) {

            $data = array(
                'value' => $item->getData('page_id'),
                'label' => $item->getData('title'));
            $_result[] = $data;
        }
        return $_result;
    }
    
    
    public function toGridArray($slider_id)
    {
        $_collection = $this->getCollection()->addFieldToFilter('lookbookslider_id', $slider_id); 

        $_result = array();
        foreach ($_collection as $item) {            
            $page = Mage::getSingleton('cms/page')->load($item->getData('page_id'));
            $_result[$page->getId()] = $page->getTitle();
        }
        return $_result;
    }
}
