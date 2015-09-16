var addParamsToHash = false;

AmConfigurableData = Class.create();
AmConfigurableData.prototype = 
{
    textNotAvailable : "",
    
    mediaUrlMain : "",

    optionProducts : null,
    
    optionDefault : new Array(),
    
    oneAttributeReload : false,

    isResetButton : false,
    
    imageContainer : '.product-img-box',
    
    initialize : function(optionProducts)
    {
        this.optionProducts = optionProducts;
    },
    //special for simple price
    reloadOptions : function()
    {
        if ('undefined' != typeof(spConfig) && spConfig.settings)
        {
            spConfig.settings.each(function(select){
                if (select.enable) {
                    spConfig.reloadOptionLabels(select);
                }    
            });    
        }
    },
     
    hasKey : function(key)
    {
        return ('undefined' != typeof(this.optionProducts[key]));
    },
    
    getData : function(key, param)
    {
        if (this.hasKey(key) && 'undefined' != typeof(this.optionProducts[key][param]))
        {
            return this.optionProducts[key][param];
        }
        return false;
    },
    
    saveDefault : function(param, data)
    {
        this.optionDefault['set'] = true;
        this.optionDefault[param] = data;
    },
    
    getDefault : function(param)
    {
        if ('undefined' != typeof(this.optionDefault[param]))
        {
            return this.optionDefault[param];
        }
        return false;
    }
}
// extension Code End

Product.Config.prototype.amOrig_resetChildren = Product.Config.prototype.resetChildren;
Product.Config.prototype.resetChildren = function(element){
    this.amOrig_resetChildren(element);
    if('undefined' != typeof(Product.ConfigurableSwatches)) {
        if (element.childSettings) {
            for (var i = 0; i < element.childSettings.length; i++) {
                element.childSettings[i].selectedIndex = 0;
                element.childSettings[i].disabled = true;
                if (element.config) {
                    this.state[element.config.id] = false;
                }
            }
        }
    }

    this.processEmpty();
}

