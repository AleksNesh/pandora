<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php
require_once Mage::getBaseDir('app').'/code/local/Infomodus/Upslabel/controllers/Adminhtml/UpslabelController.php';
class Infomodus_Upslabel_Controller_UpslabelController extends Infomodus_Upslabel_Adminhtml_UpslabelController
{
    public function __construct($op1 = NULL, $op2 = NULL, $op3 = array())
    {
        if ($op1 != NULL) {
            return parent::__construct($op1, $op2, $op3);
        } else {
            return $this;
        }
    }
}