function toggleGiftCardForm() {
    var chkBox = $('gift-card-checkbox');
    var giftCardForm = $('gift-card-form');
    var giftCardCode = $('gift-card-code');
    var giftCardPin = $('gift-card-pin');
    var giftCardApply = $('gift-card-apply');
    var giftCardCancel = $('gift-card-cancel');
    if (chkBox && giftCardForm){
        chkBox.setAttribute("name","gift-card-checkbox");
        var toggleFields = [giftCardCode, giftCardPin, giftCardApply, giftCardCancel];
        for(var i = 0; i < toggleFields.length; i++) {
            var field = toggleFields[i];
            if(field && field.hasAttribute("disabled")) {
                field.removeAttribute("disabled");
            }
        }
        if (chkBox.checked) {
            giftCardForm.show();
        }   else {
            giftCardForm.hide();
        }

        $$('.gift-card-amount-text').each(function(el) {
            if (el.hasAttribute('disabled')) {
                el.removeAttribute('disabled');
            }
        });
    }
}

function disEnableField(fieldId, enable) {
    if(enable) {
        $(fieldId).removeAttribute('disabled');
    } else {
        $(fieldId).setAttribute('disabled','disabled');
    }
    $(fieldId).setStyle({backgroundColor: enable ? "#FFF" : '#ddd'});
}

function applyGiftCardToQuote(){
    var code = $('gift-card-code').value;
    var pin = $("gift-card-pin").value;
    var url = snapBaseURL + '/giftcard/index/add/';
    new Ajax.Request(url, {
        parameters: {
            isAjax: 1,
            "snap_card": code,
            "snap_card_pin": pin
        },
        onLoading: function(){
            disEnableField("gift-card-code", false);
            disEnableField("gift-card-pin", false);
        },
        onSuccess: function(transport) {
            response = eval('(' + transport.responseText + ')');
            disEnableField("gift-card-code", true);
            disEnableField("gift-card-pin", true);
            if (response.added) {
                $('gift-card-holder').insert(response.html);
            } else {
                alert(response.msg);
            }
        }
    });
}

function removeGiftCardFromQuote(code, id){
    var url = snapBaseURL + '/giftcard/index/removeGc/code/'+code;
    new Ajax.Request(url, {
        parameters: {isAjax: 1, method: 'GET'},
        onSuccess: function(transport) {
            response = eval('(' + transport.responseText + ')');
            if (response.removed) {
                $('gift-card-item-'+id).remove();
                $('shopping-cart-totals-table').replace(response.html);
            }
        }
    });
}

function editQuoteGiftCard(id){
    $('btn_edit_gc_'+id).hide();
    $('gc_amount_value_'+id).hide();
    $('gc_amount_text_'+id).show();
    $('btn_save_gc_'+id).show();
}

function updateGiftCardQuoteAmount(id){
    var amount = $('gc_amount_text_'+id).value;
    var url = snapBaseURL + '/giftcard/index/updateGcAmount/id/'+id+'/amount/'+amount;
    new Ajax.Request(url, {
        parameters: {isAjax: 1, method: 'GET'},
        onLoading: function(){
            $('gc_amount_text_'+id).setAttribute('disabled','disabled');
            $('gc_amount_text_'+id).setStyle({
                backgroundColor: '#ddd'
            });
        },
        onSuccess: function(transport) {
            response = eval('(' + transport.responseText + ')');
            $('gc_amount_text_'+id).setStyle({
                backgroundColor: '#fff'
            });
            $('gc_amount_text_'+id).    removeAttribute('disabled');
            $('btn_edit_gc_'+id).show();
            if (response.updated) {
                $('gc_amount_value_'+id).update(response.amount);
                if ($('shopping-cart-totals-table') && response.html) {
                    $('shopping-cart-totals-table').replace(response.html);
                }

            } else if ( response.msg && typeof response.msg != 'undefined' && response.msg != '') {
                alert(response.msg);
            }
            $('gc_amount_value_'+id).show();
            $('gc_amount_text_'+id).hide();
            $('btn_save_gc_'+id).hide();
            //@TODO update totals
        }
    });

}