Product.Config.prototype.fillSelect = function(element){
    var attributeId = element.id.replace(/[a-z]*/, '');
    var options     = this.getAttributeOptions(attributeId);
    var savedValue  = element.value;
	this.clearSelect(element);
    element.options[0] = new Option(this.config.chooseText, '');

    if('undefined' != typeof(AmTooltipsterObject)) {
        AmTooltipsterObject.load();
    }

    var prevConfig = false;
    if(element.prevSetting){
        prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
    }

    if(options) {
        if ($('amconf-images-' + attributeId))
        {
            $('amconf-images-' + attributeId).parentNode.removeChild($('amconf-images-' + attributeId));
        }
            
        if (this.config.attributes[attributeId].use_image)
        {
            holder = element.parentNode;
            holderDiv = document.createElement('div');
            holderDiv = $(holderDiv); // fix for IE
            holderDiv.addClassName('amconf-images-container');
            holderDiv.id = 'amconf-images-' + attributeId;
            holder.insertBefore(holderDiv, element);
        }
        // extension Code End
        
        var index = 1, key = '';
        this.settings.each(function(select, ch){
            if (parseInt(select.value))
            {
                key += select.value + ',';   
            }
        });
        for(var i=0;i<options.length;i++){
            var allowedProducts = [];
            if(prevConfig) {
                for(var j=0;j<options[i].products.length;j++){
                    if(prevConfig.config && prevConfig.config.allowedProducts
                        && prevConfig.config.allowedProducts.indexOf(options[i].products[j])>-1){
                        allowedProducts.push(options[i].products[j]);
                    }
                }
            } else {
                allowedProducts = options[i].products.clone();
            }

            if(allowedProducts.size()>0)
            {
                if (this.config.attributes[attributeId].use_image)
                {
                    var imgContainer = document.createElement('div');
                    imgContainer = $(imgContainer); // fix for IE
                    imgContainer.addClassName('amconf-image-container');
                    imgContainer.id = 'amconf-images-container-' + attributeId;
                    imgContainer.style.float = 'left';
                    holderDiv.appendChild(imgContainer);
                    if (options[i].color) {
                        var width  = parseInt(this.config.attributes[attributeId].config.small_width)  ? parseInt(this.config.attributes[attributeId].config.small_width): 50;
                        var height = parseInt(this.config.attributes[attributeId].config.small_height) ? parseInt(this.config.attributes[attributeId].config.small_height): 50;
                        var div = document.createElement('div');
                        div.setStyle({
                            width: width + 'px',
                            height: height + 'px',
                            background: '#' + options[i].color
                        });
                        imgContainer.appendChild(div);
                        div.id = 'amconf-image-' + options[i].id;

                        var keyOpt = key +  options[i].id;
                        if(typeof confData != 'undefined' && confData.getData(keyOpt, 'not_is_in_stock')){
                            div.addClassName('amconf-image-outofstock');
                            var hr = document.createElement('hr');
                            hr = $(hr); // fix for IE
                            hr.addClassName('amconf-hr');
                            hr.writeAttribute("noshade");
                            hr.writeAttribute("size", 4);
                            hr.style.width =   Math.sqrt(width*width + height*height) + 1 + 'px';
                            hr.style.top =   height/2  + 'px';
                            hr.style.left =   -(Math.sqrt(width*width + height*height) - width)/2 + 2 + 'px';
                            var angle  = Math.atan(height/width);

                            hr.style.transform = "rotate(" + Math.floor(180-angle * 180/ Math.PI)+ "deg)";
                            imgContainer.appendChild(hr);

                            hr.observe('click', this.configureHr.bind(this));
                        }

                        div.observe('click', this.configureImage.bind(this));
                        if(this.config.attributes[attributeId].config && this.config.attributes[attributeId].config.use_tooltip != "0" && 'undefined' != typeof(jQuery)){
                            var amcontent = "";
                            width  = parseInt(this.config.attributes[attributeId].config.big_width) ? parseInt(this.config.attributes[attributeId].config.big_width) : 100;
                            height = parseInt(this.config.attributes[attributeId].config.big_height)? parseInt(this.config.attributes[attributeId].config.big_height): 100;

                            switch (this.config.attributes[attributeId].config.use_tooltip) {
                                case "1":
                                    amcontent = '<div class="amtooltip-label">' + options[i].label + '</div>';
                                    break;
                                case "2":
                                    amcontent = '<div class="amtooltip-img"><div style="width: ' + width + 'px; height:' + height + 'px; background: #' + options[i].color + '"></div></div>';
                                    break;
                                case "3":
                                    amcontent = '<div class="amtooltip-img"><div style="width: ' + width + 'px; height:' + height + 'px; background: #' + options[i].color + '"></div><div class="amtooltip-label">' + options[i].label + '</div>';
                                    break;
                            }
                            try{
                                jQuery(div).tooltipster({
                                    content: jQuery(amcontent),
                                    theme: 'tooltipster-light',
                                    animation: 'grow',
                                    touchDevices: false,
                                    position: "top"
                                });
                            }
                            catch(exc){
                                console.debug(exc);
                            }
                        }
                    }


                    else {
                        var image = document.createElement('img');
                        image = $(image); // fix for IE
                        image.id = 'amconf-image-' + options[i].id;
                        image.src = options[i].image;
                        image.alt = options[i].label;
                        image.title = options[i].label;
                        image.width = parseInt(this.config.attributes[attributeId].config.small_width)  ? parseInt(this.config.attributes[attributeId].config.small_width): 50;
                        image.height = parseInt(this.config.attributes[attributeId].config.small_height)? parseInt(this.config.attributes[attributeId].config.small_height): 50;
                        // for out of stock options
                        var keyOpt = key +  options[i].id;
                        if(typeof confData != 'undefined' && confData.getData(keyOpt, 'not_is_in_stock')){
                            image.addClassName('amconf-image-outofstock');
                            var hr = document.createElement('hr');
                            hr = $(hr); // fix for IE
                            hr.addClassName('amconf-hr');
                            hr.writeAttribute("noshade");
                            hr.writeAttribute("size", 4);

                            imgContainer.appendChild(hr);

                            hr.observe('click', this.configureHr.bind(this));
                        }

                        image.observe('click', this.configureImage.bind(this));

                        if(this.config.attributes[attributeId].config && this.config.attributes[attributeId].config.use_tooltip != "0" && 'undefined' != typeof(jQuery)){
                            var amcontent = "";
                            switch (this.config.attributes[attributeId].config.use_tooltip) {
                                case "1":
                                    amcontent = '<div class="amtooltip-label">' + options[i].label + '</div>';
                                    break;
                                case "2":
                                    amcontent = '<div class="amtooltip-img"><img src="' + options[i].bigimage + '"/></div>';
                                    break;
                                case "3":
                                    amcontent = '<div class="amtooltip-img"><img src="' + options[i].bigimage + '"/></div><div class="amtooltip-label">' + options[i].label + '</div>';
                                    break;
                            }
                            try{
                                jQuery(image).tooltipster({
                                    content: jQuery(amcontent),
                                    theme: 'tooltipster-light',
                                    animation: 'grow',
                                    touchDevices: false,
                                    position: "top"
                                });
                            }
                            catch(exc){
                                console.debug(exc);
                            }
                        }
                        imgContainer.appendChild(image);

                        image.onload = function(){
                            var optId = this.id.replace(/[a-z-]*/, '');
                            var maxW = this.getWidth();
                            var maxH = this.getHeight();
                            if(optId) {
                                var title = $('amconf-images-title-' + optId);
                                if(title && title.getWidth() && title.getWidth() > maxW) {
                                    maxW = title.getWidth();
                                }

                            }
                            if(this.parentNode){
                                this.parentNode.style.width =   maxW + 'px';
                            }
                            var hr =this.parentNode.getElementsByTagName('hr')[0];
                            if(hr){
                                hr.style.width =   Math.sqrt(maxW*maxW + maxH*maxH) + 1 + 'px';
                                hr.style.top =   maxH/2  + 'px';
                                hr.style.left =   -(Math.sqrt(maxW*maxW + maxH*maxH) - maxW)/2 + 2 + 'px';
                                var angle  = Math.atan(maxH/maxW);

                                hr.style.transform = "rotate(" + Math.floor(180 - angle * 180/ Math.PI)+ "deg)";
                            }
                            if(this.parentNode.childElements()[1] && !this.parentNode.childElements()[1].hasClassName('amconf-image')){
                                this.parentNode.childElements()[1].style.width =   maxW + 'px';
                            }
                        };
                    }
                    if(this.config.attributes[attributeId].config && this.config.attributes[attributeId].config.use_title != "0"){
                        var amImgTitle = document.createElement('div');
                        amImgTitle = $(amImgTitle); // fix for IE
                        amImgTitle.addClassName('amconf-image-title');
                        amImgTitle.id = 'amconf-images-title-' + options[i].id;
                        amImgTitle.setStyle({
                            fontWeight : 600,
                            textAlign : 'center',
                            paddingTop: '7px'
                        });
                        amImgTitle.innerHTML = options[i].label;
                        imgContainer.appendChild(amImgTitle);
                    }
                }
                // extension Code End
                
                options[i].allowedProducts = allowedProducts;
                element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);    
                element.options[index].config = options[i];
                index++;
            }
        }
        if(this.config.attributes[attributeId].use_image) {
            if(this.config.attributes[attributeId].enable_carousel == "1"){
                $holderDiv = jQuery(holderDiv);
                var imageWidth = parseInt(this.config.attributes[attributeId].config.small_width)  ? parseInt(this.config.attributes[attributeId].config.small_width): 50;
                var count = Math.floor(($holderDiv.width() - 50) / (imageWidth + 5) );
                var visibleItems = count > 0? count: 3;
                $holderDiv.parent().children('.caroufredsel_wrapper, .am-swatch-arrow').remove();

                if( $holderDiv.children().length > visibleItems){
                    var carouHeight = parseInt(this.config.attributes[attributeId].config.small_height)  ? parseInt(this.config.attributes[attributeId].config.small_height): 50;

                    $holderDiv.children().css('margin', '6px 3px 9px 3px');
                    carouHeight += 15;

                    var nextLink = jQuery('<div class="am-swatch-next am-swatch-arrow"></div>');
                    $holderDiv.parent().append(nextLink);

                    var prevLink = jQuery('<div class="am-swatch-prev am-swatch-arrow"></div>');
                    $holderDiv.parent().append(prevLink);
                    $holderDiv.parent().children('.am-swatch-arrow').css('top', carouHeight/2 - 12);

                    AmcarouFredSelObject.load();
                    $holderDiv.carouFredSel({
                        circular: false,
                        infinite: false,
                        items: {
                            visible : visibleItems,
                            minimum : visibleItems
                        },
                        scroll: {
                            items       : 1,
                            fx          : 'directscroll',
                            duration    : 700,
                            pauseOnHover: true
                        },
                        auto: {
                            play: false
                        },
                        prev: {
                            button: prevLink
                        },
                        next: {
                            button: nextLink
                        }
                    });
                }
            }

            var lastContainer = document.createElement('div');
            lastContainer = $(lastContainer); // fix for IE
            lastContainer.setStyle({clear : 'both'});
            holderDiv.appendChild(lastContainer);
        }
        /*save value from previous step*/
        if(parseInt(savedValue) && element.select('[value="' + savedValue + '"]').length){
            element.value = savedValue;
            this.configureElement(element);
        }
    }
}

