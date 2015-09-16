function disEnableField(fieldId, enable) {
    if(enable) {
        $(fieldId).removeAttribute('disabled');
    } else {
        $(fieldId).setAttribute('disabled', 'disabled');
    }
    $(fieldId).setStyle({backgroundColor: enable ? "#FFF" : "#DDD"});
}

function showSNAPMessage(isError, message) {
    $("balanceResult").setStyle({
        color: isError ? "#F00" : "#000"
    });
    $("balanceResult").update(message + "<br /><br />");
}

function pageInit() {
    Event.observe('balanceCheckForm', 'submit', function(event) {
        var code = $("card_number").value;
        var pin = $("card_pin").value;
        var url = snapBaseURL + '/giftcard/index/checkBalance';
        if(!code) {
            showSNAPMessage(true, "Please enter a gift card number.");
        } else {
            new Ajax.Request(url, {
                parameters: {
                    isAjax: 1,
                    snap_card: code,
                    snap_card_pin: pin
                },
                onLoading: function(){
                    disEnableField("card_number", false);
                    disEnableField("card_pin", false);
                },
                onSuccess: function(transport) {
                    response = eval('(' + transport.responseText + ')');
                    disEnableField("card_number", true);
                    disEnableField("card_pin", true);
                    if(response.success) {
                        showSNAPMessage(false, "Your gift card balance is " + response.balanceDisp + ".");
                    } else {
                        showSNAPMessage(true, "Error!<br/>" + response.error + ".")
                    }
                }
            });
        }
        Event.stop(event); // stop the form from submitting
    });
}


document.observe('dom:loaded', pageInit);