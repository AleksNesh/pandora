/**************************** Google Shopping Feed CONFIGURABLE PRODUCT **************************/
GSF_Configurable = Class.create();

GSF_Configurable.prototype = {

    initialize: function(){

        this.selected = {};
        this.prices = {};

        var self = this;
        var n = 1;
        $$('.super-attribute-select').each(function (element) {
            window.setTimeout(self.autoUpdate(element), 300 * n++);
        });
    },

    autoUpdate: function(element) {
        var attributeId = element.id.replace(/[a-z]*/, '');
        this.registerEvents(attributeId);
        this.selectOptions(attributeId);
    },

    selectOptions: function(attributeId) {
        var Params = document.URL.toQueryParams();
        var select = $('attribute' + attributeId);
        if (select != null && typeof select != 'undefined') {
            for (var i = 0; i < select.options.length; i++) {
                if (select.options[i].value == Params[attributeId]) {
                    select.selectedIndex = i;
                    if ("createEvent" in document) {
                        var evt = document.createEvent("HTMLEvents");
                        evt.initEvent("change", false, true);
                        select.dispatchEvent(evt);
                    }
                    else {
                        select.fireEvent("onchange");
                    }
                }
            }
        }
    },

    registerEvents: function (attributeId) {
        var self = this;

        // Select on Change
        var select = $('attribute' + attributeId);
        if (typeof select != 'undefined' && select != null) {
            Event.observe(select, 'change', function(event) {
                option_id = this.options[this.selectedIndex].value;
                self.update(attributeId, option_id);
            });
        }

        // Swatch on change
        $$('.swatch-link-' + attributeId).each(function (element) {
            Event.observe(element, 'click', function(event) {
                option_id = this.id.replace(/[a-z]*/, '');
                self.update(attributeId, option_id);
            });
        });
    },

    update: function(attributeId, option_id) {
        var self = this;

        if (typeof spConfig !== 'undefined') {
            var options = spConfig.getAttributeOptions(attributeId);
            var selected_option = null;

            // Find out which option has been selected
            options.each(function (option) {
                if (option.id == option_id) {
                    selected_option = option;
                    return false;
                }
            });

            if (selected_option != null) {

                // Select the appropriate swatch
                $$('.configurable-swatch-list').each(function(elem) {
                    var obj = elem.select('[id="option'+selected_option.id+'"]');
                    if (obj.size() > 0) {
                        obj[0].addClassName('selected');
                    }
                });

                // Fix labels not displaying on selected swatches
                if (typeof selected_option.attr.code != 'undefined' && typeof selected_option.label != 'undefined') {
                    $('select_label_'+ selected_option.attr.code).innerHTML = selected_option.label;
                }

                // Determine the corresponded product id, from the selected options
                var counts = [];
                self.selected[selected_option.id] = selected_option.products;

                for (var key in self.selected) {
                    self.selected[key].each(function(id) {
                        if (id in counts) {
                            counts[id] += 1;
                        } else {
                            counts[id] = 0;
                        }
                    });
                }
                var product_id = counts.indexOf(counts.max());

                // Update google remarketing tag
                if (typeof google_tag_params !== 'undefined' && google_tag_params != null) {

                    var old_prodid = google_tag_params.ecomm_prodid;
                    var old_prodid = google_tag_params.ecomm_prodid;

                    if (typeof gsf_associated_products != 'undefined') {
                        google_tag_params.ecomm_prodid = gsf_associated_products[product_id];
                    } else {
                        google_tag_params.ecomm_prodid = product_id;
                    }

                    self.prices[selected_option.id] = parseFloat(selected_option.price);
                    var delta = 0;
                    for (var key in self.prices) {
                        delta += self.prices[key];
                    }
                    google_tag_params.ecomm_totalvalue = (parseFloat(spConfig.config.basePrice) + parseFloat(delta)).toFixed(2);
                    google_tag_params.ecomm_pvalue = google_tag_params.ecomm_totalvalue;


                    // Call Google doubleclick service
                    if (typeof google_custom_params != 'undefined') {
                        google_custom_params = google_tag_params;
                    }
                    var div = $('gsf_associated_products');
                    if (old_prodid != google_tag_params.ecomm_prodid && typeof google_conversion_id != 'undefined' && typeof div != 'undefined') {
                        var elem = document.createElement("img");
                        elem.setAttribute("src", "//googleads.g.doubleclick.net/pagead/viewthroughconversion/"+google_conversion_id+"/?value=0&amp;guid=ON&amp;script=0");
                        elem.setAttribute("height", "1"); elem.setAttribute("width", "1"); elem.setAttribute("style", "border-style:none;");
                        div.appendChild(elem);
                    }
                }
            }
        }
    }
};

Event.observe(window, 'load', function () {
    var gsf_configurable = new GSF_Configurable();
});