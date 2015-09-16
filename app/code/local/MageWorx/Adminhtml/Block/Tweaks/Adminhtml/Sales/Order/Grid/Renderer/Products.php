<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_Tweaks
 * @copyright  Copyright (c) 2011 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Magento Tweaks extension
 *
 * @category   MageWorx
 * @package    MageWorx_Tweaks
 * @author     MageWorx Dev Team
 */

class MageWorx_Adminhtml_Block_Tweaks_Adminhtml_Sales_Order_Grid_Renderer_Products
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Render product names field
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $products = explode("\n", $this->htmlEscape($row->getData($this->getColumn()->getIndex())));                
        $prCount=count($products);
        if ($prCount>3) {
            $products[$prCount-1].='<a href="" onclick="$(\'hdiv_'.$row->getData('increment_id').'\').style.display=\'none\'; $(\'a_'.$row->getData('increment_id').'\').style.display=\'block\'; return false;" style="float:right; font-weight:bold; text-decoration: none;" title="'.Mage::helper('tweaks')->__('Less..').'">↑</a>'
                .'</div>'
                .'<a href="" id="a_'.$row->getData('increment_id').'" onclick="$(\'hdiv_'.$row->getData('increment_id').'\').style.display=\'block\'; this.style.display=\'none\'; return false;" style="float:right; font-weight:bold; text-decoration: none;" title="'.Mage::helper('tweaks')->__('More..').'">↓</a>';            
            $products[2].='<div id="hdiv_'.$row->getData('increment_id').'" style="display:none">'.$products[3];
            unset($products[3]);                                    
            
        }        
        return implode('<br/>', $products);        
    }
}
