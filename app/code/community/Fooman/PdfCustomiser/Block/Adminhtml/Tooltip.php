<?php

class Fooman_PdfCustomiser_Block_Adminhtml_Tooltip extends Mage_Core_Block_Template
{
    public function _toHtml()
    {
        $text = 'Use the following JSON encoded string to further customise the order and width of columns in the pdf '
            .'(the order of the items determines the ordering of the columns and the numbers represent '
            .'the relative width of each column):<br/><br/>';
        $columnsAdvancedString = json_encode(Mage::helper('pdfcustomiser/adminhtml_tooltip')->getDefaultColumnOrderAndWidth());
        return $text.chunk_split($columnsAdvancedString, 51, "\r\n");
    }
}