Product.Config.prototype.configureElement = function(element) 
{
    // extension Code
    optionId = element.value;
    if ($('amconf-image-' + optionId))
    {
        this.selectImage($('amconf-image-' + optionId));
    }
    else {
        attributeId = element.id.replace(/[a-z-]*/, '');
        if ($('amconf-images-' + attributeId))
        {
            $('amconf-images-' + attributeId).childElements().each(function(child){
                 child.childElements().each(function(children){
                    children.removeClassName('amconf-image-selected');
                 });
            });
        }
    }
    // extension Code End
    
    this.reloadOptionLabels(element);
    if(element.value){
        this.state[element.config.id] = element.value;
        if(element.nextSetting){
            element.nextSetting.disabled = false;
            this.fillSelect(element.nextSetting);
            this.resetChildren(element.nextSetting);
        }
    }
    else {
        // extension Code
        if(element.childSettings) {
            for(var i=0;i<element.childSettings.length;i++){
                attributeId = element.childSettings[i].id.replace(/[a-z-]*/, '');
                jQuery('#attribute' + attributeId).parent('.input-box').children('.caroufredsel_wrapper, .am-swatch-arrow').remove();
                if ($('amconf-images-' + attributeId))
                {
                    $('amconf-images-' + attributeId).parentNode.removeChild($('amconf-images-' + attributeId));
                }
            }
        }
        // extension Code End
        
        this.resetChildren(element);
        
        // extension Code
        if (this.settings[0].hasClassName('no-display'))
        {
            this.processEmpty();
        }
        // extension Code End
    }
    
    // extension Code
    var key = '';
    var stock = 1;
    this.settings.each(function(select, ch){
        // will check if we need to reload product information when the first attribute selected
        if (parseInt(select.value))
	    {
            key += select.value + ',';
            if(confData.getData(key.substr(0, key.length - 1), 'not_is_in_stock')) {
               stock = 0; 
            }  
        }
    });
    if (typeof confData != 'undefined') {
        confData.isResetButton = false;    
    }
    key = key.substr(0, key.length - 1);
    this.updateData(key);
    //<---- ---->
    if (typeof confData != 'undefined' && confData.useSimplePrice == "1")    {
        this.reloadSimplePrice(key); // replace price values with the selected simple product price
    }
    else    {
        this.reloadPrice(); // default behaviour
    }
    if(stock === 0){
        $$('.add-to-cart').each(function(elem) {
            elem.hide();
        });
    }
    else{
         $$('.add-to-cart').each(function(elem) {
            elem.show();
        });
    }
    // for compatibility with custom stock status extension:
    if ('undefined' != typeof(stStatus) && 'function' == typeof(stStatus.onConfigure))
    {
	    var keySt = '';
    	this.settings.each(function(select, ch){
                if (parseInt(select.value) || (!select.value && (!select.options[1] || !select.options[1].value))){
	            keySt += select.value + ',';   
	        }
		else {
		     keySt += select.options[1].value + ','; 
		}
    	});
	    keySt = keySt.substr(0, keySt.length - 1);
        stStatus.onConfigure(keySt, this.settings, key);
    }
	//Amasty code for Automatically select attributes that have one single value
    if(('undefined' != typeof(amConfAutoSelectAttribute) && amConfAutoSelectAttribute) ||('undefined' != typeof(amStAutoSelectAttribute) && amStAutoSelectAttribute)){
        var nextSet = element.nextSetting;
        if(nextSet && nextSet.options.length == 2 && !nextSet.options[1].selected && element && !element.options[0].selected){
            nextSet.options[1].selected = true;
            this.configureElement(nextSet);
        } 
    }
    if('undefined' != typeof(preorderState))
	    preorderState.update()

    /**add option label start*/
	var label = "";
	element.config.options.each(function(option){
		if(option.id == element.value) label = option.label;
	});
	if(label) label = " - " + label;
	var parent = element.parentNode.parentNode.previousElementSibling;
    if( typeof(parent) != 'undefined' && parent != null && parent.nodeName == "DT" && (conteiner = parent.select("label")[0])) {
		if( tmp = conteiner.select('span.amconf-label')[0]){
			tmp.innerHTML = label;
		}
		else{
			var tmp = document.createElement('span');
			tmp.addClassName('amconf-label');
			conteiner.appendChild(tmp);
			tmp.innerHTML = label;
		}			
	}
    /**add option label end*/

    /*add params to hash start*/
    if(addParamsToHash){
        var hash = window.location.hash;
        var attributeId = element.id.replace(/[a-z-]*/, '');

        if (hash.indexOf(attributeId + '=') >= 0) {
            var replaceText = new RegExp(attributeId + '=' + '.*');
            if(optionId) {
                hash = hash.replace(replaceText, attributeId + '=' + optionId);
            }
            else{
                hash = hash.replace(replaceText, "");
            }
        }
        else {
            if (hash.indexOf('#') >= 0) {
                hash = hash + '&' + attributeId + '=' + optionId;
            }
            else {
                hash = hash + '#' + attributeId + '=' + optionId;
            }
        }
        window.location.replace(window.location.href.split('#')[0] + hash);
    }
    /*add params to hash end*/
}

