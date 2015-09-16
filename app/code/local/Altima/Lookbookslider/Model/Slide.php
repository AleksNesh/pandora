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
class Altima_Lookbookslider_Model_Slide extends Mage_Core_Model_Abstract
{    
    public function _construct()
    {
        parent::_construct();
        $this->_init('lookbookslider/slide');
    }

   public function cleanCache()
        {
            $cacheTags = Mage::getModel('lookbookslider/lookbookslider')->getCacheTags();
            Mage::app()->cleanCache($cacheTags);
        }   
       
   protected function _beforeSave() {
            $this->cleanCache();
            return parent::_beforeSave();
        }
   
   protected function _beforeDelete() {
            $this->cleanCache();
            return parent::_beforeDelete();
        }
}
