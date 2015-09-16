<?php

/**
 * Ash Slideshow Extension
 *
 * @category  Ash
 * @package   Ash_Slideshow
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 *
 */
class Ash_Slideshow_Block_Slideshow_Abstract extends Mage_Core_Block_Template
{
    /**
     * $_slideshowId
     * @var integer
     */
    protected $_slideshowId;

    /**
     * $_slideshowModel
     * @var Ash_Slideshow_Model_Slideshowslides
     */
    protected $_slideshowModel;

    /**
     * $_nonBxSliderFields
     * @var array
     */
    protected $_nonBxSliderFields = array(
        'id',
        'slideshow_name',
        'slideshow_code',
        'status',
        'sort_order',
        'layout',
        'created_at',
        'updated_at'
    );

    /**
     * $_bxSliderBooleanFields
     *
     * Fields that should be converted to true/false values
     * for use in JSON data for bxSlider javascript options
     *
     * @var array
     */
    protected $_bxSliderBooleanFields = array(
        'randomStart',
        'infiniteLoop',
        'hideControlOnEnd',
        'captions',
        'ticker',
        'tickerHover',
        'adaptiveHeight',
        'video',
        'responsive',
        'useCSS',
        'touchEnabled',
        'oneToOneTouch',
        'preventDefaultSwipeX',
        'preventDefaultSwipeY',
        'pager',
        'controls',
        'autoControls',
        'autoControlsCombine',
        'auto',
        'autoStart',
        'autoHover'
    );

    /**
     * $_bxSliderIntegerFields
     *
     * Fields that should be converted to integer values
     * for use in JSON data for bxSlider javascript options
     *
     * @var array
     */
    protected $_bxSliderIntegerFields = array(
        'speed',
        'slideMargin',
        'startSlide',
        'adaptiveHeightSpeed',
        'swipeThreshold',
        'pause',
        'minSlides',
        'maxSlides',
        'moveSlides',
        'slideWidth'
    );

    protected function _construct()
    {
        parent::_construct();
        $this->_slideshowModel = Mage::getModel('ash_slideshow/slideshow');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        $slides     = $this->getSlideshowSlides();
        $config     = $this->_getSlideshowConfig();
        $bxConfig   = $this->_getBxSliderConfigJson();

        if (!empty($config)) {
            $template   = 'ash_slideshow/widget/layout_' . $config['layout'] . '.phtml';

            $this->assign('slides', $slides);
            $this->assign('config', $config);
            $this->assign('bxConfig', $bxConfig);

            $this->setTemplate($template);
        }

        return parent::_toHtml();
    }

    /**
     * getSlideshowSlides
     *
     * Return collection of slide show assets for chosen slide show
     *
     * @return Ash_Slideshow_Model_Resource_Slideshowassets_Collection
     */
    public function getSlideshowSlides()
    {
        $collection = $this->_slideshowModel->getSlidesForSlideshow($this->getSlideshowId());
        return $collection;
    }

    /**
     * _getBxSliderConfigJson
     *
     * Returns a JSON encoded string of the array
     * returned from the _getBxSliderConfig method
     *
     * @return string
     */
    protected function _getBxSliderConfigJson()
    {
        $bxConfig = $this->_getBxSliderConfig();
        return json_encode($bxConfig);
    }

    /**
     * _getBxSliderConfig
     *
     * Return an array of only the fields that are actually
     * used for BxSlider configuration options.
     *
     * Array keys are changed from underscored strings to camelCased strings.
     *
     * @return array
     */
    protected function _getBxSliderConfig()
    {
        $config     = $this->_getSlideshowConfig();
        $bxConfig   = array();

        if(!empty($config)) {
            foreach ($config as $key => $value) {
                if(!in_array($key, $this->_nonBxSliderFields)) {
                    $bxKey  = Mage::helper('ash_slideshow/inflector')->camelize($key);
                    // bxSlider has a weird pattern with their 'useCSS' argument
                    $bxKey  = ($bxKey === 'useCss') ? 'useCSS' : $bxKey;

                    switch (true) {
                        case (in_array($bxKey, $this->_bxSliderBooleanFields)):
                            $bxConfig[$bxKey] = (boolean)$value;
                            break;
                        case (in_array($bxKey, $this->_bxSliderIntegerFields)):
                            $bxConfig[$bxKey] = (integer)$value;
                            break;
                        default:
                            $bxConfig[$bxKey] = $value;
                            break;
                    }
                }
            }
        }

        return $bxConfig;
    }

    /**
     * _getSlideshowConfig
     *
     * Get the slideshow's data array
     *
     * @return array
     */
    protected function _getSlideshowConfig()
    {
        $slideshow  = $this->_getSlideshow();
        $config     = ($slideshow) ? $slideshow->getData() : array();
        return $config;
    }

    /**
     * _getSlideshow
     *
     * @return Ash_Slideshow_Model_Slideshowslides
     */
    protected function _getSlideshow()
    {
        return $this->_slideshowModel->load($this->getSlideshowId());
    }

    public function getSlideshowId()
    {
        return $this->_slideshowId;
    }

    public function setSlideshowId($value)
    {
        $this->_slideshowId = $value;
        return $this;
    }


}