Product.Config.prototype.amPreselectOneOptionAttribute  = function () {
    if(('undefined' != typeof(amConfAutoSelectAttribute) && amConfAutoSelectAttribute) ||('undefined' != typeof(amStAutoSelectAttribute) && amStAutoSelectAttribute)){
        var select  = this.settings[0];
        if(select && select.options.length == 2 && !select.options[1].selected){
            select.options[1].selected = true;
            this.configureElement(select);
        }
    }
}
    
// these are new methods introduced by the extension
// extension Code
Product.Config.prototype.configureHr = function(event){
    var element = Event.element(event);
    element.nextSibling.click();
}


Product.Config.prototype.configureImage = function(event){
    var element = Event.element(event);
    attributeId = element.parentNode.id.replace(/[a-z-]*/, '');
    optionId = element.id.replace(/[a-z-]*/, '');
    
    this.selectImage(element);
    
    $('attribute' + attributeId).value = optionId;
    this.configureElement($('attribute' + attributeId));
    /* fix for sm ajax cart*/
    if($$('body.sm_market').length > 0){
        jQuery('#attribute' + attributeId).trigger("change");
    }
}

Product.Config.prototype.selectImage = function(element)
{
    attributeId = element.parentNode.id.replace(/[a-z-]*/, '');
    $('amconf-images-' + attributeId).childElements().each(function(child){
        child.childElements().each(function(children){
            children.removeClassName('amconf-image-selected');
        });
    });
    element.addClassName('amconf-image-selected');
}

