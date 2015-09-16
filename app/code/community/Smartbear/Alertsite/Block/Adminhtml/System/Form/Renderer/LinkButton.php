<?php

class Smartbear_Alertsite_Block_Adminhtml_System_Form_Renderer_LinkButton
    extends Mage_Adminhtml_Block_System_Config_Form_Field
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'alertsite/linkbutton.phtml';

    /**
     * Unset scope label and pass further to parent render()
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        // Unset the scope label near the button
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(array(
            'button_label' => $originalData['button_label'],
            'html_id' => $element->getHtmlId(),
            'button_url' => Mage::getModel('adminhtml/url')->getUrl($originalData['button_url']),
        ));
        return $this->_toHtml();
    }
}
