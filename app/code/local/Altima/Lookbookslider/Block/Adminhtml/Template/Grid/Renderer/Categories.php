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
class Altima_Lookbookslider_Block_Adminhtml_Template_Grid_Renderer_Categories extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    
    public function _getValue(Varien_Object $row)
    {
        $data = $row->getData();
        $slider_id = $data['lookbookslider_id'];
        $categories = Mage::getModel('lookbookslider/category')->toGridArray($slider_id);
        $out='';
        if (!empty($categories)) {
            $out = '<ul>';
            foreach ($categories as $category) {
                $out .= '<li>'.$category.'</li>';                
            }
            $out .= '</ul>';            
        }

        return $out;

    }
}