Product.Config.prototype.processEmpty = function()
{
    $$('.super-attribute-select').each(function(select) {
        var attributeId = select.id.replace(/[a-z]*/, '');
        if (select.disabled)
        {
            if ($('amconf-images-' + attributeId))
            {
                $('amconf-images-' + attributeId).parentNode.removeChild($('amconf-images-' + attributeId));
            }
            holder = select.parentNode;
            holderDiv = document.createElement('div');
            holderDiv.addClassName('amconf-images-container');
            holderDiv.id = 'amconf-images-' + attributeId;
            if ('undefined' != typeof(confData))
            {
            	holderDiv.innerHTML = confData.textNotAvailable;
            } else 
            {
            	holderDiv.innerHTML = "";
            }
            holder.insertBefore(holderDiv, select);
        } else if (!select.disabled && !$(select).hasClassName("no-display")) {
            var element = $(select.parentNode).select('#amconf-images-' + attributeId)[0];
            if (typeof confData != 'undefined' && typeof element != 'undefined' && element.innerHTML == confData.textNotAvailable){
                element.parentNode.removeChild(element);
            }
        }
    }.bind(this));
}

Product.Config.prototype.clearConfig = function()
{
    this.settings[0].value = "";
    if (typeof confData != 'undefined')
    	confData.isResetButton = true;
    this.configureElement(this.settings[0]);
    $$('span.amconf-label').each(function (el){
	    el.remove();
    })
    return false;
}



