<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Adminhtml_PickupController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('upslabel/pickup')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Pickup Manager'), Mage::helper('adminhtml')->__('Pickup Manager'));

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('upslabel/pickup')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('pickup_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('upslabel/pickup');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Pickup Manager'), Mage::helper('adminhtml')->__('Pickup Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Pickup News'), Mage::helper('adminhtml')->__('Pickup News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('upslabel/adminhtml_pickup_edit'))
                ->_addLeft($this->getLayout()->createBlock('upslabel/adminhtml_pickup_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('upslabel')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('upslabel/pickup');
            
            $data['CloseTime'] = implode(",", $data['CloseTime']);
            $data['ReadyTime'] = implode(",", $data['ReadyTime']);
            $data['PickupDateYear'] = $data['PickupDateYear'] == "0" ? date("Y") : $data['PickupDateYear'];
            $data['PickupDateMonth'] = $data['PickupDateMonth'] == "0" ? date("m") : $data['PickupDateMonth'];
            $data['PickupDateDay'] = $data['PickupDateDay'] == "0" ? date("d") : $data['PickupDateDay'];
            if(isset($data['oadress']['OtherAddress']) && $data['oadress']['OtherAddress']==1){
                $data['ShipFrom'] = json_encode($data['oadress']);
            }
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }
                $pickup = Mage::getModel('upslabel/ups');
                $AccessLicenseNumber = Mage::getStoreConfig('upslabel/credentials/accesslicensenumber', $store);
                $UserId = Mage::getStoreConfig('upslabel/credentials/userid', $store);
                $Password = Mage::getStoreConfig('upslabel/credentials/password', $store);
                $shipperNumber = Mage::getStoreConfig('upslabel/credentials/shippernumber', $store);
                $pickup->setCredentials($AccessLicenseNumber, $UserId, $Password, $shipperNumber);

                $pickup->RatePickupIndicator = "N"/*$data['RatePickupIndicator']*/;
                $pickup->shipperCountryCode = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . Mage::getStoreConfig('upslabel/shipping/defaultshipper', $store) . '/countrycode', $store));
                $pickup->CloseTime = $data['CloseTime'];
                $pickup->ReadyTime = $data['ReadyTime'];
                $pickup->PickupDateYear = $data['PickupDateYear'];
                $pickup->PickupDateMonth = $data['PickupDateMonth'];
                $pickup->PickupDateDay = $data['PickupDateDay'];
                if(isset($data['oadress']['OtherAddress']) && $data['oadress']['OtherAddress']==1){
                    $pickup->shipfromCompanyName = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['companyname']);
                    $pickup->shipfromAttentionName = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['attentionname']);
                    $pickup->shipfromAddressLine1 = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['addressline1']);
                    $pickup->room = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['room']);
                    $pickup->floor = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['floor']);
                    $pickup->shipfromCity = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['city']);
                    $pickup->shipfromStateProvinceCode = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['stateprovincecode']);
                    $pickup->urbanization = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['urbanization']);
                    $pickup->shipfromPostalCode = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['postalcode']);
                    $pickup->shipfromCountryCode = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['countrycode']);
                    $pickup->residential = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['residential']);
                    $pickup->pickup_point = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['pickup_point']);
                    $pickup->shipfromPhoneNumber = Infomodus_Upslabel_Helper_Help::escapeXML($data['oadress']['phonenumber']);
                }
                else {
                    $pickup->shipfromCompanyName = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/companyname', $store));
                    $pickup->shipfromAttentionName = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/attentionname', $store));
                    $pickup->shipfromAddressLine1 = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/addressline1', $store));
                    $pickup->room = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/room', $store));
                    $pickup->floor = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/floor', $store));
                    $pickup->shipfromCity = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/city', $store));
                    $pickup->shipfromStateProvinceCode = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/stateprovincecode', $store));
                    $pickup->urbanization = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/urbanization', $store));
                    $pickup->shipfromPostalCode = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/postalcode', $store));
                    $pickup->shipfromCountryCode = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/countrycode', $store));
                    $pickup->residential = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/residential', $store));
                    $pickup->pickup_point = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/pickup_point', $store));
                    $pickup->shipfromPhoneNumber = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $data['ShipFrom'] . '/phonenumber', $store));
                }
                $pickup->AlternateAddressIndicator = $data['AlternateAddressIndicator'];
                $pickup->ServiceCode = $data['ServiceCode'];
                $pickup->Quantity = $data['Quantity'];
                $pickup->DestinationCountryCode = $data['DestinationCountryCode'];
                $pickup->ContainerCode = $data['ContainerCode'];
                $pickup->Weight = $data['Weight'];
                $pickup->UnitOfMeasurement = $data['UnitOfMeasurement'];
                $pickup->OverweightIndicator = $data['OverweightIndicator'];
                $pickup->PaymentMethod = $data['PaymentMethod'];
                $pickup->SpecialInstruction = $data['SpecialInstruction'];
                $pickup->ReferenceNumber = $data['ReferenceNumber'];
                $pickup->Notification = $data['Notification'];
                $pickup->ConfirmationEmailAddress = $data['ConfirmationEmailAddress'];
                $pickup->UndeliverableEmailAddress = $data['UndeliverableEmailAddress'];
                $pickup->testing = Mage::getStoreConfig('upslabel/testmode/testing', $store);

                if ($this->getRequest()->getParam('id') < 1) {
                    $price = $pickup->ratePickup();
                    $response = $pickup->getPickup();
                }
                else {
                    $this->cancelPickup($this->getRequest()->getParam('id'));
                    $price = $pickup->ratePickup();
                    $response = $pickup->getPickup();
                }
                if (!isset($response['error'])) {
                    if ($this->getRequest()->getParam('id') < 1) {
                        $model->setData('pickup_request', $response['data']);
                        $model->setData('pickup_response', $response['response']);
                        $model->setData('status', $response['Description']);
                        $model->setData('price', $price);
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('upslabel')->__('Pickup was successfully saved'));
                    }
                    else {
                        $model->setData('pickup_request', $response['data']);
                        $model->setData('pickup_response', $response['response']);
                        $model->setData('status', $response['Description']);
                        $model->setData('price', $price);
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('upslabel')->__('Pickup was successfully modified'));
                    }
                    $model->save();
                    Mage::getSingleton('adminhtml/session')->setFormData(false);
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                        return;
                    }
                    $this->_redirect('*/*/');
                    return;
                } else {
                    echo $response['error'];
                    exit;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('upslabel')->__('Unable to find Pickup to save'));
        $this->_redirect('*/*/');
    }

    public function cancelPickup($id){
        $model = Mage::getModel('upslabel/pickup')->load($id);
        $data = $model->getData();
        
        $pickup = Mage::getModel('upslabel/ups');
        $AccessLicenseNumber = Mage::getStoreConfig('upslabel/credentials/accesslicensenumber');
        $UserId = Mage::getStoreConfig('upslabel/credentials/userid');
        $Password = Mage::getStoreConfig('upslabel/credentials/password');
        $shipperNumber = Mage::getStoreConfig('upslabel/credentials/shippernumber');
        $pickup->setCredentials($AccessLicenseNumber, $UserId, $Password, $shipperNumber);
        $pickup->testing = Mage::getStoreConfig('upslabel/testmode/testing');



        $xml = simplexml_load_string($data['pickup_response']);
        $soap = $xml->children('soapenv', true)->Body[0];
        $PRN = $soap->children('pkup', true)->PickupCreationResponse[0]->PRN;
        return $pickup->cancelPickup($PRN);
    }

    public function cancelAction(){
        $id = $this->getRequest()->getParam('id');
        $response = $this->cancelPickup($id);
        if (!isset($response['error'])) {
            $model = Mage::getModel('upslabel/pickup')->load($id);
            $model->setData('pickup_cancel_request', $response['data']);
            $model->setData('pickup_cancel', $response['response']);
            $model->setData('status', "Canceled");
            $model->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('upslabel')->__('Pickup was successfully canceled'));
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }
            $this->_redirect('*/*/');
            return;
        } else {
            echo $response['error'];
            exit;
        }
    }

    public function statusAction(){
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('upslabel/pickup')->load($id);
        
        $pickup = Mage::getModel('upslabel/ups');
        $AccessLicenseNumber = Mage::getStoreConfig('upslabel/credentials/accesslicensenumber');
        $UserId = Mage::getStoreConfig('upslabel/credentials/userid');
        $Password = Mage::getStoreConfig('upslabel/credentials/password');
        $shipperNumber = Mage::getStoreConfig('upslabel/credentials/shippernumber');
        $pickup->setCredentials($AccessLicenseNumber, $UserId, $Password, $shipperNumber);
        $pickup->testing = Mage::getStoreConfig('upslabel/testmode/testing');

        $response = $pickup->statusPickup();
        if (!isset($response['error'])) {
            /*$model->setData('pickup_request', $response['data']);
            $model->setData('pickup_response', $response['response']);
            $model->setData('status', "Canceled");
            $model->save();*/
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('upslabel')->__('Pickup was successfully canceled'));
            if ($this->getRequest()->getParam('back')) {
                //$this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }
            $this->_redirect('*/*/');
            return;
        } else {
            echo $response['error'];
            exit;
        }
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {

                $id = $this->getRequest()->getParam('id');
                $this->cancelPickup($id);

                $model = Mage::getModel('upslabel/pickup');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Pickup was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $upslabelIds = $this->getRequest()->getParam('pickup');
        if (!is_array($upslabelIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Pickup(s)'));
        } else {
            try {
                foreach ($upslabelIds as $upslabelId) {
                    $this->cancelPickup($upslabelId);
                    $upslabel = Mage::getModel('upslabel/pickup')->load($upslabelId);
                    $upslabel->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($upslabelIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName = 'pickup.csv';
        $content = $this->getLayout()->createBlock('upslabel/adminhtml_pickup_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'pickup.xml';
        $content = $this->getLayout()->createBlock('upslabel/adminhtml_pickup_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}