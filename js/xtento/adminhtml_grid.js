/**
 * Copyright XTENTO GmbH & Co. KG / http://www.xtento.com
 */
var extendedGridMassaction = Class.create(varienGridMassaction, {
    apply:function ($super) {
        var carriers = [];
        $('sales_order_grid_table').getElementsBySelector('.carrier-selector').each(function (carrier) {
            if (carrier.value !== 'custom') carriers.push(carrier.readAttribute('rel') + '[|]' + carrier.value);
        });
        new Insertion.Bottom(this.formAdditional, this.fieldTemplate.evaluate({name:'carriers', value:carriers}));
        var trackingnumbers = [];
        $('sales_order_grid_table').getElementsBySelector('.tracking-input').each(function (trackingnumber) {
            if (trackingnumber.value !== '') trackingnumbers.push(trackingnumber.readAttribute('rel') + '[|]' + trackingnumber.value);
        });
        new Insertion.Bottom(this.formAdditional, this.fieldTemplate.evaluate({name:'trackingnumbers', value:trackingnumbers}));
        return $super();
    }
});

function xtentoOnClickJs(element) {
    if (element && element.parentNode && element.parentNode.parentNode && element.parentNode.parentNode.childNodes[1] && element.parentNode.parentNode.childNodes[1].childNodes[1] && sales_order_gridJsObject && sales_order_grid_massactionJsObject) {
        sales_order_gridJsObject.setCheckboxChecked(element.parentNode.parentNode.childNodes[1].childNodes[1], true);
        sales_order_grid_massactionJsObject.setCheckbox(element.parentNode.parentNode.childNodes[1].childNodes[1]);
    }
}