<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Model_Ups
{

    protected $AccessLicenseNumber;
    protected $UserId;
    protected $Password;
    protected $shipperNumber;
    protected $credentials;

    public $packages;
    public $weightUnits;
    public $packageWeight;
    public $weightUnitsDescription;
    public $largePackageIndicator;

    public $includeDimensions;
    public $unitOfMeasurement;
    public $unitOfMeasurementDescription;
    public $length;
    public $width;
    public $height;

    public $customerContext;
    public $shipperName;
    public $shipperPhoneNumber;
    public $shipperAddressLine1;
    public $shipperCity;
    public $shipperStateProvinceCode;
    public $shipperPostalCode;
    public $shipperCountryCode;
    public $shipmentDescription;
    public $shipperAttentionName;

    public $shiptoCompanyName;
    public $shiptoAttentionName;
    public $shiptoPhoneNumber;
    public $shiptoAddressLine1;
    public $shiptoAddressLine2;
    public $shiptoCity;
    public $shiptoStateProvinceCode;
    public $shiptoPostalCode;
    public $shiptoCountryCode;
    public $residentialAddress;

    public $shipfromCompanyName;
    public $shipfromAttentionName;
    public $shipfromPhoneNumber;
    public $shipfromAddressLine1;
    public $shipfromAddressLine2;
    public $shipfromCity;
    public $shipfromStateProvinceCode;
    public $shipfromPostalCode;
    public $shipfromCountryCode;

    public $serviceCode;
    public $serviceDescription;
    public $shipmentDigest;

    public $trackingNumber;
    public $shipmentIdentificationNumber;
    public $graphicImage;
    public $htmlImage;

    public $codYesNo;
    public $currencyCode;
    public $codMonetaryValue;
    public $codFundsCode;
    public $invoicelinetotal;
    public $carbon_neutral;
    public $testing;
    public $shipmentcharge = 0;
    public $qvn = 0;
    public $qvn_code = 0;
    public $qvn_email_shipper = '';
    public $qvn_email_shipto = '';
    public $adult;
    public $upsAccount = 0;
    public $accountData;
    public $saturdayDelivery;

    /* Pickup */
    public $RatePickupIndicator;
    public $CloseTime;
    public $ReadyTime;
    public $PickupDateYear;
    public $PickupDateMonth;
    public $PickupDateDay;
    public $AlternateAddressIndicator;
    public $ServiceCode;
    public $Quantity;
    public $DestinationCountryCode;
    public $ContainerCode;
    public $Weight;
    public $UnitOfMeasurement;
    public $OverweightIndicator;
    public $PaymentMethod;
    public $SpecialInstruction;
    public $ReferenceNumber;
    public $Notification;
    public $ConfirmationEmailAddress;
    public $UndeliverableEmailAddress;
    public $room;
    public $floor;
    public $urbanization;
    public $residential;
    public $pickup_point;
    /* END Pickup */

    /* Access Point */
    public $accesspoint = 0;
    public $accesspoint_type;
    public $accesspoint_name;
    public $accesspoint_atname;
    public $accesspoint_appuid;
    public $accesspoint_street;
    public $accesspoint_street1;
    public $accesspoint_street2;
    public $accesspoint_city;
    public $accesspoint_provincecode;
    public $accesspoint_postal;
    public $accesspoint_country;
    /* Access Point */

    

    public function setCredentials($access, $user, $pass, $shipper)
    {
        $this->AccessLicenseNumber = $access;
        $this->UserID = $user;
        $this->Password = $pass;
        $this->shipperNumber = $shipper;
        $this->credentials = 1;
        return $this->credentials;
    }

    function getShip()
    {
        if ($this->credentials != 1) {
            return array('error' => array('cod' => 1, 'message' => 'Not correct registration data'), 'success' => 0);
        }
        /* if(is_dir($filename)){} */
        $path_upsdir = Mage::getBaseDir('media') . DS . 'upslabel' . DS;
        if (!is_dir($path_upsdir)) {
            mkdir($path_upsdir, 0777);
            mkdir($path_upsdir . "label" . DS, 0777);
            mkdir($path_upsdir . "test_xml" . DS, 0777);
        }
        $path = Mage::getBaseDir('media') . DS . 'upslabel' . DS . "label" . DS;
        $path_xml = Mage::getBaseDir('media') . DS . 'upslabel' . DS . "test_xml" . DS;
        if (!file_exists($path_xml . ".htaccess")) {
            file_put_contents($path_xml . ".htaccess", "deny from all");
        }
        $this->customerContext = str_replace('&', '&amp;', strtolower(Mage::app()->getStore()->getName()));
        $data = "<?xml version=\"1.0\" ?>
<AccessRequest xml:lang='en-US'>
<AccessLicenseNumber>" . $this->AccessLicenseNumber . "</AccessLicenseNumber>
<UserId>" . $this->UserID . "</UserId>
<Password>" . $this->Password . "</Password>
</AccessRequest>
<?xml version=\"1.0\"?>
<ShipmentConfirmRequest xml:lang=\"en-US\">
  <Request>
    <TransactionReference>
      <CustomerContext>" . $this->customerContext . "</CustomerContext>
      <XpciVersion/>
    </TransactionReference>
    <RequestAction>ShipConfirm</RequestAction>
    <RequestOption>validate</RequestOption>
  </Request>
  <LabelSpecification>
    <LabelPrintMethod>
      <Code>" . Mage::getStoreConfig('upslabel/printing/printer') . "</Code>
    </LabelPrintMethod>
    ";
        if (Mage::getStoreConfig('upslabel/printing/printer') != "GIF") {
            $data .= "<LabelStockSize>
                <Height>4</Height>
                <Width>" . Mage::getStoreConfig('upslabel/printing/termal_width') . "</Width>
            </LabelStockSize>";
        }
        $data .= "
    <HTTPUserAgent>Mozilla/4.5</HTTPUserAgent>
    <LabelImageFormat>
      <Code>" . Mage::getStoreConfig('upslabel/printing/printer') . "</Code>
    </LabelImageFormat>
  </LabelSpecification>
  <Shipment>";
        if (Mage::getStoreConfig('upslabel/ratepayment/negotiatedratesindicator') == 1) {
            $data .= "
   <RateInformation>
      <NegotiatedRatesIndicator/>
    </RateInformation>";
        }
        if (strlen($this->shipmentDescription) > 0) {
            $data .= "<Description>" . $this->shipmentDescription . "</Description>";
        }
        $data .= "<Shipper>
<Name>" . $this->shipperName . "</Name>";
        $data .= "<AttentionName>" . $this->shipperAttentionName . "</AttentionName>";

        $data .= "<PhoneNumber>" . $this->shipperPhoneNumber . "</PhoneNumber>
      <ShipperNumber>" . $this->shipperNumber . "</ShipperNumber>
	  <TaxIdentificationNumber></TaxIdentificationNumber>
      <Address>
    	<AddressLine1>" . $this->shipperAddressLine1 . "</AddressLine1>
    	<City>" . $this->shipperCity . "</City>
    	<StateProvinceCode>" . $this->shipperStateProvinceCode . "</StateProvinceCode>
    	<PostalCode>" . $this->shipperPostalCode . "</PostalCode>
    	<PostcodeExtendedLow></PostcodeExtendedLow>
    	<CountryCode>" . $this->shipperCountryCode . "</CountryCode>
     </Address>
    </Shipper>
	<ShipTo>
     <CompanyName>" . $this->shiptoCompanyName . "</CompanyName>
      <AttentionName>" . $this->shiptoAttentionName . "</AttentionName>";
        if(strlen($this->shiptoPhoneNumber)>0){
            $data .= "<PhoneNumber>" . $this->shiptoPhoneNumber . "</PhoneNumber>";
        }
        else if($this->serviceCode == 14 || $this->shiptoCountryCode != $this->shipfromCountryCode){
            $data .= "<PhoneNumber>" . $this->shipfromPhoneNumber . "</PhoneNumber>";
        }
      $data .= "
      <Address>
        <AddressLine1>" . $this->shiptoAddressLine1 . "</AddressLine1>";
        if (strlen($this->shiptoAddressLine2) > 0) {
            $data .= '<AddressLine2>' . $this->shiptoAddressLine2 . '</AddressLine2>';
        }
        $data .= "<City>" . $this->shiptoCity . "</City>
        <StateProvinceCode>" . $this->shiptoStateProvinceCode . "</StateProvinceCode>
        <PostalCode>" . $this->shiptoPostalCode . "</PostalCode>
        <CountryCode>" . $this->shiptoCountryCode . "</CountryCode>
        " . $this->residentialAddress . "
      </Address>
    </ShipTo>
    <ShipFrom>
      <CompanyName>" . $this->shipfromCompanyName . "</CompanyName>
      <AttentionName>" . $this->shipfromAttentionName . "</AttentionName>
      <PhoneNumber>" . $this->shipfromPhoneNumber . "</PhoneNumber>
	  <TaxIdentificationNumber></TaxIdentificationNumber>
      <Address>
        <AddressLine1>" . $this->shipfromAddressLine1 . "</AddressLine1>
        <City>" . $this->shipfromCity . "</City>
    	<StateProvinceCode>" . $this->shipfromStateProvinceCode . "</StateProvinceCode>
    	<PostalCode>" . $this->shipfromPostalCode . "</PostalCode>
    	<CountryCode>" . $this->shipfromCountryCode . "</CountryCode>
      </Address>
    </ShipFrom>
    ";
        if ($this->shiptoCountryCode != $this->shipfromCountryCode) {
            $paymentTag = 'ItemizedPaymentInformation';
            $data .= "<" . $paymentTag . ">";
            if ($this->upsAccount != 1) {
                $data .= "<ShipmentCharge><Type>01</Type>
        <BillShipper>";
                if ($this->accesspoint == 1 && $this->accesspoint_type == '02') {
                    $data .= "<AlternatePaymentMethod>01</AlternatePaymentMethod>";
                } else {
                    $data .= "<AccountNumber>" . $this->shipperNumber . "</AccountNumber>";
                }
                $data .= "</BillShipper></ShipmentCharge>";
                $data .= "
                <ShipmentCharge>
                <Type>02</Type>
                  <BillShipper>
                    <AccountNumber>" . $this->shipperNumber . "</AccountNumber>
                  </BillShipper></ShipmentCharge>";
            } else {
                $data .= "<ShipmentCharge><BillThirdParty>
                    <BillThirdPartyShipper>
                        <AccountNumber>" . $this->accountData->getAccountnumber() . "</AccountNumber>
                        <ThirdParty>
                            <Address>
                                <PostalCode>" . $this->accountData->getPostalcode() . "</PostalCode>
                                <CountryCode>" . $this->accountData->getCountry() . "</CountryCode>
                            </Address>
                        </ThirdParty>
                    </BillThirdPartyShipper>
                </BillThirdParty></ShipmentCharge>";
            }
            $data .= "
                </" . $paymentTag . ">
            ";
        } else {
            $paymentTag = 'PaymentInformation';
            $data .= "<" . $paymentTag . ">";
            if ($this->upsAccount != 1) {
                $data .= "<Prepaid>
        <BillShipper>";
                if ($this->accesspoint == 1 && $this->accesspoint_type == '02') {
                    $data .= "<AlternatePaymentMethod>01</AlternatePaymentMethod>";
                } else {
                    $data .= "<AccountNumber>" . $this->shipperNumber . "</AccountNumber>";
                }
                $data .= "</BillShipper>
      </Prepaid>";
            } else {
                $data .= "<BillThirdParty>
                    <BillThirdPartyShipper>
                        <AccountNumber>" . $this->accountData->getAccountnumber() . "</AccountNumber>
                        <ThirdParty>
                            <Address>
                                <PostalCode>" . $this->accountData->getPostalcode() . "</PostalCode>
                                <CountryCode>" . $this->accountData->getCountry() . "</CountryCode>
                            </Address>
                        </ThirdParty>
                    </BillThirdPartyShipper>
                </BillThirdParty>";
            }
            $data .= "
                </" . $paymentTag . ">
            ";
        }
        $data .= "<Service>
      <Code>" . $this->serviceCode . "</Code>
      <Description>" . $this->serviceDescription . "</Description>
    </Service>";
        if ($this->shiptoCountryCode != $this->shipfromCountryCode || ($this->shiptoCountryCode == $this->shipfromCountryCode && $this->shiptoCountryCode != 'US' && $this->shiptoCountryCode != 'PR')) {
            $data .= "<ReferenceNumber>";
            if (Mage::getStoreConfig('upslabel/packaging/packagingreferencebarcode') == 1) {
                $data .= "<BarCodeIndicator></BarCodeIndicator>";
            }
            $data .= "<Code>" . $this->packages[0]['packagingreferencenumbercode'] . "</Code>
		<Value>" . $this->packages[0]['packagingreferencenumbervalue'] . "</Value>
	  </ReferenceNumber>";
            if (isset($this->packages[0]['packagingreferencenumbercode2'])) {
                $data .= "<ReferenceNumber>";
                if (Mage::getStoreConfig('upslabel/packaging/packagingreferencebarcode2') == 1) {
                    $data .= "<BarCodeIndicator></BarCodeIndicator>";
                }
                $data .= "<Code>" . $this->packages[0]['packagingreferencenumbercode2'] . "</Code>
		<Value>" . $this->packages[0]['packagingreferencenumbervalue2'] . "</Value>
	  </ReferenceNumber>";
            }
        }
        foreach ($this->packages AS $pv) {
            $data .= "<Package>
      <PackagingType>
        <Code>" . $pv["packagingtypecode"] . "</Code>
      </PackagingType>
      <Description>" . $pv["packagingdescription"] . "</Description>";
            if (($this->shiptoCountryCode == 'US' || $this->shiptoCountryCode == 'PR') && $this->shiptoCountryCode == $this->shipfromCountryCode) {
                $data .= "<ReferenceNumber>";
                if (Mage::getStoreConfig('upslabel/packaging/packagingreferencebarcode') == 1) {
                    $data .= "<BarCodeIndicator></BarCodeIndicator>";
                }
                $data .= "<Code>" . $pv['packagingreferencenumbercode'] . "</Code>
		<Value>" . $pv['packagingreferencenumbervalue'] . "</Value>
	  </ReferenceNumber>";
                if (isset($pv['packagingreferencenumbercode2'])) {
                    $data .= "<ReferenceNumber>";
                    $data .= "<Code>" . $pv['packagingreferencenumbercode2'] . "</Code>
		<Value>" . $pv['packagingreferencenumbervalue2'] . "</Value>
	  </ReferenceNumber>";
                }
            }
            $data .= array_key_exists('additionalhandling', $pv) ? $pv['additionalhandling'] : '';
            if ($this->includeDimensions == 1) {
                $data .= "<Dimensions>
<UnitOfMeasurement>
<Code>" . $this->unitOfMeasurement . "</Code>";
                if (strlen($this->unitOfMeasurementDescription) > 0) {
                    $data .= "
<Description>" . $this->unitOfMeasurementDescription . "</<Description>";
                }
                $data .= "</UnitOfMeasurement>";
                if ($pv['dimansion_id'] == 0) {
                    if (isset($pv['length']) && strlen($pv['length']) > 0) {
                        $data .= "<Length>" . $pv['length'] . "</Length>
<Width>" . $pv['width'] . "</Width>
<Height>" . $pv['height'] . "</Height>";
                    }
                } else {
                    $data .= "<Length>" . Mage::getStoreConfig('upslabel/dimansion_' . $pv['dimansion_id'] . '/length') . "</Length>
<Width>" . Mage::getStoreConfig('upslabel/dimansion_' . $pv['dimansion_id'] . '/width') . "</Width>
<Height>" . Mage::getStoreConfig('upslabel/dimansion_' . $pv['dimansion_id'] . '/height') . "</Height>";
                }
                $data .= "</Dimensions>";
            }
            $data .= "<PackageWeight>
        <UnitOfMeasurement>
            <Code>" . $this->weightUnits . "</Code>";
            if (strlen($this->weightUnitsDescription) > 0) {
                $data .= "
            <Description>" . $this->weightUnitsDescription . "</<Description>";
            }
            $packweight = array_key_exists('packweight', $pv) ? $pv['packweight'] : '';
            $weight = array_key_exists('weight', $pv) ? $pv['weight'] : '';
            $data .= "</UnitOfMeasurement>
        <Weight>" . round(($weight + (is_numeric(str_replace(',', '.', $packweight)) ? $packweight : 0)), 1) . "</Weight>" . (array_key_exists('large', $pv) ? $pv['large'] : '') . "
      </PackageWeight>
      <PackageServiceOptions>";
            if ($pv['insuredmonetaryvalue'] > 0) {
                $data .= "<InsuredValue>
                <CurrencyCode>" . $pv['currencycode'] . "</CurrencyCode>
                <MonetaryValue>" . $pv['insuredmonetaryvalue'] . "</MonetaryValue>
                </InsuredValue>
              ";
            }
            if ($pv['cod'] == 1 && ($this->shiptoCountryCode == 'US' || $this->shiptoCountryCode == 'PR' || $this->shiptoCountryCode == 'CA') && ($this->shipfromCountryCode == 'US' || $this->shipfromCountryCode == 'PR' || $this->shipfromCountryCode == 'CA')) {
                $data .= "
              <COD>
                  <CODCode>3</CODCode>
                  <CODFundsCode>" . $pv['codfundscode'] . "</CODFundsCode>
                  <CODAmount>
                      <CurrencyCod>" . $pv['currencycode'] . "</CurrencyCod>
                      <MonetaryValue>" . $pv['codmonetaryvalue'] . "</MonetaryValue>
                  </CODAmount>
              </COD>";
            }
            if (($this->shiptoCountryCode == 'US' || $this->shiptoCountryCode == 'PR' || $this->shiptoCountryCode == 'CA') && ($this->shipfromCountryCode == 'US' || $this->shipfromCountryCode == 'PR' || $this->shipfromCountryCode == 'CA')) {
                if ($this->adult > 0) {
                    $data .= "<DeliveryConfirmation><DCISType>" . $this->adult . "</DCISType></DeliveryConfirmation>";
                }
            }
            $data .= "</PackageServiceOptions>
              </Package>";
        }
        $data .= "<ShipmentServiceOptions>";
        if ($this->codYesNo == 1 && $this->shiptoCountryCode != 'US' && $this->shiptoCountryCode != 'PR' && $this->shiptoCountryCode != 'CA' && $this->shipfromCountryCode != 'US' && $this->shipfromCountryCode != 'PR' && $this->shipfromCountryCode != 'CA') {
            $data .= "<COD>
                  <CODCode>3</CODCode>
                  <CODFundsCode>" . $this->codFundsCode . "</CODFundsCode>
                  <CODAmount>
                      <CurrencyCode>" . $this->currencyCode . "</CurrencyCode>
                      <MonetaryValue>" . $this->codMonetaryValue . "</MonetaryValue>
                  </CODAmount>
              </COD>";

        }
        if ($this->shiptoCountryCode != 'US' && $this->shiptoCountryCode != 'PR' && $this->shiptoCountryCode != 'CA' && $this->shipfromCountryCode != 'US' && $this->shipfromCountryCode != 'PR' && $this->shipfromCountryCode != 'CA') {
            if ($this->adult > 0) {
                $data .= "<DeliveryConfirmation><DCISType>" . ($this->adult) . "</DCISType></DeliveryConfirmation>";
            }

        }
        if ($this->carbon_neutral == 1) {
            $data .= "<UPScarbonneutralIndicator/>";
        }
        if ($this->qvn == 1) {
            $email_undelivery = 0;
            foreach ($this->qvn_code AS $qvncode) {
                if ($qvncode != 2 && $qvncode != 5) {
                    $data .= "<Notification>
            <NotificationCode>" . $qvncode . "</NotificationCode>
            <EMailMessage>";
                    if (strlen($this->qvn_email_shipper) > 0) {
                        $data .= "<EMailAddress>" . $this->qvn_email_shipper . "</EMailAddress>";
                    }
                    if (strlen($this->qvn_email_shipto) > 0) {
                        $data .= "<EMailAddress>" . $this->qvn_email_shipto . "</EMailAddress>";
                    }
                    if (strlen($this->qvn_email_shipper) > 0 && $email_undelivery == 0) {
                        $data .= "<UndeliverableEMailAddress>" . $this->qvn_email_shipper . "</UndeliverableEMailAddress>";
                        $email_undelivery = 1;
                    }
                    $data .= "</EMailMessage>
            </Notification>";
                    if ($this->accesspoint == 1) {
                        break;
                    }
                }
            }
        }
        if ($this->accesspoint == 1) {
            $data .= "<Notification>
            <NotificationCode>012</NotificationCode>
            <EMailMessage>";
            if (strlen($this->qvn_email_shipper) > 0) {
                $data .= "<EMailAddress>" . $this->qvn_email_shipper . "</EMailAddress>";
            }
            if (strlen($this->qvn_email_shipto) > 0) {
                $data .= "<EMailAddress>" . $this->qvn_email_shipto . "</EMailAddress>";
            }
            $data .= "</EMailMessage>
<Locale>
                    <Language>ENG</Language>
                    <Dialect>GB</Dialect>
                </Locale>
            </Notification>";
            $data .= "<Notification>
            <NotificationCode>013</NotificationCode>
            <EMailMessage>";
            if (strlen($this->qvn_email_shipper) > 0) {
                $data .= "<EMailAddress>" . $this->qvn_email_shipper . "</EMailAddress>";
            }
            if (strlen($this->qvn_email_shipto) > 0) {
                $data .= "<EMailAddress>" . $this->qvn_email_shipto . "</EMailAddress>";
            }
            $data .= "</EMailMessage>
                <Locale>
                    <Language>ENG</Language>
                    <Dialect>GB</Dialect>
                </Locale>
            </Notification>";
        }
        $data .= $this->saturdayDelivery . "</ShipmentServiceOptions>";
        if (strlen($this->invoicelinetotal) > 0 && ($this->shiptoCountryCode == 'US' || $this->shiptoCountryCode == 'PR' || $this->shiptoCountryCode == 'CA') && ($this->shipfromCountryCode == 'US' || $this->shipfromCountryCode == 'PR' || $this->shipfromCountryCode == 'CA') && $this->shiptoCountryCode != $this->shipfromCountryCode) {
            $data .= "<InvoiceLineTotal>
                          <CurrencyCode>" . $this->currencyCode . "</CurrencyCode>
                          <MonetaryValue>" . $this->invoicelinetotal . "</MonetaryValue>
              </InvoiceLineTotal>";
        }
        if ($this->accesspoint == 1) {
            $data .= "<ShipmentIndicationType>
            <Code>" . $this->accesspoint_type . "</Code>
            </ShipmentIndicationType>
            <AlternateDeliveryAddress>
                <Name>" . $this->accesspoint_name . "</Name>
                <AttentionName>" . $this->accesspoint_atname . "</AttentionName>
                <Address>
                    <AddressLine1>" . $this->accesspoint_street . "</AddressLine1>";
            if ($this->accesspoint_street1 != "" && $this->accesspoint_street1 != "undefined") {
                $data .= "<AddressLine2>" . $this->accesspoint_street1 . "</AddressLine2>";
            }
            if ($this->accesspoint_street2 != "" && $this->accesspoint_street2 != "undefined") {
                $data .= "<AddressLine3>" . $this->accesspoint_street2 . "</AddressLine3>";
            }
            $data .= "<City>" . $this->accesspoint_city . "</City>";
            if ($this->shiptoCountryCode == "US" || $this->shiptoCountryCode == "CA") {
                $data .= "<StateProvinceCode>" . $this->accesspoint_provincecode . "</StateProvinceCode>";
            }
            $data .= "<PostalCode>" . $this->accesspoint_postal . "</PostalCode>
                    <CountryCode>" . $this->accesspoint_country . "</CountryCode>
                </Address>
            </AlternateDeliveryAddress>
            ";
            /*<UPSAccessPointID>".$this->accesspoint_appuid."</UPSAccessPointID>*/
        }
        $data .= "</Shipment>
</ShipmentConfirmRequest>
";

        file_put_contents($path_xml . "ShipConfirmRequest.xml", $data);

        $cie = 'wwwcie';
        if (0 == $this->testing) {
            $cie = 'onlinetools';
        }

        $curl = Mage::helper('upslabel/help');

        $result = $curl->curlSend('https://' . $cie . '.ups.com/ups.app/xml/ShipConfirm', $data);

        if (!$curl->error) {
            file_put_contents($path_xml . "ShipConfirmResponse.xml", $result);

            //return $result;
            $xml = simplexml_load_string($result);
            if ($xml->Response->ResponseStatusCode[0] == 1) {
                if ($xml->NegotiatedRates) {
                    $shiplabelprice = $xml->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue[0];
                    $shiplabelcurrency = $xml->NegotiatedRates->NetSummaryCharges->GrandTotal->CurrencyCode[0];
                } else {
                    $shiplabelprice = $xml->ShipmentCharges->TotalCharges->MonetaryValue[0];
                    $shiplabelcurrency = $xml->ShipmentCharges->TotalCharges->CurrencyCode[0];
                }
                $this->shipmentDigest = $xml->ShipmentDigest[0];
                $data = "<?xml version=\"1.0\" ?>
<AccessRequest xml:lang='en-US'>
<AccessLicenseNumber>" . $this->AccessLicenseNumber . "</AccessLicenseNumber>
<UserId>" . $this->UserID . "</UserId>
<Password>" . $this->Password . "</Password>
</AccessRequest>
<?xml version=\"1.0\" ?>
<ShipmentAcceptRequest>
<Request>
<TransactionReference>
<CustomerContext>" . $this->customerContext . "</CustomerContext>
<XpciVersion>1.0001</XpciVersion>
</TransactionReference>
<RequestAction>ShipAccept</RequestAction>
</Request>
<ShipmentDigest>" . $this->shipmentDigest . "</ShipmentDigest>
</ShipmentAcceptRequest>";
                file_put_contents($path_xml . "ShipAcceptRequest.xml", $data);

                $result = $curl->curlSend('https://' . $cie . '.ups.com/ups.app/xml/ShipAccept', $data);

                if (!$curl->error) {
                    file_put_contents($path_xml . "ShipAcceptResponse.xml", $result);
                    $xml = simplexml_load_string($result);
                    $this->shipmentIdentificationNumber = $xml->ShipmentResults[0]->ShipmentIdentificationNumber[0];
                    $arrResponsXML = array();
                    $i = 0;
                    foreach ($xml->ShipmentResults[0]->PackageResults AS $resultXML) {
                        $arrResponsXML[$i]['trackingnumber'] = $resultXML->TrackingNumber[0];
                        $arrResponsXML[$i]['graphicImage'] = base64_decode($resultXML->LabelImage[0]->GraphicImage[0]);
                        $arrResponsXML[$i]['type_print'] = $resultXML->LabelImage[0]->LabelImageFormat[0]->Code[0];
                        $file = fopen($path . 'label' . $arrResponsXML[$i]['trackingnumber'] . '.' . strtolower($arrResponsXML[$i]['type_print']), 'w');
                        fwrite($file, $arrResponsXML[$i]['graphicImage']);
                        fclose($file);
                        if ($arrResponsXML[$i]['type_print'] == "GIF") {
                            $arrResponsXML[$i]['htmlImage'] = base64_decode($resultXML->LabelImage[0]->HTMLImage[0]);
                            file_put_contents($path . $arrResponsXML[$i]['trackingnumber'] . ".html", $arrResponsXML[$i]['htmlImage']);
                            file_put_contents($path_xml . "HTML_image.html", $arrResponsXML[$i]['htmlImage']);
                        }
                        $i += 1;
                    }
                    if ($this->codMonetaryValue > 999) {
                        $htmlHVReport = '<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1252">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 11">
<meta name=Originator content="Microsoft Word 11">
<link rel=File-List href="sample%20UPS%20CONTROL%20LOG_files/filelist.xml">
<title>UPS CONTROL LOG </title>
<!--[if gte mso 9]><xml>
 <o:DocumentProperties>
  <o:Author>xlm8zff</o:Author>
  <o:LastAuthor>xlm8zff</o:LastAuthor>
  <o:Revision>2</o:Revision>
  <o:TotalTime>2</o:TotalTime>
  <o:Created>2010-09-27T12:53:00Z</o:Created>
  <o:LastSaved>2010-09-27T12:53:00Z</o:LastSaved>
  <o:Pages>1</o:Pages>
  <o:Words>116</o:Words>
  <o:Characters>662</o:Characters>
  <o:Company>UPS</o:Company>
  <o:Lines>5</o:Lines>
  <o:Paragraphs>1</o:Paragraphs>
  <o:CharactersWithSpaces>777</o:CharactersWithSpaces>
  <o:Version>11.9999</o:Version>
 </o:DocumentProperties>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:SpellingState>Clean</w:SpellingState>
  <w:GrammarState>Clean</w:GrammarState>
  <w:PunctuationKerning/>
  <w:ValidateAgainstSchemas/>
  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
  <w:Compatibility>
   <w:BreakWrappedTables/>
   <w:SnapToGridInCell/>
   <w:WrapTextWithPunct/>
   <w:UseAsianBreakRules/>
   <w:DontGrowAutofit/>
  </w:Compatibility>
  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
 </w:WordDocument>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:LatentStyles DefLockedState="false" LatentStyleCount="156">
 </w:LatentStyles>
</xml><![endif]-->
<style>
<!--
 /* Style Definitions */
 p.MsoNormal, li.MsoNormal, div.MsoNormal
	{mso-style-parent:"";
	margin:0in;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:10.0pt;
	mso-bidi-font-size:12.0pt;
	font-family:Arial;
	mso-fareast-font-family:"Times New Roman";}
span.GramE
	{mso-style-name:"";
	mso-gram-e:yes;}
@page Section1
	{size:8.5in 11.0in;
	margin:1.0in 1.25in 1.0in 1.25in;
	mso-header-margin:.5in;
	mso-footer-margin:.5in;
	mso-paper-source:0;}
div.Section1
	{page:Section1;}
-->
</style>
<!--[if gte mso 10]>
<style>
 /* Style Definitions */
 table.MsoNormalTable
	{mso-style-name:"Table Normal";
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	mso-style-noshow:yes;
	mso-style-parent:"";
	mso-padding-alt:0in 5.4pt 0in 5.4pt;
	mso-para-margin:0in;
	mso-para-margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:10.0pt;
	font-family:"Times New Roman";
	mso-ansi-language:#0400;
	mso-fareast-language:#0400;
	mso-bidi-language:#0400;}
</style>
<![endif]-->
</head>
<body lang=EN-US style=\'tab-interval:.5in\'>

<div class=Section1>

<p class=MsoNormal>UPS CONTROL <span class=GramE>LOG</span></p>

<p class=MsoNormal>DATE: ' . date('d') . ' ' . date('M') . ' ' . date('Y') . ' UPS SHIPPER NO. ' . $this->shipperNumber . ' </p>
<br />
<br />
<p class=MsoNormal>TRACKING # PACKAGE ID REFRENCE NUMBER DECLARED VALUE
CURRENCY </p>
<p class=MsoNormal>--------------------------------------------------------------------------------------------------------------------------
</p>
<br /><br />
<p class=MsoNormal>' . $this->trackingNumber . ' <span class=GramE>' . $this->packages[0]['packagingreferencenumbervalue'] . ' ' . round($this->codMonetaryValue, 2) . '</span> ' . Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol() . ' </p>
<br /><br />
<p class=MsoNormal>Total Number of Declared Value Packages = 1 </p>
<p class=MsoNormal>--------------------------------------------------------------------------------------------------------------------------
</p>
<br /><br />
<p class=MsoNormal>RECEIVED BY_________________________PICKUP
TIME__________________PKGS_______ </p>
</div>
</body>
</html>';
                        file_put_contents($path . "HVR" . $this->shipmentIdentificationNumber . ".html", $htmlHVReport);
                    }
                    return array(
                        'arrResponsXML' => $arrResponsXML,
                        'digest' => '' . $this->shipmentDigest . '',
                        'shipidnumber' => '' . $this->shipmentIdentificationNumber . '',
                        'price' => array('currency' => $shiplabelcurrency, 'price' => $shiplabelprice),
                    );
                } else {
                    return $result;
                }
            } else {
                $error = '<h1>Error</h1> <ul>';
                $errorss = $xml->Response->Error[0];
                $error .= '<li>Error Severity : ' . $errorss->ErrorSeverity . '</li>';
                $error .= '<li>Error Code : ' . $errorss->ErrorCode . '</li>';
                $error .= '<li>Error Description : ' . $errorss->ErrorDescription . '</li>';
                $error .= '</ul>';
                $error .= '<textarea>' . $result . '</textarea>';
                $error .= '<textarea>' . $data . '</textarea>';
                return array('errordesc' => $errorss->ErrorDescription, 'error' => $error);
            }
        } else {
            return $result;
        }
    }

    function getShipFrom()
    {
        if ($this->credentials != 1) {
            return array('error' => array('cod' => 1, 'message' => 'Not correct registration data'), 'success' => 0);
        }
        /* if(is_dir($filename)){} */
        $path_upsdir = Mage::getBaseDir('media') . DS . 'upslabel' . DS;
        if (!is_dir($path_upsdir)) {
            mkdir($path_upsdir, 0777);
            mkdir($path_upsdir . "label" . DS, 0777);
            mkdir($path_upsdir . "test_xml" . DS, 0777);
        }
        $path = Mage::getBaseDir('media') . DS . 'upslabel' . DS . "label" . DS;
        $path_xml = Mage::getBaseDir('media') . DS . 'upslabel' . DS . "test_xml" . DS;
        if (!file_exists($path_xml . ".htaccess")) {
            file_put_contents($path_xml . ".htaccess", "deny from all");
        }
        $this->customerContext = str_replace('&', '&amp;', strtolower(Mage::app()->getStore()->getName()));
        $data = "<?xml version=\"1.0\" ?>
        <AccessRequest xml:lang='en-US'>
        <AccessLicenseNumber>" . $this->AccessLicenseNumber . "</AccessLicenseNumber>
        <UserId>" . $this->UserID . "</UserId>
        <Password>" . $this->Password . "</Password>
        </AccessRequest>
        <?xml version=\"1.0\"?>
        <ShipmentConfirmRequest xml:lang=\"en-US\">
          <Request>
            <TransactionReference>
              <CustomerContext>" . $this->customerContext . "</CustomerContext>
              <XpciVersion/>
            </TransactionReference>
            <RequestAction>ShipConfirm</RequestAction>
            <RequestOption>validate</RequestOption>
          </Request>
          <LabelSpecification>
            <LabelPrintMethod>
              <Code>GIF</Code>
              <Description>gif file</Description>
            </LabelPrintMethod>
            <HTTPUserAgent>Mozilla/4.5</HTTPUserAgent>
            <LabelImageFormat>
              <Code>GIF</Code>
              <Description>gif</Description>
            </LabelImageFormat>
          </LabelSpecification>
          <Shipment>";
        if (Mage::getStoreConfig('upslabel/ratepayment/negotiatedratesindicator') == 1) {
            $data .= "<RateInformation>
      <NegotiatedRatesIndicator/>
    </RateInformation>";
        }
        $data .= "<ShipmentServiceOptions>
                    <LabelDelivery>
                        <LabelLinksIndicator />
                    </LabelDelivery>";
        if ($this->carbon_neutral == 1) {
            $data .= "<UPScarbonneutralIndicator/>";
        }
        if ($this->qvn == 1) {
            $email_undelivery = 0;
            foreach ($this->qvn_code AS $qvncode) {
                if ($qvncode == 2 || $qvncode == 5) {
                    $data .= "<Notification>
            <NotificationCode>" . $qvncode . "</NotificationCode>
            <EMailMessage>";
                    if (strlen($this->qvn_email_shipper) > 0) {
                        $data .= "<EMailAddress>" . $this->qvn_email_shipper . "</EMailAddress>";
                    }
                    if (strlen($this->qvn_email_shipto) > 0) {
                        $data .= "<EMailAddress>" . $this->qvn_email_shipto . "</EMailAddress>";
                    }
                    if (strlen($this->qvn_email_shipper) > 0 && $email_undelivery == 0) {
                        $data .= "<UndeliverableEMailAddress>" . $this->qvn_email_shipper . "</UndeliverableEMailAddress>";
                        $email_undelivery = 1;
                    }
                    $data .= "</EMailMessage>
            </Notification>";
                }
            }
        }
        $data .= $this->saturdayDelivery . "</ShipmentServiceOptions>";
        $data .= "<ReturnService><Code>8</Code></ReturnService>";
        if (strlen($this->shipmentDescription) > 0) {
            $data .= "<Description>" . $this->shipmentDescription . "</Description>";
        }
        $data .= "<Shipper>
        <Name>" . $this->shipperName . "</Name>";
        $data .= "<AttentionName>" . $this->shipperAttentionName . "</AttentionName>";

        $data .= "<PhoneNumber>" . $this->shipperPhoneNumber . "</PhoneNumber>
              <ShipperNumber>" . $this->shipperNumber . "</ShipperNumber>
        	  <TaxIdentificationNumber></TaxIdentificationNumber>
              <Address>
            	<AddressLine1>" . $this->shipperAddressLine1 . "</AddressLine1>
            	<City>" . $this->shipperCity . "</City>
            	<StateProvinceCode>" . $this->shipperStateProvinceCode . "</StateProvinceCode>
            	<PostalCode>" . $this->shipperPostalCode . "</PostalCode>
            	<PostcodeExtendedLow></PostcodeExtendedLow>
            	<CountryCode>" . $this->shipperCountryCode . "</CountryCode>
             </Address>
            </Shipper>
        	<ShipFrom>
             <CompanyName>" . $this->shiptoCompanyName . "</CompanyName>
              <AttentionName>" . $this->shiptoAttentionName . "</AttentionName>";
              if(strlen($this->shiptoPhoneNumber)>0){
                    $data .= "<PhoneNumber>" . $this->shiptoPhoneNumber . "</PhoneNumber>";
                }
                else if($this->serviceCode == 14 || $this->shiptoCountryCode != $this->shipfromCountryCode){
                    $data .= "<PhoneNumber>" . $this->shipfromPhoneNumber . "</PhoneNumber>";
                }
              $data .= "<TaxIdentificationNumber></TaxIdentificationNumber>
              <Address>
                <AddressLine1>" . $this->shiptoAddressLine1 . "</AddressLine1>
                <City>" . $this->shiptoCity . "</City>
                <StateProvinceCode>" . $this->shiptoStateProvinceCode . "</StateProvinceCode>
                <PostalCode>" . $this->shiptoPostalCode . "</PostalCode>
                <CountryCode>" . $this->shiptoCountryCode . "</CountryCode>
              </Address>
            </ShipFrom>
            <ShipTo>
              <CompanyName>" . $this->shipfromCompanyName . "</CompanyName>
              <AttentionName>" . $this->shipfromAttentionName . "</AttentionName>
              <PhoneNumber>" . $this->shipfromPhoneNumber . "</PhoneNumber>
              <Address>
                <AddressLine1>" . $this->shipfromAddressLine1 . "</AddressLine1>";
        if (strlen($this->shipfromAddressLine2) > 0) {
            $data .= '<AddressLine2>' . $this->shipfromAddressLine2 . '</AddressLine2>';
        }
        $data .= "
                <City>" . $this->shipfromCity . "</City>
            	<StateProvinceCode>" . $this->shipfromStateProvinceCode . "</StateProvinceCode>
            	<PostalCode>" . $this->shipfromPostalCode . "</PostalCode>
            	<CountryCode>" . $this->shipfromCountryCode . "</CountryCode>
              </Address>
            </ShipTo>
             <PaymentInformation>
              <Prepaid>
                <BillShipper>
                  <AccountNumber>" . $this->shipperNumber . "</AccountNumber>
                </BillShipper>
              </Prepaid>
            </PaymentInformation>
            <Service>
              <Code>" . $this->serviceCode . "</Code>
              <Description>" . $this->serviceDescription . "</Description>
            </Service>";
        if ($this->shiptoCountryCode != $this->shipfromCountryCode || ($this->shiptoCountryCode == $this->shipfromCountryCode && $this->shiptoCountryCode != 'US' && $this->shiptoCountryCode != 'PR')) {
            $data .= "<ReferenceNumber>
	  	<Code>" . $this->packages[0]['packagingreferencenumbercode'] . "</Code>
		<Value>" . $this->packages[0]['packagingreferencenumbervalue'] . "</Value>
	  </ReferenceNumber>";
            if (isset($this->packages[0]['packagingreferencenumbercode2'])) {
                $data .= "<ReferenceNumber>
	  	<Code>" . $this->packages[0]['packagingreferencenumbercode2'] . "</Code>
		<Value>" . $this->packages[0]['packagingreferencenumbervalue2'] . "</Value>
	  </ReferenceNumber>";
            }
        }
        $ttWeight = 0;
        foreach ($this->packages AS $pv) {
            $ttWeight += $pv['weight'];
        }
        foreach ($this->packages AS $pv) {
            $data .= "<Package>
      <PackagingType>
        <Code>" . $pv["packagingtypecode"] . "</Code>
      </PackagingType>
      <Description>" . $pv["packagingdescription"] . "</Description>";
            if (($this->shiptoCountryCode == 'US' || $this->shiptoCountryCode == 'PR') && $this->shiptoCountryCode == $this->shipfromCountryCode) {
                $data .= "<ReferenceNumber>
	  	<Code>" . $pv['packagingreferencenumbercode'] . "</Code>
		<Value>" . $pv['packagingreferencenumbervalue'] . "</Value>
	  </ReferenceNumber>";
                if (isset($pv['packagingreferencenumbercode2'])) {
                    $data .= "<ReferenceNumber>
	  	<Code>" . $pv['packagingreferencenumbercode2'] . "</Code>
		<Value>" . $pv['packagingreferencenumbervalue2'] . "</Value>
	  </ReferenceNumber>";
                }
            }
            $data .= array_key_exists('additionalhandling', $pv) ? $pv['additionalhandling'] : '';
            if ($this->includeDimensions == 1) {
                $data .= "<Dimensions>
<UnitOfMeasurement>
<Code>" . $this->unitOfMeasurement . "</Code>";
                if (strlen($this->unitOfMeasurementDescription) > 0) {
                    $data .= "
<Description>" . $this->unitOfMeasurementDescription . "</<Description>";
                }
                $data .= "</UnitOfMeasurement>";
                if ($pv['dimansion_id'] == 0) {
                    if(isset($pv['length']) && strlen($pv['length'])>0){
                    $data .= "<Length>" . $pv['length'] . "</Length>
<Width>" . $pv['width'] . "</Width>
<Height>" . $pv['height'] . "</Height>";
                    }
                } else {
                    $data .= "<Length>" . Mage::getStoreConfig('upslabel/dimansion_' . $pv['dimansion_id'] . '/length') . "</Length>
<Width>" . Mage::getStoreConfig('upslabel/dimansion_' . $pv['dimansion_id'] . '/width') . "</Width>
<Height>" . Mage::getStoreConfig('upslabel/dimansion_' . $pv['dimansion_id'] . '/height') . "</Height>";
                }
                $data .= "</Dimensions>";
            }
            $data .= "<PackageWeight>
        <UnitOfMeasurement>
            <Code>" . $this->weightUnits . "</Code>";
            if (strlen($this->weightUnitsDescription) > 0) {
                $data .= "
            <Description>" . $this->weightUnitsDescription . "</<Description>";
            }
            $packweight = array_key_exists('packweight', $pv) ? $pv['packweight'] : '';
            $data .= "</UnitOfMeasurement>
        <Weight>" . round(($ttWeight + (is_numeric(str_replace(',', '.', $packweight)) ? $packweight : 0)), 1) . "</Weight>" . (array_key_exists('large', $pv) ? $pv['large'] : '') . "
      </PackageWeight>
      <PackageServiceOptions>";
            if ($pv['insuredmonetaryvalue'] > 0) {
                $data .= "<InsuredValue>
                <CurrencyCode>" . $pv['currencycode'] . "</CurrencyCode>
                <MonetaryValue>" . $pv['insuredmonetaryvalue'] . "</MonetaryValue>
                </InsuredValue>
              ";
            }
            $data .= "</PackageServiceOptions>
              </Package>";
            break;
        }
        $data .= "
          </Shipment>
        </ShipmentConfirmRequest>
        ";

        file_put_contents($path_xml . "ShipConfirmRequest.xml", $data);

        $cie = 'wwwcie';
        if (0 == $this->testing) {
            $cie = 'onlinetools';
        }

        $curl = Mage::helper('upslabel/help');

        $result = $curl->curlSend('https://' . $cie . '.ups.com/ups.app/xml/ShipConfirm', $data);

        if (!$curl->error) {
            file_put_contents($path_xml . "ShipConfirmResponse.xml", $result);
        } else {
            return $result;
        }
        //return $result;
        $xml = simplexml_load_string($result);
        if ($xml->Response->ResponseStatusCode[0] == 1) {
            if ($xml->NegotiatedRates) {
                $shiplabelprice = $xml->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue[0];
                $shiplabelcurrency = $xml->NegotiatedRates->NetSummaryCharges->GrandTotal->CurrencyCode[0];
            } else {
                $shiplabelprice = $xml->ShipmentCharges->TotalCharges->MonetaryValue[0];
                $shiplabelcurrency = $xml->ShipmentCharges->TotalCharges->CurrencyCode[0];
            }
            $this->shipmentDigest = $xml->ShipmentDigest[0];
            $data = "<?xml version=\"1.0\" ?>
        <AccessRequest xml:lang='en-US'>
        <AccessLicenseNumber>" . $this->AccessLicenseNumber . "</AccessLicenseNumber>
        <UserId>" . $this->UserID . "</UserId>
        <Password>" . $this->Password . "</Password>
        </AccessRequest>
        <?xml version=\"1.0\" ?>
        <ShipmentAcceptRequest>
        <Request>
        <TransactionReference>
        <CustomerContext>" . $this->customerContext . "</CustomerContext>
        <XpciVersion>1.0001</XpciVersion>
        </TransactionReference>
        <RequestAction>ShipAccept</RequestAction>
        </Request>
        <ShipmentDigest>" . $this->shipmentDigest . "</ShipmentDigest>
        </ShipmentAcceptRequest>";

            file_put_contents($path_xml . "ShipAcceptRequest.xml", $data);

            $result = $curl->curlSend('https://' . $cie . '.ups.com/ups.app/xml/ShipAccept', $data);

            if (!$curl->error) {
                file_put_contents($path_xml . "ShipAcceptResponse.xml", $result);
            } else {
                return $result;
            }
            if (0 == $this->testing) {
                $cie = 'www';
            }
            $xml = simplexml_load_string($result);
            $this->shipmentIdentificationNumber = $xml->ShipmentResults[0]->ShipmentIdentificationNumber[0];
            $i = 0;
            foreach ($xml->ShipmentResults[0]->PackageResults AS $resultXML) {
                $arrResponsXML[$i]['trackingnumber'] = $resultXML->TrackingNumber[0];
                $arrResponsXML[$i]['type_print'] = "GIF";/*$resultXML->LabelImage[0]->LabelImageFormat[0]->Code[0];*/
                $htmlUrlUPS = 'https://' . $cie . '.ups.com';

                $c = $curl->curlSend($xml->ShipmentResults[$i]->LabelURL[0], "");

                if (!$curl->error && strlen($c) > 100) {
                    $imgName = preg_replace('/.*?FOLD\sHERE.*?<img\s*?src="(.+?)".*/is', '$1', $c);
                    $c = preg_replace('/<img\s*?src="/is', '<img src="' . $htmlUrlUPS, $c);
                    $this->htmlImage = $c; /*base64_decode($xml->ShipmentResults[0]->PackageResults[0]->LabelImage[0]->HTMLImage[0]);*/
                    /*if ($arrResponsXML[$i]['type_print'] == "GIF") {*/
                        file_put_contents($path . $arrResponsXML[$i]['trackingnumber'] . ".html", $this->htmlImage);
                        file_put_contents($path_xml . "HTML_image.html", $this->htmlImage);

                        $c = $curl->curlSend("https://" . $cie . ".ups.com" . $imgName, "");
                        /*$this->graphicImage = file_get_contents("https://".$cie.".ups.com/u.a/L.class?7IMAGE=".$this->trackingNumber."");*/
                        //echo $this->graphicImage;
                        if (!$curl->error) {
                            $file = fopen($path . 'label' . $arrResponsXML[$i]['trackingnumber'] . '.gif', 'w');
                            fwrite($file, $c);
                            fclose($file);
                        }
                   /* }*/
                }
                $i += 1;
            }

            if ($this->codMonetaryValue > 999) {
                $htmlHVReport = '<html xmlns:o="urn:schemas-microsoft-com:office:office"
        xmlns:w="urn:schemas-microsoft-com:office:word"
        xmlns="http://www.w3.org/TR/REC-html40">

        <head>
        <meta http-equiv=Content-Type content="text/html; charset=windows-1252">
        <meta name=ProgId content=Word.Document>
        <meta name=Generator content="Microsoft Word 11">
        <meta name=Originator content="Microsoft Word 11">
        <link rel=File-List href="sample%20UPS%20CONTROL%20LOG_files/filelist.xml">
        <title>UPS CONTROL LOG </title>
        <!--[if gte mso 9]><xml>
         <o:DocumentProperties>
          <o:Author>xlm8zff</o:Author>
          <o:LastAuthor>xlm8zff</o:LastAuthor>
          <o:Revision>2</o:Revision>
          <o:TotalTime>2</o:TotalTime>
          <o:Created>2010-09-27T12:53:00Z</o:Created>
          <o:LastSaved>2010-09-27T12:53:00Z</o:LastSaved>
          <o:Pages>1</o:Pages>
          <o:Words>116</o:Words>
          <o:Characters>662</o:Characters>
          <o:Company>UPS</o:Company>
          <o:Lines>5</o:Lines>
          <o:Paragraphs>1</o:Paragraphs>
          <o:CharactersWithSpaces>777</o:CharactersWithSpaces>
          <o:Version>11.9999</o:Version>
         </o:DocumentProperties>
        </xml><![endif]--><!--[if gte mso 9]><xml>
         <w:WordDocument>
          <w:SpellingState>Clean</w:SpellingState>
          <w:GrammarState>Clean</w:GrammarState>
          <w:PunctuationKerning/>
          <w:ValidateAgainstSchemas/>
          <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
          <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
          <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
          <w:Compatibility>
           <w:BreakWrappedTables/>
           <w:SnapToGridInCell/>
           <w:WrapTextWithPunct/>
           <w:UseAsianBreakRules/>
           <w:DontGrowAutofit/>
          </w:Compatibility>
          <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
         </w:WordDocument>
        </xml><![endif]--><!--[if gte mso 9]><xml>
         <w:LatentStyles DefLockedState="false" LatentStyleCount="156">
         </w:LatentStyles>
        </xml><![endif]-->
        <style>
        <!--
         /* Style Definitions */
         p.MsoNormal, li.MsoNormal, div.MsoNormal
        	{mso-style-parent:"";
        	margin:0in;
        	margin-bottom:.0001pt;
        	mso-pagination:widow-orphan;
        	font-size:10.0pt;
        	mso-bidi-font-size:12.0pt;
        	font-family:Arial;
        	mso-fareast-font-family:"Times New Roman";}
        span.GramE
        	{mso-style-name:"";
        	mso-gram-e:yes;}
        @page Section1
        	{size:8.5in 11.0in;
        	margin:1.0in 1.25in 1.0in 1.25in;
        	mso-header-margin:.5in;
        	mso-footer-margin:.5in;
        	mso-paper-source:0;}
        div.Section1
        	{page:Section1;}
        -->
        </style>
        <!--[if gte mso 10]>
        <style>
         /* Style Definitions */
         table.MsoNormalTable
        	{mso-style-name:"Table Normal";
        	mso-tstyle-rowband-size:0;
        	mso-tstyle-colband-size:0;
        	mso-style-noshow:yes;
        	mso-style-parent:"";
        	mso-padding-alt:0in 5.4pt 0in 5.4pt;
        	mso-para-margin:0in;
        	mso-para-margin-bottom:.0001pt;
        	mso-pagination:widow-orphan;
        	font-size:10.0pt;
        	font-family:"Times New Roman";
        	mso-ansi-language:#0400;
        	mso-fareast-language:#0400;
        	mso-bidi-language:#0400;}
        </style>
        <![endif]-->
        </head>
        <body lang=EN-US style=\'tab-interval:.5in\'>

        <div class=Section1>

        <p class=MsoNormal>UPS CONTROL <span class=GramE>LOG</span></p>

        <p class=MsoNormal>DATE: ' . date('d') . ' ' . date('M') . ' ' . date('Y') . ' UPS SHIPPER NO. ' . $this->shipperNumber . ' </p>
        <br />
        <br />
        <p class=MsoNormal>TRACKING # PACKAGE ID REFRENCE NUMBER DECLARED VALUE
        CURRENCY </p>
        <p class=MsoNormal>--------------------------------------------------------------------------------------------------------------------------
        </p>
        <br /><br />
        <p class=MsoNormal>' . $this->trackingNumber . ' <span class=GramE>' . $this->packages[0]['packagingreferencenumbervalue'] . ' ' . round($this->codMonetaryValue, 2) . '</span> ' . Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol() . ' </p>
        <br /><br />
        <p class=MsoNormal>Total Number of Declared Value Packages = 1 </p>
        <p class=MsoNormal>--------------------------------------------------------------------------------------------------------------------------
        </p>
        <br /><br />
        <p class=MsoNormal>RECEIVED BY_________________________PICKUP
        TIME__________________PKGS_______ </p>
        </div>
        </body>
        </html>';
                file_put_contents($path . "HVR" . $this->shipmentIdentificationNumber . ".html", $htmlHVReport);
            }
            return array(
                'arrResponsXML' => $arrResponsXML,
                'digest' => '' . $this->shipmentDigest . '',
                'shipidnumber' => '' . $this->shipmentIdentificationNumber . '',
                'price' => array('currency' => $shiplabelcurrency, 'price' => $shiplabelprice),
            );
        } else {
            $error = '<h1>Error</h1> <ul>';
            $errorss = $xml->Response->Error[0];
            $error .= '<li>Error Severity : ' . $errorss->ErrorSeverity . '</li>';
            $error .= '<li>Error Code : ' . $errorss->ErrorCode . '</li>';
            $error .= '<li>Error Description : ' . $errorss->ErrorDescription . '</li>';
            $error .= '</ul>';
            $error .= '<textarea>' . $result . '</textarea>';
            $error .= '<textarea>' . $data . '</textarea>';
            return array('errordesc' => $errorss->ErrorDescription, 'error' => $error);
            //return print_r($xml->Response->Error);
        }
    }

    function getShipPrice()
    {
        if ($this->credentials != 1) {
            return array('error' => array('cod' => 1, 'message' => 'Not correct registration data'), 'success' => 0);
        }
        $this->customerContext = str_replace('&', '&amp;', strtolower(Mage::app()->getStore()->getName()));
        $data = "<?xml version=\"1.0\" ?>
<AccessRequest xml:lang='en-US'>
<AccessLicenseNumber>" . $this->AccessLicenseNumber . "</AccessLicenseNumber>
<UserId>" . $this->UserID . "</UserId>
<Password>" . $this->Password . "</Password>
</AccessRequest>
<?xml version=\"1.0\"?>
<RatingServiceSelectionRequest xml:lang=\"en-US\">
  <Request>
    <TransactionReference>
      <CustomerContext>" . $this->customerContext . "</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
    <RequestAction>Rate</RequestAction>
    <RequestOption>Rate</RequestOption>
  </Request>
  <Shipment>";
        if (Mage::getStoreConfig('upslabel/ratepayment/negotiatedratesindicator') == 1) {
            $data .= "
   <RateInformation>
      <NegotiatedRatesIndicator/>
    </RateInformation>";
        }
        if (strlen($this->shipmentDescription) > 0) {
            $data .= "<Description>" . $this->shipmentDescription . "</Description>";
        }
        $data .= "<Shipper>
<Name>" . $this->shipperName . "</Name>";
        $data .= "<AttentionName>" . $this->shipperAttentionName . "</AttentionName>";

        $data .= "<PhoneNumber>" . $this->shipperPhoneNumber . "</PhoneNumber>
      <ShipperNumber>" . $this->shipperNumber . "</ShipperNumber>
	  <TaxIdentificationNumber></TaxIdentificationNumber>
      <Address>
    	<AddressLine1>" . $this->shipperAddressLine1 . "</AddressLine1>
    	<City>" . $this->shipperCity . "</City>
    	<StateProvinceCode>" . $this->shipperStateProvinceCode . "</StateProvinceCode>
    	<PostalCode>" . $this->shipperPostalCode . "</PostalCode>
    	<PostcodeExtendedLow></PostcodeExtendedLow>
    	<CountryCode>" . $this->shipperCountryCode . "</CountryCode>
     </Address>
    </Shipper>
	<ShipTo>
     <CompanyName>" . $this->shiptoCompanyName . "</CompanyName>
      <AttentionName>" . $this->shiptoAttentionName . "</AttentionName>
      <PhoneNumber>" . $this->shiptoPhoneNumber . "</PhoneNumber>
      <Address>
        <AddressLine1>" . $this->shiptoAddressLine1 . "</AddressLine1>";
        if (strlen($this->shiptoAddressLine2) > 0) {
            $data .= '<AddressLine2>' . $this->shiptoAddressLine2 . '</AddressLine2>';
        }
        $data .= "<City>" . $this->shiptoCity . "</City>
        <StateProvinceCode>" . $this->shiptoStateProvinceCode . "</StateProvinceCode>
        <PostalCode>" . $this->shiptoPostalCode . "</PostalCode>
        <CountryCode>" . $this->shiptoCountryCode . "</CountryCode>
        " . $this->residentialAddress . "
      </Address>
    </ShipTo>
    <ShipFrom>
      <CompanyName>" . $this->shipfromCompanyName . "</CompanyName>
      <AttentionName>" . $this->shipfromAttentionName . "</AttentionName>
      <PhoneNumber>" . $this->shipfromPhoneNumber . "</PhoneNumber>
	  <TaxIdentificationNumber></TaxIdentificationNumber>
      <Address>
        <AddressLine1>" . $this->shipfromAddressLine1 . "</AddressLine1>
        <City>" . $this->shipfromCity . "</City>
    	<StateProvinceCode>" . $this->shipfromStateProvinceCode . "</StateProvinceCode>
    	<PostalCode>" . $this->shipfromPostalCode . "</PostalCode>
    	<CountryCode>" . $this->shipfromCountryCode . "</CountryCode>
      </Address>
    </ShipFrom>";
        if ($this->shipmentcharge == 1) {
            $data .= "<ItemizedPaymentInformation>
            <ShipmentCharge>
      <Type>01</Type>
        <BillShipper>
          <AccountNumber>" . $this->shipperNumber . "</AccountNumber>
        </BillShipper>
      </ShipmentCharge>
      <ShipmentCharge>
      <Type>02</Type>
        <BillShipper>
          <AccountNumber>" . $this->shipperNumber . "</AccountNumber>
        </BillShipper>
      </ShipmentCharge>
    </ItemizedPaymentInformation>
    ";
        } else {
            $data .= "<PaymentInformation>
      <Prepaid>
        <BillShipper>
          <AccountNumber>" . $this->shipperNumber . "</AccountNumber>
        </BillShipper>
      </Prepaid>
    </PaymentInformation>
    ";
        }
        $data .= "<Service>
      <Code>" . $this->serviceCode . "</Code>
      <Description>" . $this->serviceDescription . "</Description>
    </Service>";
        foreach ($this->packages AS $pv) {
            $data .= "<Package>
      <PackagingType>
        <Code>" . $pv["packagingtypecode"] . "</Code>
      </PackagingType>
      <Description>" . $pv["packagingdescription"] . "</Description>";
            if (($this->shiptoCountryCode == 'US' || $this->shiptoCountryCode == 'PR') && $this->shiptoCountryCode == $this->shipfromCountryCode) {
                $data .= "<ReferenceNumber>
	  	<Code>" . $pv['packagingreferencenumbercode'] . "</Code>
		<Value>" . $pv['packagingreferencenumbervalue'] . "</Value>
	  </ReferenceNumber>";
                if (isset($pv['packagingreferencenumbercode2'])) {
                    $data .= "<ReferenceNumber>
	  	<Code>" . $pv['packagingreferencenumbercode2'] . "</Code>
		<Value>" . $pv['packagingreferencenumbervalue2'] . "</Value>
	  </ReferenceNumber>";
                }
            }
            $data .= array_key_exists('additionalhandling', $pv) ? $pv['additionalhandling'] : '';
            if ($this->includeDimensions == 1) {
                $data .= "<Dimensions>
<UnitOfMeasurement>
<Code>" . $this->unitOfMeasurement . "</Code>";
                if (strlen($this->unitOfMeasurementDescription) > 0) {
                    $data .= "
<Description>" . $this->unitOfMeasurementDescription . "</<Description>";
                }
                $data .= "</UnitOfMeasurement>";
                if ($pv['dimansion_id'] == 0) {
                    if(isset($pv['length']) && strlen($pv['length'])>0){
                    $data .= "<Length>" . $pv['length'] . "</Length>
<Width>" . $pv['width'] . "</Width>
<Height>" . $pv['height'] . "</Height>";
                    }
                } else {
                    $data .= "<Length>" . Mage::getStoreConfig('upslabel/dimansion_' . $pv['dimansion_id'] . '/length') . "</Length>
<Width>" . Mage::getStoreConfig('upslabel/dimansion_' . $pv['dimansion_id'] . '/width') . "</Width>
<Height>" . Mage::getStoreConfig('upslabel/dimansion_' . $pv['dimansion_id'] . '/height') . "</Height>";
                }
                $data .= "</Dimensions>";
            }
            $data .= "<PackageWeight>
        <UnitOfMeasurement>
            <Code>" . $this->weightUnits . "</Code>";
            if (strlen($this->weightUnitsDescription) > 0) {
                $data .= "
            <Description>" . $this->weightUnitsDescription . "</<Description>";
            }
            $packweight = array_key_exists('packweight', $pv) ? $pv['packweight'] : '';
            $weight = array_key_exists('weight', $pv) ? $pv['weight'] : '';
            $data .= "</UnitOfMeasurement>
        <Weight>" . round(($weight + (is_numeric(str_replace(',', '.', $packweight)) ? $packweight : 0)), 1) . "</Weight>" . (array_key_exists('large', $pv) ? $pv['large'] : '') . "
      </PackageWeight>все
      <PackageServiceOptions>";
            if (array_key_exists('insuredmonetaryvalue', $pv) && $pv['insuredmonetaryvalue'] > 0) {
                $currencycode = array_key_exists('currencycode', $pv) ? $pv['currencycode'] : '';
                $insuredmonetaryvalue = array_key_exists('insuredmonetaryvalue', $pv) ? $pv['insuredmonetaryvalue'] : '';
                $data .= "<InsuredValue>
                <CurrencyCode>" . $currencycode . "</CurrencyCode>
                <MonetaryValue>" . $insuredmonetaryvalue . "</MonetaryValue>
                </InsuredValue>
              ";
            }
            $cod = array_key_exists('cod', $pv) ? $pv['cod'] : 0;
            if ($cod == 1 && ($this->shiptoCountryCode == 'US' || $this->shiptoCountryCode == 'PR' || $this->shiptoCountryCode == 'CA') && ($this->shipfromCountryCode == 'US' || $this->shipfromCountryCode == 'PR' || $this->shipfromCountryCode == 'CA')) {
                $codfundscode = array_key_exists('codfundscode', $pv) ? $pv['codfundscode'] : '';
                $codmonetaryvalue = array_key_exists('codmonetaryvalue', $pv) ? $pv['codmonetaryvalue'] : '';
                $data .= "
              <COD>
                  <CODCode>3</CODCode>
                  <CODFundsCode>" . $codfundscode . "</CODFundsCode>
                  <CODAmount>
                      <CurrencyCod>" . $currencycode . "</CurrencyCod>
                      <MonetaryValue>" . $codmonetaryvalue . "</MonetaryValue>
                  </CODAmount>
              </COD>";
            }
            $data .= "</PackageServiceOptions>
              </Package>";
        }
        $data .= "<ShipmentServiceOptions>";
        if ($this->codYesNo == 1 && $this->shiptoCountryCode != 'US' && $this->shiptoCountryCode != 'PR' && $this->shiptoCountryCode != 'CA' && $this->shipfromCountryCode != 'US' && $this->shipfromCountryCode != 'PR' && $this->shipfromCountryCode != 'CA') {
            $data .= "<COD>
                  <CODCode>3</CODCode>
                  <CODFundsCode>" . $this->codFundsCode . "</CODFundsCode>
                  <CODAmount>
                      <CurrencyCod>" . $this->currencyCode . "</CurrencyCod>
                      <MonetaryValue>" . $this->codMonetaryValue . "</MonetaryValue>
                  </CODAmount>
              </COD>";
        }
        if ($this->carbon_neutral == 1) {
            $data .= "<UPScarbonneutralIndicator/>";
        }
        $data .= "</ShipmentServiceOptions>";
        $data .= "</Shipment>
</RatingServiceSelectionRequest>
";
        $cie = 'wwwcie';
        if (0 == $this->testing) {
            $cie = 'onlinetools';
        }

        $curl = Mage::helper('upslabel/help');

        $result = $curl->curlSend('https://' . $cie . '.ups.com/ups.app/xml/Rate', $data);
        if (!$curl->error) {
            $xml = simplexml_load_string($result);
            if ($xml->Response->ResponseStatusCode[0] == 1) {
                $defaultPrice = $xml->RatedShipment[0]->TotalCharges[0]->MonetaryValue[0];
                $defaultCurrencyCode = $xml->RatedShipment[0]->TotalCharges[0]->CurrencyCode[0];
                if (!$defaultPrice) {
                    $defaultPrice = $xml->RatedShipment[0]->TotalCharges[0]->MonetaryValue;
                }
                if (!$defaultCurrencyCode) {
                    $defaultCurrencyCode = $xml->RatedShipment[0]->TotalCharges[0]->CurrencyCode;
                }
                $priceNegotiatedRates = array();
                if ($xml->RatedShipment[0]->NegotiatedRates || $xml->RatedShipment[0]->NegotiatedRates[0] || $xml->RatedShipment->NegotiatedRates || $xml->RatedShipment->NegotiatedRates[0]) {
                    $priceNegotiatedRates['MonetaryValue'] = $xml->RatedShipment[0]->NegotiatedRates[0]->NetSummaryCharges[0]->GrandTotal[0]->MonetaryValue[0];
                    $priceNegotiatedRates['CurrencyCode'] = $xml->RatedShipment[0]->NegotiatedRates[0]->NetSummaryCharges[0]->GrandTotal[0]->CurrencyCode[0];
                    if (!$priceNegotiatedRates['MonetaryValue']) {
                        $priceNegotiatedRates['MonetaryValue'] = $xml->RatedShipment[0]->NegotiatedRates[0]->NetSummaryCharges[0]->GrandTotal[0]->MonetaryValue;
                    }
                    if (!$priceNegotiatedRates['CurrencyCode']) {
                        $priceNegotiatedRates['CurrencyCode'] = $xml->RatedShipment[0]->NegotiatedRates[0]->NetSummaryCharges[0]->GrandTotal[0]->CurrencyCode;
                    }
                }
                return json_encode(array(
                    'price' => array(
                        'def' => array('MonetaryValue' => $defaultPrice, 'CurrencyCode' => $defaultCurrencyCode),
                        'negotiated' => $priceNegotiatedRates
                    ),
                ));
            } else {
                $error = array('error' => $xml->Response[0]->Error[0]->ErrorDescription[0]);
                return json_encode($error);
            }
        } else {
            return $result;
        }
    }

    function getShipPriceFrom()
    {
        if ($this->credentials != 1) {
            return array('error' => array('cod' => 1, 'message' => 'Not correct registration data'), 'success' => 0);
        }
        $this->customerContext = str_replace('&', '&amp;', strtolower(Mage::app()->getStore()->getName()));
        $data = "<?xml version=\"1.0\" ?>
        <AccessRequest xml:lang='en-US'>
        <AccessLicenseNumber>" . $this->AccessLicenseNumber . "</AccessLicenseNumber>
        <UserId>" . $this->UserID . "</UserId>
        <Password>" . $this->Password . "</Password>
        </AccessRequest>
        <?xml version=\"1.0\"?>
        <RatingServiceSelectionRequest xml:lang=\"en-US\">
          <Request>
            <TransactionReference>
              <CustomerContext>" . $this->customerContext . "</CustomerContext>
              <XpciVersion/>
            </TransactionReference>
            <RequestAction>Rate</RequestAction>
            <RequestOption>Rate</RequestOption>
          </Request>
          <Shipment>";
        if (Mage::getStoreConfig('upslabel/ratepayment/negotiatedratesindicator') == 1) {
            $data .= "<RateInformation>
      <NegotiatedRatesIndicator/>
    </RateInformation>";
        }
        if (strlen($this->shipmentDescription) > 0) {
            $data .= "<Description>" . $this->shipmentDescription . "</Description>";
        }
        $data .= "<Shipper>
        <Name>" . $this->shipperName . "</Name>";
        $data .= "<AttentionName>" . $this->shipperAttentionName . "</AttentionName>";

        $data .= "<PhoneNumber>" . $this->shipperPhoneNumber . "</PhoneNumber>
              <ShipperNumber>" . $this->shipperNumber . "</ShipperNumber>
        	  <TaxIdentificationNumber></TaxIdentificationNumber>
              <Address>
            	<AddressLine1>" . $this->shipperAddressLine1 . "</AddressLine1>
            	<City>" . $this->shipperCity . "</City>
            	<StateProvinceCode>" . $this->shipperStateProvinceCode . "</StateProvinceCode>
            	<PostalCode>" . $this->shipperPostalCode . "</PostalCode>
            	<PostcodeExtendedLow></PostcodeExtendedLow>
            	<CountryCode>" . $this->shipperCountryCode . "</CountryCode>
             </Address>
            </Shipper>
        	<ShipFrom>
             <CompanyName>" . $this->shiptoCompanyName . "</CompanyName>
              <AttentionName>" . $this->shiptoAttentionName . "</AttentionName>
              <PhoneNumber>" . $this->shiptoPhoneNumber . "</PhoneNumber>
              <TaxIdentificationNumber></TaxIdentificationNumber>
              <Address>
                <AddressLine1>" . $this->shiptoAddressLine1 . "</AddressLine1>
                <City>" . $this->shiptoCity . "</City>
                <StateProvinceCode>" . $this->shiptoStateProvinceCode . "</StateProvinceCode>
                <PostalCode>" . $this->shiptoPostalCode . "</PostalCode>
                <CountryCode>" . $this->shiptoCountryCode . "</CountryCode>
              </Address>
            </ShipFrom>
            <ShipTo>
              <CompanyName>" . $this->shipfromCompanyName . "</CompanyName>
              <AttentionName>" . $this->shipfromAttentionName . "</AttentionName>
              <PhoneNumber>" . $this->shipfromPhoneNumber . "</PhoneNumber>
              <Address>
                <AddressLine1>" . $this->shipfromAddressLine1 . "</AddressLine1>";
        if (strlen($this->shipfromAddressLine2) > 0) {
            $data .= '<AddressLine2>' . $this->shipfromAddressLine2 . '</AddressLine2>';
        }
        $data .= "<City>" . $this->shipfromCity . "</City>
            	<StateProvinceCode>" . $this->shipfromStateProvinceCode . "</StateProvinceCode>
            	<PostalCode>" . $this->shipfromPostalCode . "</PostalCode>
            	<CountryCode>" . $this->shipfromCountryCode . "</CountryCode>
              </Address>
            </ShipTo>
            <Service>
              <Code>" . $this->serviceCode . "</Code>
              <Description>" . $this->serviceDescription . "</Description>
            </Service>";
        $ttWeight = 0;
        foreach ($this->packages AS $pv) {
            $ttWeight += $pv['weight'];
        }
        foreach ($this->packages AS $pv) {
            $data .= "<Package>
      <PackagingType>
        <Code>" . $pv["packagingtypecode"] . "</Code>
      </PackagingType>
      <Description>" . $pv["packagingdescription"] . "</Description>";
            if (($this->shiptoCountryCode == 'US' || $this->shiptoCountryCode == 'PR') && $this->shiptoCountryCode == $this->shipfromCountryCode) {
                $data .= "<ReferenceNumber>
	  	<Code>" . $pv['packagingreferencenumbercode'] . "</Code>
		<Value>" . $pv['packagingreferencenumbervalue'] . "</Value>
	  </ReferenceNumber>";
                if (isset($pv['packagingreferencenumbercode2'])) {
                    $data .= "<ReferenceNumber>
	  	<Code>" . $pv['packagingreferencenumbercode2'] . "</Code>
		<Value>" . $pv['packagingreferencenumbervalue2'] . "</Value>
	  </ReferenceNumber>";
                }
            }
            $data .= array_key_exists('additionalhandling', $pv) ? $pv['additionalhandling'] : '';
            if ($this->includeDimensions == 1) {
                $data .= "<Dimensions>
<UnitOfMeasurement>
<Code>" . $this->unitOfMeasurement . "</Code>";
                if (strlen($this->unitOfMeasurementDescription) > 0) {
                    $data .= "
<Description>" . $this->unitOfMeasurementDescription . "</<Description>";
                }
                $data .= "</UnitOfMeasurement>";
                if ($pv['dimansion_id'] == 0) {
                    if (isset($pv['length']) && strlen($pv['length']) > 0) {
                        $data .= "<Length>" . $pv['length'] . "</Length>
<Width>" . $pv['width'] . "</Width>
<Height>" . $pv['height'] . "</Height>";
                    }
                } else {
                    $data .= "<Length>" . Mage::getStoreConfig('upslabel/dimansion_' . $pv['dimansion_id'] . '/length') . "</Length>
<Width>" . Mage::getStoreConfig('upslabel/dimansion_' . $pv['dimansion_id'] . '/width') . "</Width>
<Height>" . Mage::getStoreConfig('upslabel/dimansion_' . $pv['dimansion_id'] . '/height') . "</Height>";
                }
                $data .= "</Dimensions>";
            }
            $data .= "<PackageWeight>
        <UnitOfMeasurement>
            <Code>" . $this->weightUnits . "</Code>";
            if (strlen($this->weightUnitsDescription) > 0) {
                $data .= "
            <Description>" . $this->weightUnitsDescription . "</<Description>";
            }
            $data .= "</UnitOfMeasurement>
        <Weight>" . round(($ttWeight + (is_numeric(str_replace(',', '.', $pv['packweight'])) ? $pv['packweight'] : 0)), 1) . "</Weight>" . $pv['large'] . "
      </PackageWeight>
      <PackageServiceOptions>";
            if ($pv['insuredmonetaryvalue'] > 0) {
                $data .= "<InsuredValue>
                <CurrencyCode>" . $pv['currencycode'] . "</CurrencyCode>
                <MonetaryValue>" . $pv['insuredmonetaryvalue'] . "</MonetaryValue>
                </InsuredValue>
              ";
            }
            if ($pv['cod'] == 1 && ($this->shiptoCountryCode == 'US' || $this->shiptoCountryCode == 'PR' || $this->shiptoCountryCode == 'CA') && ($this->shipfromCountryCode == 'US' || $this->shipfromCountryCode == 'PR' || $this->shipfromCountryCode == 'CA')) {
                $data .= "
              <COD>
                  <CODCode>3</CODCode>
                  <CODFundsCode>0</CODFundsCode>
                  <CODAmount>
                      <CurrencyCod>" . $pv['currencycode'] . "</CurrencyCod>
                      <MonetaryValue>" . $pv['codmonetaryvalue'] . "</MonetaryValue>
                  </CODAmount>
              </COD>";
            }
            $data .= "</PackageServiceOptions>
              </Package>";
            break;
        }
        $data .= "</Shipment>
        </RatingServiceSelectionRequest>
        ";
        $cie = 'wwwcie';
        if (0 == $this->testing) {
            $cie = 'onlinetools';
        }

        $curl = Mage::helper('upslabel/help');

        $result = $curl->curlSend('https://' . $cie . '.ups.com/ups.app/xml/Rate', $data);

        if (!$curl->error) {
            $xml = simplexml_load_string($result);
            if ($xml->Response->ResponseStatusCode[0] == 1) {
                $defaultPrice = $xml->RatedShipment[0]->TotalCharges[0]->MonetaryValue[0];
                $defaultCurrencyCode = $xml->RatedShipment[0]->TotalCharges[0]->CurrencyCode[0];
                if (!$defaultPrice) {
                    $defaultPrice = $xml->RatedShipment[0]->TotalCharges[0]->MonetaryValue;
                }
                if (!$defaultCurrencyCode) {
                    $defaultCurrencyCode = $xml->RatedShipment[0]->TotalCharges[0]->CurrencyCode;
                }
                $priceNegotiatedRates = array();
                if ($xml->RatedShipment[0]->NegotiatedRates || $xml->RatedShipment[0]->NegotiatedRates[0] || $xml->RatedShipment->NegotiatedRates || $xml->RatedShipment->NegotiatedRates[0]) {
                    $priceNegotiatedRates['MonetaryValue'] = $xml->RatedShipment[0]->NegotiatedRates[0]->NetSummaryCharges[0]->GrandTotal[0]->MonetaryValue[0];
                    $priceNegotiatedRates['CurrencyCode'] = $xml->RatedShipment[0]->NegotiatedRates[0]->NetSummaryCharges[0]->GrandTotal[0]->CurrencyCode[0];
                    if (!$priceNegotiatedRates['MonetaryValue']) {
                        $priceNegotiatedRates['MonetaryValue'] = $xml->RatedShipment[0]->NegotiatedRates[0]->NetSummaryCharges[0]->GrandTotal[0]->MonetaryValue;
                    }
                    if (!$priceNegotiatedRates['CurrencyCode']) {
                        $priceNegotiatedRates['CurrencyCode'] = $xml->RatedShipment[0]->NegotiatedRates[0]->NetSummaryCharges[0]->GrandTotal[0]->CurrencyCode;
                    }
                }
                return json_encode(array(
                    'price' => array(
                        'def' => array('MonetaryValue' => $defaultPrice, 'CurrencyCode' => $defaultCurrencyCode),
                        'negotiated' => $priceNegotiatedRates
                    ),
                ));
            } else {
                $error = array('error' => $xml->Response[0]->Error[0]->ErrorDescription[0]);
                return json_encode($error);
            }
        } else {
            return $result;
        }
    }

    public function deleteLabel($trnum)
    {
        $path_xml = Mage::getBaseDir('media') . DS . 'upslabel' . DS . "test_xml" . DS;
        $cie = 'wwwcie';
        $testing = $this->testing;
        $shipIndefNumbr = $trnum;
        if (0 == $testing) {
            $cie = 'onlinetools';
        } else {
            /*$trnum = '1Z2220060291994175';*/
            $shipIndefNumbr = '1ZISDE016691676846';
        }
        $data = "<?xml version=\"1.0\" ?>
<AccessRequest xml:lang='en-US'>
<AccessLicenseNumber>" . $this->AccessLicenseNumber . "</AccessLicenseNumber>
<UserId>" . $this->UserID . "</UserId>
<Password>" . $this->Password . "</Password>
</AccessRequest>
<?xml version=\"1.0\" ?>
<VoidShipmentRequest>
<Request>
<RequestAction>1</RequestAction>
</Request>
<ShipmentIdentificationNumber>" . $shipIndefNumbr . "</ShipmentIdentificationNumber>
    <ExpandedVoidShipment>
          <ShipmentIdentificationNumber>" . $shipIndefNumbr . "</ShipmentIdentificationNumber>
          </ExpandedVoidShipment>
</VoidShipmentRequest> ";
        /*<TrackingNumber>" . $trnum . "</TrackingNumber>*/
        /*  */
        file_put_contents($path_xml . "VoidShipmentRequest.xml", $data);
        $curl = Mage::helper('upslabel/help');

        $result = $curl->curlSend('https://' . $cie . '.ups.com/ups.app/xml/Void', $data);
        if (!$curl->error) {
            file_put_contents($path_xml . "VoidShipmentResponse.xml", $result);
            $xml = simplexml_load_string($result);
            if ($xml->Response->Error[0] && (int)$xml->Response->Error[0]->ErrorCode != 190117) {
                $error = '<h1>Error</h1> <ul>';
                $errorss = $xml->Response->Error[0];
                $error .= '<li>Error Severity : ' . $errorss->ErrorSeverity . '</li>';
                $error .= '<li>Error Code : ' . $errorss->ErrorCode . '</li>';
                $error .= '<li>Error Description : ' . $errorss->ErrorDescription . '</li>';
                $error .= '</ul>';
                $error .= '<textarea>' . $result . '</textarea>';
                $error .= '<textarea>' . $data . '</textarea>';
                return array('error' => $error);
            } else {
                return true;
            }
        } else {
            return $result;
        }
    }

    function getPickup()
    {
        if ($this->credentials != 1) {
            return array('error' => array('cod' => 1, 'message' => 'Not correct registration data'), 'success' => 0);
        }
        /* if(is_dir($filename)){} */
        $path_upsdir = Mage::getBaseDir('media') . DS . 'upslabel' . DS;
        if (!is_dir($path_upsdir)) {
            mkdir($path_upsdir, 0777);
            mkdir($path_upsdir . "label" . DS, 0777);
            mkdir($path_upsdir . "test_xml" . DS, 0777);
        }
        $path_xml = Mage::getBaseDir('media') . DS . 'upslabel' . DS . "test_xml" . DS;
        if (!file_exists($path_xml . ".htaccess")) {
            file_put_contents($path_xml . ".htaccess", "deny from all");
        }
        $this->customerContext = str_replace('&', '&amp;', strtolower(Mage::app()->getStore()->getName()));

        $data = '<envr:Envelope xmlns:envr="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:common="http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0" xmlns:wsf="http://www.ups.com/schema/wsf" xmlns:upss="http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0">
	<envr:Header>
		<upss:UPSSecurity>
			<upss:UsernameToken>
				<upss:Username>' . $this->UserID . '</upss:Username>
				<upss:Password>' . $this->Password . '</upss:Password>
			</upss:UsernameToken>
			<upss:ServiceAccessToken>
				<upss:AccessLicenseNumber>' . $this->AccessLicenseNumber . '</upss:AccessLicenseNumber>
			</upss:ServiceAccessToken>
		</upss:UPSSecurity>
		<common:ClientInformation>
			<common:Property Key="DataSource">AG</common:Property>
			<common:Property Key="ClientCode">APS</common:Property>
		</common:ClientInformation>
	</envr:Header>';
        $data .= "<envr:Body><PickupCreationRequest xmlns=\"http://www.ups.com/XMLSchema/XOLTWS/Pickup/v1.1\" xmlns:common=\"http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">
    <RatePickupIndicator>" . $this->RatePickupIndicator . "</RatePickupIndicator>
    <Shipper>
        <Account>
            <AccountNumber>" . $this->shipperNumber . "</AccountNumber>
            <AccountCountryCode>" . $this->shipperCountryCode . "</AccountCountryCode>
        </Account>
    </Shipper>
    <PickupDateInfo>
        <CloseTime>" . str_replace(",", "", substr($this->CloseTime, 0, 5)) . "</CloseTime>
        <ReadyTime>" . str_replace(",", "", substr($this->ReadyTime, 0, 5)) . "</ReadyTime>
        <PickupDate>" . ($this->PickupDateYear . $this->PickupDateMonth . $this->PickupDateDay) . "</PickupDate>
    </PickupDateInfo>
    <PickupAddress>
        <CompanyName>" . $this->shipfromCompanyName . "</CompanyName>
        <ContactName>" . $this->shipfromAttentionName . "</ContactName>
        <AddressLine>" . $this->shipfromAddressLine1 . "</AddressLine>";
        if (strlen($this->room) > 0) {
            $data .= "<Room>" . $this->room . "</Room>";
        }
        if (strlen($this->floor) > 0) {
            $data .= "<Floor>" . $this->floor . "</Floor>";
        }
        $data .= "<City>" . $this->shipfromCity . "</City>";
        if (strlen($this->shipfromStateProvinceCode) > 0) {
            $data .= "<StateProvince>" . $this->shipfromStateProvinceCode . "</StateProvince>";
        }
        if (strlen($this->urbanization) > 0) {
            $data .= "<Urbanization>" . $this->urbanization . "</Urbanization>";
        }
        $data .= "<PostalCode>" . $this->shipfromPostalCode . "</PostalCode>
        <CountryCode>" . $this->shipfromCountryCode . "</CountryCode>
        <ResidentialIndicator>" . $this->residential . "</ResidentialIndicator>";
        if (strlen($this->pickup_point) > 0) {
            $data .= "<PickupPoint>" . $this->pickup_point . "</PickupPoint>";
        }
        $data .= "<Phone><Number>" . $this->shipfromPhoneNumber . "</Number></Phone>
    </PickupAddress>
    <AlternateAddressIndicator>" . $this->AlternateAddressIndicator . "</AlternateAddressIndicator>
    <PickupPiece>
        <ServiceCode>" . $this->ServiceCode . "</ServiceCode>
        <Quantity>" . $this->Quantity . "</Quantity>
        <DestinationCountryCode>" . $this->DestinationCountryCode . "</DestinationCountryCode>
        <ContainerCode>" . $this->ContainerCode . "</ContainerCode>
    </PickupPiece>";
        if (strlen($this->Weight) > 0) {
            $data .= "<TotalWeight>
            <Weight>" . $this->Weight . "</Weight>
            <UnitOfMeasurement>" . $this->UnitOfMeasurement . "</UnitOfMeasurement>
            <OverweightIndicator>" . $this->OverweightIndicator . "</OverweightIndicator>
        </TotalWeight>";
        }
        $data .= "
    <PaymentMethod>" . $this->PaymentMethod . "</PaymentMethod>
    ";
        if (strlen($this->SpecialInstruction) > 0) {
            $data .= "<SpecialInstruction>" . $this->SpecialInstruction . "</SpecialInstruction>";
        }
        if (strlen($this->ReferenceNumber) > 0) {
            $data .= "<ReferenceNumber>" . $this->ReferenceNumber . "</ReferenceNumber>";
        }
        if ($this->Notification == 1) {
            $data .= "<Notification>";
            $confirmEmail = explode(",", $this->ConfirmationEmailAddress);
            if (count($confirmEmail) > 0) {
                foreach ($confirmEmail AS $v) {
                    $data .= "<ConfirmationEmailAddress>" . trim($v) . "</ConfirmationEmailAddress>";
                }
            }
            $data .= "<UndeliverableEmailAddress>" . $this->UndeliverableEmailAddress . "</UndeliverableEmailAddress>";
            $data .= "</Notification>";
        }
        $data .= "
</PickupCreationRequest></envr:Body>
</envr:Envelope>";
        $file = file_put_contents($path_xml . "PickupRequest.xml", $data);
        $cie = 'wwwcie';
        if (0 == $this->testing) {
            $cie = 'onlinetools';
        }
        $curl = Mage::helper('upslabel/help');
        $result = $curl->curlSetOption('https://' . $cie . '.ups.com/webservices/Pickup', $data);
        $result = strstr($result, '<soapenv:');
        if ($result) {
            $file = file_put_contents($path_xml . "PickupResponse.xml", $result);
        }
        //return $result;
        $xml = simplexml_load_string($result);
        $soap = $xml->children('soapenv', true)->Body[0];
        $response = $soap->children('pkup', true);
        if ($response->children('common', true)->Response[0]->ResponseStatus[0]->Code[0] == 1 && $response->children('common', true)->Response[0]->ResponseStatus[0]->Description[0] == "Success") {
            return array(
                'Description' => $response->children('common', true)->Response[0]->ResponseStatus[0]->Description[0],
                'data' => $data,
                'response' => $result
            );
        } else {
            $error = '<h1>Error</h1> <ul>';
            $errorss = $soap->Fault[0]->children()->detail[0]->children('err', true)->Errors[0]->ErrorDetail[0];
            $error .= '<li>Error Severity : ' . $errorss->Severity[0] . '</li>';
            $error .= '<li>Error Code : ' . $errorss->PrimaryErrorCode[0]->Code[0] . '</li>';
            $error .= '<li>Error Description : ' . $errorss->PrimaryErrorCode[0]->Description[0] . '</li>';
            $error .= '</ul>';
            $error .= '<textarea>' . $result . '</textarea>';
            $error .= '<textarea>' . $data . '</textarea>';
            return array('error' => $error, 'data' => $data, 'response' => $result);
            //return print_r($xml->Response->Error);
        }
    }

    function cancelPickup($PRN)
    {
        if ($this->credentials != 1) {
            return array('error' => array('cod' => 1, 'message' => 'Not correct registration data'), 'success' => 0);
        }
        /* if(is_dir($filename)){} */
        $path_upsdir = Mage::getBaseDir('media') . DS . 'upslabel' . DS;
        if (!is_dir($path_upsdir)) {
            mkdir($path_upsdir, 0777);
            mkdir($path_upsdir . DS . "label" . DS, 0777);
            mkdir($path_upsdir . DS . "test_xml" . DS, 0777);
        }
        $path_xml = Mage::getBaseDir('media') . DS . 'upslabel' . DS . "test_xml" . DS;
        if (!file_exists($path_xml . ".htaccess")) {
            file_put_contents($path_xml . ".htaccess", "deny from all");
        }
        $cie = 'wwwcie';
        if (0 == $this->testing) {
            $cie = 'onlinetools';
            /*$PRN = '02';*/
        }
        /*else {
            $PRN = '2929602E9CP';
        }*/
        $data = '<envr:Envelope xmlns:envr="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:common="http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0" xmlns:wsf="http://www.ups.com/schema/wsf" xmlns:upss="http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0">
	<envr:Header>
		<upss:UPSSecurity>
			<upss:UsernameToken>
				<upss:Username>' . $this->UserID . '</upss:Username>
				<upss:Password>' . $this->Password . '</upss:Password>
			</upss:UsernameToken>
			<upss:ServiceAccessToken>
				<upss:AccessLicenseNumber>' . $this->AccessLicenseNumber . '</upss:AccessLicenseNumber>
			</upss:ServiceAccessToken>
		</upss:UPSSecurity>
		<common:ClientInformation>
			<common:Property Key="DataSource">AG</common:Property>
			<common:Property Key="ClientCode">APS</common:Property>
		</common:ClientInformation>
	</envr:Header>';
        $data .= "<envr:Body><PickupCancelRequest xmlns=\"http://www.ups.com/XMLSchema/XOLTWS/Pickup/v1.1\" xmlns:common=\"http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">
        <Request></Request>
        <CancelBy>02</CancelBy>
        <PRN>" . $PRN . "</PRN>";
        $data .= "</PickupCancelRequest></envr:Body>
</envr:Envelope>";
        file_put_contents($path_xml . "PickupCancelRequest.xml", $data);

        $curl = Mage::helper('upslabel/help');
        $result = $curl->curlSetOption('https://' . $cie . '.ups.com/webservices/Pickup', $data);
        $result = strstr($result, '<soapenv:');
        if ($result) {
            $file = file_put_contents($path_xml . "PickupCancelResponse.xml", $result);
        }

        $xml = simplexml_load_string($result);
        $soap = $xml->children('soapenv', true)->Body[0];
        $response = $soap->children('pkup', true);
        if ($response->children('common', true)->Response[0]->ResponseStatus[0]->Code[0] == 1 && $response->children('common', true)->Response[0]->ResponseStatus[0]->Description[0] == "Success") {
            return array(
                'Description' => "Canceled",
                'data' => $data,
                'response' => $result
            );
        } else {
            $error = '<h1>Error</h1> <ul>';
            $errorss = $soap->Fault[0]->children()->detail[0]->children('err', true)->Errors[0]->ErrorDetail[0];
            $error .= '<li>Error Severity : ' . $errorss->Severity[0] . '</li>';
            $error .= '<li>Error Code : ' . $errorss->PrimaryErrorCode[0]->Code[0] . '</li>';
            $error .= '<li>Error Description : ' . $errorss->PrimaryErrorCode[0]->Description[0] . '</li>';
            $error .= '</ul>';
            $error .= '<textarea>' . $result . '</textarea>';
            $error .= '<textarea>' . $data . '</textarea>';
            return array('error' => $error, 'data' => $data, 'response' => $result);
            //return print_r($xml->Response->Error);
        }
    }

    function statusPickup()
    {
        if ($this->credentials != 1) {
            return array('error' => array('cod' => 1, 'message' => 'Not correct registration data'), 'success' => 0);
        }
        /* if(is_dir($filename)){} */
        $path_upsdir = Mage::getBaseDir('media') . DS . 'upslabel' . DS;
        if (!is_dir($path_upsdir)) {
            mkdir($path_upsdir, 0777);
            mkdir($path_upsdir . DS . "label" . DS, 0777);
            mkdir($path_upsdir . DS . "test_xml" . DS, 0777);
        }
        $path_xml = Mage::getBaseDir('media') . DS . 'upslabel' . DS . "test_xml" . DS;
        if (!file_exists($path_xml . ".htaccess")) {
            file_put_contents($path_xml . ".htaccess", "deny from all");
        }
        $data = '<envr:Envelope xmlns:envr="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:common="http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0" xmlns:wsf="http://www.ups.com/schema/wsf" xmlns:upss="http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0">
	<envr:Header>
		<upss:UPSSecurity>
			<upss:UsernameToken>
				<upss:Username>' . $this->UserID . '</upss:Username>
				<upss:Password>' . $this->Password . '</upss:Password>
			</upss:UsernameToken>
			<upss:ServiceAccessToken>
				<upss:AccessLicenseNumber>' . $this->AccessLicenseNumber . '</upss:AccessLicenseNumber>
			</upss:ServiceAccessToken>
		</upss:UPSSecurity>
		<common:ClientInformation>
			<common:Property Key="DataSource">AG</common:Property>
			<common:Property Key="ClientCode">APS</common:Property>
		</common:ClientInformation>
	</envr:Header>';
        $data .= "<envr:Body><PickupPendingStatusRequest xmlns=\"http://www.ups.com/XMLSchema/XOLTWS/Pickup/v1.1\" xmlns:common=\"http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">
        <Request></Request>
        <PickupType>01</PickupType>
        <AccountNumber>" . $this->shipperNumber . "</AccountNumber>";
        $data .= "</PickupPendingStatusRequest></envr:Body>
</envr:Envelope>";
        $file = file_put_contents($path_xml . "PickupPendingStatusRequest.xml", $data);
        $cie = 'wwwcie';
        if (0 == $this->testing) {
            $cie = 'onlinetools';
        }

        $curl = Mage::helper('upslabel/help');
        $result = $curl->curlSetOption('https://' . $cie . '.ups.com/webservices/Pickup', $data);
        $result = strstr($result, '<soapenv:');
        if ($result) {
            $file = file_put_contents($path_xml . "PickupPendingStatusResponse.xml", $result);
        }
        $xml = simplexml_load_string($result);
        $soap = $xml->children('soapenv', true)->Body[0];
        $response = $soap->children('pkup', true);
        if ($response->children('common', true)->Response[0]->ResponseStatus[0]->Code[0] == 1 && $response->children('common', true)->Response[0]->ResponseStatus[0]->Description[0] == "Success") {
            return array(
                'Description' => "Canceled",
                'data' => $data,
                'response' => $result
            );
        } else {
            $error = '<h1>Error</h1> <ul>';
            $errorss = $soap->Fault[0]->children()->detail[0]->children('err', true)->Errors[0]->ErrorDetail[0];
            $error .= '<li>Error Severity : ' . $errorss->Severity[0] . '</li>';
            $error .= '<li>Error Code : ' . $errorss->PrimaryErrorCode[0]->Code[0] . '</li>';
            $error .= '<li>Error Description : ' . $errorss->PrimaryErrorCode[0]->Description[0] . '</li>';
            $error .= '</ul>';
            $error .= '<textarea>' . $result . '</textarea>';
            $error .= '<textarea>' . $data . '</textarea>';
            return array('error' => $error, 'data' => $data, 'response' => $result);
            //return print_r($xml->Response->Error);
        }
    }

    function ratePickup()
    {
        if ($this->credentials != 1) {
            return array('error' => array('cod' => 1, 'message' => 'Not correct registration data'), 'success' => 0);
        }
        $data = '<envr:Envelope xmlns:envr="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:common="http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0" xmlns:wsf="http://www.ups.com/schema/wsf" xmlns:upss="http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0">
	<envr:Header>
		<upss:UPSSecurity>
			<upss:UsernameToken>
				<upss:Username>' . $this->UserID . '</upss:Username>
				<upss:Password>' . $this->Password . '</upss:Password>
			</upss:UsernameToken>
			<upss:ServiceAccessToken>
				<upss:AccessLicenseNumber>' . $this->AccessLicenseNumber . '</upss:AccessLicenseNumber>
			</upss:ServiceAccessToken>
		</upss:UPSSecurity>
		<common:ClientInformation>
			<common:Property Key="DataSource">AG</common:Property>
			<common:Property Key="ClientCode">APS</common:Property>
		</common:ClientInformation>
	</envr:Header>';
        $data .= "<envr:Body><PickupRateRequest xmlns=\"http://www.ups.com/XMLSchema/XOLTWS/Pickup/v1.1\" xmlns:common=\"http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">
        <Request></Request>
    <PickupAddress>
        <AddressLine>" . $this->shipfromAddressLine1 . "</AddressLine>";
        $data .= "<City>" . $this->shipfromCity . "</City>";
        $data .= "<StateProvince>" . $this->shipfromStateProvinceCode . "</StateProvince>";
        $data .= "<PostalCode>" . $this->shipfromPostalCode . "</PostalCode>
        <CountryCode>" . $this->shipfromCountryCode . "</CountryCode>
        <ResidentialIndicator>" . $this->residential . "</ResidentialIndicator>";
        $data .= "</PickupAddress>
    <AlternateAddressIndicator>" . $this->AlternateAddressIndicator . "</AlternateAddressIndicator>
    <ServiceDateOption>" . ($this->PickupDateYear . $this->PickupDateMonth . $this->PickupDateDay == date("Ymd") ? "01" : "02") . "</ServiceDateOption>
    <PickupDateInfo>
        <CloseTime>" . str_replace(",", "", substr($this->CloseTime, 0, 5)) . "</CloseTime>
        <ReadyTime>" . str_replace(",", "", substr($this->ReadyTime, 0, 5)) . "</ReadyTime>
        <PickupDate>" . ($this->PickupDateYear . $this->PickupDateMonth . $this->PickupDateDay) . "</PickupDate>
    </PickupDateInfo>";
        $data .= "</PickupRateRequest></envr:Body>
</envr:Envelope>";
        $cie = 'wwwcie';
        if (0 == $this->testing) {
            $cie = 'onlinetools';
        }
        $curl = Mage::helper('upslabel/help');
        $result = $curl->curlSetOption('https://' . $cie . '.ups.com/webservices/Pickup', $data);
        $result = strstr($result, '<soapenv:');
        $xml = simplexml_load_string($result);
        $soap = $xml->children('soapenv', true)->Body[0];
        $response = $soap->children('pkup', true);
        if ($response->children('common', true)->Response[0]->ResponseStatus[0]->Code[0] == 1 && $response->children('common', true)->Response[0]->ResponseStatus[0]->Description[0] == "Success") {
            return $soap->children('pkup', true)->PickupRateResponse[0]->RateResult[0]->GrandTotalOfAllCharge;
        }
    }
    function getShipRate()
    {
        $this->customerContext = str_replace('&', '&amp;', strtolower(Mage::app()->getStore()->getName()));
        $data = "<?xml version=\"1.0\" ?>
<AccessRequest xml:lang='en-US'>
<AccessLicenseNumber>" . $this->AccessLicenseNumber . "</AccessLicenseNumber>
<UserId>" . $this->UserID . "</UserId>
<Password>" . $this->Password . "</Password>
</AccessRequest>
<?xml version=\"1.0\"?>
<RatingServiceSelectionRequest xml:lang=\"en-US\">
  <Request>
    <TransactionReference>
      <CustomerContext>" . $this->customerContext . "</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
    <RequestAction>Rate</RequestAction>
    <RequestOption>Rate</RequestOption>
  </Request>
  <Shipment>";
        /*if (Mage::getStoreConfig('upslabel/profile/negotiatedratesindicator') == 1) {
            $data .= "
   <RateInformation>
      <NegotiatedRatesIndicator/>
    </RateInformation>";
        }*/
        $data .= "<Shipper>
<Name>" . $this->shipperName . "</Name>";
        $data .= "<ShipperNumber>" . $this->shipperNumber . "</ShipperNumber>
	  <TaxIdentificationNumber></TaxIdentificationNumber>
      <Address>
    	<AddressLine1>" . $this->shipperAddressLine1 . "</AddressLine1>
    	<City>" . $this->shipperCity . "</City>
    	<StateProvinceCode>" . $this->shipperStateProvinceCode . "</StateProvinceCode>
    	<PostalCode>" . $this->shipperPostalCode . "</PostalCode>
    	<PostcodeExtendedLow></PostcodeExtendedLow>
    	<CountryCode>" . $this->shipperCountryCode . "</CountryCode>
     </Address>
    </Shipper>
	<ShipTo>
      <Address>
        <AddressLine1>" . $this->shiptoAddressLine1 . "</AddressLine1>";
        if (strlen($this->shiptoAddressLine2) > 0) {
            $data .= '<AddressLine2>' . $this->shiptoAddressLine2 . '</AddressLine2>';
        }
        $data .= "<City>" . $this->shiptoCity . "</City>
        <StateProvinceCode>" . $this->shiptoStateProvinceCode . "</StateProvinceCode>
        <PostalCode>" . $this->shiptoPostalCode . "</PostalCode>
        <CountryCode>" . $this->shiptoCountryCode . "</CountryCode>
        " . $this->residentialAddress . "
      </Address>
    </ShipTo>
    <ShipFrom>
	  <TaxIdentificationNumber></TaxIdentificationNumber>
      <Address>
        <AddressLine1>" . $this->shipfromAddressLine1 . "</AddressLine1>
        <City>" . $this->shipfromCity . "</City>
    	<StateProvinceCode>" . $this->shipfromStateProvinceCode . "</StateProvinceCode>
    	<PostalCode>" . $this->shipfromPostalCode . "</PostalCode>
    	<CountryCode>" . $this->shipfromCountryCode . "</CountryCode>
      </Address>
    </ShipFrom>";
        $data .= "<Service>
      <Code>" . $this->serviceCode . "</Code>
    </Service>";
        foreach ($this->packages AS $pv) {
            $data .= "<Package>
      <PackagingType>
        <Code>" . $pv["packagingtypecode"] . "</Code>
      </PackagingType>";
            $data .= array_key_exists('additionalhandling', $pv) ? $pv['additionalhandling'] : '';
            $data .= "<PackageWeight>
        <UnitOfMeasurement>
            <Code>" . $this->weightUnits . "</Code>";
            $packweight = array_key_exists('packweight', $pv) ? $pv['packweight'] : '';
            $weight = array_key_exists('weight', $pv) ? $pv['weight'] : '';
            $data .= "</UnitOfMeasurement>
        <Weight>" . round(($weight + (is_numeric(str_replace(',', '.', $packweight)) ? $packweight : 0)), 1) . "</Weight>" . (array_key_exists('large', $pv) ? $pv['large'] : '') . "
      </PackageWeight>
      <PackageServiceOptions>";
            if (array_key_exists('insuredmonetaryvalue', $pv) && $pv['insuredmonetaryvalue'] > 0) {
                $currencycode = array_key_exists('currencycode', $pv) ? $pv['currencycode'] : '';
                $insuredmonetaryvalue = array_key_exists('insuredmonetaryvalue', $pv) ? $pv['insuredmonetaryvalue'] : '';
                $data .= "<InsuredValue>
                <CurrencyCode>" . $currencycode . "</CurrencyCode>
                <MonetaryValue>" . $insuredmonetaryvalue . "</MonetaryValue>
                </InsuredValue>
              ";
            }
            /*$cod = array_key_exists('cod', $pv) ? $pv['cod'] : 0;
            if ($cod == 1 && ($this->shiptoCountryCode == 'US' || $this->shiptoCountryCode == 'PR' || $this->shiptoCountryCode == 'CA') && ($this->shipfromCountryCode == 'US' || $this->shipfromCountryCode == 'PR' || $this->shipfromCountryCode == 'CA')) {
                $codfundscode = array_key_exists('codfundscode', $pv) ? $pv['codfundscode'] : '';
                $codmonetaryvalue = array_key_exists('codmonetaryvalue', $pv) ? $pv['codmonetaryvalue'] : '';
                $data .= "
              <COD>
                  <CODCode>3</CODCode>
                  <CODFundsCode>" . $codfundscode . "</CODFundsCode>
                  <CODAmount>
                      <CurrencyCod>" . $currencycode . "</CurrencyCod>
                      <MonetaryValue>" . $codmonetaryvalue . "</MonetaryValue>
                  </CODAmount>
              </COD>";
            }*/
            $data .= "</PackageServiceOptions>
              </Package>";
        }
        $data .= "<ShipmentServiceOptions>";
        /*if ($this->codYesNo == 1 && $this->shiptoCountryCode != 'US' && $this->shiptoCountryCode != 'PR' && $this->shiptoCountryCode != 'CA' && $this->shipfromCountryCode != 'US' && $this->shipfromCountryCode != 'PR' && $this->shipfromCountryCode != 'CA') {
            $data .= "<COD>
                  <CODCode>3</CODCode>
                  <CODFundsCode>" . $this->codFundsCode . "</CODFundsCode>
                  <CODAmount>
                      <CurrencyCod>" . $this->currencyCode . "</CurrencyCod>
                      <MonetaryValue>" . $this->codMonetaryValue . "</MonetaryValue>
                  </CODAmount>
              </COD>";
        }*/
        if ($this->carbon_neutral == 1) {
            $data .= "<UPScarbonneutralIndicator/>";
        }
        $data .= "</ShipmentServiceOptions>";
        $data .= "</Shipment></RatingServiceSelectionRequest>";
        $cie = 'wwwcie';
        if (0 == $this->testing) {
            $cie = 'onlinetools';
        }
        $ch = curl_init('https://' . $cie . '.ups.com/ups.app/xml/Rate');
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        $result = strstr($result, '<?xml');

        //return $data;
        $xml = simplexml_load_string($result);
        if ($xml->Response->ResponseStatusCode[0] == 1) {
            $defaultPrice = $xml->RatedShipment[0]->TotalCharges[0]->MonetaryValue[0];
            return array(
                'price' => $defaultPrice,
            );
        } else {
            $error = array('error' => $xml->Response[0]->Error[0]->ErrorDescription[0]);
            return $error;
        }
    }
}

?>