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

$installer = $this;

$installer->startSetup();


$installer->run("

ALTER TABLE {$this->getTable('lookbookslider')} ADD `include_jquery` TINYINT(1) NOT NULL DEFAULT '1';
ALTER TABLE {$this->getTable('lookbookslider')} ADD `include_slides_js` TINYINT(1) NOT NULL DEFAULT '1';

");


try {
    
    $slides = Mage::getModel('lookbookslider/slide')->getCollection();
    foreach ($slides as $slide) {
        $hotspots = $slide->getHotspots();
	if (!empty($hotspots)) {
	        $decoded_array = json_decode($hotspots,true);
	        foreach($decoded_array as $key => $value){
	           $decoded_array[$key]['sku']=$decoded_array[$key]['text']; 
	           $decoded_array[$key]['text']='';
	           $decoded_array[$key]['href']='';
	        }
	        $encoded_array = json_encode($decoded_array);
	        $slide->setHotspots($encoded_array);		      
	        $slide->save();
	}
    }
    
} catch (Exception $exc) {
    Mage::log($exc);
}

$installer->endSetup(); 