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
class Altima_Lookbookslider_Block_Content_Bottom extends Altima_Lookbookslider_Block_Lookbookslider {
    protected $_position = 'CONTENT_BOTTOM';
    
    protected function _construct()
    {
        $this->addData(array(
        'cache_lifetime' => false,
        'cache_tags'     => array(Altima_Lookbookslider_Model_Lookbookslider::CACHE_TAG),
        'cache_key'      => $this->getCacheKey(),
        ));
    }
    
    public function _afterToHtml($html) {
        $formkey = Mage::getSingleton('core/session')->getFormKey();
        $form_key_placeholder = '<!-- form_key_placeholder -->';
        $html = str_replace($form_key_placeholder, $formkey, $html, $count);

        return parent::_afterToHtml($html);
    }

    
}