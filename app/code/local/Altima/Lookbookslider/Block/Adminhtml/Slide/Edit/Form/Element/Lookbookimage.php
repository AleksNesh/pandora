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
class Altima_Lookbookslider_Block_Adminhtml_Slide_Edit_Form_Element_Lookbookimage extends Varien_Data_Form_Element_Abstract
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('hidden');
    }

 public function getElementHtml()
 {
    $block_class =  Mage::getBlockSingleton('lookbookslider/adminhtml_slide_edit_form');
    $slider_id = Mage::registry('slide_data')->getData('lookbookslider_id'); 
    $upload_action  = Mage::getUrl('lookbookslider/adminhtml_slide/upload', array('slider_id'=>$slider_id, '_secure'=>true)).'?isAjax=true';
    $media_url  = Mage::getBaseUrl('media');
    $upload_folder_path = str_replace("/",DS, Mage::getBaseDir("media").DS);
    $helper = Mage::helper('lookbookslider');
    $sizeLimit = $helper->getMaxUploadFilesize();
    $allowed_extensions = implode('","',explode(',',$helper->getAllowedExtensions()));
    $html = '<script type="text/javascript">
                //<![CDATA[
		jQuery.noConflict();
                jQuery(document).ready(function() { 
                    
                  InitHotspotBtn(); 
                  
                    img_uploader = new qq.FileUploader({
                        element: document.getElementById(\'maket_image\'),
                        action: "'.$upload_action.'",
                        params: {"form_key":"'.$block_class->getFormKey().'"},
                        multiple: false,
                        buttontext: "'.$helper->__('Upload file').'",
                        messages: {
                            typeError: "'.$helper->__('{file} has invalid extension. Only {extensions} are allowed.').'",
                            sizeError: "'.$helper->__('{file} is too large, maximum file size is {sizeLimit}.').'",
                            minSizeError: "'.$helper->__('{file} is too small, minimum file size is {minSizeLimit}.').'",
                            emptyError: "'.$helper->__('{file} is empty, please select files again without it.').'",
                            onLeave: "'.$helper->__('The files are being uploaded, if you leave now the upload will be cancelled.').'"            
                        },
                        allowedExtensions: ["'.$allowed_extensions.'"],
                        sizeLimit: '. $sizeLimit .',
                        onComplete: function(id, fileName, responseJSON){                           
                                    if (responseJSON.success) 
                                    {
                                        if (jQuery(\'#LookbookImageBlock\')) 
                                        {
                                          jQuery.each(jQuery(\'#LookbookImageBlock\').children(),function(index) {
                                            jQuery(this).remove();
                                          });
                                        }
                                       jQuery(\'#LookbookImageBlock\').append(\'<img id="LookbookImage"';
                                       $html .= ' src="'.$media_url.'lookbookslider/\'+responseJSON.filename+\'" alt="\'+responseJSON.filename+\'"'; 
                                       $html .= ' width="\'+responseJSON.dimensions.width+\'" height="\'+responseJSON.dimensions.height+\'"/>\');
                                       
                                        if (jQuery(\'#advice-required-entry-image\')) 
                                        {
                                            jQuery(\'#advice-required-entry-image\').remove();
                                        }
                                        jQuery(\'#LookbookImage\').load(function(){
                                           jQuery(this).attr(\'width\',responseJSON.dimensions.width);
                                           jQuery(this).attr(\'height\',responseJSON.dimensions.height);
                                           InitHotspotBtn();
                                        });                       
                                        jQuery(\'#image_path\').val(\'lookbookslider/\'+responseJSON.filename);
                                        jQuery(\'#image_path\').removeClass(\'validation-failed\');
                                    }

                        }
                    });
                });
                //]]>
                </script>
                   <div id="LookbookImageBlock">';      
        if ($this->getValue()) {
            $img_src = $media_url.$this->getValue();
            $img_path = $upload_folder_path.$this->getValue();
            
        $slider = Mage::getModel('lookbookslider/lookbookslider')->getCollection()->addFieldToFilter('lookbookslider_id', $slider_id)->getFirstItem();
        $sl_width = $slider->getData('width');
        $sl_height = $slider->getData('height');
        $resize_src = $helper->getResizedUrl($this->getValue(), $sl_width, $sl_height);

            $dimensions = Mage::helper('lookbookslider')->getImageDimensions($img_path);
            if (isset($dimensions['error'])) { 
                $html .= '<h4 id="LookbookImage" style="color:red;">'.$helper->__("File %s does not exists",$img_src).'.</h4>';   
            }
            else
            {
                $html .= '<img id="LookbookImage" src="'.$resize_src.'" alt="'.basename($resize_src).'" width="'.$sl_width.'" height="'.$sl_height.'"/>';
            }     
        }
        $html .= '</div>
                <div id="maket_image">     
                    <noscript>          
                        <p>'.$helper->__("Please enable JavaScript to use file uploader.").'</p>
                    </noscript>         
                </div>';
                
        $html.= parent::getElementHtml();
        
        return $html;
 }
}