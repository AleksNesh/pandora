var Giftwrap = Class.create();
Giftwrap.prototype = {
    initialize: function(reloadstyleUrl, chooseStyleUrl, reloadTotalUrl, saveMessageUrl, wrapAllUrl) {
        this.reloadstyleUrl = reloadstyleUrl;
        this.chooseStyleUrl = chooseStyleUrl;
        this.reloadTotalUrl = reloadTotalUrl;
        this.saveMessageUrl = saveMessageUrl;
        this.wrapAllUrl = wrapAllUrl;
        this.loadWaiting = false;
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
        //this.onSave = this.nextStep.bindAsEventListener(this);
    },
    reloadStyle: function(itemId) {
        this.setLoadWaiting(true);
        var action;
        action = "delete";
        if ($('cart[' + itemId + '][giftwrap]') && ($('cart[' + itemId + '][giftwrap]').checked)) {
            action = "add";
        }
        url = this.reloadstyleUrl + "item_id/" + itemId + "/action/" + action;
        if ($('giftwrap_area') != undefined) {
            $('giftwrap_area').remove();
        }
        var request = new Ajax.Request(
                url,
                {
                    method: 'post',
                    onComplete: '',
                    onSuccess: this.uncheckedItemSelection.bindAsEventListener(this),
                    onFailure: ""
                }
        );
    },
    wrapAll: function() {
        this.setLoadWaiting(true);
        var action;
        action = "delete";
        if ($('giftwrap_all_in_one') && ($('giftwrap_all_in_one').checked)) {
            action = "add";
        }
        url = this.wrapAllUrl + "action/" + action;
        if ($('giftwrap_area') != undefined) {
            $('giftwrap_area').remove();
        }
        var request = new Ajax.Request(
                url,
                {
                    method: 'post',
                    parameters: "Params_Here",
                    onComplete: '',
                    onSuccess: this.disableItemSelection.bindAsEventListener(this),
                    onFailure: ""
                }
        );
    },
    uncheckedItemSelection: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = '';
            }
        }
        if (response.items) {
            for (var i = 0; i < response.items.length; i++) {
                if ($('cart[' + response.items[i] + '][giftwrap]')) {
                    $('cart[' + response.items[i] + '][giftwrap]').checked = false;
                }
            }
        }
        this.reloadStyleSelection(transport);
    },
    disableItemSelection: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = '';
            }
        }
        if (response.items) {
            for (var i = 0; i < response.items.length; i++) {
                if ($('cart[' + response.items[i] + '][giftwrap]')) {
                    if ($('giftwrap_all_in_one') && $('giftwrap_all_in_one').checked) {
                        /*
                         *	insert by Kend : 05-13-2010
                         */
                        $('cart[' + response.items[i] + '][giftwrap]').checked = false;
                        /*
                         *	finish.
                         */
                        $('cart[' + response.items[i] + '][giftwrap]').hide();
                    }
                    else {
                        $('cart[' + response.items[i] + '][giftwrap]').show();
                    }
                }
            }
        }
        this.reloadStyleSelection(transport);
    },
    reloadStyleSelection: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = '';
            }
        }
        if (response != '') {
            if (response.error) {
                alert(response.error);
                this.setLoadWaiting(false);
                return false;
            }
            $('giftwrap_styleselection').update(response.html);
            this.reloadCartTotal();
        }
    },
    chooseStyle: function(styleId, itemId) {
        this.setLoadWaiting(true);
        var url = this.chooseStyleUrl;
        url = url + 'itemId/' + itemId + '/styleId/' + styleId;
        var request = new Ajax.Request(
                url,
                {
                    method: 'post',
                    onComplete: '',
                    onSuccess: this.updateStyle.bindAsEventListener(this),
                    onFailure: ''
                });
    },
    resetLoadWaiting: function(transport) {
        this.setLoadWaiting(false);
   },
    setLoadWaiting: function(step, keepDisabled) {
        if (step) {
            if (this.loadWaiting) {
                this.setLoadWaiting(false);
            }
            var container = $('giftwrap-checkout-cart-container');
            container.setStyle({opacity: .5});
            this._disableEnableAll(container, true);
            Element.show('loading_mask_loader');
        } else {
            if (this.loadWaiting) {
                var container = $('giftwrap-checkout-cart-container');
                container.setStyle({opacity: 1});
                this._disableEnableAll(container, false);
                Element.hide('loading_mask_loader');
            }
        }
        this.loadWaiting = step;
    },
    _disableEnableAll: function(element, isDisabled) {
        var descendants = element.descendants();
        for (var k in descendants) {
            descendants[k].disabled = isDisabled;
        }
        element.disabled = isDisabled;
    },
    updateStyle: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = '';
            }
        }
        if (response != '') {
            $('giftwrap_style_image_' + response.item_id).src = GIFTWRAP_IMAGE_URL + response.image;
            $('giftwrap_style_price_' + response.item_id).update(response.price);
            if (response.flag == 1) {
                $('message_turned_off_' + response.item_id).className = 'turned_on';
                $('giftwrap_message_area_' + response.item_id).className = 'turned_off';
            } else {
                $('giftwrap_message_area_' + response.item_id).value = response.personal_message;
                $('message_turned_off_' + response.item_id).className = 'turned_off';
                $('giftwrap_message_area_' + response.item_id).className = 'turned_on';
            }
            if (response.character) {
                alert(response.character);
            }
            this.reloadCartTotal();
        }
   },
    reloadCartTotal: function() {
        var request = new Ajax.Request(
                this.reloadTotalUrl,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.updateTotal.bindAsEventListener(this),
                    onFailure: ''
                }
        );
   },
    updateTotal: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = '';
            }
        }

        if (response != '') {
            $('shopping-cart-totals-table').update(response.html);
        }
   },
    saveMessage: function(message, itemId) {
        this.setLoadWaiting(true);
        message = escape(message);
        var url = this.saveMessageUrl + 'itemId/' + itemId + '/message/' + message;
        var request = new Ajax.Request(
                url,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.updateMessage.bindAsEventListener(this),
                    onFailure: ''
                }
        );
   },
    updateMessage: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = '';
            }
        }
        if (response != '') {
            $('giftwrap_message_area_' + response.item_id).value = response.personal_message;
        }
        if (response.character) {
            alert(response.character);
        }
   },
    afterSave: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = '';
            }
        }
        if (response.error != '') {
            alert(response.error);
            return false;
        }
    }
}

function check_giftwrap(saveUrl, checkoutUrl)
{
    var saveUrl = saveUrl;
    var checkoutUrl = checkoutUrl;
    var options = document.getElementsByName('cart_giftwrap_message');
    var message_array = new Array();
    if (options.length > 0) {
        var j = 0;
        var url = '';
        for (var i = 0; i < options.length; i++) {
            if (options[i].value != '') {
                j++;
                message_array[i] = options[i].value + '_array_' + options[i].id;
                url += '/a' + i + '/' + message_array[i];
            }
        }
        if (j > 0) {
            url = 'count/' + j + url;
            url = saveUrl + url;
            var request = new Ajax.Request(
                    url,
                    {
                        method: 'post',
                        onComplete: this.onComplete,
                        onSuccess: function(transport) {
                            if (200 == transport.status)
                                window.location = checkoutUrl;
                        },
                        onFailure: ''
                    }
            );
        } else {
            window.location = checkoutUrl;
        }
    } else {
        window.location = checkoutUrl;
    }
}