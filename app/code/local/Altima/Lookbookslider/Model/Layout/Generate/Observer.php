<?php
class Altima_Lookbookslider_Model_Layout_Generate_Observer {
    
	public function includeJavascripts($observer) {            
            $helper = Mage::helper('lookbookslider');
            $has_lookbookslider = false;
            if ($helper->getEnabled()) {                                                                        
                       $_head = $this->__getHeadBlock();
                        if ($_head) { 
	                            if ($this->__NeedJS()) {
                             	   
	                                if ($helper->getEnableJquery()) {
	                                $_head->addLast('js', 'jquery/jquery-1.8.2.min.js');
                                        $_head->addLast('js', 'jquery/noconflict.js');
                                        $_head->addLast('skin_js', 'lookbookslider/js/jquery-migrate-1.2.1.min.js');
					$_head->addLast('skin_js', 'lookbookslider/js/jquery.mobile.customized.min.js');
                                        $_head->addLast('skin_js', 'lookbookslider/js/jquery.easing.1.3.js');
                                        $_head->addLast('skin_js', 'lookbookslider/js/camera.min.js');
                                        $_head->addLast('skin_js', 'lookbookslider/js/hotspots.js');                           
	                                }
	                                else
	                                {
                                        $_head->addLast('skin_js', 'lookbookslider/js/jquery-migrate-1.2.1.min.js');
					$_head->addLast('skin_js', 'lookbookslider/js/jquery.mobile.customized.min.js');
                                        $_head->addLast('skin_js', 'lookbookslider/js/jquery.easing.1.3.js');
                                        $_head->addLast('skin_js', 'lookbookslider/js/camera.min.js');
                                        $_head->addLast('skin_js', 'lookbookslider/js/hotspots.js');                           
	                                }                                
                                
                                    $layout = Mage::app()->getLayout();
	                                $content = $layout->getBlock('content');
	                                $block = $layout->createBlock('lookbookslider/valid');
	                                $content->insert($block); 
				     }
                                           
                        }            
        }       
    }
    /*
     * Get head block
     */
    private function __getHeadBlock() {
        return Mage::getSingleton('core/layout')->getBlock('lookbookslider_head');
    }
    
    private function __NeedJS() {
        
        $top_block = Mage::getSingleton('core/layout')->getBlock('lookbookslider_content_top');
        $bottom_block = Mage::getSingleton('core/layout')->getBlock('lookbookslider_content_bottom');
	if ($top_block) {
        	$top_sliders = $top_block->_getCollection();
	        foreach ($top_sliders as $slider) {
	            $slides = $top_block->_getSlidesCollection($slider->getId());
	            if ($slides->getSize()) return true;
	        }
	}

	if ($bottom_block) {
	        $bottom_sliders = $bottom_block->_getCollection();
	        foreach ($bottom_sliders as $slider) {
	            $slides = $bottom_block->_getSlidesCollection($slider->getId());
	            if ($slides->getSize()) return true;
	        }
	}
                       
        return false; 
    }
}