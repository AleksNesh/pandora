function disEnableField(fieldId, enable) {
    if(enable) {
        $(fieldId).removeAttribute('disabled');
    } else {
        $(fieldId).setAttribute('disabled', 'disabled');
    }
    $(fieldId).setStyle({backgroundColor: enable ? "#FFF" : "#DDD"});
}

function handleChargeBack(chargeId, wasCharged) {
    var url = chargeBackURL;
    new Ajax.Request(url, {
        parameters: {
            isAjax: 1,
            chargeId: chargeId
        },
        onSuccess: function(transport) {
            response = eval('(' + transport.responseText + ')');
            if (response.success) {
                if(wasCharged) {
                    $("charge-" + chargeId).update("No");
                    $("return-" + chargeId).update("Yes");
                } else {
                    $("hold-" + chargeId).update("No");
                    $("return-" + chargeId).update("Yes");
                }
            } else {
                alert("Error! " + response.error);
            }
        }
    });
}

function pageInit() {
    $$(".return-charge").invoke("observe", "click", function(event) {
        var element = Event.element(event);
        var chargeId = element.getAttribute("charge-id");
        handleChargeBack(chargeId, true);
        Event.stop(event); // stop the form from submitting
    });
    $$(".return-hold").invoke("observe", "click", function(event) {
        var element = Event.element(event);
        var chargeId = element.getAttribute("charge-id");
        handleChargeBack(chargeId, false);
        Event.stop(event); // stop the form from submitting
    });
}


document.observe('dom:loaded', pageInit);