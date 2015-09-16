<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
    class Amasty_Ogrid_Block_Adminhtml_Settings extends Mage_Adminhtml_Block_Template{
        
        protected function _construct()
        {
            $this->setTemplate('amogrid/settings.phtml');
        }

        protected function _prepareLayout()
        {
            $this->setChild('save_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label' => Mage::helper('adminhtml')->__('Save Columns'),
                        'onclick' => 'varienAttributesForm.submit()',
                        'class' => 'save'
            )));


            return parent::_prepareLayout();
        }

        protected function getHeader()
        {
            return Mage::helper('amogrid')->__('Manage Columns');
        }

        protected function getSaveButtonHtml()
        {
            return $this->getChildHtml('save_button');
        }

        protected function getSaveFormAction()
        {
            return $this->getUrl('*/*/process');
        }
    }
?>