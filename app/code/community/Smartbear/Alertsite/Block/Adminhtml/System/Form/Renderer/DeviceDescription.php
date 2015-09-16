<?php

class Smartbear_Alertsite_Block_Adminhtml_System_Form_Renderer_DeviceDescription
    extends Mage_Adminhtml_Block_System_Config_Form_Field
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Get equipped with API!
     *
     * @return Smartbear_Alertsite_Model_AlertsiteApi
     */
    public function getApi()
    {
        return Mage::getModel('alertsite/alertsiteapi');
    }

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

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->addData(array(
            'html_id' => $element->getHtmlId()
        ));

        return $this->_toHtml();
    }

    protected function _toHtml()
    {

        $api = $this->getApi();
        $response = $api->getDeviceStatus();
        $deviceDescription = $api->getDeviceDescription();

        if (empty($deviceDescription))
            $deviceDescription = 'None configured';

        $html = '<h5 id="'. $this->getHtmlId() . '"><span>';
        $html .= $deviceDescription;
        $html .= '</span></h5>';

        return $html;

    }
}
