<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Helper_Pdf_Creditmemo extends Fooman_PdfCustomiser_Helper_Pdf
{
    /**
     * return column order and width for credit memos
     * either use default or json_decode value from Advanced field in the back-end
     *
     * @param void
     *
     * @return array
     * @access public
     */
    public function getColumnOrderAndWidth()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['creditmemocolumnwidths'])) {
            if (Mage::getStoreConfig('sales_pdf/creditmemo/creditmemocolumnwidths', $this->getStoreId())) {
                $this->_parameters[$this->getStoreId()]['creditmemocolumnwidths'] =
                    json_decode(
                        Mage::getStoreConfig('sales_pdf/creditmemo/creditmemocolumnwidths', $this->getStoreId()),
                        true
                    );
            } else {
                $this->_parameters[$this->getStoreId()]['creditmemocolumnwidths'] =
                    $this->getDefaultColumnOrderAndWidth();
            }
        }
        return $this->_parameters[$this->getStoreId()]['creditmemocolumnwidths'];
    }

    /**
     * get main heading for creditmemo title
     *
     * @param void
     *
     * @return string
     * @access public
     */
    public function getPdfTitle()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['creditmemotitle'])) {
            $this->_parameters[$this->getStoreId()]['creditmemotitle'] =
                Mage::getStoreConfig('sales_pdf/creditmemo/creditmemotitle', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['creditmemotitle'];
    }

    /**
     * return which addresses to display
     *
     * @param void
     *
     * @return string billing/shipping/both
     * @access public
     */
    public function getPdfAddresses()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['creditmemoaddresses'])) {
            $this->_parameters[$this->getStoreId()]['creditmemoaddresses'] =
                Mage::getStoreConfig('sales_pdf/creditmemo/creditmemoaddresses', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['creditmemoaddresses'];
    }

    /**
     * custom text for underneath creditmemo
     *
     * @param void
     *
     * @return string
     * @access public
     */
    public function getPdfCustom()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['creditmemocustom'])) {
            $this->_parameters[$this->getStoreId()]['creditmemocustom'] =
                Mage::getStoreConfig('sales_pdf/creditmemo/creditmemocustom', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['creditmemocustom'];
    }

    /**
     * are we displaying the order increment id on the creditmemo?
     *
     * @param void
     *
     * @return bool
     * @access public
     */
    public function getPutOrderId()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['creditmemoputorderid'])) {
            $this->_parameters[$this->getStoreId()]['creditmemoputorderid'] = Mage::getStoreConfig(
                Mage_Sales_Model_Order_Pdf_Abstract::XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID,
                $this->getStoreId()
            );
        }
        return $this->_parameters[$this->getStoreId()]['creditmemoputorderid'];
    }

    /**
     * returns the text preceding the credit memo increment id
     *
     * @param void
     *
     * @return string
     * @access public
     */
    public function getNumberText()
    {
        return Mage::helper('sales')->__('Credit Memo #');
    }

    /**
     * return which columns the user chosen to display on the creditmemo
     *
     * @param void
     *
     * @return string
     * @access public
     */
    public function getPdfColumns()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['creditmemocolumns'])) {
            $this->_parameters[$this->getStoreId()]['creditmemocolumns'] =
                Mage::getStoreConfig('sales_pdf/creditmemo/creditmemocolumns', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['creditmemocolumns'];
    }

    /**
     * return what column values should be used to reorder items
     *
     * @return bool
     * @access public
     */
    public function getColumnsSortOrder()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['creditmemocolumnssort'])) {
            $this->_parameters[$this->getStoreId()]['creditmemocolumnssort'] =
                Mage::getStoreConfig('sales_pdf/creditmemo/creditmemocolumnssort', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['creditmemocolumnssort'];
    }

    /**
     * should we print a barcode of the increment id
     *
     * @param  void
     *
     * @return bool
     * @access public
     */
    public function getPrintSalesObjectBarcode()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['creditmemobarcode'])) {
            $this->_parameters[$this->getStoreId()]['creditmemobarcode'] =
                Mage::getStoreConfig('sales_pdf/creditmemo/printbarcode', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['creditmemobarcode'];
    }

    /**
     * get hideBackground
     * @return mixed
     * @access public
     */
    public function getHideBackground()
    {
        return $this->_hideBackground;
    }

    /**
     * set hideBackground
     * @param mixed
     * @return void
     * @access public
     */
    public function setHideBackground($request)
    {
        $this->_hideBackground = $request;
    }
}
