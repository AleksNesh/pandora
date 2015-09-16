<?php
/**
 * Applet fieldset
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 * @copyright   Copyright (c) 2014 Alpine Consulting, Inc
 * @author      dmitry.ilin@alpineinc.com
 */

/**
 * Class Alpine_PrintPdf_Block_System_Config_Fieldset_Printer_Applet
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 */
class Alpine_PrintPdf_Block_System_Config_Fieldset_Printer_Applet
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
    implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_getHeaderHtml($element);
        $html .= $this->_getAppletHtml($element);

        foreach ($element->getSortedElements() as $field) {
            $html .= $field->toHtml();
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * Return applet html for fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getAppletHtml($element)
    {
        $html  = '<script type="text/javascript" src="' . $this->getJsUrl('qz/deployJava.js') . '"></script>';
        $html .= '<script type="text/javascript" src="' . $this->getJsUrl('qz/qzbase.js') . '"></script>';
        $html .= '<script type="text/javascript" src="' . $this->getJsUrl('qz/qzsettings.js') . '"></script>';

        $html .= '
            <script type="text/javascript">
                alpineQz = new AlpineQZ("' . $this->getJsUrl('qz') . '");
                alpineQz.deploy();
            </script>
        ';

        return $html;
    }

}