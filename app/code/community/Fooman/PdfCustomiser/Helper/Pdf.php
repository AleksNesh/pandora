<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Fooman_PdfCustomiser_Helper_Pdf extends Mage_Core_Helper_Abstract
{
    protected $_pdf = null;

    abstract public function getNumberText();

    abstract public function getPdfColumns();

    /**
     * initalise the helper with a storeId
     *
     * @param int $storeId
     */
    public function __construct($storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        $this->setStoreId($storeId);
    }

    /**
     * keep track if we have adjusted the pdf total sort order
     * @var bool
     */
    public $hasAdjustedTotalsSort = false;

    /**
     * storeId
     * @access protected
     */
    protected $_storeId;

    /**
     * hideBackground
     * @access protected
     */
    protected $_hideBackground;

    /**
     * get storeId
     * @return  int
     * @access public
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * set storeId
     *
     * @param $id int
     *
     * @return  void
     * @access public
     */
    public function setStoreId($id)
    {
        $this->_storeId = $id;
    }

    /**
     * @param $pdf
     *
     * @return $this
     */
    public function setPdf($pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    public function getPdf()
    {
        return $this->_pdf;
    }

    /**
     *
     * @var Mage_Sales_Model_Abstract
     */
    protected $_salesObject;

    /**
     * retrieve current sales object for which pdf
     * is being generated
     *
     * @return Mage_Sales_Model_Abstract
     */
    public function getSalesObject()
    {
        return $this->_salesObject;
    }

    /**
     * set current sales object for which pdf
     * is being generated
     *
     * @param Mage_Sales_Model_Abstract $salesObject
     */
    public function setSalesObject (Mage_Sales_Model_Abstract $salesObject)
    {
        $this->_salesObject = $salesObject;
    }

    /**
     * retrieve order object associated with current
     * sales object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $salesObject = $this->getSalesObject();
        if ($salesObject instanceof Mage_Sales_Model_Order) {
            return $salesObject;
        } else {
            return $salesObject->getOrder();
        }
    }

    /**
     * parameters
     * @access protected
     */
    protected $_parameters = array();

    /**
     * set array of parameters manually - can be used to override settings from DB
     *
     * @param array $parameters
     *
     * @return  void
     * @access public
     */
    public function setParameters(array $parameters = array())
    {
        $this->_parameters = $parameters;
    }

    /**
     * set a single parameter manually
     *
     * @param int    $storeId
     * @param string $parameterName
     * @param mixed  $parameterValue
     */
    public function setParameter($storeId, $parameterName, $parameterValue)
    {
        $this->_parameters[$storeId][$parameterName] = $parameterValue;
    }

    /**
     * store owner address
     *
     * @return  string | bool
     * @access public
     */
    public function getPdfOwnerAddresss()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allowneraddress'])) {
            $this->_parameters[$this->getStoreId()]['allowneraddress'] =
                Mage::getStoreConfig('sales_pdf/all/allowneraddress', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allowneraddress'];
    }

    /**
     * get store flag to display base and order currency
     * @return  bool
     * @access public
     */
    public function getDisplayBoth()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['displayboth'])) {
            $this->_parameters[$this->getStoreId()]['displayboth'] =
                Mage::getStoreConfig('sales_pdf/all/displayboth', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['displayboth'] && $this->getOrder()->isCurrencyDifferent();
    }

    /**
     * option to use alternative layout files
     *
     * @return  bool
     * @access public
     */
    public function getAlternativeLayout()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allaltlayout'])) {
            $this->_parameters[$this->getStoreId()]['allaltlayout'] =
                Mage::getStoreConfig('sales_pdf/all/allaltlayout', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allaltlayout'];
    }

    /**
     * font for pdf - courier, times, helvetica
     * not embedded
     * @return  string
     * @access public
     */
    public function getPdfFont()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allfont'])) {
            $this->_parameters[$this->getStoreId()]['allfont'] =
                Mage::getStoreConfig('sales_pdf/all/allfont', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allfont'];
    }

    /**
     * @param string $size (otpional) normal | large | small
     * @return float
     * @access public
     */
    public function getPdfFontsize($size = 'normal')
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allfontsize'])) {
            $this->_parameters[$this->getStoreId()]['allfontsize'] =
                Mage::getStoreConfig('sales_pdf/all/allfontsize', $this->getStoreId());
        }
        $fontSize = $this->_parameters[$this->getStoreId()]['allfontsize'];
        switch ($size) {
            case 'normal':
                return $fontSize;
                break;
            case 'large':
                return $fontSize * 1.33;
                break;
            case 'small':
                return $fontSize * ($fontSize < 12 ? 1 : 0.8);
                break;
            default:
                return $fontSize;
        }
    }

    /**
     * font for pdf - courier, times, helvetica
     * not embedded
     * @return  string
     * @access public
     */
    public function getPdfQtyAsInt()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allqtyasint'])) {
            $this->_parameters[$this->getStoreId()]['allqtyasint'] =
                Mage::getStoreConfig('sales_pdf/all/allqtyasint', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allqtyasint'];
    }

    public function getNewWindow()
    {
        if (!isset($this->_parameters['allnewwindow'])) {
            $this->_parameters['allnewwindow'] = Mage::getStoreConfigFlag('sales_pdf/all/allnewwindow') ? 'D' : 'I';
        }
        return $this->_parameters['allnewwindow'];
    }

    /**
     * get path for print logo
     * @return string path information for logo
     * @access public
     */
    public function getPdfLogo()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['alllogo'])) {
            if (Mage::getStoreConfig('sales_pdf/all/alllogo', $this->getStoreId())) {
                $pdfLogo = Mage::getBaseDir('media') . DS . 'pdf-printouts' . DS . Mage::getStoreConfig(
                    'sales_pdf/all/alllogo',
                    $this->getStoreId()
                );
            } else {
                $pdfLogo = false;
            }
            $this->_parameters[$this->getStoreId()]['alllogo'] = $pdfLogo;
        }
        return $this->_parameters[$this->getStoreId()]['alllogo'];
    }

    /**
     * get logo placement auto / manual
     * @return string
     * @access public
     */
    public function getPdfLogoPlacement()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['alllogoplacement'])) {
            $this->_parameters[$this->getStoreId()]['alllogoplacement'] =
                Mage::getStoreConfig('sales_pdf/all/alllogoplacement', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['alllogoplacement'];
    }

    /**
     * get logo placement coordinates
     * @return array
     * @access public
     */
    public function getPdfLogoCoords()
    {

        if (!isset($this->_parameters[$this->getStoreId()]['alllogocoords'])) {
            $returnArray = array();
            $returnArray['w'] = Mage::getStoreConfig('sales_pdf/all/alllogowidth', $this->getStoreId());
            $returnArray['h'] = Mage::getStoreConfig('sales_pdf/all/alllogoheight', $this->getStoreId());
            $returnArray['x'] = Mage::getStoreConfig('sales_pdf/all/alllogofromleft', $this->getStoreId());
            $returnArray['y'] = Mage::getStoreConfig('sales_pdf/all/alllogofromtop', $this->getStoreId());
            $this->_parameters[$this->getStoreId()]['alllogocoords'] = $returnArray;
        }
        return $this->_parameters[$this->getStoreId()]['alllogocoords'];
    }

    /**
     * get path for print background
     * @return string path information for logo
     * @access public
     */
    public function getPdfBgImage()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allbgimage'])) {
            if (Mage::getStoreConfig('sales_pdf/all/allbgimage', $this->getStoreId())) {
                $bgImage = Mage::getBaseDir('media') . DS . 'pdf-printouts' . DS . Mage::getStoreConfig(
                    'sales_pdf/all/allbgimage',
                    $this->getStoreId()
                );
            } else {
                $bgImage = false;
            }
            $this->_parameters[$this->getStoreId()]['allbgimage'] = $bgImage;
        }
        return $this->_parameters[$this->getStoreId()]['allbgimage'];
    }

    /**
     * get path for print logo
     * @return string path information for logo
     * @access public
     */
    public function getPdfBgOnlyFirst()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allbgimagefirstpageonly'])) {
            $this->_parameters[$this->getStoreId()]['allbgimagefirstpageonly'] =
                Mage::getStoreConfig('sales_pdf/all/allbgimagefirstpageonly', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allbgimagefirstpageonly'];
    }

    /**
     * get Logo Dimensions
     * @param string $which (optional) identify the dimension to return  all | w | h
     * @return bool|float|array
     * @access public
     */
    public function getPdfLogoDimensions($which = 'all')
    {
        if (!$this->getPdfLogo()) {
            return false;
        }

        if (!isset($this->_parameters[$this->getStoreId()]['logodimensions'])) {
            list($width, $height, $type, $attr) = getimagesize($this->getPdfLogo());
            $this->_parameters[$this->getStoreId()]['logodimensions']['width'] =
                $width / Fooman_PdfCustomiser_Model_Mypdf::FACTOR_PIXEL_PER_MM;
            $this->_parameters[$this->getStoreId()]['logodimensions']['height'] =
                $height / Fooman_PdfCustomiser_Model_Mypdf::FACTOR_PIXEL_PER_MM;
        }

        switch ($which) {
            case 'w':
                return $this->_parameters[$this->getStoreId()]['logodimensions']['width'];
                break;
            case 'h-scaled':
                //calculate if image will be scaled apply factor to height
                $maxWidth = ($this->getPageWidth() / 2) - $this->getPdfMargins('sides');
                if ($this->getPdfLogoDimensions('w') > $maxWidth) {
                    $scaleFactor = $maxWidth / $this->getPdfLogoDimensions('w');
                } else {
                    $scaleFactor = 1;
                }
                return $scaleFactor * $this->_parameters[$this->getStoreId()]['logodimensions']['height'];
                break;
            case 'h':
                return $this->_parameters[$this->getStoreId()]['logodimensions']['height'];
                break;
            case 'all':
            default:
                return $this->_parameters[$this->getStoreId()]['logodimensions'];
        }
    }

    /**
     * get Margins
     *
     * @param string $which (optional) identify the dimension to return  all | top | bottom | sides
     *
     * @return mixed
     * @access public
     */
    public function getPdfMargins($which = 'all')
    {
        if (!isset($this->_parameters[$this->getStoreId()]['pdfmargins'])) {
            $this->_parameters[$this->getStoreId()]['pdfmargins']['top'] =
                Mage::getStoreConfig('sales_pdf/all/allmargintop', $this->getStoreId());
            $this->_parameters[$this->getStoreId()]['pdfmargins']['bottom'] =
                Mage::getStoreConfig('sales_pdf/all/allmarginbottom', $this->getStoreId());
            $this->_parameters[$this->getStoreId()]['pdfmargins']['sides'] =
                Mage::getStoreConfig('sales_pdf/all/allmarginsides', $this->getStoreId());
        }

        switch ($which) {
            case 'top':
                return $this->_parameters[$this->getStoreId()]['pdfmargins']['top'];
                break;
            case 'bottom':
                return $this->_parameters[$this->getStoreId()]['pdfmargins']['bottom'];
                break;
            case 'sides':
                return $this->_parameters[$this->getStoreId()]['pdfmargins']['sides'];
                break;
            case 'all':
            default:
                return $this->_parameters[$this->getStoreId()]['pdfmargins'];
        }
    }

    public function getBottomPageBreak()
    {
        if ($this->getPdfIntegratedLabels()) {
            return 75;
        } else {
            return $this->getPdfMargins('bottom') + 10;
        }
    }

    /**
     * return page width in mm
     *
     * @param  void
     *
     * @return float
     * @access public
     */
    public function getPageWidth()
    {

        if (!isset($this->_parameters[$this->getStoreId()]['allpagesize'])) {
            $this->_parameters[$this->getStoreId()]['allpagesize'] =
                Mage::getStoreConfig('sales_pdf/all/allpagesize', $this->getStoreId());
        }

        if (!isset($this->_parameters[$this->getStoreId()]['allpageorientation'])) {
            $this->_parameters[$this->getStoreId()]['allpageorientation'] =
                Mage::getStoreConfig('sales_pdf/all/allpageorientation', $this->getStoreId());
        }

        $pageSize = $this->_parameters[$this->getStoreId()]['allpagesize'];
        $orientation = $this->_parameters[$this->getStoreId()]['allpageorientation'];

        switch ($pageSize.'-'.$orientation) {
            case 'A4-L':
                return 297;
                break;
            case 'A4-P':
                return 210;
                break;
            case 'letter-L':
                return 279;
                break;
            case 'letter-P':
                return 260;
                break;
            default:
                return 210;
        }
    }

    /**
     * return if we want to print comments and statusses
     *
     * @param  void
     *
     * @return bool
     * @access public
     */
    public function getPrintComments()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allprintcomments'])) {
            $this->_parameters[$this->getStoreId()]['allprintcomments'] =
                Mage::getStoreConfig('sales_pdf/all/allprintcomments', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allprintcomments'];
    }

    /**
     * return if we want to print a barcode of the increment id
     *
     * @param  void
     *
     * @return bool
     * @access public
     */
    public function getPrintBarcode()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allprintbarcode'])) {
            $this->_parameters[$this->getStoreId()]['allprintbarcode'] =
                Mage::getStoreConfig('sales_pdf/all/allprintbarcode', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allprintbarcode'];
    }

    /**
     * return data for all blocks set for the footers
     *
     * @return array    array[0] contains how many blocks we need to set up
     * @access public
     */
    public function getFooters()
    {

        if (!isset($this->_parameters[$this->getStoreId()]['footers'])) {
            $this->_parameters[$this->getStoreId()]['footers'][0] = 0;
            for ($i = 1; $i < 5; $i++) {
                $this->_parameters[$this->getStoreId()]['footers'][$i] =
                    nl2br(Mage::getStoreConfig('sales_pdf/all/allfooter' . $i, $this->getStoreId()));
                if (!empty($this->_parameters[$this->getStoreId()]['footers'][$i])) {
                    $this->_parameters[$this->getStoreId()]['footers'][0] = $i;
                }
            }
        }
        return $this->_parameters[$this->getStoreId()]['footers'];
    }

    /**
     * return data for all blocks set for the footers
     *
     * @return bool
     * @access public
     */
    public function hasFooter()
    {
        $footers = $this->getFooters();
        return (bool)$footers[0];
    }


    /**
     * return if weight should be displayed as part of the shipping information
     *
     * @return bool
     * @access public
     */
    public function displayWeight()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['alldisplayweight'])) {
            $this->_parameters[$this->getStoreId()]['alldisplayweight'] =
                Mage::getStoreConfig('sales_pdf/all/alldisplayweight', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['alldisplayweight'];
    }

    /**
     * return flag if detailed tax breakdown should be displayed
     *
     * @return bool
     * @access public
     */
    public function displayTaxSummary()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['alltaxsummary'])) {
            $this->_parameters[$this->getStoreId()]['alltaxsummary'] =
                Mage::getStoreConfig('sales_pdf/all/alltaxsummary', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['alltaxsummary'];
    }

    /**
     * should we display the gift message? default false
     * @return bool
     * @access public
     */
    public function displayGiftMessage()
    {
        return false;
    }

    /**
     * are we using integrated labels - what to print?
     *
     * @param void
     *
     * @return  mixed bool
     * @access public
     */
    public function getPdfIntegratedLabels()
    {
        return false;
    }

    /**
     * should we display totals? default true
     *
     * @return bool
     * @access public
     */
    public function displayTotals()
    {
        return true;
    }

    /**
     * print product images?
     * @return  bool
     * @access public
     */
    public function printProductImages()
    {
        return strpos($this->getPdfColumns(), 'image') !== false;
    }

    /**
     * should we display the order id? default false
     * @return bool
     * @access public
     */
    public function getPutOrderId()
    {
        return false;
    }

    /**
     * setter to update the image height used in @see Fooman_PdfCustomiser_Model_Mypdf::Header()
     * @param $imageHeight
     * @return Fooman_PdfCustomiser_Helper_Pdf
     * @access public
     */
    public function setImageHeight ($imageHeight)
    {
        $this->_parameters[$this->getStoreId()]['allimageheight'] = $imageHeight;
        return $this;
    }

    /**
     * retrieve image height of the last added logo @see Fooman_PdfCustomiser_Model_Mypdf::Header()
     * @return float
     * @access public
     */
    public function getImageHeight()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allimageheight'])) {
            $this->_parameters[$this->getStoreId()]['allimageheight'] = false;
        }
        return $this->_parameters[$this->getStoreId()]['allimageheight'];
    }

    /**
     * return the formatted date of the current sales object, store time
     * @return string
     * @access public
     */
    public function getDate()
    {
        return Mage::helper('core')->formatDate($this->getSalesObject()->getCreatedAtStoreDate(), 'medium', false);
    }

    /**
     * return the current formatted store time
     * @return string
     * @access public
     */
    public function getPrintTime()
    {
        return Mage::helper('core')->formatDate(null, 'short', true);
    }

    /**
     * return the formatted date of the current order, store time
     * @return string
     * @access public
     */
    public function getOrderDate ()
    {
        return Mage::helper('core')->formatDate($this->getOrder()->getCreatedAtStoreDate(), 'medium', false);
    }

    /**
     * return additional content to be added in the top section
     * @return bool
     * @access public
     */
    public function getTopAdditional()
    {
        return false;
    }

    /**
     * define defaults for column width and order
     * @return array
     * @access public
     */
    public function getDefaultColumnOrderAndWidth()
    {
        return json_decode(
            '{
            "position":8,
            "name":20,
            "name-space":25,
            "name-sku":25,
            "sku":18,
            "image":18,
            "custom":20,
            "custom2":20,
            "custom3":20,
            "custom4":20,
            "custom5":20,
            "barcode":28,
            "price":12,
            "qty_ordered":8,
            "qty":8,
            "qty_backordered":8,
            "qty_detailed":12,
            "qty_stock":12,
            "item_status":12,
            "tax":12,
            "taxrate":8,
            "subtotal":12,
            "subtotal2":12,
            "discount":12,
            "rowtotal2":12
            }',
            true
        );
    }

    /**
     * return what column values should be used to reorder items
     *
     * @return bool
     * @access public
     */
    public function getColumnsSortOrder()
    {
        return false;
    }

    /**
     * construct columns based on default widths and user column choices
     * @return array|bool
     * @access public
     */
    public function getPdfColumnHeaders()
    {
        $columnsToPrint = explode(',', $this->getPdfColumns());
        $columnWidths = $this->getColumnOrderAndWidth();

        $columnTitles = array(
            'position' => htmlentities(Mage::helper('pdfcustomiser')->__('Pos'), ENT_QUOTES, 'UTF-8', false),
            'name' => htmlentities(Mage::helper('sales')->__('Product'), ENT_QUOTES, 'UTF-8', false),
            'name-space' => htmlentities(Mage::helper('sales')->__('Product'), ENT_QUOTES, 'UTF-8', false),
            'name-sku' => htmlentities(Mage::helper('sales')->__('Product'), ENT_QUOTES, 'UTF-8', false),
            'sku' => htmlentities(Mage::helper('sales')->__('SKU'), ENT_QUOTES, 'UTF-8', false),
            'image' => '',
            'custom' => '', 'custom2' => '', 'custom3' => '', 'custom4' => '', 'custom5' => '',
            'price' => htmlentities(Mage::helper('sales')->__('Price'), ENT_QUOTES, 'UTF-8', false),
            'discount' => htmlentities(Mage::helper('sales')->__('Discount'), ENT_QUOTES, 'UTF-8', false),
            'qty' => htmlentities(Mage::helper('sales')->__('Qty'), ENT_QUOTES, 'UTF-8', false),
            'qty_ordered' => htmlentities(Mage::helper('sales')->__('Qty Ordered'), ENT_QUOTES, 'UTF-8', false),
            'qty_backordered' => htmlentities(Mage::helper('pdfcustomiser')->__('Qty Back Ordered'), ENT_QUOTES, 'UTF-8', false),
            'qty_detailed' => htmlentities(Mage::helper('sales')->__('Qty'), ENT_QUOTES, 'UTF-8', false),
            'qty_stock' => htmlentities(Mage::helper('sales')->__('Stock Qty'), ENT_QUOTES, 'UTF-8', false),
            'item_status' => htmlentities(Mage::helper('sales')->__('Item Status'), ENT_QUOTES, 'UTF-8', false),
            'tax' => htmlentities($this->getTranslatedString('Tax', 'sales'), ENT_QUOTES, 'UTF-8', false),
            'taxrate' => htmlentities(Mage::helper('tax')->__('Tax Rate'), ENT_QUOTES, 'UTF-8', false),
            'subtotal' => htmlentities(Mage::helper('sales')->__('Subtotal'), ENT_QUOTES, 'UTF-8', false),
            'subtotal2' => htmlentities(Mage::helper('sales')->__('Subtotal'), ENT_QUOTES, 'UTF-8', false),
            'rowtotal2' => htmlentities(Mage::helper('sales')->__('Row Total'), ENT_QUOTES, 'UTF-8', false),
            'cost' => htmlentities(Mage::helper('sales')->__('Cost'), ENT_QUOTES, 'UTF-8', false),
            'row-cost' => htmlentities(Mage::helper('sales')->__('Row Cost'), ENT_QUOTES, 'UTF-8', false),
            'barcode' => ''
        );

        $i=1;
        $attributeCodes = $this->getCustomColumnAttributes();
        foreach ($attributeCodes as $attributeCode) {
            $customName = $i == 1 ? 'custom' : 'custom' . $i;
            if ($attributeCode == 'category_ids') {
                $columnTitles[$customName] = htmlentities(
                    Mage::helper('catalog')->__('Category'), ENT_QUOTES, 'UTF-8', false
                );
            } elseif ($attributeCode) {
                $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
                //$attribute->initLabels($this->getStoreId());
                if ($attribute->getStoreLabel()) {
                    $columnTitles[$customName] = htmlentities(
                        $attribute->getStoreLabel(), ENT_QUOTES, 'UTF-8', false
                    );
                } else {
                    $storeLabels = $attribute->getStoreLabels();
                    if (isset($storeLabels[$this->getStoreId()])) {
                        $columnTitles[$customName] = htmlentities(
                            $storeLabels[$this->getStoreId()], ENT_QUOTES, 'UTF-8', false
                        );
                    } else {
                        $columnTitles[$customName] = '';
                    }
                }
            }
            $i++;
        }

        $totalWidth = 0;
        foreach ($columnsToPrint as $columnToPrint) {
            if (isset($columnWidths[$columnToPrint])) {
                $totalWidth += $columnWidths[$columnToPrint];
            } else {
                //apply a default in case someone previously created a Columns Advanced override
                $totalWidth += 8;
            }
        }
        if ($totalWidth > 0) {
            $widthFactor = 100 / $totalWidth;
        } else {
            $widthFactor = 1;
        }

        $columnHeadings = array();
        $i = -1;
        if ($columnWidths) {
            foreach ($columnWidths as $key => $standardWidth) {
                if (in_array($key, $columnsToPrint)) {
                    $columnHeadings[] = array(
                        'width' => $standardWidth * $widthFactor,
                        'title' => $columnTitles[$key],
                        'key' => $key,
                        'align' => 'center',
                        'style_first' => 'border-top:1px solid black;',
                        'style_last' => 'border-bottom:1px solid black;'
                    );
                    $i++;
                }
            }
            if ($this->isRtl()) {
                $columnHeadings[0]['align'] = 'right';
                $columnHeadings[$i]['align'] = 'left';
            } else {
                $columnHeadings[0]['align'] = 'left';
                $columnHeadings[$i]['align'] = 'right';
            }
            //no columns
        } else {
            $columnHeadings = false;
        }
        return $columnHeadings;
    }

    /**
     * render the html for 1 sales object item line <tr>$trInner</tr>
     *
     * @param      $pdfItem
     * @param      $vertSpacing
     * @param bool $styleOverride
     * @param int  $position
     *
     * @return string html
     * @access public
     */
    public function getPdfItemRow($pdfItem, $vertSpacing, $styleOverride = false, $position = 1)
    {
        $html = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_item')
            ->setPdfHelper($this)
            ->setPdfItem($pdfItem)
            ->setVertSpacing($vertSpacing)
            ->setStyleOverride($styleOverride)
            ->setPosition($position)
            ->setTemplate('fooman/pdfcustomiser/item.phtml')
            ->toHtml();

        $transport = new Varien_Object();
        $transport->setHtml($html);
        Mage::dispatchEvent(
            'fooman_pdfcustomiser_pdf_item_row',
            array(
                'item'      => $pdfItem,
                'transport' => $transport
            )
        );
        return $transport->getHtml();
    }

    /**
     * render the html for 1 bundled sales object item line <tr>$trInner</tr>
     *
     * @param      $pdfItem
     * @param      $subItems
     * @param      $vertSpacing
     * @param bool $styleOverride
     * @param int  $position
     *
     * @return string
     * @access public
     */
    public function getPdfBundleItemRow($pdfItem, $subItems, $vertSpacing, $styleOverride = false, $position = 1)
    {
        $columns = $this->getPdfColumnHeaders();
        if ($columns) {
            //check if the subitems of the bundle have separate prices
            $subItemsSum = 0;
            foreach ($subItems as $bundleItem) {
                $subItemsSum += $bundleItem['price'];
            }
            //don't display bundle price if subitems have prices
            //TODO: make the below distinction configurable
            if ($subItemsSum > 0) {
                $html = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_item')
                    ->setPdfHelper($this)
                    ->setPdfItem($pdfItem)
                    ->setSubItems($subItems)
                    ->setVertSpacing($vertSpacing)
                    ->setStyleOverride($styleOverride)
                    ->setPosition($position)
                    ->setTemplate('fooman/pdfcustomiser/bundle-with-subitems.phtml')
                    ->toHtml();
            } else {
                $pdfItem['productDetails']['Subitems'] = array();
                foreach ($subItems as $bundleItem) {
                    $bundleItem['Name'] = $bundleItem['productDetails']['Name']; //keep BC
                    $pdfItem['productDetails']['Subitems'][] = $bundleItem;
                }
                $html = $this->getPdfItemRow($pdfItem, $vertSpacing, $styleOverride, $position);
            }
        }
        $transport = new Varien_Object();
        $transport->setHtml($html);
        Mage::dispatchEvent(
            'fooman_pdfcustomiser_pdf_item_row_bundle',
            array(
                'item'      => $pdfItem,
                'subitems'  => $subItems,
                'transport' => $transport
            )
        );
        return $transport->getHtml();
    }

    /**
     * get price output for items
     *
     * @param $price
     * @param $basePrice
     * @param bool    $displayBoth
     *
     * @internal param \Mage_Sales_Model_Order $salesObject
     * @return string html
     * @access   public
     */
    public function OutputPrice($price, $basePrice, $displayBoth)
    {
        $order = $this->getOrder();
        if ($this->isRtl()) {
            $price = sprintf("%F", $price);
            $basePrice = sprintf("%F", $basePrice);
            $price = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())
                ->toCurrency($price, array('position' => Zend_Currency::LEFT));
            $basePrice = Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())
                ->toCurrency($basePrice, array('position' => Zend_Currency::LEFT));
            if ($displayBoth) {
                $html = htmlentities($basePrice, ENT_QUOTES, 'UTF-8', false) . '<br/>' .
                    htmlentities($price, ENT_QUOTES, 'UTF-8', false);
            } else {
                $html = htmlentities($price, ENT_QUOTES, 'UTF-8', false);
            }
        } else {
            if ($displayBoth) {
                $html = htmlentities(strip_tags($order->formatBasePrice($basePrice)), ENT_QUOTES, 'UTF-8', false).
                        '<br/>' .
                        htmlentities(strip_tags($order->formatPrice($price)), ENT_QUOTES, 'UTF-8', false);
            } else {
                    $html = htmlentities($order->formatPriceTxt($price), ENT_QUOTES, 'UTF-8', false);
            }
        }

        return $html;
    }

    /**
     * prepare the order's gift message for display
     * @return array|bool
     * @access public
     */
    public function OutputGiftMessage()
    {
        if (!$this->displayGiftMessage()) {
            return false;
        }
        $order = $this->getOrder();

        if ($order->getGiftMessageId()) {
            $giftMessage = Mage::helper('giftmessage/message')->getGiftMessage($order->getGiftMessageId());
            if ($giftMessage) {
                $message = array();
                $message['from'] = htmlspecialchars($giftMessage->getSender());
                $message['to'] = htmlspecialchars($giftMessage->getRecipient());
                $message['message'] = htmlspecialchars($giftMessage->getMessage());
                return $message;
            }
        }
        return false;
    }

    /**
     * prepare the item's gift message for display
     *
     * @param array $message
     *
     * @return array|bool
     * @access public
     */
    public function OutputGiftMessageItem($message)
    {
        $html = '';
        if ($message['message']) {
            $html = '<br/><br/>';
            $html .= "<b>" . Mage::helper('giftmessage')->__('From:') . "</b> " . $message['from'] . "<br/>";
            $html .= "<b>" . Mage::helper('giftmessage')->__('To:') . "</b> " . $message['to'] . "<br/>";
            $html .= "<b>" . Mage::helper('giftmessage')->__('Message:') . "</b> " . $message['message'] . "<br/>";
        }
        return $html;
    }

    /**
     * prepare the sales object's comment history for display
     * @return array|bool
     * @access public
     */
    public function OutputCommentHistory ()
    {
        if ($this->getPrintComments()) {
            $comments = array();
            $salesObject = $this->getSalesObject();
            if ($salesObject instanceof Mage_Sales_Model_Order) {
                switch ($this->getPrintComments()) {
                    case Fooman_PdfCustomiser_Model_System_PrintComments::PRINT_ALL:
                        $commentObject = $salesObject->getAllStatusHistory();
                        break;
                    case Fooman_PdfCustomiser_Model_System_PrintComments::PRINT_FRONTEND_VISIBLE:
                        $commentObject = $salesObject->getVisibleStatusHistory();
                        break;
                    case Fooman_PdfCustomiser_Model_System_PrintComments::PRINT_BACKEND_VISIBLE:
                        $allCommentObject = $salesObject->getAllStatusHistory();
                        $commentObject = array();
                        foreach ($allCommentObject as $history) {
                            if (!$history->getIsVisibleOnFront()) {
                                $commentObject[] = $history;
                            }
                        }
                        break;
                }
                if (!empty($commentObject)) {
                    foreach ($commentObject as $history) {
                        $comments[] = array(
                            'date' => Mage::helper('core')->formatDate($history->getCreatedAtStoreDate(), 'medium'),
                            'label' => $history->getStatusLabel(),
                            'comment' => $history->getComment()
                        );
                    }
                }
            } else {
                if ($salesObject->getCommentsCollection()) {
                    switch ($this->getPrintComments()) {
                        case Fooman_PdfCustomiser_Model_System_PrintComments::PRINT_ALL:
                            $commentObject = $salesObject->getCommentsCollection();
                            break;
                        case Fooman_PdfCustomiser_Model_System_PrintComments::PRINT_FRONTEND_VISIBLE:
                            $allCommentObject = $salesObject->getCommentsCollection();
                            foreach ($allCommentObject as $comment) {
                                if ($comment->getIsVisibleOnFront()) {
                                    $commentObject[] = $comment;
                                }
                            }
                            break;
                        case Fooman_PdfCustomiser_Model_System_PrintComments::PRINT_BACKEND_VISIBLE:
                            $allCommentObject = $salesObject->getCommentsCollection();
                            $commentObject = array();
                            foreach ($allCommentObject as $comment) {
                                if (!$comment->getIsVisibleOnFront()) {
                                    $commentObject[] = $comment;
                                }
                            }
                            break;
                    }
                    if (!empty($commentObject)) {
                        foreach ($commentObject as $comment) {
                            if ($comment->getCreatedAt()) {
                                $date = Mage::helper('core')->formatDate($comment->getCreatedAtStoreDate(), 'medium');
                            } else {
                                $date = '';
                            }
                            $comments[] = array(
                                'date' => $date,
                                'label' => '',
                                'comment' => $comment->getComment()
                            );
                        }
                    }
                }
            }
            if (!empty($comments)) {
                return $comments;
            }
        }
        return false;
    }

    /**
     * output customer order comments - requires additional extensions
     *
     * @return array|bool
     * @access public
     */
    public function OutputCustomerOrderComment()
    {
        $order = $this->getOrder();
        $orderComments = array();

        if ($order->getBiebersdorfCustomerordercomment()) {
            $orderComments[] = array(
                'title' => Mage::helper('biebersdorfcustomerordercomment')->__('Customer Order Comment'),
                'comment' => Mage::helper('biebersdorfcustomerordercomment')->escapeHtml(
                    $order->getBiebersdorfCustomerordercomment()
                )
            );
        }

        if (Mage::helper('core')->isModuleEnabled('Magemaven_OrderComment')) {
            $mageMavenComment = $this->getOrder()->getCustomerComment()
                ? $this->getOrder()->getCustomerComment()
                : $this->getOrder()->getCustomerNote();
            if ($mageMavenComment) {
                $orderComments[] = array(
                    'title'   => Mage::helper('ordercomment')->__('Order Comment'),
                    'comment' => Mage::helper('ordercomment')->escapeHtml($mageMavenComment)
                );
            }
        } elseif ($order->getCustomerNoteNotify() && $order->getCustomerNote() || $order->getCustomerNotes()) {
            $orderComments[] = array(
                'title'   => Mage::helper('pdfcustomiser')->__('Customer Order Comment'),
                'comment' => Mage::helper('pdfcustomiser')->escapeHtml(
                        $order->getCustomerNote() ? $order->getCustomerNote() : $order->getCustomerNotes()
                    )
            );
        }

        //FME_Fieldsmanager
        if ((string)Mage::getConfig()->getModuleConfig('FME_Fieldsmanager')->active == 'true') {
            $pdfType = $this->getSalesObject() instanceof Mage_Sales_Model_Shipment? 22 : 21;
            $fields = Mage::getModel('fieldsmanager/fieldsmanager')->GetFMData($order->getEntityId(),'orders', $pdfType);
            if(!empty($fields)){
                $collected = '';
                foreach ($fields as $field) {
                    if(!empty($field['value'])){
                        $collected
                            .= $field['label'] . ': ' . Mage::helper('pdfcustomiser')->escapeHtml(
                                htmlspecialchars_decode($field['value'])
                            ) . '<br/>';
                    }
                }
                if(!empty($collected)){
                    $orderComments[] = array(
                        'title' => Mage::helper('fieldsmanager')->getheading(),
                        'comment' => $collected
                    );
                }
            }
        }

        //hello extension
        if ($this->getOrder()->getHCheckoutcomment()) {
            $orderComments[] = array(
                'title' => Mage::helper('checkoutcomment')->__('This is a message from the customer'),
                'comment' => Mage::helper('checkoutcomment')->escapeHtml($this->getOrder()->getHCheckoutcomment())
            );
        }

        if ($order->getShipNoteId()) {
            $shipNote = Mage::getModel('shipnote/note')->load($order->getShipNoteId());
            if ($shipNote->getId()) {
                $orderComments[] = array(
                    'title'   => Mage::helper('shipnote')->getFrontendLabel(),
                    'comment' => Mage::helper('pdfcustomiser')->escapeHtml($shipNote->getNote())
                );
            }
        }

        if ($order->getOnestepcheckoutCustomercomment()) {
            $orderComments[] = array(
                'title' => Mage::helper('onestepcheckout')->__('Customer Comments'),
                'comment' => Mage::helper('pdfcustomiser')->escapeHtml($order->getOnestepcheckoutCustomercomment())
            );
        }

        if (Mage::helper('core')->isModuleEnabled('MW_Onestepcheckout')) {
            $mwComment = Mage::getModel('onestepcheckout/onestepcheckout')->load($order->getId(), 'sales_order_id');
            if($mwComment->getMwCustomercommentInfo()){
                $orderComments[] = array(
                    'title' => Mage::helper('onestepcheckout')->__('Customer Order Comment'),
                    'comment' => Mage::helper('pdfcustomiser')->escapeHtml($mwComment->getMwCustomercommentInfo())
                    );
            }
        }

        if (Mage::helper('core')->isModuleEnabled('Aitoc_Aitcheckoutfields')) {
            if($this->getSalesObject() instanceof Mage_Sales_Model_Order) {
                $aitCheckoutFields = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getOrderCustomData(
                    $this->getSalesObject()->getId(), null, true
                );
            } else {
                $aitCheckoutFields = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getInvoiceCustomData(
                    $this->getOrder()->getId(), null, true
                );
            }
            if ($aitCheckoutFields) {
                $collected = '';
                foreach ($aitCheckoutFields as $aitCheckoutField) {
                    $collected
                        .= $aitCheckoutField['label'] . ': ' . Mage::helper('pdfcustomiser')->escapeHtml(
                            htmlspecialchars_decode($aitCheckoutField['value'])
                        ) . '<br/>';
                }
                $orderComments[] = array(
                    'title' => Mage::getStoreConfig(
                        'aitcheckoutfields/common_settings/aitcheckoutfields_additionalblock_label'
                    ),
                    'comment' => $collected
                );
            }
        }

        if (Mage::helper('core')->isModuleEnabled('Amasty_Orderattr')) {
            $fields = Mage::helper('pdfcustomiser/amasty_attributes')->getAttributes($order, $this->getSalesObject());
            foreach ($fields as $label => $value) {
                if (is_array($value)) {
                    $orderComments[] = array(
                        'title' => $label,
                        'comment' => str_replace(
                            "\n",
                            '<br/>',
                            Mage::helper('pdfcustomiser')->escapeHtml(implode("\n", $value))
                        )
                    );
                } else {
                    $orderComments[] = array(
                        'title'   => $label,
                        'comment' => Mage::helper('pdfcustomiser')->escapeHtml($value)
                    );
                }
            }
        }

        if (Mage::helper('core')->isModuleEnabled('TM_CheckoutFields')) {
            $fields = Mage::helper('checkoutfields')->getFields();
            foreach ($fields as $field => $config) {
                $value = (string)$order->getData($field);
                if ($value) {
                    $orderComments[] = array(
                        'title'   => $config['label'],
                        'comment' => Mage::helper('pdfcustomiser')->escapeHtml($value)
                    );
                }
            }
        }

        if ($order->getFirecheckoutCustomerComment()) {
            $orderComments[] = array(
                'title' => Mage::helper('pdfcustomiser')->__('Order Comment'),
                'comment' => Mage::helper('pdfcustomiser')->escapeHtml($order->getFirecheckoutCustomerComment())
            );
        }

        if (!empty($orderComments)) {
            return $orderComments;
        }
        return false;
    }


    /**
     * prepare full tax summary for output
     *
     * @param array $taxTotal
     * @param array $taxAmount
     *
     * @return array
     * @access public
     */
    public function OutputTaxSummary(array $taxTotal, array $taxAmount)
    {
        $html = array();
        $zero = '0.0000';
        $printedZero = false;

        if (!$this->displayTaxSummary()) {
            return $html;
        }

        $order = $this->getOrder();

        //$filteredTaxrates = Mage::helper('pdfcustomiser')->getCalculatedTaxes($order);

        //if ($filteredTaxrates || isset($taxTotal[$zero])) {
            $html[] = array(
                Mage::helper('tax')->__('Tax Rate'),
                Mage::helper('pdfcustomiser')->__('Base Amount'),
                Mage::helper('pdfcustomiser')->__('Tax Amount'),
                Mage::helper('sales')->__('Subtotal')
            );
            if (!empty($taxTotal)) {
                foreach ($taxTotal as $rate=>$amount) {
                    if ($rate == 0) {
                        $printedZero = true;
                    }
                    if (isset($taxTotal[sprintf("%01.4f", $rate)])) {
                        $taxBase = $taxTotal[sprintf("%01.4f", $rate)];
                    } else {
                        $taxBase = 0;
                    }
                    if (isset($taxAmount[sprintf("%01.4f", $rate)])) {
                        $taxBaseAmount = $taxAmount[sprintf("%01.4f", $rate)];
                    } else {
                        $taxBaseAmount = 0;
                    }
                    if ($taxBase != 0 || $taxBaseAmount != 0) {
                        $html[] = array(
                            $rate . "%",
                            $order->formatBasePrice($taxBase),
                            $order->formatBasePrice($taxBaseAmount),
                            $order->formatBasePrice($taxBaseAmount + $taxBase)
                        );
                    }

                }
            }
        //}

        if (isset($taxTotal[$zero]) && !$printedZero) {
            $html[] = array(
                (float)$zero . "%",
                $order->formatBasePrice($taxTotal[$zero]),
                $order->formatBasePrice($zero),
                $order->formatBasePrice($taxTotal[$zero])
            );
        }
        return $html;
    }

    /**
     * are we in right to left mode?
     * @return bool
     * @access public
     */
    public function isRtl()
    {
        return (bool)$this->_parameters[0]['rtl'];
    }

    /**
     * get attribute code for custom columns
     * @return array
     * @access public
     */
    public function getCustomColumnAttributes()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allcustomcolumn'])) {
            $this->_parameters[$this->getStoreId()]['allcustomcolumn'] =
                Mage::getStoreConfig('sales_pdf/all/allcustomcolumn', $this->getStoreId());
        }
        return explode(',', $this->_parameters[$this->getStoreId()]['allcustomcolumn']);
    }

    /**
     * get attribute code for custom columns
     * @return array
     * @access public
     */
    public function getBarcodeType()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allbarcode'])) {
            $this->_parameters[$this->getStoreId()]['allbarcode'] =
                Mage::getStoreConfig('sales_pdf/all/allbarcode', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allbarcode'];
    }

    /**
     * Magento changed the translation strings without backwards compatibility
     * SOLD TO: to Sold to:
     * also used as a workaround since Mage::helper('sales')->__('Tax')
     * can sometimes return an empty string
     */
    public function getTranslatedString($string, $module = 'sales')
    {
        $translatedFromUppercase = Mage::helper($module)->__(strtoupper($string));
        if ($translatedFromUppercase != strtoupper($string) && !empty($translatedFromUppercase)) {
            return $translatedFromUppercase;
        }

        $translatedWithExtraColon = Mage::helper($module)->__($string . ':');
        if ($translatedWithExtraColon != $string . ':' && !empty($translatedWithExtraColon)) {
            return trim($translatedWithExtraColon, ':');
        }

        if($string == 'Tax' && empty($translated)){
            return Mage::helper('pdfcustomiser')->__($string);
        }

        return Mage::helper($module)->__($string);
    }

    /**
     * return flag if instead of coupon code we should load the rule title
     *
     * @return bool
     * @access public
     */
    public function displaySalesruleTitle()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allsalesruletitle'])) {
            $this->_parameters[$this->getStoreId()]['allsalesruletitle'] =
                Mage::getStoreConfig('sales_pdf/all/allsalesruletitle', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allsalesruletitle'];
    }

    /**
     * return naming template for pdf file name
     *
     * @return bool
     * @access public
     */
    public function getNameFormat()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['nameformat'])) {
            $this->_parameters[$this->getStoreId()]['nameformat'] =
                Mage::getStoreConfig('sales_pdf/all/nameformat', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['nameformat'];
    }

    /**
     * return name to use for pdf file
     *
     * @param array  $printedIncrements
     *
     * @param string $ext
     *
     * @param string $outputFileName
     *
     * @return string
     */
    public function getPdfFileName(array $printedIncrements, $ext = '.pdf', $outputFileName = '')
    {
        if (!empty($outputFileName)) {
            return $outputFileName . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf';
        }
        //available placeholders
        //{TITLE}
        //{INCREMENT}
        //{DATE}
        $format = $this->getNameFormat();
        $safeTitle = preg_replace('/[^\p{L}\p{N}_\.-]/u', '', $this->getPdfTitle());
        $date = Mage::getSingleton('core/date')->date('Y-m-d_H-i-s');
        sort($printedIncrements);
        $increment = array_shift($printedIncrements);
        if (sizeof($printedIncrements)) {
            $increment .= '-' . array_pop($printedIncrements);
        }
        $search = array('{TITLE}', '{INCREMENT}', '{DATE}');
        $replace = array($safeTitle, $increment, $date);
        return str_replace($search, $replace, $format) . $ext;
    }

    /**
     * depending on the chosen setting the tax amount is displayed with the
     * standard totals or between the grandtotal excl. and grandtotal incl.
     *
     * @return bool
     */
    public function displayTaxAmountWithGrandTotals()
    {
        return Mage::getStoreConfigFlag('tax/sales_display/grandtotal', $this->getStoreId())
        && !Mage::getStoreConfigFlag('sales_pdf/all/allonly1grandtotal', $this->getStoreId());
    }

    /**
     * retrieve template file, supply alt if requested and available
     *
     * @param        $helper
     * @param        $file
     * @param string $type
     *
     * @return mixed
     */
    public function getTemplateFileWithPath($helper, $file, $type = '')
    {
        $returnPath = $this->getTemplateFullPath($helper, $file, $type);

        $transport = new Varien_Object();
        $transport->setTemplateFileWithPath($returnPath);
        Mage::dispatchEvent(
            'fooman_pdfcustomiser_pdf_template',
            array(
                 'helper'    => $helper,
                 'transport' => $transport,
                 'type'      => $type,
                 'file'      => $file
            )
        );
        return $transport->getTemplateFileWithPath();
    }

    public function getTemplateFullPath($helper, $file, $type = '')
    {
        $altFilename = sprintf('%s-alt.phtml', $file);
        $filename = sprintf('%s.phtml', $file);

        if ($type) {
            $full = sprintf('fooman/pdfcustomiser/%s/%s', $type, $filename);
            $altFull = sprintf('fooman/pdfcustomiser/%s/%s', $type, $altFilename);
        } else {
            $full = sprintf('fooman/pdfcustomiser/%s', $filename);
            $altFull = sprintf('fooman/pdfcustomiser/%s', $altFilename);
        }
        $altFullSafe
            = Mage::getConfig()->getOptions()->getDir('design') . DS . 'frontend' . DS . 'base' . DS . 'default' . DS
            . 'template' . DS . str_replace(
                '/', DS, $altFull
            );

        if ($helper->getAlternativeLayout() && file_exists($altFullSafe)) {
            $returnPath = $altFull;
        } else {
            $returnPath = $full;
        }
        return $returnPath;
    }

    /**
     * should we print a barcode of the tracking number?
     *
     * @param  void
     *
     * @return bool
     * @access public
     */
    public function getPrintTrackingBarcode()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allprinttrackingbarcode'])) {
            $this->_parameters[$this->getStoreId()]['allprinttrackingbarcode']
                = Mage::getStoreConfig('sales_pdf/all/allprinttrackingbarcode', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allprinttrackingbarcode'];
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
        return false;
    }

    public function fixEncoding($input)
    {
        return Mage::helper('core')->escapeHtml($input);
    }

    /**
     * format the price according to locale settings
     *
     * @param      $order
     * @param      $price
     * @param null $currency
     *
     * @return string
     */
    public function formatPrice($order, $price, $currency=null)
    {
        if (is_null($price)) {
            return '';
        }
        $price = sprintf("%F", $price);
        if ($this->isRtl()) {
            if ($currency == 'base') {
                $price = Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())
                    ->toCurrency($price, array('position' => Zend_Currency::LEFT));
            } else {
                $price = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())
                    ->toCurrency($price, array('position' => Zend_Currency::LEFT));
            }
        } else {
            if ($currency == 'base') {
                $price = Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())
                    ->toCurrency($price, array());
            } else {
                $price = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())
                    ->toCurrency($price, array());
            }
        }
        return $price;
    }

    /**
     * validate barcode
     *
     * @param      $productAttribute
     * @param      $type
     *
     * @return bool
     * @access public
     */
    public function validateBarcode($barcode)
    {
        //EAN 13 validation
        if ($this->getBarcodeType() == 'EAN13') {
            // check to see if barcode is 13 digits long
            if (!preg_match("/^[0-9]{13}$/", $barcode)) {
                return false;
            }
            $digits = $barcode;
            // 1. Add the values of the digits in the
            // even-numbered positions: 2, 4, 6, etc.
            $even_sum = $digits[1] + $digits[3] + $digits[5] +
                        $digits[7] + $digits[9] + $digits[11];
            // 2. Multiply this result by 3.
            $even_sum_three = $even_sum * 3;
            // 3. Add the values of the digits in the
            // odd-numbered positions: 1, 3, 5, etc.
            $odd_sum = $digits[0] + $digits[2] + $digits[4] +
                       $digits[6] + $digits[8] + $digits[10];
            // 4. Sum the results of steps 2 and 3.
            $total_sum = $even_sum_three + $odd_sum;
            // 5. The check character is the smallest number which,
            // when added to the result in step 4, produces a multiple of 10.
            $next_ten = (ceil($total_sum / 10)) * 10;
            $check_digit = $next_ten - $total_sum;
            // if the check digit and the last digit of the
            // barcode are OK return true;
            if ($check_digit == $digits[12]) {
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * get logo/background printing option
     * @return string
     * @access public
     */
    public function getPdfLogoBackgroundPrinting()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['alllogobgprinting'])) {
            $this->_parameters[$this->getStoreId()]['alllogobgprinting'] =
                Mage::getStoreConfig('sales_pdf/all/alllogobgprinting', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['alllogobgprinting'];
    }

    /**
     * get key/value pair to append to the print url
     * @return array
     * @access public
     */
    public function getPdfLogoBgUrlParams()
    {
        $hiddenItem = $this->getPdfLogoBackgroundPrinting();
        switch ($hiddenItem) {
            case 'yes-logo':
                return array('hide_logo' => 1);
                break;
            case 'yes-background':
                return array('hide_background' => 1);
                break;
            case 'yes-all':
                return array('hide_logo' => 1, 'hide_background' => 1);
                break;
            default:
                //default is 'no', return empty array
                return array();
                break;
        }
    }
}
