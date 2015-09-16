<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Pickup_Edit_Render_Title extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
  public function render(Varien_Object $row)
  {
      $data =  $row->getData();
      $xml = simplexml_load_string($data['pickup_response']);
      $soap = $xml->children('soapenv', true)->Body[0];
      $PRN = $soap->children('pkup', true)->PickupCreationResponse[0]->PRN;
      return Mage::helper('upslabel')->__('PRN:')." ".$PRN
      ." ".Mage::helper('upslabel')->__('Date:')." ".$data['PickupDateDay']."-".$data['PickupDateMonth']."-".$data['PickupDateYear']
          ." ".Mage::helper('upslabel')->__('Reference Number:')." ".$data['ReferenceNumber'];
  }
}