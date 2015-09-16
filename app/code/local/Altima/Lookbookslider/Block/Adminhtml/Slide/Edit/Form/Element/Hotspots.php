<?php
/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 */
class Altima_Lookbookslider_Block_Adminhtml_Slide_Edit_Form_Element_Hotspots extends Varien_Data_Form_Element_Abstract
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('hidden');
    }

    public function getElementHtml()
    {
        $helper = Mage::helper('lookbookslider');
	    $hotspot_icon  = $helper->getHotspotIcon();
        $products_link = Mage::helper("adminhtml")->getUrl('adminhtml/catalog_product/');
        $interdict_overlap = $helper->getInterdictOverlap();
        $captions = array(  
                  "add_btn"               =>  $helper->__("Add Hotspot"),
                  "cancel_btn"            =>  $helper->__("Cancel"),
                  "delete_btn"            =>  $helper->__("Delete"),
                  "note_saving_err"       =>  $helper->__("An error occurred saving this hotspot."),
                  "note_overlap_err"      =>  $helper->__("Areas should not overlap."),
                  "link_text"             =>  $helper->__("Link text"),
                  "link_href"             =>  $helper->__("Link url"),
                  "enter_text_err"        =>  $helper->__("Please, enter link text"),
                  "enter_href_err"        =>  $helper->__("Please, enter link url"),
                  "link_type"             =>  $helper->__("Select link type"),
                  "link_required_err"     =>  $helper->__("Please, enter link text and link url"),
                  "enter_sku_err"         =>  $helper->__("Please, enter product SKU"),
                  "select_link_type_err"  =>  $helper->__("Please, select link type"),
                  "prod_dont_exists_err"  =>  $helper->__("The product with SKU="),
                  "prod_sku"              =>  $helper->__("Product SKU:"),
                  "delete_note_err"       =>  $helper->__("An error occurred deleting this hotspot."),
                  "product_page"          =>  $helper->__("Product page"),
                  "other_page"            =>  $helper->__("Other page"),
                  
        );	
        $html = '
        <style>
            .image-annotate-area, .image-annotate-edit-area {
                background: url('.$hotspot_icon.') no-repeat center center;
            }                                                              
        </style>
                <script type="text/javascript">
                //<![CDATA[
                        function InitHotspotBtn() {
                             if (jQuery("img#LookbookImage")) {
                				var annotObj = jQuery("img#LookbookImage").annotateImage({                				    
                					editable: true,
                					useAjax: false,
                                    interdict_areas_overlap: '.$interdict_overlap.',
                                    captions: '. $helper->jsonEncode($captions).',';
   if ($this->getValue()) $html .= '
                                    notes: '. $this->getValue() . ',';
   
       $html .= '                   input_field_id: "hotspots"                             
                				});
                                
                               jQuery("img#LookbookImage").before(\'<div class="products-link"><a href="'.$products_link.'" title="'.$helper->__('Products List').'" target="_blank">'. $helper->__('Products List').'</a></div>\');
                                
                                var top = Math.round(jQuery("img#LookbookImage").height()/2);
                                jQuery(".image-annotate-canvas").append(\'<div class="hotspots-msg" style="top:\' + top + \'px;">'. $helper->__('Rollover on the image to see hotspots').'</div>\');
                        
                                jQuery(".image-annotate-canvas").hover(
                                      function () {
                                            ShowHideHotspotsMsg();
                                      },
                                      function () {
                                            ShowHideHotspotsMsg();
                                      }
                                    );
                                    
                                return annotObj;
                            }
                            else
                            {
                                return false;
                            }
                        };
                        
                        function checkSKU(){
                                    result = "";
                                    request = new Ajax.Request(
                                    "'. Mage::getUrl("lookbookslider/adminhtml_slide/getproduct", array('_secure'=>true)).'",
                                    {
                                        method: \'post\',
                                        asynchronous: false,
                                        onComplete: function(transport){
                                            if (200 == transport.status) {
                                                result = transport.responseText;
                                                return result;
                                            }
                                            if (result.error) {
                                                alert("'.$helper->__("Unable to check product SKU.").'");
                                                return result;                                                                                                                                                
                                            }
                                        },
                                        parameters: Form.serialize($("annotate-edit-form"))
                                    }
                                );
                                return result;
                        };
                //]]>
                </script>';

        $html.= parent::getElementHtml();

        return $html;
    }
}
               
  
 