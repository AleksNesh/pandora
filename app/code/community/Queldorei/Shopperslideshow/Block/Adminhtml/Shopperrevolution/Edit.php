<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shopperslideshow_Block_Adminhtml_Shopperrevolution_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'shopperslideshow';
        $this->_controller = 'adminhtml_shopperrevolution';
        
        $this->_updateButton('save', 'label', Mage::helper('shopperslideshow')->__('Save Slide'));
        $this->_updateButton('delete', 'label', Mage::helper('shopperslideshow')->__('Delete Slide'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $script =
<<<EOD
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }

            $('text').insert({before:'<a href="#add_caption" id="add_caption_btn">Add new caption to content</a>'});
            $('content').insert({after:'
            <div style="display:none">
            <table id="add_caption" cellspacing="10" cellpadding="0" bgcolor="#ffffff">
            <tr>
                <td colspan="2"><b>Add new caption to slide content</b></td>
            </tr>
            <tr>
                <td width="150" align="right">Caption text</td>
                <td>
                    <input type="text" id="data_text" style="width:310px" />
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Color class</td>
                <td width="250">
                    <select id="color_class">
                        <option value="">- none -</option>

                        <option value="shopper_large_caption_light">shopper_large_caption_light</option>
                        <option value="shopper_large_caption_medium">shopper_large_caption_medium</option>
                        <option value="shopper_large_caption_bold">shopper_large_caption_bold</option>

                        <option value="shopper_caption_light">shopper_caption_light</option>
                        <option value="shopper_caption_medium">shopper_caption_medium</option>
                        <option value="shopper_caption_bold">shopper_caption_bold</option>

                        <option value="shopper_caption_underline_light">shopper_caption_underline_light</option>
                        <option value="shopper_caption_underline_medium">shopper_caption_underline_medium</option>
                        <option value="shopper_caption_underline_bold">shopper_caption_underline_bold</option>

                        <option value="shopper_small_caption_light">shopper_small_caption_light</option>
                        <option value="shopper_small_caption_medium">shopper_small_caption_medium</option>
                        <option value="shopper_small_caption_bold">shopper_small_caption_bold</option>

                        <option value="shopper_small_text_light">shopper_small_text_light</option>
                        <option value="shopper_small_text_medium">shopper_small_text_medium</option>
                        <option value="shopper_small_text_bold">shopper_small_text_bold</option>

                        <option value="shopper_medium_text_light">shopper_medium_text_light</option>
                        <option value="shopper_medium_text_medium">shopper_medium_text_medium</option>
                        <option value="shopper_medium_text_bold">shopper_medium_text_bold</option>

                        <option value="shopper_white_bg_light">shopper_white_bg_light</option>
                        <option value="shopper_white_bg_medium">shopper_white_bg_medium</option>
                        <option value="shopper_white_bg_bold">shopper_white_bg_bold</option>

                        <option value="big_white">big_white</option>
                        <option value="big_orange">big_orange</option>
                        <option value="medium_grey">medium_grey</option>
                        <option value="small_text">small_text</option>
                        <option value="medium_text">medium_text</option>
                        <option value="large_text">large_text</option>
                        <option value="very_large_text">very_large_text</option>
                        <option value="large_black_text">large_black_text</option>
                        <option value="very_large_black_text">very_large_black_text</option>
                        <option value="very_big_black">very_big_black</option>
                        <option value="big_black">big_black</option>
                        <option value="bold_red_text">bold_red_text</option>
                        <option value="bold_brown_text">bold_brown_text</option>
                        <option value="bold_green_text">bold_green_text</option>
                        <option value="very_big_white">very_big_white</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Incoming Animations</td>
                <td width="250">
                    <select id="incoming_animation">
                        <option value="randomrotate">Fade in, Rotate from a Random position and Degree</option>
                        <option value="sft">Short from Top</option>
                        <option value="sfb">Short from Bottom</option>
                        <option value="sfr">Short from Right</option>
                        <option value="sfl">Short from Left</option>
                        <option value="lft">Long from Top</option>
                        <option value="lfb">Long from Bottom</option>
                        <option value="lfr">Long from Right</option>
                        <option value="lfl">Long from Left</option>
                        <option value="fade">Fading</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Outgoing Animations</td>
                <td width="250">
                    <select id="outgoing_animation">
                        <option value="">- none -</option>
                        <option value="randomrotate">Fade in, Rotate from a Random position and Degree</option>
                        <option value="sft">Short from Top</option>
                        <option value="sfb">Short from Bottom</option>
                        <option value="sfr">Short from Right</option>
                        <option value="sfl">Short from Left</option>
                        <option value="lft">Long from Top</option>
                        <option value="lfb">Long from Bottom</option>
                        <option value="lfr">Long from Right</option>
                        <option value="lfl">Long from Left</option>
                        <option value="fade">Fading</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">X position</td>
                <td>
                    <input type="text" id="data_x" />
                    <br/> <small>horizontal position in the standard (via startwidth option defined) screen size (other screen sizes will be calculated)</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Y position</td>
                <td>
                    <input type="text" id="data_y" />
                    <br/> <small>vertical position in the standard (via startheight option defined) screen size (other screen sizes will be calculated)</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Start time</td>
                <td>
                    <input type="text" id="data_start_after" />
                    <br/> <small>how many milliseconds should this caption start to show</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Hide time</td>
                <td>
                    <input type="text" id="data_end" />
                    <br/> <small>after how many milliseconds should this caption leave the stage (should be bigger than data-start+data-speed !</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Show animation Speed</td>
                <td>
                    <input type="text" id="data_speed" />
                    <br/> <small>duration of the animation in milliseconds</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Hide animation Speed</td>
                <td>
                    <input type="text" id="data_end_speed" />
                    <br/> <small>duration of the animation when caption leaves the stage in milliseconds</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Start Easing</td>
                <td>
                    <select id="easing">
                        <option value="easeOutBack">easeOutBack</option>
                        <option value="easeInQuad">easeInQuad</option>
                        <option value="easeOutQuad">easeOutQuad</option>
                        <option value="easeInOutQuad">easeInOutQuad</option>
                        <option value="easeInCubic">easeInCubic</option>
                        <option value="easeOutCubic">easeOutCubic</option>
                        <option value="easeInOutCubic">easeInOutCubic</option>
                        <option value="easeInQuart">easeInQuart</option>
                        <option value="easeOutQuart">easeOutQuart</option>
                        <option value="easeInOutQuart">easeInOutQuart</option>
                        <option value="easeInQuint">easeInQuint</option>
                        <option value="easeOutQuint">easeOutQuint</option>
                        <option value="easeInOutQuint">easeInOutQuint</option>
                        <option value="easeInSine">easeInSine</option>
                        <option value="easeOutSine">easeOutSine</option>
                        <option value="easeInOutSine">easeInOutSine</option>
                        <option value="easeInExpo">easeInExpo</option>
                        <option value="easeOutExpo">easeOutExpo</option>
                        <option value="easeInOutExpo">easeInOutExpo</option>
                        <option value="easeInCirc">easeInCirc</option>
                        <option value="easeOutCirc">easeOutCirc</option>
                        <option value="easeInOutCirc">easeInOutCirc</option>
                        <option value="easeInElastic">easeInElastic</option>
                        <option value="easeOutElastic">easeOutElastic</option>
                        <option value="easeInOutElastic">easeInOutElastic</option>
                        <option value="easeInBack">easeInBack</option>
                        <option value="easeOutBack">easeOutBack</option>
                        <option value="easeInOutBack">easeInOutBack</option>
                        <option value="easeInBounce">easeInBounce</option>
                        <option value="easeOutBounce">easeOutBounce</option>
                        <option value="easeInOutBounce">easeInOutBounce</option>
                    </select>
                    <br/> <small>special easing effect of the animation</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">End Easing </td>
                <td>
                    <select id="end_easing">
                        <option value="">- none -</option>
                        <option value="easeOutBack">easeOutBack</option>
                        <option value="easeInQuad">easeInQuad</option>
                        <option value="easeOutQuad">easeOutQuad</option>
                        <option value="easeInOutQuad">easeInOutQuad</option>
                        <option value="easeInCubic">easeInCubic</option>
                        <option value="easeOutCubic">easeOutCubic</option>
                        <option value="easeInOutCubic">easeInOutCubic</option>
                        <option value="easeInQuart">easeInQuart</option>
                        <option value="easeOutQuart">easeOutQuart</option>
                        <option value="easeInOutQuart">easeInOutQuart</option>
                        <option value="easeInQuint">easeInQuint</option>
                        <option value="easeOutQuint">easeOutQuint</option>
                        <option value="easeInOutQuint">easeInOutQuint</option>
                        <option value="easeInSine">easeInSine</option>
                        <option value="easeOutSine">easeOutSine</option>
                        <option value="easeInOutSine">easeInOutSine</option>
                        <option value="easeInExpo">easeInExpo</option>
                        <option value="easeOutExpo">easeOutExpo</option>
                        <option value="easeInOutExpo">easeInOutExpo</option>
                        <option value="easeInCirc">easeInCirc</option>
                        <option value="easeOutCirc">easeOutCirc</option>
                        <option value="easeInOutCirc">easeInOutCirc</option>
                        <option value="easeInElastic">easeInElastic</option>
                        <option value="easeOutElastic">easeOutElastic</option>
                        <option value="easeInOutElastic">easeInOutElastic</option>
                        <option value="easeInBack">easeInBack</option>
                        <option value="easeOutBack">easeOutBack</option>
                        <option value="easeInOutBack">easeInOutBack</option>
                        <option value="easeInBounce">easeInBounce</option>
                        <option value="easeOutBounce">easeOutBounce</option>
                        <option value="easeInOutBounce">easeInOutBounce</option>
                    </select>
                    <br/><small>special easing effect of the animation</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right"></td>
                <td>
                    <input type="button" id="add_caption_action" value="Add caption" />
                </td>
            </tr>
            </table>
            </div>
            '});

            jQuery(function($){

                $('#add_caption_btn').fancybox({
                    'titlePosition'		: 'inside',
                    'transitionIn'		: 'none',
                    'transitionOut'		: 'none',
					'onClosed'		: function() {}
				});

				$('#add_caption_action').click(function(){
					var css_class = 'caption';
					css_class += ' ' + $('#incoming_animation').val();
					if ( $('#outgoing_animation').val() != '' ) css_class += ' ' + $('#outgoing_animation').val();
					if ( $('#color_class').val() != '' ) css_class += ' ' + $('#color_class').val();

                    var params = '';
                    if ( $('#data_x').val() != '' ) params += ' data-x="' + $('#data_x').val() + '"';
                    if ( $('#data_y').val() != '' ) params += ' data-y="' + $('#data_y').val() + '"';
                    if ( $('#data_speed').val() != '' ) params += ' data-speed="' + $('#data_speed').val() + '"';
                    if ( $('#data_start_after').val() != '' ) params += ' data-start="' + $('#data_start_after').val() + '"';
                    if ( $('#easing').val() != '' ) params += ' data-easing="' + $('#easing').val() + '"';
                    if ( $('#data_end_speed').val() != '' ) params += ' data-endspeed="' + $('#data_end_speed').val() + '"';
                    if ( $('#data_end').val() != '' ) params += ' data-end="' + $('#data_end').val() + '"';
                    if ( $('#end_easing').val() != '' ) params += ' data-endeasing="' + $('#end_easing').val() + '"';

					$('#text').val( $('#text').val() + "\r\n\r\n" + '<div class="'+ css_class +'" '+params+'>'+ $('#data_text').val() +'</div>' );
					$.fancybox.close();
				});

            });

EOD;

        $this->_formScripts[] = str_replace(array("\r\n", "\r", "\n"), "", $script );

	    Mage::app()->getLayout()->getBlock('head')->addItem('js_css', 'queldorei/fancybox/jquery.fancybox-1.3.4.css');
	    Mage::app()->getLayout()->getBlock('head')->addJs('queldorei/jquery-1.8.2.min.js');
	    Mage::app()->getLayout()->getBlock('head')->addJs('queldorei/jquery.noconflict.js');
	    Mage::app()->getLayout()->getBlock('head')->addJs('queldorei/fancybox/jquery.fancybox-1.3.4.pack.js');

    }

    public function getHeaderText()
    {
        if( Mage::registry('shopperrevolution_data') && Mage::registry('shopperrevolution_data')->getId() ) {
            return Mage::helper('shopperslideshow')->__("Edit Slide");
        } else {
            return Mage::helper('shopperslideshow')->__('Add Slide');
        }
    }
}