<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-08-10T14:18:45+02:00
 * File:          app/code/local/Xtento/OrderExport/Model/Output/Xsl.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Output_Xsl extends Xtento_OrderExport_Model_Output_Abstract
{
    protected $_searchCharacters;
    protected $_replaceCharacters;

    public function convertData($exportArray)
    {
        if (!@class_exists('XSLTProcessor')) {
            Mage::throwException(Mage::helper('xtento_orderexport')->__('The XSLTProcessor class could not be found. This means your PHP installation is missing XSL features. You cannot export output formats using XSL Templates without the PHP XSL extension. Please get in touch with your hoster or server administrator to add XSL to your PHP configuration.'));
        }
        // Some libxml settings
        $useInternalXmlErrors = libxml_use_internal_errors(true);
        if (function_exists('libxml_disable_entity_loader')) {
            #$loadXmlEntities = libxml_disable_entity_loader(true);
        }
        libxml_clear_errors();

        $outputArray = array();
        // Should the ampersand character etc. be encoded?
        $escapeSpecialChars = false;
        if (preg_match('/method="(xml|html)"/', $this->getProfile()->getXslTemplate())) {
            $escapeSpecialChars = true;
        }
        // Convert to XML first
        $convertedData = Mage::getModel('xtento_orderexport/output_xml', array('profile' => $this->getProfile(), 'escape_special_chars' => $escapeSpecialChars))->convertData($exportArray);
        // Get "first" file from returned data.
        $convertedXml = array_pop($convertedData);
        $xmlDoc = new DOMDocument;
        if (!$xmlDoc->loadXML($convertedXml)) {
            $this->_throwXmlException(Mage::helper('xtento_orderexport')->__("Could not load internally processed XML. Bad data maybe?"));
        }
        // Load different file templates
        $outputFormatMarkup = $this->getProfile()->getXslTemplate();
        try {
            $outputFormatXml = new SimpleXMLElement($outputFormatMarkup, null, strpos($outputFormatMarkup, '<') === false);
        } catch (Exception $e) {
            $this->_throwXmlException(Mage::helper('xtento_orderexport')->__("Please repair the XSL Template of this profile. You need to have a valid XSL Template in order to export orders. Could not load XSL Template:"));
        }
        $outputFormats = $outputFormatXml->xpath('//files/file');
        if (empty($outputFormats)) {
            Mage::throwException(Mage::helper('xtento_orderexport')->__('No <files><file></file></files> markup found in XSL Template. Please repair your XSL Template.'));
        }
        // Loop through each <file> node
        foreach ($outputFormats as $outputFormat) {
            $fileAttributes = $outputFormat->attributes();
            $filename = $this->_replaceFilenameVariables(current($fileAttributes->filename), $exportArray);
            $fileType = @current($fileAttributes->type); // Currently supported: xsl (default), invoice_pdf, packingslip_pdf

            if (!$fileType || empty($fileType) || $fileType == 'xsl') {
                $charsetEncoding = @current($fileAttributes->encoding);
                $searchCharacters = @current($fileAttributes->search);
                $replaceCharacters = @current($fileAttributes->replace);

                $xslTemplate = current($outputFormat->xpath('*'))->asXML();
                $xslTemplate = $this->_preparseXslTemplate($xslTemplate);

                // XSL Template
                $xslTemplateObj = new XSLTProcessor();
                $xslTemplateObj->registerPHPFunctions();
                // Add some parameters accessible as $variables in the XSL Template (example: <xsl:value-of select="$exportid"/>)
                $this->_addVariablesToXSLT($xslTemplateObj, $exportArray, $xslTemplate);
                // Import stylesheet
                /* Alternative DOMDocument version for versions that don't like SimpleXMLElements in importStylesheet */
                /*
                $domDocument = new DOMDocument();
                $domDocument->loadXML($xslTemplate);
                $xslTemplateObj->importStylesheet($domDocument);
                */
                $xslTemplateObj->importStylesheet(new SimpleXMLElement($xslTemplate));
                if (libxml_get_last_error() !== FALSE) {
                    $this->_throwXmlException(Mage::helper('xtento_orderexport')->__("Please repair the XSL Template of this profile. There was a problem processing the XSL Template:"));
                }

                // Replace certain characters
                if (!empty($searchCharacters)) {
                    $this->_searchCharacters = str_split(str_replace(array('quote'), array('"'), $searchCharacters));
                    if (in_array('"', $this->_searchCharacters)) {
                        $replacePosition = array_search('"', $this->_searchCharacters);
                        if ($replacePosition !== false) {
                            $this->_searchCharacters[$replacePosition] = '&quot;';
                        }
                    }
                    $this->_replaceCharacters = str_split($replaceCharacters);
                    $actualXmlData = preg_replace_callback('/<(.*)>(.*)<\/(.*)>/um', array($this, '_replaceCharacters'), $convertedXml);
                    $xmlDoc->loadXML($actualXmlData);
                }

                $outputBeforeEncoding = @$xslTemplateObj->transformToXML($xmlDoc);
                $output = $this->_changeEncoding($outputBeforeEncoding, $charsetEncoding);
                if (!$output && !empty($outputBeforeEncoding)) {
                    $this->_throwXmlException(Mage::helper('xtento_orderexport')->__("Please repair the XSL Template of this profile, check the encoding tag, or make sure output has been generated by this template. No output has been generated."));
                }
                $outputArray[$filename] = $output;
            }
            if (($fileType == 'invoice_pdf' || $fileType == 'packingslip_pdf') && Mage::registry('is_test_orderexport') !== true) {
                $orderIds = array();
                foreach ($exportArray as $exportObject) {
                    if (isset($exportObject['order'])) {
                        $orderIds[] = $exportObject['order']['entity_id'];
                    } else {
                        $orderIds[] = $exportObject['entity_id'];
                    }
                }
                if (!empty($orderIds)) {
                    $pdfContent = $this->_getPdfsForOrderIds($orderIds, $fileType);
                    if ($pdfContent) {
                        $outputArray[$filename] = $pdfContent;
                    }
                }
            }
        }
        // Reset libxml settings
        libxml_use_internal_errors($useInternalXmlErrors);
        if (function_exists('libxml_disable_entity_loader')) {
            #libxml_disable_entity_loader($loadXmlEntities);
        }
        // Return generated files
        return $outputArray;
    }

    protected function _replaceCharacters($matches)
    {
        return "<$matches[1]>" . str_replace($this->_searchCharacters, $this->_replaceCharacters, $matches[2]) . "</$matches[3]>";
    }

    protected function _addVariablesToXSLT(XSLTProcessor $xslTemplateObj, $exportArray, $xslTemplateXml)
    {
        if ($this->_isRequiredInXslTemplate('$totalitemcount', $xslTemplateXml)) {
            // Total item count
            $xslTemplateObj->setParameter('', 'totalitemcount', $this->getVariableValue('total_item_count', $exportArray));
        }
        if ($this->_isRequiredInXslTemplate('$collectioncount', $xslTemplateXml)) {
            // Collection count
            $xslTemplateObj->setParameter('', 'collectioncount', $this->getVariableValue('collection_count', $exportArray));
        }
        if ($this->_isRequiredInXslTemplate('$ordercount', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'ordercount', $this->getVariableValue('collection_count', $exportArray)); // Legacy
        }
        // Export ID
        if ($this->_isRequiredInXslTemplate('$exportid', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'exportid', $this->getVariableValue('export_id', $exportArray));
        }
        // Date information
        if ($this->_isRequiredInXslTemplate('$dateFromTimestamp', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'dateFromTimestamp', $this->getVariableValue('date_from_timestamp', $exportArray));
        }
        if ($this->_isRequiredInXslTemplate('$dateToTimestamp', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'dateToTimestamp', $this->getVariableValue('date_to_timestamp', $exportArray));
        }
        // GUID
        if ($this->_isRequiredInXslTemplate('$guid', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', '$guid', $this->getVariableValue('guid', $exportArray));
        }
        return $this;
    }

    /*
     * Check if the variable is used in the XSL Template and only if yes return true
     */
    protected function _isRequiredInXslTemplate($variable, $xslTemplateXml)
    {
        if (strpos($xslTemplateXml, $variable) === FALSE) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * Many old XSL Templates are still using orders/order. Replace with objects/object on the fly.
     */
    private function _preparseXslTemplate($xslTemplate)
    {
        return str_replace(
            array('<xsl:for-each select="orders/order">', '<xsl:for-each select="customers/customer">', '<xsl:for-each select="invoices/invoice">', '<xsl:for-each select="shipments/shipment">'),
            '<xsl:for-each select="objects/object">',
            $xslTemplate
        );
    }

    private function _getPdfsForOrderIds($orderIds, $fileType)
    {
        $pdfType = 'invoice';
        if ($fileType == 'packingslip_pdf') {
            $pdfType = 'shipment';
        }
        $collection = Mage::getResourceModel('sales/order_' . $pdfType . '_collection')
            ->setOrderFilter($orderIds) // Be careful: Could be because of PdfCustomizer extension. Should be $orderId - why does the PDF get returned instantly?
            ->load();
        if ($collection->getSize() > 0) {
            if (!isset($pdf)) {
                $pdf = Mage::getModel('sales/order_pdf_' . $pdfType)->getPdf($collection);
            } else {
                $pages = Mage::getModel('sales/order_pdf_' . $pdfType)->getPdf($collection);
                $pdf->pages = array_merge($pdf->pages, $pages->pages);
            }
        }
        if (isset($pdf)) {
            return $pdf->render();
        }
        return false;
    }
}