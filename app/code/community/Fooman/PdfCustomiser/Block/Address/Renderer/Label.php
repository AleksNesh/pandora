<?php
class Fooman_PdfCustomiser_Block_Address_Renderer_Label extends Mage_Customer_Block_Address_Renderer_Default
{
    /**
     * @param Mage_Customer_Model_Address_Abstract $address
     * @param null                                 $format
     * @param string                               $template
     * @param string                               $helper
     *
     * @return string
     */
    public function render(Mage_Customer_Model_Address_Abstract $address, $format=null, $template = '', $helper = '')
    {
        $type =  new Varien_Object();
        $type->setCode('html');
        $this->setType($type);
        $format = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block')
            ->setPdfHelper($helper)
            ->setTemplate($template)
            ->toHtml();

        return parent::render($address, $format);
    }
}