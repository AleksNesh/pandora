<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php
class Infomodus_Upslabel_Block_Upslabel extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getUpslabel()     
     { 
        if (!$this->hasData('upslabel')) {
            $this->setData('upslabel', Mage::registry('upslabel'));
        }
        return $this->getData('upslabel');
        
    }
}