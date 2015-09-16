<?php

class Altima_Lookbookslider_Model_Config_Source_Sliders {

    public function toOptionArray() {

        $sliders = array();
        $collection = Mage::getModel('lookbookslider/lookbookslider')->getCollection()->addEnableFilter(1);
        foreach ($collection as $slider) {
            $sliders[] = ( array(
                'label' => (string) $slider->getName(),
                'value' => $slider->getId()
                    ));
        }
        return $sliders;
    }

}