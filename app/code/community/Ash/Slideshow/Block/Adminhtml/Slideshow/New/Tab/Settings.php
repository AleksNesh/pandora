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

class Ash_Slideshow_Block_Adminhtml_Slideshow_New_Tab_Settings extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * $_helper
     * @var Ash_Slideshow_Helper_Data
     */
    protected $_helper;

    /**
     * Magento's class contructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_helper = Mage::helper('ash_slideshow');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('slideshow_');
        $form->setFieldNameSuffix('slideshow');
        $this->setForm($form);

        $yesNoOptions = array(
            array('value' => '1', 'label' => $this->_helper->__('Yes')),
            array('value' => '0', 'label' => $this->_helper->__('No')),
        );

        /**
         * Define fieldsets to organize fields into groups similar to BxSlider's options
         */
        $generalFieldset = $form->addFieldset('slideshow_settings_general', array('legend' => $this->_helper->__('Slideshow Basic Settings')));

        $pagerFieldset = $form->addFieldset('slideshow_settings_pager', array('legend' => $this->_helper->__('Slideshow Pager Settings')));

        $controlFieldset = $form->addFieldset('slideshow_settings_controls', array('legend' => $this->_helper->__('Slideshow Control Settings')));


        $autoFieldset = $form->addFieldset('slideshow_settings_auto', array('legend' => $this->_helper->__('Slideshow Auto Settings')));

        $carouselFieldset = $form->addFieldset('slideshow_settings_carousel', array('legend' => $this->_helper->__('Slideshow Carousel Settings')));


        /**
         * GROUP - General Configuration Options
         */

        $generalFieldset->addField('mode', 'select', array(
            'name'      => 'mode',
            'label'     => $this->_helper->__('Mode'),
            'note'      => 'Type of transition between slides.',
            'values'    => array(
                array(
                    'value'     => 'horizontal',
                    'label'     => $this->_helper->__('Horizontal'),
                ),

                array(
                    'value'     => 'vertical',
                    'label'     => $this->_helper->__('Vertical'),
                ),
                array(
                    'value'     => 'fade',
                    'label'     => $this->_helper->__('Fade'),
                ),
            ),
            'value'     => 'horizontal',
        ));

        $generalFieldset->addField('speed', 'text', array(
            'name'      => 'speed',
            'label'     => $this->_helper->__('Speed'),
            'note'      => 'Slide transition duration (in ms) ex: 1000 (1 second).',
            'value'     => 500,
        ));

        $generalFieldset->addField('slide_margin', 'text', array(
            'name'      => 'slide_margin',
            'label'     => $this->_helper->__('Slide Margin'),
            'note'      => 'Margin between each slide.',
            'value'     => 0,
        ));

        $generalFieldset->addField('start_slide', 'text', array(
            'name'      => 'start_slide',
            'label'     => $this->_helper->__('Start Slide'),
            'note'      => 'Starting slide index (zero-based).',
            'value'     => 0,
        ));

        $generalFieldset->addField('random_start', 'select', array(
            'name'      => 'random_start',
            'label'     => $this->_helper->__('Random Start'),
            'note'      => 'Start slider on a random slide.',
            'values'    => $yesNoOptions,
            'value'     => '0',
        ));

        $generalFieldset->addField('infinite_loop', 'select', array(
            'name'      => 'infinite_loop',
            'label'     => $this->_helper->__('Infinite Loop'),
            'note'      => 'If true, clicking "Next" while on the last slide will transition to the first slide and vice-versa.',
            'values'    => $yesNoOptions,
            'value'     => '1',
        ));

        $generalFieldset->addField('hide_control_on_end', 'select', array(
            'name'      => 'hide_control_on_end',
            'label'     => $this->_helper->__('Hide Control On End'),
            'note'      => 'If enabled, "Next" control will be hidden on last slide and vice-versa. Only used when "Infinite Loop" is disabled.',
            'values'    => $yesNoOptions,
            'value'     => '0',
        ));

        $generalFieldset->addField('easing', 'select', array(
            'name'      => 'easing',
            'label'     => $this->_helper->__('Easing With CSS'),
            'note'      => 'The type of "easing" to use during transitions. IF USING CSS transitions.',
            'values'    => array(
                array(
                    'value'     => '',
                    'label'     => $this->_helper->__('Please Select...'),
                ),
                array(
                    'value'     => 'linear',
                    'label'     => $this->_helper->__('Linear'),
                ),
                array(
                    'value'     => 'ease',
                    'label'     => $this->_helper->__('Ease'),
                ),
                array(
                    'value'     => 'ease-in',
                    'label'     => $this->_helper->__('Ease-In'),
                ),
                array(
                    'value'     => 'ease-out',
                    'label'     => $this->_helper->__('Ease-Out'),
                ),
                array(
                    'value'     => 'ease-in-out',
                    'label'     => $this->_helper->__('Ease-In-Out'),
                ),
                array(
                    'value'     => 'cubic-bezier',
                    'label'     => $this->_helper->__('Cubic-Bezier'),
                ),
            ),
            'value'     => '',
        ));


        $generalFieldset->addField('captions', 'select', array(
            'name'      => 'captions',
            'label'     => $this->_helper->__('Captions'),
            'note'      => 'Include image captions. Captions are derived from the image\'s "description" attribute.',
            'values'    => $yesNoOptions,
            'value'     => '0',
        ));

        $generalFieldset->addField('ticker', 'select', array(
            'name'      => 'ticker',
            'label'     => $this->_helper->__('Ticker'),
            'note'      => 'Use slider in ticker mode (similar to a news ticker).',
            'values'    => $yesNoOptions,
            'value'     => '0',
        ));

        $generalFieldset->addField('ticker_hover', 'select', array(
            'name'      => 'ticker_hover',
            'label'     => $this->_helper->__('Ticker Hover'),
            'note'      => 'Ticker will pause when mouse hovers over slider. Note: this functionality does NOT work if using CSS transitions!',
            'values'    => $yesNoOptions,
            'value'     => '0',
        ));

        $generalFieldset->addField('adaptive_height', 'select', array(
            'name'      => 'adaptive_height',
            'label'     => $this->_helper->__('Adaptive Height'),
            'note'      => 'Dynamically adjust slider height based on each slide\'s height.',
            'values'    => $yesNoOptions,
            'value'     => '0',
        ));

        $generalFieldset->addField('adaptive_height_speed', 'text', array(
            'name'      => 'adaptive_height_speed',
            'label'     => $this->_helper->__('Adaptive Height Speed'),
            'note'      => 'Slide height transition duration (in ms). Only used if Adaptive Height is enabled.',
            'value'     => 500,
        ));

        // $generalFieldset->addField('video', 'select', array(
        //     'label'     => $this->_helper->__('Video'),
        //     'note'      => 'NOT IMPLEMENTED ON THIS VERSION - If any slides contain video, set this to true. Also, include plugins/jquery.fitvids.js See http://fitvidsjs.com/ for more info.',
        //     'values'    => $yesNoOptions,
        //     'value'     => '0',
        // ));

        $generalFieldset->addField('responsive', 'select', array(
            'name'      => 'responsive',
            'label'     => $this->_helper->__('Responsive'),
            'note'      => 'Enable or disable auto resize of the slider. Useful if you need to use fixed width sliders.',
            'values'    => $yesNoOptions,
            'value'     => '1',
        ));

        $generalFieldset->addField('use_css', 'select', array(
            'name'      => 'use_css',
            'label'     => $this->_helper->__('Use CSS'),
            'note'      => 'If enabled, CSS transitions will be used for horizontal and vertical slide animations (this uses native hardware acceleration). If disabled, jQuery animate() will be used.',
            'values'    => $yesNoOptions,
            'value'     => '1',
        ));

        $generalFieldset->addField('preload_images', 'select', array(
            'name'      => 'preload_images',
            'label'     => $this->_helper->__('Preload Images'),
            'note'      => 'If \'all\', preloads all images before starting the slider. If \'visible\', preloads only images in the initially visible slides before starting the slider (tip: use \'visible\' if all slides are identical dimensions).',
            'values'    => array(
                array(
                    'value'     => 'visible',
                    'label'     => $this->_helper->__('Visible'),
                ),

                array(
                    'value'     => 'all',
                    'label'     => $this->_helper->__('All'),
                ),
            ),
            'value'     => 'visible',
        ));

        $generalFieldset->addField('touch_enabled', 'select', array(
            'name'      => 'touch_enabled',
            'label'     => $this->_helper->__('Touch Enabled'),
            'note'      => 'If true, slider will allow touch swipe transitions.',
            'values'    => $yesNoOptions,
            'value'     => '1',
        ));

        $generalFieldset->addField('swipe_threshold', 'text', array(
            'name'      => 'swipe_threshold',
            'label'     => $this->_helper->__('Swipe Threshold'),
            'note'      => 'Amount of pixels a touch swipe needs to exceed in order to execute a slide transition. Only used if "Toutch Enabled" is enabled.',
            'value'     => 50,
        ));

        $generalFieldset->addField('one_to_one_touch', 'select', array(
            'name'      => 'one_to_one_touch',
            'label'     => $this->_helper->__('One To One Touch'),
            'note'      => 'If true, non-fade slides follow the finger as it swipes.',
            'values'    => $yesNoOptions,
            'value'     => '1',
        ));

        $generalFieldset->addField('prevent_default_swipe_x', 'select', array(
            'name'      => 'prevent_default_swipe_x',
            'label'     => $this->_helper->__('Prevent Horizontal Swipe'),
            'note'      => 'If true, touch screen will not move horizontally as the finger swipes.',
            'values'    => $yesNoOptions,
            'value'     => '1',
        ));

        $generalFieldset->addField('prevent_default_swipe_y', 'select', array(
            'name'      => 'prevent_default_swipe_y',
            'label'     => $this->_helper->__('Prevent Vertical Swipe'),
            'note'      => 'If true, touch screen will not move vertically as the finger swipes.',
            'values'    => $yesNoOptions,
            'value'     => '0',
        ));

        /**
         * GROUP - Pager configuration fields
         */

        $pagerFieldset->addField('pager', 'select', array(
            'name'      => 'pager',
            'label'     => $this->_helper->__('Pager'),
            'note'      => 'If true, a pager will be added.',
            'values'    => $yesNoOptions,
            'value'     => '1',
        ));

        $pagerFieldset->addField('pager_type', 'select', array(
            'name'      => 'pager_type',
            'label'     => $this->_helper->__('Pager Type'),
            'note'      => 'If "Full", a pager link will be generated for each slide. If "Short", a x / y pager will be used (ex. 1 / 5).',
            'values'    => array(
                array(
                    'value'     => 'full',
                    'label'     => $this->_helper->__('Full'),
                ),
                array(
                    'value'     => 'short',
                    'label'     => $this->_helper->__('Short'),
                ),
            ),
            'value'     => 'full',
        ));

        $pagerFieldset->addField('pager_short_separator', 'text', array(
            'name'      => 'pager_short_separator',
            'label'     => $this->_helper->__('Pager Short Separator'),
            'note'      => 'If Pager Type is set to "Short", pager will use this value as the separating character',
            'value'     => '/',
        ));

        $pagerFieldset->addField('pager_selector', 'text', array(
            'name'      => 'pager_selector',
            'label'     => $this->_helper->__('Pager Selector'),
            'note'      => 'Element used to populate the pager. By default, the pager is appended to the bx-viewport (use a jQuery selector)',
            'value'     => '',
        ));


        /**
         * GROUP - Controls configuration fields
         */

        $controlFieldset->addField('controls', 'select', array(
            'name'      => 'controls',
            'label'     => $this->_helper->__('Controls'),
            'note'      => 'If true, "Next" / "Prev" controls will be added.',
            'values'    => $yesNoOptions,
            'value'     => '1'
        ));

        $controlFieldset->addField('next_text', 'text', array(
            'name'      => 'next_text',
            'label'     => $this->_helper->__('Next Text'),
            'note'      => 'Text to be used for the "Next" control.',
            'value'     => 'Next',
        ));

        $controlFieldset->addField('prev_text', 'text', array(
            'name'      => 'prev_text',
            'label'     => $this->_helper->__('Prev Text'),
            'note'      => 'Text to be used for the "Prev" control.',
            'value'     => 'Prev',
        ));

        $controlFieldset->addField('next_selector', 'text', array(
            'name'      => 'next_selector',
            'label'     => $this->_helper->__('Next Selector'),
            'note'      => 'Element used to populate the "next" controls (use a jQuery selector).',
            'value'     => '',
        ));

        $controlFieldset->addField('prev_selector', 'text', array(
            'name'      => 'prev_selector',
            'label'     => $this->_helper->__('Prev Selector'),
            'note'      => 'Element used to populate the "prev" controls (use a jQuery selector).',
            'value'     => '',
        ));

        $controlFieldset->addField('auto_controls', 'select', array(
            'name'      => 'auto_controls',
            'label'     => $this->_helper->__('Auto Controls'),
            'note'      => 'If true, "Start" / "Stop" controls will be added.',
            'values'    => $yesNoOptions,
            'value'     => '0',
        ));

        $controlFieldset->addField('start_text', 'text', array(
            'name'      => 'start_text',
            'label'     => $this->_helper->__('Start Text'),
            'note'      => 'Text to be used for the "Start" control.',
            'value'     => 'Start',
        ));

        $controlFieldset->addField('stop_text', 'text', array(
            'name'      => 'stop_text',
            'label'     => $this->_helper->__('Stop Text'),
            'note'      => 'Text to be used for the "Stop" control.',
            'value'     => 'Stop',
        ));

        $controlFieldset->addField('auto_controls_combine', 'select', array(
            'name'      => 'auto_controls_combine',
            'label'     => $this->_helper->__('Auto Controls Combine'),
            'note'      => 'When slideshow is playing only "Stop" control is displayed and vice-versa.',
            'values'    => $yesNoOptions,
            'value'     => '0',
        ));

        $controlFieldset->addField('auto_controls_selector', 'text', array(
            'name'      => 'auto_controls_selector',
            'label'     => $this->_helper->__('Auto Controls Selector'),
            'note'      => 'Element used to populate the auto controls (use a jQuery selector).',
            'value'     => '',
        ));


        /**
         * GROUP - Auto configuration fields
         */

        $autoFieldset->addField('auto', 'select', array(
            'name'      => 'auto',
            'label'     => $this->_helper->__('Auto'),
            'note'      => 'Slides will automatically transition.',
            'values'    => $yesNoOptions,
            'value'     => '0',
        ));

        $autoFieldset->addField('pause', 'text', array(
            'name'      => 'pause',
            'label'     => $this->_helper->__('Pause'),
            'note'      => 'The amount of time (in ms) between each auto transition. (1000 ms = 1 second)',
            'value'     => 4000,
        ));

        $autoFieldset->addField('auto_start', 'select', array(
            'name'      => 'auto_start',
            'label'     => $this->_helper->__('Auto Start'),
            'note'      => 'Slide show starts playing on load. If disabled, slideshow will start when the "Start" control is clicked.',
            'values'    => $yesNoOptions,
            'value'     => '1',
        ));

        $autoFieldset->addField('auto_direction', 'select', array(
            'name'      => 'auto_direction',
            'label'     => $this->_helper->__('Auto Direction'),
            'note'      => 'The direction of auto show slide transitions.',
            'values'    => array(
                array(
                    'value'     => 'next',
                    'label'     => $this->_helper->__('Next'),
                ),

                array(
                    'value'     => 'prev',
                    'label'     => $this->_helper->__('Prev'),
                ),
            ),
            'value'     => 'next',
        ));

        $autoFieldset->addField('auto_hover', 'select', array(
            'name'      => 'auto_hover',
            'label'     => $this->_helper->__('Auto Hover'),
            'note'      => 'Slide show will pause when mouse hovers over slider.',
            'values'    => $yesNoOptions,
            'value'     => '0',
        ));

        $autoFieldset->addField('auto_delay', 'text', array(
            'name'      => 'auto_delay',
            'label'     => $this->_helper->__('Auto Delay'),
            'note'      => 'Time (in ms) slide show should wait before starting. (1000 ms = 1 second).',
            'value'     => 0,
        ));


        /**
         * GROUP - Carousel configuration fields
         */

        $carouselFieldset->addField('min_slides', 'text', array(
            'name'      => 'min_slides',
            'label'     => $this->_helper->__('Min Slides'),
            'note'      => 'The minimum number of slides to be shown. Slides will be sized down if carousel becomes smaller than the original size.',
            'value'     => 1,
        ));

        $carouselFieldset->addField('max_slides', 'text', array(
            'name'      => 'max_slides',
            'label'     => $this->_helper->__('Max Slides'),
            'note'      => 'The maximum number of slides to be shown. Slides will be sized up if carousel becomes larger than the original size.',
            'value'     => 1,
        ));

        $carouselFieldset->addField('move_slides', 'text', array(
            'name'      => 'move_slides',
            'label'     => $this->_helper->__('Move Slides'),
            'note'      => 'The number of slides to move on transition. This value must be >= "Min Slides", and <= "Max Slides". If zero (default), the number of fully-visible slides will be used.',
            'value'     => 0,
        ));

        $carouselFieldset->addField('slide_width', 'text', array(
            'name'      => 'slide_width',
            'label'     => $this->_helper->__('Slide Width'),
            'note'      => 'The width of each slide. This setting is required for all horizontal carousels!',
            'value'     => 0,
        ));


        if (Mage::getSingleton('adminhtml/session')->getSlideshowData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getSlideshowData());
            Mage::getSingleton('adminhtml/session')->setSlideshowData(null);
        } elseif (Mage::registry('slideshow_slide_data')) {
            $form->setValues(Mage::registry('slideshow_slide_data')->getData());
        }

        return parent::_prepareForm();
    }
}
