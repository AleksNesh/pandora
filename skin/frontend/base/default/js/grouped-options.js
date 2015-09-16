/**
 * Brim LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brim LLC Commercial Extension License
 * that is bundled with this package in the file license.pdf.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.brimllc.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@brimllc.com so we can send you a copy immediately.
 *
 * @category   Brim
 * @package    Brim_Groupedoptions
 * @copyright  Copyright (c) 2011-2012 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */

var GroupedOptions = {
    simple : {},
    configurable : {}
};

// Modified version of Product.Options found in options.phtml
// This class handles multiple products on a single page.
Product.GroupedSimpleOptions = Class.create();
Product.GroupedSimpleOptions.prototype = {
    initialize : function(config, optionsPrice, optionsClass){
        this.config = config;
        this.optionsPrice = optionsPrice;
        this.optionsClass = optionsClass;
        this.reloadPrice();

        // register self with the global object
        GroupedOptions.simple[optionsPrice.productId] = this;
    },
    reloadPrice : function(){
        var object = this;
        var price = new Number();
        var config = this.config;
        skipIds = [];
        $$('.'+this.optionsClass).each(function(element){
            var optionId = 0;
            element.name.sub(/\[([0-9]+)\]\[([0-9]+)\]/, function(match){
                optionId = match[2]; // expects the name to be in the form super_options[product_id][option_id]
            });

            if (config[optionId]) {
                if (element.type == 'checkbox' || element.type == 'radio') {
                    if (element.checked) {
                        if (typeof config[optionId][element.getValue()] != 'undefined') {
                            price += object._getOptionPrice(config[optionId][element.getValue()]);
                        }
                    }
                } else if(element.hasClassName('datetime-picker') && !skipIds.include(optionId)) {
                    dateSelected = true;
                    $$('.product-custom-option[id^="superoptions_' + optionId + '"]').each(function(dt){
                        if (dt.getValue() == '') {
                            dateSelected = false;
                        }
                    });
                    if (dateSelected) {
                        price += object._getOptionPrice(config[optionId]);
                        skipIds[optionId] = optionId;
                    }
                } else if(element.type == 'select-one' || element.type == 'select-multiple') {
                    if (element.options) {
                        $A(element.options).each(function(selectOption){
                            if (selectOption.selected) {
                                if (typeof config[optionId][selectOption.value] != 'undefined') {
                                    price += object._getOptionPrice(config[optionId][selectOption.value]);
                                }
                            }
                        });
                    }
                } else {
                    if (element.getValue().strip() != '') {
                        price += object._getOptionPrice(config[optionId]);
                    }
                }
            }
        });
        try {
            var productId = this.optionsPrice.productId;
            if (typeof GroupedOptions.configurable[productId] != 'undefined') {
                // cross set pricing changes.
                var confOptionsPrice = GroupedOptions.configurable[productId].optionsPrice;
                if (typeof confOptionsPrice.optionPrices['config'] != 'undefined') {
                    this.optionsPrice.changePrice('config', confOptionsPrice.optionPrices['config']);
                }
                confOptionsPrice.changePrice('options', price);
            }

            this.optionsPrice.changePrice('options', price);
            this.optionsPrice.reload();
        } catch (e) {
        }
    },
    _getOptionPrice: function(optionConfig) {
        var price = 0;
        if (typeof optionConfig == 'object') {
            price += parseFloat(optionConfig.priceValue);
        } else {
            price += parseFloat(optionConfig);
        }
        return price;
    }
}

// This new js class extends the Product.Config class to allow
// us to specify a optionsPrice. This allows us to maintain multiple
// instances of this class on a single page.
Product.GroupedConfig = Class.create(Product.Config, {
    initialize: function(config, productId){
        this.config     = config;
        this.taxConfig  = this.config.taxConfig;
        this.settings   = $$('#product_addtocart_wrapper_' + productId + ' .super-attribute-select');
        this.state      = new Hash();
        this.priceTemplate = new Template(this.config.template);
        this.prices     = config.prices;

        this.settings.each(function(element){
            Event.observe(element, 'change', this.configure.bind(this))
        }.bind(this));

        // register self with the global object
        GroupedOptions.configurable[productId] = this;

        // fill state
        this.settings.each(function(element){
            var attributeId = element.id.replace(/[a-z]*/, '');
            if(attributeId && this.config.attributes[attributeId]) {
                element.config = this.config.attributes[attributeId];
                element.attributeId = attributeId;
                this.state[attributeId] = false;
            }
        }.bind(this))

        // Init settings dropdown
        var childSettings = [];
        for(var i=this.settings.length-1;i>=0;i--){
            var prevSetting = this.settings[i-1] ? this.settings[i-1] : false;
            var nextSetting = this.settings[i+1] ? this.settings[i+1] : false;
            if(i==0){
                this.fillSelect(this.settings[i])
            }
            else {
                this.settings[i].disabled=true;
            }
            $(this.settings[i]).childSettings = childSettings.clone();
            $(this.settings[i]).prevSetting   = prevSetting;
            $(this.settings[i]).nextSetting   = nextSetting;
            childSettings.push(this.settings[i]);
        }

        // try retireve options from url
        var separatorIndex = window.location.href.indexOf('#');
        if (separatorIndex!=-1) {
            var paramsStr = window.location.href.substr(separatorIndex+1);
            this.values = paramsStr.toQueryParams();
            this.settings.each(function(element){
                var attributeId = element.attributeId;
                element.value = (typeof(this.values[attributeId]) == 'undefined')? '' : this.values[attributeId];
                this.configureElement(element);
            }.bind(this));
        }
    },
    setOptionsPrice: function(optionsPrice){
        this.optionsPrice = optionsPrice;
    },
    reloadPrice: function($super) {
        optionsPrice = this.optionsPrice;
        var price = $super.call(this);

        var productId = this.optionsPrice.productId;
        if (typeof GroupedOptions.simple[productId] != 'undefined') {
            // cross set pricing changes.
            var simpleOptionsPrice =  GroupedOptions.simple[productId].optionsPrice;
            if (typeof simpleOptionsPrice.optionPrices['options'] != 'undefined') {
                this.optionsPrice.changePrice('options', simpleOptionsPrice.optionPrices['options']);
            }
            simpleOptionsPrice.changePrice('config', price);
        }

        return price;
    }
});

Validation.addAllThese([['go-qty-required-entry', 'This is a required field.', function(v, elm) {
    try {
        var result = /[a-z_-]+\[(\d+)\]\[\d+\]/gi.exec(elm.name);
        if (result[1]) {
            var qtyElm = $$('[name="super_group[' + result[1] + ']"]').first();
            if (qtyElm.value > 0) {
                return !Validation.get('IsEmpty').test(v);
            }
        }
    } catch(e) { console.log(e); }

    return true;
}],
['go-qty-one-required', 'Please select one.', function(v, elm){
    try {
        var result = /[a-z_-]+\[(\d+)\]\[\d+\]/gi.exec(elm.name);
        if (result[1]) {
            var qtyElm = $$('[name="super_group[' + result[1] + ']"]').first();
            if (qtyElm.value > 0) {
                var p = elm.parentNode.parentNode;
                var options = p.getElementsByTagName('INPUT');
                return $A(options).any(function(elm) {
                    return $F(elm);
                });
            }
        }
    } catch(e) { console.log(e); }

    return true;
}]
]);