//start code for reload simple price

Product.Config.prototype.reloadSimplePrice = function(key)
{
     if ('undefined' == typeof(confData))
    {
        return false;
    }
    
    var container;
    var result = false;
    if (confData.hasKey(key))
    {
        // convert div.price-box into price info container
        // top price box
        if (confData.getData(key, 'price_html'))
        {
	        $$('.product-shop .price-box, #product_addtocart_form .price-box').each(function(container)
            {
                if (!confData.getDefault('price_html'))
                {
                    confData.saveDefault('price_html', container.innerHTML);
                }
                container.addClassName('amconf_price_container');
            }.bind(this));


            $$('.product-shop .tax-details, .product-shop .tier-prices').each(function(container)
            {
                container.remove();
            }.bind(this));
   
            $$('.amconf_price_container').each(function(container)
            {
		        container.outerHTML = confData.getData(key, 'price_html');	
	        }.bind(this));        
        }
        
        // bottom price box
        if (confData.getData(key, 'price_clone_html'))
        {
            $$('.product-options-bottom .price-box').each(function(container)
            {
                if (!confData.getDefault('price_clone_html'))
                {
                    confData.saveDefault('price_clone_html', container.innerHTML);
                }
                container.addClassName('amconf_price_clone_container');
            }.bind(this));

            $$('.product-options-bottom .tax-details, .product-options-bottom .tier-prices').each(function(container)
            {
                container.remove();
            }.bind(this));
            
            $$('.amconf_price_clone_container').each(function(container)
            {
		        container.outerHTML = confData.getData(key, 'price_clone_html');	
	        }.bind(this));

        }
        
        // function return value
        if (confData.getData(key, 'price'))
        {
            result = confData.getData(key, 'price');
        }
    } 
    else 
    {
        // setting values of default product
        if (true == confData.getDefault('set'))
        {
            // restore price info containers into default price-boxes
            if (confData.getDefault('price_html'))
            {
		        $$('.product-shop .price-box, #product_addtocart_form .price-box').each(function(container)
                {
                    container.addClassName('amconf_price_container');
                }.bind(this));
		        $$('.product-shop .tax-details, .product-shop .tier-prices').each(function(container)
                {
                    container.remove();
                }.bind(this));
                          
                $$('.amconf_price_container').each(function(container)
            	{
			        container.innerHTML  = confData.getDefault('price_html');
                	container.removeClassName('amconf_price_container');	
	    	    }.bind(this));
            }
            
            if (confData.getDefault('price_clone_html'))
            {
		        $$('.product-options-bottom .price-box').each(function(container)
                {
                    container.addClassName('amconf_price_clone_container');
                }.bind(this));

                $$('.amconf_price_clone_container').each(function(container){
			        container.innerHTML = confData.getDefault('price_clone_html');
                	container.removeClassName('amconf_price_clone_container');	
	    	    }.bind(this));
                
            }
            
            // function return value
            if (confData.getDefault('price'))
            {
                result = confData.getDefault('price');
            }
        }
    }
    
    return result; // actually the return value is never used
}

