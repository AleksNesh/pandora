<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shopperslideshow_Block_Adminhtml_Shopperrevolution_Grid_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

	public function _getValue(Varien_Object $row)
    {
        if ($getter = $this->getColumn()->getGetter()) {
            $val = $row->$getter();
        }
        $val = $row->getData($this->getColumn()->getIndex());
        $val = str_replace("no_selection", "", $val);
	    $out = '';
	    if ( !empty($val) ) {
	        $url = Mage::getBaseUrl('media') . $val;
	        $out = '<center><a href="'.$url.'" target="_blank" id="imageurl">';
	        $out .= "<img src=". $url ." width='150px' />";
	        $out .= '</a></center>';
	    }

	    return $out;

    }
}