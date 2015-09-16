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

class Ash_Slideshow_Block_Adminhtml_Asset_New_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
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
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('slideshow_asset_form', array('legend' => $this->_helper->__('Asset information')));

        $fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => $this->_helper->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldset->addField('subtitle', 'text', array(
            'name'      => 'subtitle',
            'label'     => $this->_helper->__('Subtitle'),
        ));

        $fieldset->addField('link_text', 'text', array(
            'name'      => 'link_text',
            'label'     => $this->_helper->__('Link Text'),
        ));

        $fieldset->addField('link_url', 'text', array(
            'name'      => 'link_url',
            'label'     => $this->_helper->__('URL Target'),
        ));

        $fieldset->addField('use_modal', 'select', array(
            'name'      => 'use_modal',
            'label'     => $this->_helper->__('Display Content in Modal?'),
            'values'    => array(
                array(
                    'value'     => '',
                    'label'     => $this->_helper->__('Please Select...'),
                ),
                array(
                    'value'     => '1',
                    'label'     => $this->_helper->__('Yes'),
                ),
                array(
                    'value'     => '0',
                    'label'     => $this->_helper->__('No'),
                ),
            ),
        ));

        $fieldset->addField('description', 'textarea', array(
            'name'      => 'description',
            'label'     => $this->_helper->__('Description'),
            'note'      => 'The asset description. Used as caption for slideshow.',
        ));

        $fieldset->addField('image', 'file', array(
            'name'      => 'image',
            'label'     => $this->_helper->__('Image'),
            'required'  => false,
        ));

        $fieldset->addField('status', 'select', array(
            'name'      => 'status',
            'label'     => $this->_helper->__('Enabled'),
            'required'  => false,
            'values'    => array(
                array(
                    'value'     => '1',
                    'label'     => $this->_helper->__('Enabled'),
                ),

                array(
                    'value'     => '0',
                    'label'     => $this->_helper->__('Disabled'),
                ),
            ),
        ));

        if (Mage::getSingleton('adminhtml/session')->getSlideshowAssetData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getSlideshowAssetData());
            Mage::getSingleton('adminhtml/session')->setSlideshowAssetData(null);
        } elseif (Mage::registry('slideshow_asset_data')) {
            $form->setValues(Mage::registry('slideshow_asset_data')->getData());
        }

        return parent::_prepareForm();
    }
}