Product.Config.prototype.getOptionLabel = function(option, price){
    var price = parseFloat(price);
    if (this.taxConfig.includeTax) {
        var tax = price / (100 + this.taxConfig.defaultTax) * this.taxConfig.defaultTax;
        var excl = price - tax;
        var incl = excl*(1+(this.taxConfig.currentTax/100));
    } else {
        var tax = price * (this.taxConfig.currentTax / 100);
         var excl = price;
         var incl = excl + tax;
    }
    if (this.taxConfig.showIncludeTax || this.taxConfig.showBothPrices) {
        price = incl;
    } else {
        price = excl;
    }
    var str = option.label;
	return str;
    if(price){
        if('undefined' != typeof(confData) && confData.useSimplePrice == "1" && confData['optionProducts'] && confData['optionProducts'][option.id] && confData['optionProducts'][option.id]['price']) {
            str+= ' ' + this.formatPrice(confData['optionProducts'][option.id]['price'], true);
            pos = str.indexOf("+");
            str = str.substr(0, pos) + str.substr(pos + 1, str.length);
        }
        else {
            if (this.taxConfig.showBothPrices) {
                str+= ' ' + this.formatPrice(excl, true) + ' (' + this.formatPrice(price, true) + ' ' + this.taxConfig.inclTaxTitle + ')';
            } else {
                str+= ' ' + this.formatPrice(price, true);
            }
        }
    }
    else {
        if('undefined' != typeof(confData) && confData.useSimplePrice == "1" && confData['optionProducts'] && confData['optionProducts'][option.id] && confData['optionProducts'][option.id]['price']) {
            str+= ' ' + this.formatPrice(confData['optionProducts'][option.id]['price'], true);
            pos = str.indexOf("+");
            str = str.substr(0, pos) + str.substr(pos + 1, str.length);
        }    
    }
    return str;
}

Event.observe(window, 'load', function(){
    if ('undefined' != typeof(confData) && confData.useSimplePrice == "1")
    {
        confData.reloadOptions();
    }
    if('undefined' != typeof(spConfig) ){
        spConfig.amPreselectOneOptionAttribute();
    }
});

Product.Config.prototype.updateData = function(key)
{
    if ('undefined' == typeof(confData) || key == this.currentUpdateDataKey ) {
        return false;
    }
    else{
        this.currentUpdateDataKey = key;
    }
  
    if (confData.hasKey(key))
    { 
        // getting values of selected configuration
        this.updateSimpleData('name', '.product-name h1', key);
        this.updateSimpleData('short_description', '.short-description div', key);
        this.updateSimpleData('description', '.box-description div', key); 
        this.updateSimpleData('attributes', '#product-attribute-specs-table', key);

        this.reloadImageBlock(confData.getData(key, 'media_url'));
        decorateTable('product-attribute-specs-table');
    } else  {
        // setting values of default product
        if (true == confData.getDefault('set'))
        {
            this.getDefaultSimpleData('name', '.product-name h1');
            this.getDefaultSimpleData('short_description', '.short-description div');
            this.getDefaultSimpleData('description', '.box-description div');
            this.getDefaultSimpleData('attributes', '#product-attribute-specs-table');

            this.reloadImageBlock(confData.mediaUrlMain);
            decorateTable('product-attribute-specs-table');
        }
    }
}

Product.Config.prototype.reloadImageBlock = function(url)
{
    if(!url || url == this.reloadImageBlockUrl ){
        return false;
    }
    else{
        this.reloadImageBlockUrl = url;
    }

    // should reload images
    var tmpContainer = $$(confData.imageContainer).first();
    if(!tmpContainer){
        console.debug("Please set correctly CSS selector at module configuration!");
    }
    else{
        new Ajax.Updater(tmpContainer, url, {
            evalScripts: true,
            onComplete: function()
            {
                if('undefined' != typeof(AmZoomerObj)) {
                    if($$('.zoomContainer')[0]) $$('.zoomContainer')[0].remove();
                    AmZoomerObj.loadZoom();
                }
                jQuery('.cloud-zoom, .cloud-zoom-gallery').CloudZoom();
            }
        });
    }
}

Product.Config.prototype.updateSimpleData= function(type, selector, key){
    if (confData.getData(key, type))
    {
        $$(selector).each(function(container){
            if(container.hasClassName('data-table')) container = container.parentNode;
            if (!confData.getDefault(type))
            {
                confData.saveDefault(type, container.innerHTML);
            }
            if(confData.getData(key, type) != "") container.innerHTML = confData.getData(key, type);
        }.bind(this));
    }
}

Product.Config.prototype.getDefaultSimpleData= function(type, selector){
    if (confData.getDefault(type))
    {
        $$(selector).each(function(container){
            if(container.hasClassName('data-table')) container = container.parentNode;
            container.innerHTML = confData.getDefault(type);
        }.bind(this));
    }
}

// extension Code End