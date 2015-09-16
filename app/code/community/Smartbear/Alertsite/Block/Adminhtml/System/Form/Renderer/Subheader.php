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
class Smartbear_Alertsite_Block_Adminhtml_System_Form_Renderer_Subheader
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
        return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5">'
            . '<div id="system-fieldset-sub-head-comment">%s</div></td></tr>',
            $element->getHtmlId(), $element->getComment()
        );
    }
}
