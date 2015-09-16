<?php
/**
* Ash Slideshow Extension
*
* @category  Ash
* @package   Ash_Slideshow
* @copyright Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
* @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* @author    August Ash Team <core@augustash.com>
*
**/

class Ash_Slideshow_Block_Adminhtml_Asset_New_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    /**
     * $_helper
     * @var Ash_Slideshow_Helper_Data
     */
    protected $_helper;

    /**
     * Magento's class contructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_helper = Mage::helper('ash_slideshow');
        $this->setId('slideshow_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->_helper->__('Slide Asset'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => $this->_helper->__('Asset Information'),
            'title'     => $this->_helper->__('Asset Information'),
            'content'   => $this->getLayout()->createBlock('ash_slideshow/adminhtml_asset_edit_tab_form')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
