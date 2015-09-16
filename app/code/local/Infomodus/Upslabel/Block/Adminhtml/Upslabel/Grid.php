<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Upslabel_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('upslabelGrid');
      $this->setDefaultSort('upslabel_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }
}