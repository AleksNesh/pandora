// Wait until dom is fully loaded (the below referenced objects are called later in dom)
Event.observe(window, 'load', function() {
	if (typeof Billing === 'undefined' || typeof Shipping === 'undefined') return;

	// Override core billing nextStep function to bypass USPS Address Verification
	Billing.prototype.nextStep = function(transport){
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error){
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                if (window.billingRegionUpdater) {
                    billingRegionUpdater.update();
                }

                alert(response.message.join("\n"));
            }

            // Check for address verification error
            if (response.error_uspsav) {
            	// Add 'Bypass Address Verification' button if it doesn't already exist on page
            	if ($('uspsav_billing') == undefined) {
            		$$('#co-billing-form button').first().insert({
            			after: '<button type="button" title="Bypass Address Verification & Continue" id="uspsav_billing" class="button uspsav" onclick="bypassUspsavBilling()"><span><span>Bypass Address Verification & Continue</span></span></button>'
            		});
            	}
            }

            return false;
        }

        checkout.setStepResponse(response);
        payment.initWhatIsCvvListeners();
    }

	// Override core shipping nextStep function to bypass USPS Address Verification
	Shipping.prototype.nextStep = function(transport){
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.error){
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                if (window.shippingRegionUpdater) {
                    shippingRegionUpdater.update();
                }
                alert(response.message.join("\n"));
            }

            // Check for address verification error
            if (response.error_uspsav) {
            	// Add 'Bypass Address Verification' button if it doesn't already exist on page
            	if ($('uspsav_shipping') == undefined) {
            		$$('#co-shipping-form button').first().insert({
            			after: '<button type="button" title="Bypass Address Verification & Continue" id="uspsav_shipping" class="button uspsav" onclick="bypassUspsavShipping()"><span><span>Bypass Address Verification & Continue</span></span></button>'
            		});
            	}
            }

            return false;
        }

        checkout.setStepResponse(response);
    }
});
