<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Pickup_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('pickup_form', array('legend' => Mage::helper('upslabel')->__('Pickup information')));

        

        if ($this->getRequest()->getParam('id') > 0) {
            $fieldset->addField('price', 'text', array(
                'label' => Mage::helper('upslabel')->__('Grand Total Of All Charge'),
                'disabled' => true,
            ));
        }

        $fieldset->addField('CloseTime', 'time', array(
            'name' => 'CloseTime',
            'label' => Mage::helper('upslabel')->__('Close Time'),
            'title' => Mage::helper('upslabel')->__('Close Time'),
            'required' => true,
            'value' => Mage::getStoreConfig('upslabel/pickup/CloseTime', $store),
        ));

        $fieldset->addField('ReadyTime', 'time', array(
            'name' => 'ReadyTime',
            'label' => Mage::helper('upslabel')->__('Ready Time'),
            'title' => Mage::helper('upslabel')->__('Ready Time'),
            'required' => true,
            'value' => Mage::getStoreConfig('upslabel/pickup/ReadyTime', $store),
        ));

        $fieldset->addField('PickupDateYear', 'select', array(
            'name' => 'PickupDateYear',
            'label' => Mage::helper('upslabel')->__('Pickup Date Year'),
            'title' => Mage::helper('upslabel')->__('Pickup Date Year'),
            'required' => true,
            'value' => Mage::getStoreConfig('upslabel/pickup/PickupDateYear', $store),
            'values' => Mage::getModel('upslabel/config_pickup_year')->toOptionArray(),
        ));

        $fieldset->addField('PickupDateMonth', 'select', array(
            'name' => 'PickupDateMonth',
            'label' => Mage::helper('upslabel')->__('Pickup Date Month'),
            'title' => Mage::helper('upslabel')->__('Pickup Date Month'),
            'required' => true,
            'value' => Mage::getStoreConfig('upslabel/pickup/PickupDateMonth', $store),
            'values' => Mage::getModel('upslabel/config_pickup_month')->toOptionArray(),
        ));

        $fieldset->addField('PickupDateDay', 'select', array(
            'name' => 'PickupDateDay',
            'label' => Mage::helper('upslabel')->__('Pickup Date Day'),
            'title' => Mage::helper('upslabel')->__('Pickup Date Day'),
            'required' => true,
            'value' => Mage::getStoreConfig('upslabel/pickup/PickupDateDay', $store),
            'values' => Mage::getModel('upslabel/config_pickup_day')->toOptionArray(),
        ));

        $fieldset->addField('AlternateAddressIndicator', 'select', array(
            'name' => 'AlternateAddressIndicator',
            'label' => Mage::helper('upslabel')->__('Alternate Address Indicator'),
            'title' => Mage::helper('upslabel')->__('Alternate Address Indicator'),
            'required' => true,
            'value' => Mage::getStoreConfig('upslabel/pickup/AlternateAddressIndicator', $store),
            'values' => Mage::getModel('upslabel/config_pickup_alternateindicator')->toOptionArray(),
        ));

        $fieldset->addField('ServiceCode', 'select', array(
            'name' => 'ServiceCode',
            'label' => Mage::helper('upslabel')->__('Service Code'),
            'title' => Mage::helper('upslabel')->__('Service Code'),
            'required' => true,
            'value' => Mage::getStoreConfig('upslabel/pickup/ServiceCode', $store),
            'values' => Mage::getModel('upslabel/config_pickup_servicecode')->toOptionArray(),
        ));

        $fieldset->addField('Quantity', 'text', array(
            'name' => 'Quantity',
            'label' => Mage::helper('upslabel')->__('Quantity'),
            'title' => Mage::helper('upslabel')->__('Quantity'),
            'required' => true,
            'value' => Mage::getStoreConfig('upslabel/pickup/Quantity', $store),
        ));

        $fieldset->addField('DestinationCountryCode', 'select', array(
            'name' => 'DestinationCountryCode',
            'label' => Mage::helper('upslabel')->__('Destination Country'),
            'title' => Mage::helper('upslabel')->__('Destination Country'),
            'required' => true,
            'value' => Mage::getStoreConfig('upslabel/pickup/DestinationCountryCode', $store),
            'values' => Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(),
        ));

        $fieldset->addField('ContainerCode', 'select', array(
            'name' => 'ContainerCode',
            'label' => Mage::helper('upslabel')->__('Container Code'),
            'title' => Mage::helper('upslabel')->__('Container Code'),
            'required' => true,
            'value' => Mage::getStoreConfig('upslabel/pickup/ContainerCode', $store),
            'values' => Mage::getModel('upslabel/config_pickup_containercode')->toOptionArray(),
        ));

        $fieldset->addField('Weight', 'text', array(
            'name' => 'Weight',
            'label' => Mage::helper('upslabel')->__('Weight'),
            'title' => Mage::helper('upslabel')->__('Weight'),
            'value' => Mage::getStoreConfig('upslabel/pickup/Weight', $store),
        ));

        $fieldset->addField('UnitOfMeasurement', 'select', array(
            'name' => 'UnitOfMeasurement',
            'label' => Mage::helper('upslabel')->__('Unit Of Measurement'),
            'title' => Mage::helper('upslabel')->__('Unit Of Measurement'),
            'value' => Mage::getStoreConfig('upslabel/pickup/UnitOfMeasurement', $store),
            'values' => Mage::getModel('upslabel/config_weight')->toOptionArray(),
        ));

        $fieldset->addField('OverweightIndicator', 'select', array(
            'name' => 'OverweightIndicator',
            'label' => Mage::helper('upslabel')->__('Overweight Indicator'),
            'title' => Mage::helper('upslabel')->__('Overweight Indicator'),
            'value' => Mage::getStoreConfig('upslabel/pickup/OverweightIndicator', $store),
            'values' => Mage::getModel('upslabel/config_pickup_overweightindicator')->toOptionArray(),
        ));

        $fieldset->addField('PaymentMethod', 'select', array(
            'name' => 'PaymentMethod',
            'label' => Mage::helper('upslabel')->__('Payment Method'),
            'title' => Mage::helper('upslabel')->__('Payment Method'),
            'required' => true,
            'value' => Mage::getStoreConfig('upslabel/pickup/PaymentMethod', $store),
            'values' => Mage::getModel('upslabel/config_pickup_paymentmethod')->toOptionArray(),
        ));

        $fieldset->addField('SpecialInstruction', 'textarea', array(
            'name' => 'SpecialInstruction',
            'label' => Mage::helper('upslabel')->__('Special Instruction'),
            'title' => Mage::helper('upslabel')->__('Special Instruction'),
            'value' => Mage::getStoreConfig('upslabel/pickup/SpecialInstruction', $store),
        ));

        $fieldset->addField('ReferenceNumber', 'textarea', array(
            'name' => 'ReferenceNumber',
            'label' => Mage::helper('upslabel')->__('Reference Number'),
            'title' => Mage::helper('upslabel')->__('Reference Number'),
            'value' => Mage::getStoreConfig('upslabel/pickup/ReferenceNumber', $store),
        ));

        $fieldset->addField('Notification', 'select', array(
            'name' => 'Notification',
            'label' => Mage::helper('upslabel')->__('Notification'),
            'title' => Mage::helper('upslabel')->__('Notification'),
            'value' => Mage::getStoreConfig('upslabel/pickup/Notification', $store),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('ConfirmationEmailAddress', 'textarea', array(
            'name' => 'ConfirmationEmailAddress',
            'label' => Mage::helper('upslabel')->__('Confirmation Email Address'),
            'title' => Mage::helper('upslabel')->__('Confirmation Email Address'),
            'value' => Mage::getStoreConfig('upslabel/pickup/ConfirmationEmailAddress', $store),
        ));

        $fieldset->addField('UndeliverableEmailAddress', 'text', array(
            'name' => 'UndeliverableEmailAddress',
            'label' => Mage::helper('upslabel')->__('Undeliverable Email Address'),
            'title' => Mage::helper('upslabel')->__('Undeliverable Email Address'),
            'value' => Mage::getStoreConfig('upslabel/pickup/UndeliverableEmailAddress', $store),
        ));
        /*$fieldset->addField('status', 'hidden', array(
            'name'      => 'status',
        ));*/

        $fieldset->addField('ShipFrom', 'select', array(
            'name' => 'ShipFrom',
            'label' => Mage::helper('upslabel')->__('Ship From'),
            'title' => Mage::helper('upslabel')->__('Ship From'),
            'required' => true,
            'value' => Mage::getStoreConfig('upslabel/shipping/defaultshipfrom', $store),
            'values' => Mage::getModel('upslabel/config_defaultaddress')->toOptionArray(),
        ));

        $fieldset->addField('OtherAddress', 'select', array(
            'name' => 'oadress[OtherAddress]',
            'label' => Mage::helper('upslabel')->__('Other address'),
            'title' => Mage::helper('upslabel')->__('Other address'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('companyname', 'text', array(
            'name' => 'oadress[companyname]',
            'label' => Mage::helper('upslabel')->__('Company name'),
            'title' => Mage::helper('upslabel')->__('Company name'),
        ));

        $fieldset->addField('attentionname', 'text', array(
            'name' => 'oadress[attentionname]',
            'label' => Mage::helper('upslabel')->__('Attention name'),
            'title' => Mage::helper('upslabel')->__('Attention name'),
        ));

        $fieldset->addField('phonenumber', 'text', array(
            'name' => 'oadress[phonenumber]',
            'label' => Mage::helper('upslabel')->__('Phone number'),
            'title' => Mage::helper('upslabel')->__('Phone number'),
        ));

        $fieldset->addField('addressline1', 'text', array(
            'name' => 'oadress[addressline1]',
            'label' => Mage::helper('upslabel')->__('Address'),
            'title' => Mage::helper('upslabel')->__('Address'),
        ));

        $fieldset->addField('room', 'text', array(
            'name' => 'oadress[room]',
            'label' => Mage::helper('upslabel')->__('Room'),
            'title' => Mage::helper('upslabel')->__('Room'),
        ));

        $fieldset->addField('floor', 'text', array(
            'name' => 'oadress[floor]',
            'label' => Mage::helper('upslabel')->__('Floor'),
            'title' => Mage::helper('upslabel')->__('Floor'),
        ));

        $fieldset->addField('city', 'text', array(
            'name' => 'oadress[city]',
            'label' => Mage::helper('upslabel')->__('City'),
            'title' => Mage::helper('upslabel')->__('City'),
        ));

        $fieldset->addField('stateprovincecode', 'text', array(
            'name' => 'oadress[stateprovincecode]',
            'label' => Mage::helper('upslabel')->__('State province code'),
            'title' => Mage::helper('upslabel')->__('State province code'),
        ));

        $fieldset->addField('urbanization', 'text', array(
            'name' => 'oadress[urbanization]',
            'label' => Mage::helper('upslabel')->__('Urbanization'),
            'title' => Mage::helper('upslabel')->__('Urbanization'),
        ));

        $fieldset->addField('postalcode', 'text', array(
            'name' => 'oadress[postalcode]',
            'label' => Mage::helper('upslabel')->__('Postal code'),
            'title' => Mage::helper('upslabel')->__('Postal code'),
        ));

        $fieldset->addField('countrycode', 'select', array(
            'name' => 'oadress[countrycode]',
            'label' => Mage::helper('upslabel')->__('Country'),
            'title' => Mage::helper('upslabel')->__('Country'),
            'values' => Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(),
        ));

        $fieldset->addField('residential', 'select', array(
            'name' => 'oadress[residential]',
            'label' => Mage::helper('upslabel')->__('Residential'),
            'title' => Mage::helper('upslabel')->__('Residential'),
            'values' => Mage::getModel('upslabel/config_pickup_residential')->toOptionArray(),
        ));

        $fieldset->addField('pickup_point', 'text', array(
            'name' => 'oadress[pickup_point]',
            'label' => Mage::helper('upslabel')->__('Pickup point'),
            'title' => Mage::helper('upslabel')->__('Pickup point'),
        ));

        if (Mage::registry('pickup_data') && count(Mage::registry('pickup_data')->getData()) > 0) {
            $fieldset->addField('pickup_request', 'textarea', array(
                'name' => 'pickup_request',
                'readonly' => true,
                'disabled' => true,
                'style' => 'display:none;'
            ));
            $fieldset->addField('pickup_response', 'textarea', array(
                'name' => 'pickup_response',
                'readonly' => true,
                'disabled' => true,
                'style' => 'display:none;'
            ));
            $fieldset->addField('pickup_cancel', 'textarea', array(
                'name' => 'pickup_cancel',
                'readonly' => true,
                'disabled' => true,
                'style' => 'display:none;'
            ));
            $fieldset->addField('pickup_cancel_request', 'textarea', array(
                'name' => 'pickup_cancel_request',
                'readonly' => true,
                'disabled' => true,
                'style' => 'display:none;'
            ));
        }

        if (Mage::getSingleton('adminhtml/session')->getPickupData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getPickupData());
            Mage::getSingleton('adminhtml/session')->setPickupData(null);
        } elseif (Mage::registry('pickup_data') && count(Mage::registry('pickup_data')->getData()) > 0) {
            $data = Mage::registry('pickup_data')->getData();
            $dataShip = json_decode($data['ShipFrom'], true);
            if (is_array($dataShip) && isset($dataShip['OtherAddress']) && $dataShip['OtherAddress'] == 1) {
                $data = array_merge($data, $dataShip);
            }
            $form->setValues($data);
        }
        return parent::_prepareForm();
    }
}