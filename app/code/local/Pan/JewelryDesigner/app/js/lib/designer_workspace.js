/* jshint laxcomma: true */

var PAN = PAN || {};

(function(){
  var DesignerWorkspace = {}
    , dw                = DesignerWorkspace
    , rDist             = 60000 // Distance beyond which don't rotate
    , lockDist          = 50    // Distance beyond which don't lock
    , dev               = { useLargeBracelet: false, scale: false }
    , buffer            = 1.5;

  dw.trashDist              = 42;
  dw.trashCordsDefault      = { x: 540, y: 590 };
  dw.bracelet;
  dw.designData;

  dw.windowOrigin           = window.location.protocol + '//' + window.location.host;

  dw.loggedIn               = false;
  dw.customerLoggedIn       = false;
  dw.snapshotImgData;

  dw.currentDesignId;

  dw.fade                   = 0.5;
  dw.beads                  = {};
  dw.placeholderBraceletSrc = '/app/code/local/Pan/JewelryDesigner/app/img/placeholder_bracelet.png';
  dw.placeholderBracelet    = new Image();
  dw.Zone                   = PAN.Zone;

  // default (assumed) center of the design canvas workspace
  dw.center                 = { x: 287, y: 301 };

  // use a timeout to wait for the DOM element to load
  // and then dynamically find the center of the design canvas
  setTimeout(function(){ dw.findWorkspaceCenter(); }, 2000);

  /**
   * Dynamically find the center of the workspace via JavaScript
   * so that the bracelet's radius and path the beads travel on
   * matches up as expected
   */
  dw.findWorkspaceCenter = function(){
    var claspHeightInHalf = 10
      , workspace         = jQuery('.design-canvas')
      , workspaceWidth    = workspace.width()
      , workspaceHeight   = workspace.height()
      , centerX           = Math.ceil(workspaceWidth / 2)
      , centerY           = Math.ceil( (workspaceHeight / 2) - claspHeightInHalf)
      , inAdminArea       = false;

    if(jQuery('.designer_app_wrapper').hasClass('admin')) {
      inAdminArea = true;
    }


    // only update the dw.center position if we are actually able
    // to determine the workspace's width and height just in case
    // the timeout duration wasn't long enough
    if (workspaceWidth !== null && workspaceHeight !== null) {
      // update the DesignWorkspace.center point
      dw.center = { x: centerX, y: centerY };
    }
  };

  dw.init = function(preloaded) {
    dw.placeholderBracelet.onload = function() {
      preloaded || dw.setBracelet(null);
    };

    dw.initLoggedInValues();

    dw.placeholderBracelet.src = dw.placeholderBraceletSrc;

    setTimeout(function(){
      jQuery('#trash').move({
        regX: 0,
        regY: 0,
        x: jQuery('.design-canvas').width() - 33,
        y: 590
      }).background();
    }, 2000);
  };

  dw.isLoggedIn = function() {
    return dw.loggedIn;
  };

  dw.isCustomerLoggedIn = function() {
    return dw.customerLoggedIn;
  };

  dw.initLoggedInValues = function() {
    var apiUrl              = ''
      , inAdminArea         = false;

    if(jQuery('.designer_app_wrapper').hasClass('admin')) {
      inAdminArea = true;
      apiUrl = '/jewelrydesigner/adminhtml_api/authorize';
    } else {
      apiUrl = '/jewelrydesigner/api/authorize';
    }

    var adminId = (inAdminArea) ? jQuery('.designer_app_wrapper').data('admin-id') : null;
    var formKey = (inAdminArea) ? jQuery('.designer_app_wrapper').data('form-key') : null;

    jQuery.ajax({
        url: apiUrl
      , type: 'POST'
      , dataType: 'json'
      , data: {
          form_key: formKey
      }
    }).done(function(data){
      var loggedIn = data.logged_in;

      if(jQuery('.designer_app_wrapper').hasClass('frontend')) {
        dw.customerLoggedIn = loggedIn;
      }
      dw.loggedIn = loggedIn;
    });
  };

  dw.getCurrentBracelet = function() {
    return dw.bracelet;
  };


  dw.getBraceletCenter = function() {
    var bracelet = dw.getCurrentBracelet()
      , center = (bracelet !== null && bracelet !== undefined) ? bracelet.center() : dw.center;

    return center;
  };

  dw.getCurrentDesignId = function() {
    return dw.currentDesignId;
  };

  dw.setCurrentDesignId = function(designId) {
    dw.currentDesignId = designId;
  };


  dw.setBracelet = function(braceletConfig, formData) {
    if (formData === undefined) {
      formData = {};
    }

    var illegalZones  = {}
      , lockedZones   = {};

    // console.log(braceletConfig);

    /**
     * registration point is at 3 o'clock position
     * because that is 0 degrees so these are min
     * and max values of angles relative to that
     * 3 o'clock position
     */
    if (braceletConfig !== null && braceletConfig.bracelet_has_clip_spots) {
      illegalZones = {
        charm: [
            [22.5, 47]  // clip spot #1 going clockwise
          , [127, 151]  // clip spot #2 going clockwise
          , [250, 285]  // clasp spot (12 o'clock position)
        ],
        spacer: [
            [22.5, 47]  // clip spot #1 going clockwise
          , [127, 151]  // clip spot #2 going clockwise
          , [250, 285]  // clasp spot (12 o'clock position)
        ],
        clip: [
          [250, 285]
        ]
      };

      lockedZones = {
        clip: [
            [22.5, 47]  // clip spot #1 going clockwise
          , [127, 151]  // clip spot #2 going clockwise
        ]
      };
    } else {
      illegalZones = {
        charm: [
          [250, 285]  // clasp spot (12 o'clock position)
        ],
        spacer: [
          [250, 285]  // clasp spot (12 o'clock position)
        ],
        clip: [
          [250, 285]
        ]
      };

      lockedZones = {
        clip: [
            [22.5, 47]  // clip spot #1 going clockwise
          , [127, 151]  // clip spot #2 going clockwise
        ]
      };
    }

    var wsImg
      , bracelet
      , inAdminArea     = jQuery('.designer_app_wrapper').hasClass('admin')
      , radius          = 158 // (inAdminArea) ? 158 : 158
      , center          = { x: dw.center.x, y: dw.center.y }
      , canvasImageSrc  = dw.placeholderBraceletSrc
      , zones           = [ new dw.Zone(illegalZones, lockedZones, 0, 365) ]
      , sku             = '';

    if (dw.bracelet) {
      for (var charmId in dw.bracelet.beads){
        dw.bracelet.popOffBead(jQuery(dw.beads[charmId]));
      }
      dw.bracelet.ele.remove();
    }

    if (braceletConfig !== null && braceletConfig !== undefined) {
      if (jQuery('#bracelet-workspace img').length > 0) {
        // remove the old bracelet image and append a new one
        jQuery('#bracelet-workspace img').remove();

        wsImg = new Image();
        wsImg.id  = 'designer-bracelet-base';
        wsImg.src = braceletConfig.images.designer_canvas.rel_url;
        jQuery("#bracelet-workspace").append(wsImg);

      } else {
        wsImg = new Image();
        wsImg.id  = 'designer-bracelet-base';
        wsImg.src = braceletConfig.images.designer_canvas.rel_url;
        jQuery("#bracelet-workspace").append(wsImg);
      }

      canvasImageSrc        = braceletConfig.images.designer_canvas.url;
      sku                   = braceletConfig.sku;
      braceletConfig.zones  = zones;
    } else {
      wsImg = jQuery('#designer-bracelet-base');
      wsImg.attr('src', canvasImageSrc);
    }

    var bracelet = jQuery(wsImg).clone();

    bracelet.data('sku', sku);
    bracelet.data('canvasImageSrc', canvasImageSrc);

    var braceletPosition = {
        regX: center.x
      , regY: center.y
      , x: dw.center.x
      , y: dw.center.y
    };

    bracelet.move(braceletPosition);

    bracelet.addClass("workspaceBracelet");

    dw.bracelet = new dw.Bracelet(bracelet, radius, zones, braceletPosition);

    if (braceletConfig === null) {
      var tally = jQuery('#tally', '#design-price');
      tally.text('0.00');
    } else {
      var product = dw.bracelet.products[sku] || {};

      product.id    = braceletConfig.id;
      product.name  = braceletConfig.name;
      product.sku   = braceletConfig.sku;
      product.bracelet_has_clip_spots = braceletConfig.bracelet_has_clip_spots;

      // mark bracelet as already owned
      var already_owned   = 0 // (false)
        , quantity_owned  = 0;

      if (formData.is_already_owned !== undefined) {
        already_owned = formData.is_already_owned;
        if(already_owned) {
          quantity_owned = 1;
        }
      }

      product.quantity_owned = quantity_owned;

      if (product.instances === undefined) {
        product.instances = {};
      }
      var instance = product.instances[braceletConfig.sku + '_0'] || {};

      instance.angle        = null;                     // just a dummy placeholder value to keep similar structure
      instance.html_element = bracelet[0].outerHTML;    // HTML element as it is placed on the stage

      // add the instance to the instances object
      product.instances[braceletConfig.sku + '_0'] = instance;

      // add the product to the dw.bracelet.products object
      dw.bracelet.products[braceletConfig.sku] = product;

      // default bracelet price
      braceletPrice = braceletConfig.price;

      // loop through formData.attribute_options to add any
      // additional price markups based off of options chosen
      if (formData.attribute_options !== undefined && Object.keys(formData.attribute_options).length > 0) {
        for(var attr_id in formData.attribute_options) {
          var value_id = formData.attribute_options[attr_id];

          var prodInstance    = product.instances[braceletConfig.sku + '_0'];
          var superAttributes = prodInstance.super_attributes || {};

          superAttributes[attr_id] = value_id;

          dw.bracelet.products[braceletConfig.sku].instances[braceletConfig.sku + '_0'].super_attributes = superAttributes;


          for(var i in braceletConfig.attribute_options) {
            if (braceletConfig.attribute_options.hasOwnProperty(i)) {
              var valueOption = braceletConfig.attribute_options[i][attr_id]['values'][value_id];
              // only add to the base braceletPrice if the `pricing_value` is not null
              if(valueOption.pricing_value !== undefined && valueOption.pricing_value !== null) {
                var priceValue  = parseFloat(valueOption.pricing_value);
                braceletPrice   = parseFloat(parseFloat(braceletPrice) + priceValue);
              }
            }
          }
        }
      }

      var prod =  {
          sku: braceletConfig.sku
        , unit_price: braceletPrice
        , type: 'bracelet'
        , instance_id: braceletConfig.sku + '_0'
        , is_already_owned: already_owned
      };

      var qty = (!already_owned) ? 1 : 0;
      dw.updateBraceletPrice(prod, '+', qty);
    }
  };

  /**
   * Adds a bead to the workspace,
   */
  dw.addBead = function(beadConfig, pos, evt, formData) {
    var width             = beadConfig.images.designer_canvas.width
      , height            = beadConfig.images.designer_canvas.height
      , regX              = beadConfig.images.designer_canvas.regX // (width / 2)
      , regY              = beadConfig.images.designer_canvas.regY // (height / 2)
      , reg               = { regX: regX, regY: regY };

    var bead = makeBead(beadConfig, pos, reg, width, formData);

    checkCanvasLock(bead);
    if (pos.rotate && dw.bracelet) {
      setRotation(bead, bead.cords(), dw.bracelet.center());
    }
    if (pos.lock && dw.bracelet) {
      checkLock(bead);
    }
    if (evt) {
      var interval = setInterval(function() {
        if (bead[0].complete) {
          bead.trigger(jQuery.extend(evt, {
            type: 'mousedown'
          }));
          jQuery(bead).data('creatingBead', false);
          clearInterval(interval);
        }
      }, 50);
    }

    return bead;
  };

  dw.saveDesign = function(isAvailable, isInspiration) {
    var inspirationCheckBox       = jQuery('#inspiration_chkbox')
      , inspirationCheckBoxValue  = (inspirationCheckBox.length > 0) ? inspirationCheckBox.is(':checked') : false
      , availableCheckBox         = jQuery('#is_available_chkbox')
      , availableCheckBoxValue    = (availableCheckBox.length > 0) ? availableCheckBox.is(':checked') : true;

    if (isAvailable === undefined) {
      isAvailable = availableCheckBoxValue;
    }

    if (isInspiration === undefined) {
      isInspiration = inspirationCheckBoxValue;
    }

    var loggedIn    = dw.isLoggedIn()
      , inAdminArea = jQuery('.designer_app_wrapper').hasClass('admin');


    if (loggedIn || inAdminArea) {
      // console.log('hit PAN.DesignerWorkspace.saveDesign()');
      var bracelet        = dw.getCurrentBracelet()
        // create a clone of the bracelet.products object
        , products        = JSON.parse(JSON.stringify(bracelet.products))
        , total_price     = products['total_price']
        , productsAsJson;

      // remove the 'total_price' key from the cloned products object
      delete products['total_price'];
      // JSON encode the products object
      productsAsJson  = JSON.stringify(products);

      var designId = dw.getCurrentDesignId();
      if (designId === 'new') {
        designId = null;
      }

      var apiUrl = '/jewelrydesigner/api/saveDesign';
      if (inAdminArea) {
        apiUrl = '/jewelrydesigner/adminhtml_api/saveDesign';
      }

      dw.takeSnapshot();

      var adminId = (inAdminArea) ? jQuery('.designer_app_wrapper').data('admin-id') : null;
      var formKey = (inAdminArea) ? jQuery('.designer_app_wrapper').data('form-key') : null;

      // use a timeout of half-second to give enough time for
      // the dw.takeSnapshot() to finish and set the dw.snapshotImgData
      setTimeout(function(){
        var imgData = dw.snapshotImgData;
        jQuery.ajax({
            url: apiUrl
          , type: 'POST'
          , dataType: 'json'
          , data: {
              id: designId
            , admin_user_id: adminId
            , jewelry_type: 'bracelet'
            , name: jQuery('#designName').text()
            , configuration: productsAsJson
            , price: total_price
            , is_inspiration_design: isInspiration
            , is_available: isAvailable
            , base64Image: imgData
            , form_key: formKey
          }
        }).done(function(data){
          if (data.error === false) {
            // update the bracelet's current design ID
            // w/o having to do a full page refresh
            dw.setCurrentDesignId(data.design_id);
          }
          // dw.showMessage(data.message);
        });
      }, 500);
    } else {
      var designId = dw.getCurrentDesignId();
      // dw.showMessage('Not saving because you are not logged in!');
    }
  };

  dw.takeSnapshot = function() {
    html2canvas([document.getElementById("snapshot-workspace")], {
        logging: false
      , onrendered: function(canvas) {
          var img = canvas.toDataURL("image/png");
          dw.snapshotImgData = img;
          return img;
        }
      }
    );
  };

  dw.reloadDesignOnResize = function(){
    if (dw.designData !== null && dw.designData !== undefined) {
      // clear the stage
      dw.startOver(false);

      // reload the design
      dw.loadDesign(dw.designData);
    }
  };

  dw.loadDesign = function(design) {
    if (design !== undefined && design !== null) {
      dw.designData = design;

      jQuery(document).ready(function(){
        // re-discover the center of the .design-canvas
        dw.findWorkspaceCenter();

        var products        = JSON.parse(design.configuration)
          , braceletConfig  = { images: { designer_canvas: {} } }
          , tally           = jQuery('#tally', '#design-price');

        for(var sku in products){
          var prod = products[sku];
          if (prod.type === 'bracelet') {
            var pattern                 = /\s+src=\W(.*(?:\.png|\.jpe?g))\W\s+/i
              , htmlElement             = prod.instances[sku + '_0'].html_element
              , canvasImageSrc          = htmlElement.match(pattern)[1]
              , braceletSuperAttributes = prod.instances[sku + '_0'].super_attributes;

            braceletConfig.sku                    = sku;
            braceletConfig.id                     = prod.id;
            braceletConfig.price                  = prod.base_price;
            braceletConfig.name                   = prod.name;
            braceletConfig.bracelet_has_clip_spots = prod.bracelet_has_clip_spots;
            braceletConfig.status                 = true;
            braceletConfig.item_type              = 'bracelet';
            braceletConfig.images.designer_canvas.url      = dw.windowOrigin  + '/' + canvasImageSrc;
            braceletConfig.images.designer_canvas.rel_url  = canvasImageSrc;
          }
        }

        // set the bracelet
        dw.setBracelet(braceletConfig);

        // loop through products and re-create the product instances on the bracelet
        for(var sku in products){
          var prod = products[sku];
          if (prod.type !== 'bracelet') {
            for (var instance_id in prod.instances) {
              var instance    = prod.instances[instance_id]
                , diffX       = 0
                , diffY       = 0
                , cordX       = instance.x
                , cordY       = instance.y
                , pos         = {}
                , prodConfig  = {};

              /**
               * Translate the bead's original X position by comparing
               * the original bracelet center vs the current bracelet center
               */
              switch (true) {
                case(dw.center.x < instance.braceletX):
                  // console.log('dw.center.x < instance.braceletX')
                  diffX = instance.braceletX - dw.center.x;
                  cordX = instance.x - diffX;
                  break;
                case(dw.center.x > instance.braceletX):
                  // console.log('dw.center.x > instance.braceletX')
                  diffX = dw.center.x - instance.braceletX;
                  cordX = instance.x + diffX;
                  break;
                case(dw.center.x == instance.braceletX):
                  // console.log('dw.center.x == instance.braceletX')
                  diffX = 0;
                  break;
              }

              /**
               * Translate the bead's original Y position by comparing
               * the original bracelet center vs the current bracelet center
               */
              switch (true) {
                case(dw.center.y < instance.braceletY):
                  // console.log('dw.center.y < instance.braceletY')
                  diffY = instance.braceletY - dw.center.y;
                  cordY = instance.y - diffY;
                  break;
                case(dw.center.y > instance.braceletY):
                  // console.log('dw.center.y > instance.braceletY')
                  diffY = dw.center.y - instance.braceletY;
                  cordY = instance.y + diffY;
                  break;
                case(dw.center.y == instance.braceletY):
                  // console.log('dw.center.y == instance.braceletY')
                  diffY = 0;
                  break;
              }


              // remap the x,y coordinates of the bead's position
              pos = { x: cordX, y: cordY, rotate: true };

              // stub out a basic product object
              prodConfig  = {
                  id: prod.id
                , item_type: prod.type
                , price: prod.base_price
                , name: prod.name
                , sku: sku
                , status: true
                , images: {
                    designer_canvas: {
                        width: instance.width
                      , height: instance.height
                      , url: instance.canvasImageSrc
                      , rel_url: instance.canvasImageSrc.replace(dw.windowOrigin  + '/', '')
                      , regX: instance.regX
                      , regY: instance.regY
                    }
                  , thumbnail: {
                      url: instance.canvasImageSrc
                    , rel_url: instance.canvasImageSrc.replace(dw.windowOrigin  + '/', '')
                  }
                }
              };

              // add an instance of the bead to the bracelet
              var bead = dw.addBead(prodConfig, pos, null, { is_already_owned: instance.is_already_owned });

              // position the bead on the bracelet
              dw.bracelet.positionBead(bead);
            }
          }
        }

        // re-apply the original bracelet's super_attributes
        dw.bracelet.products[braceletConfig.sku].instances[braceletConfig.sku + '_0'].super_attributes = braceletSuperAttributes;

        // re-tally the total price for the bracelet
        dw.bracelet.products.total_price = dw.retallyTotalPrice();

        // update the UI price for the bracelet design
        tally.text(dw.bracelet.products.total_price);

      });
    }
  };

  /**
   * Display a "flash" message to the user
   */
  dw.showMessage = function(message) {
    jQuery('body').append('<div class="alert"></div>');

    var flash = jQuery('.alert');
    flash.slideDown(400);
    flash.html(message).append('<button></button>');
    jQuery('button').click(function () {
      flash.slideUp(400);
    });
    flash.slideDown('400', function () {
      setTimeout(function () {
        flash.slideUp('400', function () {
          jQuery(this).slideUp(400, function(){
            jQuery(this).detach();
          });
        });
      }, 7000);
    });
  };


  dw.startOver = function(runSaveDesign) {
    if (runSaveDesign === undefined || runSaveDesign === null) {
      runSaveDesign = true;
    }

    for (var id in dw.beads) {
      var bead = jQuery(dw.beads[id]);
      deleteBead(bead);
    }

    // reset the bracelet's products object/array,
    // essentially wiping out the configuration
    // used to rebuild a workspace
    dw.bracelet.products = {};

    if (runSaveDesign) {
      // save the design
      // (should wipe out all of the design items associated with the design)
      dw.saveDesign();
    }

    dw.setBracelet(null);
  };

  function checkCanvasLock(bead) {
    //console.log('hit checkCanvasLock()');
    bead = jQuery(bead);
    var cords = bead.cords();
    var canvasWidth = jQuery('.design-canvas').width();
    if (cords.x <= canvasWidth && cords.y <= 590) {
      bead.data('onCanvas', true);
      bead.maxX(canvasWidth);
      bead.maxY(590);
    }
  }


  /**
   * DEPRECATED - I don't think this is in use anywhere anymore
   *
   * Check if the bead's identifier (i.e., `bead.data('id')` )
   * is within the the bracelet's bead object (i.e., `dw.bracelet.beads`)
   * and return a boolean value.
   */
  dw.checkIfOnBracelet = function(bead) {
    var beadId          = bead.data('id')
      , braceletBeads   = dw.bracelet.beads
      , beadOnBracelet  = false;

    if (beadId in braceletBeads) {
      beadOnBracelet = true;
    }

    return beadOnBracelet;
  };

  /**
   * Updates the visible price of the bracelet design when a bead/charm
   * is added (locks/snaps to bracelet) or removed (visually off of the
   * bracelet).
   */
  dw.updateBraceletPrice = function(productObj, plusOrMinusAction, quantity) {
    plusOrMinusAction         = plusOrMinusAction || 'add';

    if (quantity === undefined || quantity === null) {
      quantity = 1;
    }

    var additionAliases       = ['add', 'plus', 'addition', '+']
      , subtractionAliases    = ['subtract', 'minus', 'subtraction', '-']
      , adding                = additionAliases.indexOf(plusOrMinusAction)
      , subtracting           = subtractionAliases.indexOf(plusOrMinusAction)
      , productUnitPrice      = productObj.unit_price
      , sku                   = productObj.sku
      , productType           = productObj.type
      , product               = dw.bracelet.products[sku] || {}
      , instanceId            = productObj.instance_id
      , current_total_price   = dw.bracelet.products.total_price || 0
      , numInstances          = Object.keys(product.instances).length
      , braceletWorkspace     = jQuery('#bracelet-workspace')
      , currentBraceletPrice  = parseFloat(braceletWorkspace.data('bracelet-price'))
      , tally                 = jQuery('#tally', '#design-price')
      , currentPrice          = parseFloat(tally.text())
      , updatedPrice
      , qty
      , productAlreadyOwned;


    if (product.is_already_owned !== undefined && product.is_already_owned) {
      productAlreadyOwned = true; // product.is_already_owned;
    } else {
      productAlreadyOwned = productObj.is_already_owned;
    }


    if ( (sku in dw.bracelet.products) && ('qty' in product) ) {
      if (adding !== -1) {
        // we're adding
        qty = product.qty + quantity;
      } else {
        // we're subtracting
        qty = ((product.qty - quantity) > 0) ? product.qty - quantity : 0;
      }
    } else {
      qty = quantity;
    }

    /**
     * TODO: BUG! there seems to be an issue of constantly adding a bead
     * to bracelet when dragging around the 3 o'clock position and a bead
     * seems to go on/off the bracelet. It drives the qty through the roof,
     * so for quick sanity check we'll reset the qty to the number of instances
     * of the bead on the bracelet.
     */
    if (qty > numInstances) {
      // console.log('resetting qty to be equal to numInstances (' + numInstances + ')');
      qty = numInstances;
    }

    // Keep track of the products on the bracelet
    product.qty               = qty;
    product.base_price        = parseFloat(productUnitPrice).toFixed(2);
    product.line_item_price   = parseFloat(qty * productUnitPrice).toFixed(2);
    product.type              = productType;
    product.is_already_owned  = productAlreadyOwned;


    // update the product in the array of products
    dw.bracelet.products[sku] = product;

    var currentPriceAsFloat   = parseFloat(current_total_price)
      , instancePriceAsFloat  = parseFloat(product.base_price * quantity);

    if (subtracting !== -1) {
      if (product.type !== 'bracelet' && dw.bracelet.beads[instanceId] === undefined) {
        // remove a specific instance of the product
        // from the product's instances array (object)
        delete product.instances[instanceId];

        var numInstances = Object.keys(product.instances).length;

        if (productAlreadyOwned) {
          product.quantity_owned = ((product.quantity_owned - 1) > 0) ? product.quantity_owned - 1 : 0;
        }

        // product.qty = numInstances;

        // completely remove the product from the array of products
        // since all instances of it are off the bracelet
        if (numInstances < 1) {
          delete dw.bracelet.products[sku];
          // console.log('removing product sku ' + sku + ' from dw.bracelet.products');
          // console.log(dw.bracelet.products);
        }
      }
    }

    // update the total price for the bracelet
    dw.bracelet.products.total_price = dw.retallyTotalPrice();

    // console.log('bracelet:');
    // console.log(dw.bracelet);

    // update the UI price for the bracelet design
    tally.text(dw.bracelet.products.total_price);
  };

  /**
   * Loop through the dw.bracelet.products obj/array and
   * re-tally the total to ensure the total price is correct
   *
   * @return float
   */
  dw.retallyTotalPrice = function(){
    var total = 0;

    jQuery.each(dw.bracelet.products, function(index, product){
      if (index !== 'total_price') {
        total = parseFloat(parseFloat(total) + parseFloat(product.base_price * product.qty)).toFixed(2);
      }
    });

    return total;
  };

  function makeBead(beadConfig, pos, reg, width, formData) {
    // console.log('hit DesignerWorkspace.makeBead()');

    var sku               = beadConfig.sku
      , img               = new Image()
      , is_already_owned  = 0;

    if (formData === undefined) {
      formData = {};
    } else {
      if (formData.is_already_owned !== undefined) {
        is_already_owned = formData.is_already_owned;
      }
    }

    img.src = beadConfig.images.designer_canvas.url;

    // data attributes
    img.setAttribute('data-sku', sku);
    img.setAttribute('data-canvasImageSrc', beadConfig.images.thumbnail.url);
    img.setAttribute('data-width', width);
    img.setAttribute('data-type', beadConfig.item_type);
    img.setAttribute('data-prevX', pos.x);
    img.setAttribute('data-prevY', pos.y);

    // Set uniq
    makeBead.skuIds = makeBead.skuIds || {};
    if (!makeBead.skuIds[sku]) {
        makeBead.skuIds[sku] = 0;
    }

    var beadId = sku + '_' + makeBead.skuIds[sku]++;
    img.setAttribute('data-id', beadId);
    img.id = "bead_" + beadId;


    jQuery("#charms-workspace").append(img);


    var bead = jQuery("#bead_" + beadId);

    bead.data('sku', sku);
    bead.data('canvasImageSrc', beadConfig.images.designer_canvas.url);
    bead.data('width', width);
    bead.data('height', beadConfig.images.designer_canvas.height);
    bead.data('regX', beadConfig.images.designer_canvas.regX);
    bead.data('regY', beadConfig.images.designer_canvas.regY);
    bead.data('type', beadConfig.item_type);
    bead.data('prevX', pos.x);
    bead.data('prevY', pos.y);
    bead.data('x', pos.x);
    bead.data('y', pos.y);
    bead.data('price', beadConfig.price);

    bead.data('product-id', beadConfig.id);
    bead.data('name', beadConfig.name);

    bead.data('is_already_owned', is_already_owned);

    if(is_already_owned) {
      bead.addClass('already-owned');
    }

    var moveObj = {
        regX: reg.regX
      , regY: reg.regY
      , x: pos.x
      , y: pos.y
      , minX: -10
      , minY: 0
    };

    bead.data('move', moveObj);

    if (dev.scale) {
      var scale   = dw.beadScale
        , width   = width * scale
        , height  = bead.height * scale;

      reg.regX    = parseInt(reg.regX * scale);
      reg.regY    = parseInt(reg.regY * scale);
    }

    if (dev.scale) {
      bead.css({
        width: width,
        height: height
      });

    }
    bead.move(moveObj).drag();

    // bead.opacity(dw.fade);

    // updates the bead's angle/position within the bracelet's beads object
    dw.beads[beadId] = bead[0];

    bead.on('dragstep', function(evt) {
      if (dw.bracelet) {
        setRotation(jQuery(this), jQuery(this).cords(), dw.bracelet.center());
        checkLock(jQuery(this), dw.bracelet.center(), dw.bracelet.radius);
      }

      jQuery(this).data('prevX', jQuery(this).x());
      jQuery(this).data('prevY', jQuery(this).y());
    });

    var checkTrashCan = function(evt) {
      var cords               = alchemy.getCords(evt)
        , trashCords          = jQuery('#trash').cords()
        , trashOffset         = jQuery('#trash').offset()
        , adjustedTrashCords  = { x: trashOffset.left, y: trashOffset.top }
        , dist                = alchemy.dist(cords, adjustedTrashCords);

      if (dist < dw.trashDist) {
        jQuery('#closedTrashCan').hide();
        jQuery('#openedTrashCan').show();
      } else {
        jQuery('#closedTrashCan').show();
        jQuery('#openedTrashCan').hide();
      }
    };

    bead.on('dragstep', function() {
      checkCanvasLock(jQuery(this));
    });

    bead.on('dragstep', checkTrashCan);

    bead.on('mousedown touchstart', function(evt) {
      var ignore = function(evt) {
        bead.off('dragstep', ignore);
        bead.off('mouseup touchend', showMessage);
        jQuery('#bubble').hide();
      };
      var showMessage = function(evt) {
        bead.off('dragstep', ignore);
        bead.off('mouseup touchend', showMessage);
        if (jQuery(this).data('onCanvas')) {
            setUpBubbleBox(alchemy.getCords(evt), jQuery(this));
        }
      };
      bead.on('dragstep', ignore);
      bead.on('mouseup touchend', showMessage);
    });

    bead.on('dragstop', function(evt) {
      if (!jQuery(this).data('onCanvas')) {
        deleteBead(jQuery(this));
      }
      var cords               = alchemy.getCords(evt)
        , trashCords          = jQuery('#trash').cords()
        , trashOffset         = jQuery('#trash').offset()
        , adjustedTrashCords  = { x: trashOffset.left, y: trashOffset.top }
        , dist                = alchemy.dist(cords, adjustedTrashCords);

      // Magic number, seems to be a good distance though.
      if (dist < dw.trashDist) {
        deleteBead(jQuery(this));
        jQuery('#closedTrashCan').show();
        jQuery('#openedTrashCan').hide();
        dw.showMessage('Charm deleted!');
      }

      // auto-save the design whenever the movement of a bead has stopped
      dw.saveDesign();
    });
    return bead;
  }

  dw.deleteBeads = function(type) {
    for (var i in dw.beads) {
      var bead = jQuery(dw.beads[i]);
      if (bead.data('type') == type) {
        deleteBead(bead);
      }
    }
  };

  function deleteBead(bead) {
    bead = jQuery(bead);

    delete dw.beads[bead.data('id')];
    if (dw.bracelet) {
      dw.bracelet.removeBead(bead);
    }
    bead.remove();
  }

  /**
   * DEPRECATED - I don't think this is in use anywhere anymore
   */
  function copyBead(bead) {
    bead = jQuery(bead);

    var img     = new Image();
    img.src     = bead.attr('src');
    img.onload  = function() {
      var copy = makeBead(
          bead.data('sku')
        , img
        , bead.data('type')
        , {
            x: dw.center.x,
            y: dw.center.y
          }
        , {
            regX: bead.regX(),
            regY: bead.regY()
          }
        , bead.data('width')
        , bead.data('canvasImageSrc')
      );
      checkCanvasLock(copy);
      jQuery('#workspace').append(copy);
    };
  }

  /**
   * DEPRECATED - I don't think this is in use anywhere anymore
   */
  function setUpBubbleBox(cords, ele) {
    jQuery('#bubble').data('bead', ele);
    jQuery('#bubble').x(ele.x() + ele.data('width') / 2);
    jQuery('#bubble').y(ele.y());
    jQuery('#bubble').show();

    var delay = false;
    var exit  = function(evt) {
      delay = true;
      setTimeout(function() {
        if (delay) {
            jQuery('#bubble').hide();
        }
      }, 200);
      ele.off('mouseenter mouseleave');
    };
    jQuery('#bubble').children().on('mouseleave', exit);
    jQuery('#bubble').children().on('mouseenter', function() {
        delay = false;
    });
    ele.on('mouseleave', exit);
    ele.on('mouseenter', function() {
      delay = false;
    });
  }

  // Sets the rotation of a bead. Typically used during dragstep events

  function setRotation(target, rPoint, center) {
    rPoint = typeof rPoint != 'undefined' ? rPoint : target.cords();
    if (alchemy.dist(center, rPoint) < rDist) {
      target.rotate(alchemy.angle(rPoint, center) - 90);
    }

  }

  /**
   * Checks if a bead should be locked to the bracelet. If so it will also shift any other
   * beads and kick them off if they're in an illegal space
   */
  function checkLock(target) {
    var a = alchemy.closestPointOnCircle(
      dw.bracelet.center(),
      dw.bracelet.radius,
      target.cords()
    );

    var isLegal = dw.bracelet.isLegalPlacement(target, a, buffer);

    // var isLockedPosition = dw.bracelet.isLockedPosition(target, a, buffer);


    if (a.dist < lockDist && isLegal) {
      var moveX = target.x()
        , moveY = target.y();

      target.x(a.x);
      target.y(a.y);

      if (!dw.bracelet.positionBead(target)) {
        if (typeof dw.bracelet.beads[target.data('id')] == 'undefined') {
          target.x(moveX);
          target.y(moveY);
        }
      }
    } else {
      dw.bracelet.removeBead(target);
    }
  }

  //----------------
  // Nested Classes
  //----------------

  // Defines the bracelet object
  (function() {
    function Bracelet(ele, radius, zones, position) {
      this.ele      = ele;
      this.radius   = radius;
      this.zones    = zones;
      this.position = position;

      // Maps bead id to angle on bead
      this.beads        = {};

      // keep track of the products on the bracelet design
      this.products     = {};
    }

    Bracelet.prototype.center = function() {
      return this.ele.cords();
    };

    /**
     * maps sorted index -> bead id
     */
    Bracelet.prototype.sortedBeads = function() {
      var sortable    = []
        , returnable  = [];

      for (var beadId in this.beads) {
        sortable.push([beadId, this.beads[beadId]]);
      }

      sortable.sort(function(a, b) {
        return a[1] - b[1];
      });

      for (var i = 0;  i <= sortable.length; i++) {
        var a = sortable[i];
        if (a !== undefined && a.length > 0) {
          returnable.push(a[0]);
        }
      }

      return returnable;
    };

    Bracelet.prototype.popOffBead = function(bead) {
      bead = jQuery(bead);

      if (bead.length > 0) {
        var x = (bead.x() - this.ele.x()) * 1.5 + this.ele.x();
        var y = (bead.y() - this.ele.y()) * 1.5 + this.ele.y();

        bead.x(x);
        bead.y(y);
        this.removeBead(bead);
      } else {
        // console.log('FROM Bracelet.prototype.popOffBead(): Unable to find instance of bead so skipping this instance');
      }
    };

    Bracelet.prototype.removeBead = function(bead) {
      bead = jQuery(bead);

      bead.opacity(dw.fade);
      var id = bead.data('id')
        , already_owned = bead.data('is_already_owned');

      if (typeof this.beads[id] != 'undefined') {
        delete this.beads[id];

        var product =  {
            sku: bead.data('sku')
          , unit_price: bead.data('price')
          , type: bead.data('type')
          , instance_id: id
          , is_already_owned: already_owned
        };

        var qty = (!already_owned) ? 1 : 0;
        // decrement the bracelet price and update the Bracelet.products array
        dw.updateBraceletPrice(product, '-', qty);
      }
    };

    Bracelet.prototype.positionBead = function(bead) {
      bead = jQuery(bead);

      var angle = alchemy.angle(bead.cords(), this.center());
      bead.opacity(1);

      if (!this.beads[bead.data('id')]) {
        if (this.addBead(bead, angle)) {
          // console.log('adding bead!');
          return true;
        } else {
          return;
        }
      } else {
        this.moveBeads(bead, angle);
      }
    };

    Bracelet.prototype.addBead = function(bead, angle) {
      // console.log('adding bead!');

      bead = jQuery(bead);

      var id = bead.data('id')
        , id_2
        , angle_2
        , moveSuccessful;

      /**
       * Technically no movement is considered clockwise
       * so when a bead is placed on the bracelet is
       * is thought to have moved clockwise.
       * This leads to aberrant behavior if a bead
       * is placed clockwise to the only other bead
       * on the bracelet. This if statement does
       * a fake out to make it appear as if the bead
       * approached counter clockwise.
       *
       */
      if (Object.keys(this.beads).length == 1) {
        id_2    = Object.keys(this.beads)[0];
        angle_2 = this.beads[id_2];

        if (!this.clockwise(angle, angle_2)) {
          angle += .01;
        }
      }

      // Preform initial shift
      this.beads[id]  = angle;
      moveSuccessful  = this.moveBeads(bead, angle);

      var product   = this.products[bead.data('sku')] || {};

      product.id    = bead.data('product-id');
      product.name  = bead.data('name');
      product.sku   = bead.data('sku');

      // update bracelet price if bead is added to the bracelet
      var instanceIsAlreadyOwned  = bead.data('is_already_owned')
        , itemPrice               = bead.data('price');

      if (product.is_already_owned !== undefined && product.is_already_owned) {
        product.is_already_owned = true;
      } else {
        product.is_already_owned = instanceIsAlreadyOwned;
      }

      if (product.quantity_owned === undefined) {
        product.quantity_owned = 0;
      }

      // increment the number of items owned but don't increment the line item's qty!
      if (instanceIsAlreadyOwned) {
        product.quantity_owned = product.quantity_owned + 1;
      }

      if (product['instances'] === undefined) {
        product.instances = {};
      }

      // simply a way of keeping track of instances
      // of the same product on a bracelet
      var instance = product.instances[id] || {};

      // add/update this instance's angle and html element
      instance.angle          = angle;
      instance.html_element   = bead[0].outerHTML;

      instance.css_rotate     = instance.angle - 90;
      instance.x              = bead.x();
      instance.y              = bead.y();

      instance.canvasImageSrc = bead.data('canvasImageSrc');
      instance.width          = bead.data('width');
      instance.height         = bead.data('height');
      instance.regX           = bead.data('regX');
      instance.regY           = bead.data('regY');

      instance.braceletX  = dw.bracelet.position.x;
      instance.braceletY  = dw.bracelet.position.y;

      instance.is_already_owned = instanceIsAlreadyOwned;

      product.instances[id] = instance;


      // add/update this product to the bracelet's array of products
      this.products[bead.data('sku')] = product;

      var prod =  {
          sku: bead.data('sku')
        , unit_price: itemPrice
        , type: bead.data('type')
        , instance_id: id
        , is_already_owned: instanceIsAlreadyOwned
      };

      // update bracelet price and product line items
      var qty = (!instanceIsAlreadyOwned) ? 1 : 0;
      dw.updateBraceletPrice(prod, '+', qty);


      // Required to shift bead ccw if they don't connect
      if (Object.keys(this.beads).length > 2) {
        this.beads[id]  = angle + .01;
        moveSuccessful  = this.moveBeads(bead, angle);
      }
      if (!moveSuccessful) {
        bead.opacity(dw.fade);
        delete this.beads[id];
      }
      return moveSuccessful;
    };

    Bracelet.prototype.moveBeads = function(bead, angle) {
      bead   = jQuery(bead);
      var id = bead.data('id');

      if (Object.keys(this.beads).length == 1) {
        this.beads[id] = angle;
        this.postMove();
        return true;
      }

      var sort = this.sortedBeads()
        , tarIndex;

      for (var i in sort) {
        if (sort[i] == id) {
          tarIndex = parseInt(i);
          break;
        }
      }

      var prevAngle   = this.beads[id]
        , curAngle    = alchemy.angle(bead.cords(), this.center())
        , dir         = this.clockwise(prevAngle, curAngle) ? 1 : -1
        , prevIndex   = tarIndex
        , curIndex    = alchemy.mod(tarIndex + dir, sort.length)
        , finalShift  = true
        , id_1
        , id_2
        , shiftReturn
        , bead_2;

      // Move preferred direction, break out early, if not done move other direction
      while (curIndex != tarIndex) {
        id_1        = sort[prevIndex];
        id_2        = sort[curIndex];
        shiftReturn = this.shiftBead(id_1, id_2);

        if (shiftReturn === null) {
          this.undoMove(bead);
          return false;
        }
        if (!shiftReturn) {
          finalShift = false;
          break;
        }
        prevIndex = curIndex;
        curIndex  = alchemy.mod(curIndex + dir, sort.length);
      }

      if (finalShift) {
        this.beads[id_2]  = alchemy.angle(jQuery(dw.beads[id_2]).cords(), this.center());
        bead_2            = jQuery(dw.beads[id_2]);

        if (this.overlap(bead, bead_2)) {
          if (dw.allowPopoff) {
            this.popOffBead(bead_2);
          } else {
            this.undoMove(bead);
            return false;
          }
        }
      }

      return this.postMove(bead);
    };

    // Assume only the first has moved
    Bracelet.prototype.shiftBead = function(id_1, id_2) {
      // console.log('hit Bracelet.shiftBead()!!');

      var bead_1      = jQuery(dw.beads[id_1])
        , bead_2      = jQuery(dw.beads[id_2])
        , prevAngle_1 = this.beads[id_1]
        , curAngle_1  = (typeof angleOverride != 'undefined')
            ? angleOverride
            : alchemy.angle(bead_1.cords(), this.center())
        , curAngle_2  = alchemy.angle(bead_2.cords(), this.center())
        , shift       = false
        , a1
        , a2
        , angle
        , newPoint;

      if (this.passed(prevAngle_1, curAngle_1, curAngle_2) || this.overlap(bead_1, bead_2)) {
          a1 = this.getBoundaryAngles(bead_1);
          a2 = this.getBoundaryAngles(bead_2);
          // Hack, beads shouldn't be that wide so this should work
          if (a1.left < a1.right || a2.left < a2.right) {
              a1.right  = (a1.right + 90) % 360;
              a1.left   = (a1.left + 90) % 360;
              a2.right  = (a2.right + 90) % 360;
              a2.left   = (a2.left + 90) % 360;
          }

          if (this.clockwise(prevAngle_1, curAngle_1)) {
              angle = a1.left - a2.right;
          } else {
              angle = a1.right - a2.left;
          }

          newPoint = alchemy.rotateAroundPoint(
              bead_2.cords(),
              this.center(),
              angle
          );

          bead_2.x(newPoint.x);
          bead_2.y(newPoint.y);

          setRotation(bead_2, bead_2.cords(), this.center());

          if (!this.isLegalPlacement(bead_2, bead_2.cords(), buffer)) {
            if (dw.allowPopoff) {
              this.popOffBead(bead_2);
            } else {
              return null;
            }
          } else {
            shift = true;
          }

      }
      this.beads[id_1] = curAngle_1;
      return shift;
    };

    Bracelet.prototype.postMove = function(bead) {
      var valid = true;
      if (typeof bead != 'undefined') {
        for (var id in this.beads) {
          if (id != bead.data('id')) {
            var bead2 = jQuery(dw.beads[id]);
            if (this.overlap(bead, bead2)) {
                valid = false;
            }
          }
        }
      }
      for (var id in this.beads) {
        var bead = jQuery(dw.beads[id]);

        // update the product's instance angle value
        var product = dw.bracelet.products[bead.data('sku')];
        if (product !== undefined && product['instances'] !== undefined) {
          if (product.instances[id] !== undefined) {
            var instance          = product.instances[id] || {};
            /**
             * keep track of the instance's current angle
             * and html element (will be used to rebuild
             * the design)
             */
            instance.angle          = dw.bracelet.beads[id];
            instance.html_element   = bead[0].outerHTML;
            instance.css_rotate     = instance.angle - 90;
            instance.x              = bead.x();
            instance.y              = bead.y();

            instance.canvasImageSrc = bead.data('canvasImageSrc');
            instance.width          = bead.data('width');
            instance.height         = bead.data('height');

            instance.braceletX  = dw.bracelet.position.x;
            instance.braceletY  = dw.bracelet.position.y;

            // add/update the instance to the dw.bracelet.products object
            product.instances[id] = instance;
          }
        }

        bead.data('prevX', bead.x());
        bead.data('prevY', bead.y());
      }

      return valid;
    };

    Bracelet.prototype.undoMove = function(bead) {
      for (var id in this.beads) {
        var bead = jQuery(dw.beads[id]);
        bead.x(bead.data('prevX'));
        bead.y(bead.data('prevY'));
        var angle       = alchemy.angle(bead.cords(), this.center());
        this.beads[id]  = angle;

        // un-rotate!
        setRotation(bead, bead.cords(), this.center());
      }
    };

    /*
     * Uses shortest angle stuff, If end is more than 180 degrees
     * cw away it's ccw. That's just how it's gonna work.
     */
    Bracelet.prototype.clockwise = function(start, end) {
      end = alchemy.mod(end - start, 360);
      return end < 180;
    };

    /*
     * Going from start to end was angle passed.
     */
    Bracelet.prototype.passed = function(start, end, angle) {
      var perc  = 100;
      start     = Math.round(start * perc) / perc;
      end       = Math.round(end * perc) / perc;
      if (!this.clockwise(start, end)) {
        var t = end;
        end   = start;
        start = t;
      }
      angle = Math.round(angle * perc) / perc;
      return o = alchemy.angleContain({
          start: start
        , end: end
      }, {
          start: angle
        , end: angle
      });
    };

    //Angle of bead boundaries (uses width)
    Bracelet.prototype.getBoundaryAngles = function(bead) {
      var leftBoundary  = alchemy.moveThroughArc(bead.cords(), this.center(), bead.data('width') / 2 + buffer)
        , rightBoundary = alchemy.moveThroughArc(bead.cords(), this.center(), -bead.data('width') / 2 - buffer)
        , leftAngle     = alchemy.angle(leftBoundary, this.center())
        , rightAngle    = alchemy.angle(rightBoundary, this.center());

      return {
          left: leftAngle
        , right: rightAngle
      };
    };

    //Is the bead allowed.
    Bracelet.prototype.isLegalPlacement = function(bead, point, buffer) {
      //console.log('hit Bracelet.isLegalPlacement()');

      bead = jQuery(bead);

      for (i = 0; i < this.zones.length; i++) {
        var z = this.zones[i];

        var beadProjection = {
            x: point.x,
            y: point.y,
            width: bead.data('width')
        };

        if (z.inZone(beadProjection, this.center(), buffer)) {
          var beadAngle = alchemy.angle(beadProjection, this.center());
          if (!z.isAllowed(bead.data('type'), beadAngle, this)) {
              return false;
          }
          if (z.max != Infinity) {
            var count = 0;
            for (var id in this.beads) {
              if (id != bead.data('id')) {
                var cbead = jQuery(dw.beads[id]);
                var proj = {
                  x: cbead.x(),
                  y: cbead.y(),
                  width: cbead.data('width')
                };
                if (z.inZone(proj, this.center(), buffer)) {
                  count++;
                }
              }
            }
            if (count >= z.max) {
              return false;
            }
          }
        }
      }
      return true;
    };

    Bracelet.prototype.isLockedPosition = function(bead, point) {
      bead = jQuery(bead);

      for (i = 0; i < this.zones.length; i++) {
        var z = this.zones[i];

        var beadProjection = {
            x: point.x,
            y: point.y,
            width: bead.data('width')
        };

        var beadAngle = alchemy.angle(beadProjection, this.center())
          , isLocked  = z.isInLockedZone(bead.data('type'), beadAngle);

        if (isLocked) {
          return true;
        }
      }
      return false;
    };

    // Do two beads overlap
    Bracelet.prototype.overlap = function(bead1, bead2) {
      var perc  = 100;
      var a1    = this.getBoundaryAngles(bead1);
      var a2    = this.getBoundaryAngles(bead2);
      var o     = alchemy.angleOverlap({
        start: Math.round(a1.right * perc) / perc,
        end: Math.round(a1.left * perc) / perc
      }, {
        start: Math.round(a2.right * perc) / perc,
        end: Math.round(a2.left * perc) / perc
      });
      return o;
    };

    dw.Bracelet = Bracelet;
  }());


  PAN.DesignerWorkspace = DesignerWorkspace;
})();
