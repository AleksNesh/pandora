<?php
/**
 * @category    Signaturelink
 * @package     SL_Signaturelink
 */

/**
 * Renderer for sub-heading in fieldset
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Smartbear_Alertsite_Block_Adminhtml_System_Form_Renderer_AdvanceLink
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return sprintf('<tr class="system-fieldset-sub-head"><td colspan="5">'
            . '<a href="%s">(click here to edit more advanced configuration options on your account)</a>'
            . '</td></tr>',
            Mage::getModel('adminhtml/url')->getUrl('*/alertsite/advance')
        );
    }
}
