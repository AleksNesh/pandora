// Bypass USPS Address Verification by adding hidden element to billing form
function bypassUspsavBilling(){
    // Insert hidden element to bypass uspsav
    $('co-billing-form').insert({
        bottom: '<input type="hidden" name="billing[uspsav_bypass]" id="billing:uspsav_bypass" value="1"/>'
    });
    
    billing.save();
    
    // Remove elements after saving information
    $('uspsav_billing').remove();
    $('billing:uspsav_bypass').remove();
}

// Bypass USPS Address Verification by adding hidden element to shipping form
function bypassUspsavShipping(){
    // Insert hidden element to bypass uspsav
    $('co-shipping-form').insert({
        bottom: '<input type="hidden" name="shipping[uspsav_bypass]" id="shipping:uspsav_bypass" value="1"/>'
    });
    
    shipping.save();
    
    // Remove elements after saving information
    $('uspsav_shipping').remove();
    $('shipping:uspsav_bypass').remove();
}

//Bypass USPS Address Verification by adding hidden element to address form
function bypassUspsavAddress(){
    // Insert hidden element to bypass uspsav
    $('form-validate').insert({
        bottom: '<input type="hidden" name="uspsav_bypass" id="uspsav_bypass" value="1"/>'
    });
    
    $('form-validate').submit();
}
