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

class Ash_Slideshow_Block_Adminhtml_Slideshow_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
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
        $form->setHtmlIdPrefix('slideshow_');
        $form->setFieldNameSuffix('slideshow');
        $this->setForm($form);
        $fieldset = $form->addFieldset('slideshow_form', array('legend' => $this->_helper->__('Slide information')));

        $fieldset->addField('id', 'hidden', array(
            'name'      => 'id',
            'label'     => $this->_helper->__('Id'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldset->addField('slideshow_name', 'text', array(
            'name'      => 'slideshow_name',
            'label'     => $this->_helper->__('Name'),
            'class'     => 'required-entry',
            'note'      => 'The slide show name.',
            'required'  => true,
        ));

        $fieldset->addField('layout', 'select', array(
            'name'      => 'layout',
            'label'     => $this->_helper->__('Layout'),
            'note'      => 'The layout to be used with this slide show.',
            'values'    => array(
                array(
                    'value'     => 'default',
                    'label'     => $this->_helper->__('Default'),
                ),
                array(
                    'value'     => 'hero',
                    'label'     => $this->_helper->__('Hero'),
                ),
                array(
                    'value'     => 'product_slider',
                    'label'     => $this->_helper->__('Product Slider'),
                ),
                array(
                    'value'     => 'carousel_modal',
                    'label'     => $this->_helper->__('Carousel with Modal Content'),
                ),
            ),
        ));

        $fieldset->addField('status', 'select', array(
            'name'      => 'status',
            'label'     => $this->_helper->__('Status'),
            'note'      => 'The slide show status.',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => $this->_helper->__('Enabled'),
                ),

                array(
                    'value'     => 0,
                    'label'     => $this->_helper->__('Disabled'),
                ),
            ),
        ));


        if (Mage::getSingleton('adminhtml/session')->getSlideshowData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getSlideshowData());
            Mage::getSingleton('adminhtml/session')->setSlideshowData(null);
        } elseif (Mage::registry('slideshow_slide_data')) {
            $form->setValues(Mage::registry('slideshow_slide_data')->getData());
        }

        return parent::_prepareForm();
    }
}
