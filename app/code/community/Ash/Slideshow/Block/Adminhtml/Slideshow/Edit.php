<?php
/**
 * Ash Slideshow Extension
 *
 * @category  Ash
 * @package   Ash_Slideshow
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

class Ash_Slideshow_Block_Adminhtml_Slideshow_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * $_helper
     * @var Ash_Slideshow_Helper_Data
     */
    protected $_helper;

    public function __construct()
    {
        parent::__construct();

        $this->_helper      = Mage::helper('ash_slideshow');
        $this->_objectId    = 'id';
        $this->_blockGroup  = 'ash_slideshow';
        $this->_controller  = 'adminhtml_slideshow';

        $this->_updateButton('save', 'label', $this->_helper->__('Save Slideshow'));
        $this->_updateButton('delete', 'label', $this->_helper->__('Delete Slideshow'));
    }

    public function getHeaderText()
    {
        if(Mage::registry('slideshow_slide_data') && Mage::registry('slideshow_slide_data')->getId()) {
            return $this->_helper->__("Edit Slide '%s'", $this->htmlEscape(Mage::registry('slideshow_slide_data')->getData('slideshow_name')));
        } else {
            return $this->_helper->__('Add New Slideshow');
        }
    }
}
