var updateBalance = function update(url,customerId){
    new Ajax.Request(url,{
            parameters:{giftcard_code:$F('giftcard_code'),customer_id:customerId},
            onSuccess: function() {
                order.loadArea(['items', 'shipping_method', 'totals', 'billing_method'], true, {reset_shipping: true});
            }
        }
    );

    $('giftcard_code').value="";
};


function deActivate(el, id) {
    var url = el.href;
    new Ajax.Request(url,{
            parameters:{id: id},
            onSuccess: function() {
                order.loadArea(['items', 'shipping_method', 'totals', 'billing_method'], true, {reset_shipping: true});
            }
        }
    );
}