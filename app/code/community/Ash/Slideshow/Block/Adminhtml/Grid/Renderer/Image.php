 <?php
/**
* Ash Slideshow Extension
*
* @category  Ash
* @package   Ash_Slideshow
* @copyright Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
* @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* @author    August Ash Team <core@augustash.com>
* 
**/

/**
 * To create a grid thumbnail use the following code
 *
 * <code>
 *  
 *  $this->addColumn('<Tbl_Colname>', array(
 *      'header'    => Mage::helper('<mycomapname>_<mymodule>')->__('Image'),
 *      'align'     => 'left',
 *      'width'     => '100px',
 *      'index'     => '<Tbl_Colname>',
 *      'type'      => 'image',
 *      'escape'    => true,
 *      'sortable'  => false,
 *      'filter'    => false,
 *      'renderer'  => new <Mycomapname>_<Mymodule>_Block_Adminhtml_Grid_Renderer_Image,
 *  ));
 *   
 * </code>
 *
 */

class Ash_Slideshow_Block_Adminhtml_Grid_Renderer_Image extends  Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getData($this->getColumn()->getIndex())=="")
        {
            return "";
        }
        else
        {
            $html  = '<img ';
            $html .= 'id="' . $this->getColumn()->getId() . '" ';
            $html .= 'width="100" ';
            $html .= 'height="100" ';
            //$html .= 'src="'.$row->getData($this->getColumn()->getIndex()) . '"';
            $html .= 'src="'.Mage::getBaseUrl("media").$row->getData($this->getColumn()->getIndex()) . '"';
            $html .= 'class="thumb-grid-image ' . $this->getColumn()->getInlineCss() . '"/>';
            return $html;
        }
    }
}