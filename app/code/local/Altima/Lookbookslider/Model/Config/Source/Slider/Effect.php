<?php

class Altima_Lookbookslider_Model_Config_Source_Slider_Effect extends Mage_Core_Model_Abstract
{
    const SIMPLE_FADE = 'simpleFade';
    const CURTAIN_TOP_LEFT = 'curtainTopLeft';
    const CURTAIN_TOP_RIGHT = 'curtainTopRight';
    const CURTAIN_BOTTOM_LEFT = 'curtainBottomLeft';
    const CURTAIN_BOTTOM_RIGHT = 'curtainBottomRight';
    const CURTAIN_SLISE_LEFT = 'curtainSliceLeft';
    const CURTAIN_SLISE_RIGHT = 'curtainSliceRight';
    const BLIND_CURTAIN_TOP_LEFT = 'blindCurtainTopLeft';
    const BLIND_CURTAIN_TOP_RIGHT = 'blindCurtainTopRight';
    const BLIND_CURTAIN_BOTTOM_LEFT = 'blindCurtainBottomLeft';
    const BLIND_CURTAIN_BOTTOM_RIGHT = 'blindCurtainBottomRight';
    const BLIND_CURTAIN_SLICE_BOTTOM = 'blindCurtainSliceBottom';
    const BLIND_CURTAIN_SLICE_TOP = 'blindCurtainSliceTop';
    const STAMPEDE = 'stampede';
    const MOSAIC = 'mosaic';
    const MOSAIC_REVERSE = 'mosaicReverse';
    const MOSAIC_RANDOM = 'mosaicRandom';
    const MOSAIC_SPIRAL = 'mosaicSpiral';
    const MOSAIC_SPIRAL_REVERSE = 'mosaicSpiralReverse';
    const TOP_LEFT_BOTTOM_RIGHT = 'topLeftBottomRight';
    const BOTTOM_RIGHT_TOP_LEFT = 'bottomRightTopLeft';
    const BOTTOM_LEFT_TOP_RIGHT = 'bottomLeftTopRight';
    const SCROLL_LEFT = 'scrollLeft';
    const SCROLL_RIGHT = 'scrollRight';
    const SCROLL_HORZ = 'scrollHorz';
    const SCROLL_BOTTOM = 'scrollBottom';
    const SCROLL_TOP = 'scrollTop';
    
    static public function getAllOptions()
    {
        return array(
                array( 'value'=> self::SIMPLE_FADE , 'label' => 'simpleFade'),
                array( 'value'=> self::CURTAIN_TOP_LEFT , 'label' => 'curtainTopLeft'),
                array( 'value'=> self::CURTAIN_TOP_RIGHT , 'label' => 'curtainTopRight'),
                array( 'value'=> self::CURTAIN_BOTTOM_LEFT , 'label' => 'curtainBottomLeft'),
                array( 'value'=> self::CURTAIN_BOTTOM_RIGHT , 'label' => 'curtainBottomRight'),
                array( 'value'=> self::CURTAIN_SLISE_LEFT , 'label' => 'curtainSliceLeft'),
                array( 'value'=> self::CURTAIN_SLISE_RIGHT , 'label' => 'curtainSliceRight'),
                array( 'value'=> self::BLIND_CURTAIN_TOP_LEFT , 'label' => 'blindCurtainTopLeft'),
                array( 'value'=> self::BLIND_CURTAIN_TOP_RIGHT , 'label' => 'blindCurtainTopRight'),
                array( 'value'=> self::BLIND_CURTAIN_BOTTOM_LEFT , 'label' => 'blindCurtainBottomLeft'),
                array( 'value'=> self::BLIND_CURTAIN_BOTTOM_RIGHT , 'label' => 'blindCurtainBottomRight'),
                array( 'value'=> self::BLIND_CURTAIN_SLICE_BOTTOM , 'label' => 'blindCurtainSliceBottom'),
                array( 'value'=> self::BLIND_CURTAIN_SLICE_TOP , 'label' => 'blindCurtainSliceTop'),
                array( 'value'=> self::STAMPEDE , 'label' => 'stampede'),
                array( 'value'=> self::MOSAIC , 'label' => 'mosaic'),
                array( 'value'=> self::MOSAIC_REVERSE , 'label' => 'mosaicReverse'),
                array( 'value'=> self::MOSAIC_RANDOM , 'label' => 'mosaicRandom'),
                array( 'value'=> self::MOSAIC_SPIRAL , 'label' => 'mosaicSpiral'),
                array( 'value'=> self::MOSAIC_SPIRAL_REVERSE , 'label' => 'mosaicSpiralReverse'),
                array( 'value'=> self::TOP_LEFT_BOTTOM_RIGHT , 'label' => 'topLeftBottomRight'),
                array( 'value'=> self::BOTTOM_RIGHT_TOP_LEFT , 'label' => 'bottomRightTopLeft'),
                array( 'value'=> self::BOTTOM_LEFT_TOP_RIGHT , 'label' => 'bottomLeftTopRight'),
                array( 'value'=> self::SCROLL_LEFT , 'label' => 'scrollLeft'),
                array( 'value'=> self::SCROLL_RIGHT , 'label' => 'scrollRight'),
                array( 'value'=> self::SCROLL_HORZ , 'label' => 'scrollHorz'),
                array( 'value'=> self::SCROLL_BOTTOM , 'label' => 'scrollBottom'),
                array( 'value'=> self::SCROLL_TOP , 'label' => 'scrollTop')
            );
    }
}
