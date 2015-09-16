<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php
class Infomodus_Upslabel_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {			
		$this->loadLayout();     
		$this->renderLayout();
    }
}