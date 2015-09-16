<?php
/**
 * Altima Lookbook Free Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Altima
 * @package    Altima_LookbookFree
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Altima_Lookbook_Block_Adminhtml_Lookbook_Edit_Form_Element_Hotspots extends Varien_Data_Form_Element_Abstract
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('hidden');
    }

    public function getElementHtml()
    {
	    $hotspot_icon  = Mage::getBaseUrl('media').'lookbook/icons/default/hotspot-icon.png';	
        $products_link = Mage::helper("adminhtml")->getUrl('adminhtml/catalog_product/');
        $helper = Mage::helper('lookbook');
    
        $html = '
        <style>
            .image-annotate-area, .image-annotate-edit-area {
                background: url('.$hotspot_icon.') no-repeat center center;
            }                                                              
        </style>
                <script type="text/javascript">
                //<![CDATA[                    
                        function InitHotspotBtn() {
                             if (jQuery("img#LookbookImage").attr("id")) {
                				var annotObj = jQuery("img#LookbookImage").annotateImage({                				    
                					editable: true,
                					useAjax: false,';
   if ($this->getValue()) $html .= 'notes: '. $this->getValue() . ',';
   
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
                                    "'. Mage::getUrl("lookbook/adminhtml_lookbook/getproduct", array('_secure'=>true)).'",
                                    {
                                        method: \'post\',
                                        asynchronous: false,
                                        onComplete: function(transport){
                                            if (200 == transport.status) {
                                                result = transport.responseText;
                                                return result;
                                            }
                                            if (result.error) {
                                                alert("Unable to check product SKU");
                                                return false;                                                                                                
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
               
  
 