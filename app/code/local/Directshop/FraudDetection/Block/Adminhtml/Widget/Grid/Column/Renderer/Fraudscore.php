<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */
 
/**
 * Renderer for Fraud Score column
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 */
class Directshop_FraudDetection_Block_Adminhtml_Widget_Grid_Column_Renderer_Fraudscore
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
			 $threshold = Mage::getStoreConfig('frauddetection/general/threshold');
			
			
			$textColor = "auto";
			$fontSize = "auto";
			if (intval($row->getFraudScore()) >= $threshold)
			{
				$textColor = "red";
				$fontSize = "1.7em";
			}
			$score = $row->getFraudScore();
			return "<span style='color:$textColor; font-size:$fontSize;'>".$row->getFraudScore()."</span>";
			return "";
    }
} 