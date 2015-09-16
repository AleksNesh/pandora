/// <reference path="jquery-1.2.6-vsdoc.js" />
(function($) {

    $.fn.annotateImage = function(options) {
        ///	<summary>
        ///		Creates annotations on the given image.
        ///     Images are loaded from the "getUrl" propety passed into the options.
        ///	</summary>
        var opts = $.extend({}, $.fn.annotateImage.defaults, options);
        var image = this;

        this.image = this;
        this.mode = 'view';

        // Assign defaults
        this.input_field_id = opts.input_field_id;
        this.interdict_areas_overlap = opts.interdict_areas_overlap;
        this.captions = opts.captions;
        this.getUrl = opts.getUrl;
        this.saveUrl = opts.saveUrl;
        this.deleteUrl = opts.deleteUrl;
        this.editable = opts.editable;
        this.useAjax = opts.useAjax;
        this.notes = opts.notes;

        // Add the canvas
        this.canvas = $('<div class="image-annotate-canvas"><div class="image-annotate-view"></div><div class="image-annotate-edit"><div class="image-annotate-edit-area"></div></div></div>');
        this.canvas.children('.image-annotate-edit').hide();
        this.canvas.children('.image-annotate-view').hide();
        this.image.after(this.canvas);

        // Give the canvas and the container their size and background
        this.canvas.height(this.height());
        this.canvas.width(this.width());
        this.canvas.css('background-image', 'url("' + this.attr('src') + '")');
        this.canvas.children('.image-annotate-view, .image-annotate-edit').height(this.height());
        this.canvas.children('.image-annotate-view, .image-annotate-edit').width(this.width());

        // Add the behavior: hide/show the notes when hovering the picture
        this.canvas.hover(function() {
            if ($(this).children('.image-annotate-edit').css('display') == 'none') {
                $(this).children('.image-annotate-view').show();
            }
        }, function() {
            $(this).children('.image-annotate-view').hide();
        });

        this.canvas.children('.image-annotate-view').hover(function() {
            $(this).show();
        }, function() {
            $(this).hide();
        });

        // load the notes
        if (this.useAjax) {
            $.fn.annotateImage.ajaxLoad(this);
        } else {
            $.fn.annotateImage.load(this);
        }

        // Add the "Add a note" button
        if (this.editable) {
            this.button = $('<button class="image-annotate-add" id="image-annotate-add" onclick="javascript: return false;"><span>'+this.captions.add_btn+'</span></button>');
            this.button.click(function() {
                $.fn.annotateImage.add(image);
            });
            this.canvas.after(this.button);
        }

        // Hide the original
        this.hide();

        return this;
    };

    /**
    * Plugin Defaults
    **/
    $.fn.annotateImage.defaults = {
        getUrl: 'your-get.rails',
        saveUrl: 'your-save.rails',
        deleteUrl: 'your-delete.rails',
        editable: true,
        useAjax: true,
        notes: new Array()
    };

    $.fn.annotateImage.clear = function(image) {
        ///	<summary>
        ///		Clears all existing annotations from the image.
        ///	</summary>    
        for (var i = 0; i < image.notes.length; i++) {
            image.notes[image.notes[i]].destroy();
        }
        image.notes = new Array();
    };

    $.fn.annotateImage.ajaxLoad = function(image) {
        ///	<summary>
        ///		Loads the annotations from the "getUrl" property passed in on the
        ///     options object.
        ///	</summary>
        $.getJSON(image.getUrl + '?ticks=' + $.fn.annotateImage.getTicks(), function(data) {
            image.notes = data;
            $.fn.annotateImage.load(image);
        });
    };

    $.fn.annotateImage.load = function(image) {
        ///	<summary>
        ///		Loads the annotations from the notes property passed in on the
        ///     options object.
        ///	</summary>
        for (var i = 0; i < image.notes.length; i++) {
            image.notes[image.notes[i]] = new $.fn.annotateView(image, image.notes[i]);
        }
    };

    $.fn.annotateImage.getTicks = function() {
        ///	<summary>
        ///		Gets a count og the ticks for the current date.
        ///     This is used to ensure that URLs are always unique and not cached by the browser.
        ///	</summary>        
        var now = new Date();
        return now.getTime();
    };

    $.fn.annotateImage.add = function(image) {
        ///	<summary>
        ///		Adds a note to the image.
        ///	</summary>        
        if (image.mode == 'view') {
            image.mode = 'edit';

            // Create/prepare the editable note elements
            var editable = new $.fn.annotateEdit(image);

            $.fn.annotateImage.createCancelButton(editable, image);
            $.fn.annotateImage.createSaveButton(editable, image);
            $("#image-annotate-edit-ok").css('float', 'right').css('margin-right', '25px');
            $(".image-annotate-edit-close").css('margin-left', '25px');
        }

    };

    $.fn.annotateImage.createSaveButton = function(editable, image, note) {
        ///	<summary>
        ///		Creates a Save button on the editable note.
        ///	</summary>
        var ok = $('<button class="image-annotate-edit-ok" id="image-annotate-edit-ok" onclick="javascript: return false;"><span>OK</span></button>');

        ok.click(function() {
            var form = $('#image-annotate-edit-form form');
                var text = '';                      
                var href = '';
                var sku = '';
                             
            if ($("#data-type-link").attr('checked')=='checked') {
                var text = $('#image-annotate-text').val();                      
                var href = $('#image-annotate-href').val();                
            }
            if ($("#data-type-sku").attr('checked')=='checked') {
                var sku = $('#image-annotate-sku').val();               
            }
            $.fn.annotateImage.appendPosition(form, editable)
            image.mode = 'view';

            // Save via AJAX
            if (image.useAjax) {
                $.ajax({
                    url: image.saveUrl,
                    data: form.serialize(),
                    error: function(e) { alert(image.captions.note_saving_err) },
                    success: function(data) {
				if (data.annotation_id != undefined) {
					editable.note.id = data.annotation_id;
				}
		    },
                    dataType: "json"
                });
            }

            if (image.interdict_areas_overlap) {
                var test_area = Object();
                 test_area.id = editable.note.id;
                 test_area.height = editable.area.height();
                 test_area.width = editable.area.width();
                 test_area.left = editable.area.position().left;
                 test_area.top = editable.area.position().top;
 			
		 var notes_obj = $.parseJSON(image.notes);

                if (notes_obj && !CheckPosition(test_area, notes_obj)) {
                    alert(image.captions.note_overlap_err);
                    return false;
                }
            }  
            
        
            if (($("#data-type-link").attr('checked')!='checked') && ($("#data-type-sku").attr('checked')!='checked')) {
                alert(image.captions.select_link_type_err);
                return false;
            }
            else
            {
               if ($("#data-type-link").attr('checked')=='checked') {
                    if (($.trim(text)=='') || ($.trim(href)=='')) {
                        alert(image.captions.link_required_err);
                        return false;
                    }
               }
               else
               {
                    if ($.trim(sku)=='') {
                        alert(image.captions.enter_sku_err);
                        return false;
                    } 
                    else
                    {
                        var response = checkSKU();
                        if (response!=1) {
                            alert(image.captions.prod_dont_exists_err+'"'+sku+'" ' + response);
                            return false;
                        }               
                    }
               }  
            }

            // Add to canvas
            if (note) {
                note.resetPosition(editable, text, href, sku);             
            } else {
                editable.note.editable = true;
                note = new $.fn.annotateView(image, editable.note);
                note.resetPosition(editable, text, href, sku);
                image.notes.push(editable.note);
            }  

            $('#'+image.input_field_id).val(JSON.stringify(image.notes));
            editable.destroy();
        });
        editable.form.append(ok);
    };

    $.fn.annotateImage.createCancelButton = function(editable, image) {
        ///	<summary>
        ///		Creates a Cancel button on the editable note.
        ///	</summary>
        var cancel = $('<button class="image-annotate-edit-close" onclick="javascript: return false;"><span>'+image.captions.cancel_btn+'</span></button>');
        cancel.click(function() {
            editable.destroy();
            image.mode = 'view';
        });
        editable.form.append(cancel);
    };

    $.fn.annotateImage.saveAsHtml = function(image, target) {
        var element = $(target);
        var html = "";
        for (var i = 0; i < image.notes.length; i++) {
            html += $.fn.annotateImage.createHiddenField("text_" + i, image.notes[i].text);
            html += $.fn.annotateImage.createHiddenField("href_" + i, image.notes[i].href);
            html += $.fn.annotateImage.createHiddenField("sku_" + i, image.notes[i].sku);
            html += $.fn.annotateImage.createHiddenField("top_" + i, image.notes[i].top);
            html += $.fn.annotateImage.createHiddenField("left_" + i, image.notes[i].left);
            html += $.fn.annotateImage.createHiddenField("height_" + i, image.notes[i].height);
            html += $.fn.annotateImage.createHiddenField("width_" + i, image.notes[i].width);
        }
        element.html(html);
    };

    $.fn.annotateImage.createHiddenField = function(name, value) {
        return '&lt;input type="hidden" name="' + name + '" value="' + value + '" /&gt;<br />';
    };

    $.fn.annotateEdit = function(image, note) {
        ///	<summary>
        ///		Defines an editable annotation area.
        ///	</summary>
        this.image = image;

        if (note) {
            this.note = note;
        } else {
            var newNote = new Object();
            newNote.id = ""+new Date().getTime();
            newNote.top = 30;
            newNote.left = 30;
            newNote.width = 30;
            newNote.height = 30;
            newNote.text = "";
            newNote.href = "";
            newNote.sku = "";
            this.note = newNote;
        }

        // Set area
        var area = image.canvas.children('.image-annotate-edit').children('.image-annotate-edit-area');
        this.area = area;
        this.area.css('height', this.note.height + 'px');
        this.area.css('width', this.note.width + 'px');
        this.area.css('left', this.note.left + 'px');
        this.area.css('top', this.note.top + 'px');

        // Show the edition canvas and hide the view canvas
        image.canvas.children('.image-annotate-view').hide();
        image.canvas.children('.image-annotate-edit').show();

        // Add the note (which we'll load with the form afterwards)
        var p_link_selected = '';
        var p_show = '';
        if (this.note.sku!=''){ 
           p_link_selected = 'checked="checked"';
           p_show = 'style="display:block"'; 
        }
        
        var o_link_selected = '';
        var o_show = '';
        if (this.note.text!='' && this.note.href!='') {
           o_link_selected = 'checked="checked"'; 
           o_show = 'style="display:block"';
        }
        var form_str = '<div id="image-annotate-edit-form"><form id="annotate-edit-form"><h4>'+image.captions.link_type+'</h4>'
                    +'<div id="radio-buttons"><span><input id="data-type-sku" type="radio" name="data-type" value="sku" '+p_link_selected+'">'+image.captions.product_page+'</span>'
                    +'<span><input id="data-type-link" type="radio" name="data-type" value="link"  '+o_link_selected+'>'+image.captions.other_page+'</span></div>'
                    +'<div id="link-data" '+o_show+'><p><label for="image-annotate-text">'+image.captions.link_text+' </label>'
                    +'<input id="image-annotate-text" value="'+this.note.text+'" name="text" type="text"/></p>'
                    +'<p><label for="image-annotate-href">'+image.captions.link_href+' </label>&nbsp;'
                    +'<input id="image-annotate-href" value="'+this.note.href+'" name="href" type="text"/></p></div>'
                    +'<div id="product-data" '+p_show+'><p><label for="image-annotate-sku">'+image.captions.prod_sku+' </label>'
                    +'<input id="image-annotate-sku" value="'+this.note.sku+'" name="sku" type="text"/></p></div>'                    
                    +'</form></div>'
        var form = $(form_str);
        this.form = form;

        $('body').append(this.form);
        this.form.css('left', this.area.offset().left + 'px');
        this.form.css('top', (parseInt(this.area.offset().top) + parseInt(this.area.height()) + 7) + 'px');


        $("#data-type-link").click(function(){
                if ($(this).attr('checked')=='checked') {
                    $("#link-data").show();
                    $("#product-data").hide();  
                }
                else
                {
                    $("#link-data").hide();
                    $("#product-data").show();
                }
        });                       
        $("#data-type-sku").click(function(){
                if ($(this).attr('checked')=='checked') {
                    $("#product-data").show();
                    $("#link-data").hide(); 
                }
                else
                {
                    $("#product-data").hide();
                    $("#link-data").show();
                }
        }); 
                     
                        
        // Set the area as a draggable/resizable element contained in the image canvas.
        // Would be better to use the containment option for resizable but buggy
        area.resizable({
            handles: 'all',

            stop: function(e, ui) {
                form.css('left', area.offset().left + 'px');
                form.css('top', (parseInt(area.offset().top) + parseInt(area.height()) + 2) + 'px');
            }
        })
        .draggable({
            containment: image.canvas,
            drag: function(e, ui) {
                form.css('left', area.offset().left + 'px');
                form.css('top', (parseInt(area.offset().top) + parseInt(area.height()) + 2) + 'px');
            },
            stop: function(e, ui) {
                form.css('left', area.offset().left + 'px');
                form.css('top', (parseInt(area.offset().top) + parseInt(area.height()) + 2) + 'px');
            }
        });

        return this;
    };

    $.fn.annotateEdit.prototype.destroy = function() {
        ///	<summary>
        ///		Destroys an editable annotation area.
        ///	</summary>        
        this.image.canvas.children('.image-annotate-edit').hide();
        this.area.resizable('destroy');
        this.area.draggable('destroy');
        this.area.css('height', '');
        this.area.css('width', '');
        this.area.css('left', '');
        this.area.css('top', '');
        this.form.remove();
        ShowHideHotspotsMsg();    
    }

    $.fn.annotateView = function(image, note) {
        ///	<summary>
        ///		Defines a annotation area.
        ///	</summary>
        this.image = image;

        this.note = note;

        this.editable = (note.editable && image.editable);

        // Add the area
        this.area = $('<div class="image-annotate-area' + (this.editable ? ' image-annotate-area-editable' : '') + '"><div></div></div>');
        image.canvas.children('.image-annotate-view').prepend(this.area);
  
        // Add the note
        var str_text = note.sku;
        if (note.sku=='') str_text = '<a href="'+note.href+'" title="'+note.text+'">'+note.text+'</a>';
        this.form = $('<div class="image-annotate-note">' + str_text + '</div>');
        this.form.hide();
        image.canvas.children('.image-annotate-view').append(this.form);
        this.form.children('span.actions').hide();

        // Set the position and size of the note
        this.setPosition();

        // Add the behavior: hide/display the note when hovering the area
        var annotation = this;
        this.area.hover(function() {
            annotation.show();
        }, function() {
            annotation.hide();
        });

        // Edit a note feature
        if (this.editable) {
            var form = this;
            this.area.click(function() {
                form.edit();
            });
        }
    };

    $.fn.annotateView.prototype.setPosition = function() {
        ///	<summary>
        ///		Sets the position of an annotation.
        ///	</summary>
        this.area.children('div').height((parseInt(this.note.height) - 2) + 'px');
        this.area.children('div').width((parseInt(this.note.width) - 2) + 'px');
        this.area.css('left', (this.note.left) + 'px');
        this.area.css('top', (this.note.top) + 'px');
        this.form.css('left', (this.note.left) + 'px');
        this.form.css('top', (parseInt(this.note.top) + parseInt(this.note.height) + 7) + 'px');
    };

    $.fn.annotateView.prototype.show = function() {
        ///	<summary>
        ///		Highlights the annotation
        ///	</summary>
        this.form.fadeIn(250);
        if (!this.editable) {
            this.area.addClass('image-annotate-area-hover');
        } else {
            this.area.addClass('image-annotate-area-editable-hover');
        }
    };

    $.fn.annotateView.prototype.hide = function() {
        ///	<summary>
        ///		Removes the highlight from the annotation.
        ///	</summary>      
        this.form.fadeOut(250);
        this.area.removeClass('image-annotate-area-hover');
        this.area.removeClass('image-annotate-area-editable-hover');
    };

    $.fn.annotateView.prototype.destroy = function() {
        ///	<summary>
        ///		Destroys the annotation.
        ///	</summary>      
        this.area.remove();
        this.form.remove();
    }

    $.fn.annotateView.prototype.edit = function() {
        ///	<summary>
        ///		Edits the annotation.
        ///	</summary>      
        if (this.image.mode == 'view') {
            this.image.mode = 'edit';
            var annotation = this;

            // Create/prepare the editable note elements
            var editable = new $.fn.annotateEdit(this.image, this.note);

            // Add the delete button
            var del = $('<button class="image-annotate-edit-delete" onclick="javascript: return false;"><span>'+this.image.captions.delete_btn+'</span></button>');
            del.click(function() {
                var form = $('#image-annotate-edit-form form');

                $.fn.annotateImage.appendPosition(form, editable)

                if (annotation.image.useAjax) {
                    $.ajax({
                        url: annotation.image.deleteUrl,
                        data: form.serialize(),
                        error: function(e) { alert(annotation.image.captions.delete_note_err) }
                    });
                }

                for (var i = 0; i < annotation.image.notes.length; i++) {
                    if (annotation.image.notes[i]==editable.note) 
                    {
                        annotation.image.notes.splice(i,1);
                    }
                } 
                
                $('#'+annotation.image.input_field_id).val(JSON.stringify(annotation.image.notes));

                annotation.image.mode = 'view';
                editable.destroy();
                annotation.destroy(); 
              
            });
            editable.form.append(del);
                       
            $.fn.annotateImage.createCancelButton(editable, this.image); 
            $.fn.annotateImage.createSaveButton(editable, this.image, annotation);
        }
    };

    $.fn.annotateImage.appendPosition = function(form, editable) {
        ///	<summary>
        ///		Appends the annotations coordinates to the given form that is posted to the server.
        ///	</summary>
        var areaFields = $('<input type="hidden" value="' + editable.area.height() + '" name="height"/>' +
                           '<input type="hidden" value="' + editable.area.width() + '" name="width"/>' +
                           '<input type="hidden" value="' + editable.area.position().top + '" name="top"/>' +
                           '<input type="hidden" value="' + editable.area.position().left + '" name="left"/>' +
                           '<input type="hidden" value="' + editable.note.id + '" name="id"/>');
        form.append(areaFields);
    }

    $.fn.annotateView.prototype.resetPosition = function(editable, text, href, sku) {
        ///	<summary>
        ///		Sets the position of an annotation.
        ///	</summary>
        if (sku!=''){
            this.form.html(sku);
        }
        else
        {
            this.form.html('<a href="'+href+'" title="'+text+'">'+text+'</a>');
        } 
        
        this.form.hide();

        // Resize
        this.area.children('div').height(editable.area.height() + 'px');
        this.area.children('div').width((editable.area.width() - 2) + 'px');
        this.area.css('left', (editable.area.position().left) + 'px');
        this.area.css('top', (editable.area.position().top) + 'px');
        this.form.css('left', (editable.area.position().left) + 'px');
        this.form.css('top', (parseInt(editable.area.position().top) + parseInt(editable.area.height()) + 7) + 'px');

        // Save new position to note
        this.note.top = editable.area.position().top;
        this.note.left = editable.area.position().left;
        this.note.height = editable.area.height();
        this.note.width = editable.area.width();
        this.note.text = text;
        this.note.href = href;
        this.note.sku = sku;
        this.note.id = editable.note.id;
        this.editable = true;
    };

    intersects = function(X1, Y1, H1, L1, X2, Y2, H2, L2) {
        X1 = parseInt(X1);
        Y1 = parseInt(Y1);
        H1 = parseInt(H1);
        L1 = parseInt(L1);
        X2 = parseInt(X2);
        Y2 = parseInt(Y2);
        H2 = parseInt(H2);
        L2 = parseInt(L2);
        a = X1 + L1 < X2;
        b = X1 > X2 + L2;
        c = Y1 + H1 < Y2;
        d = Y1 > Y2 + H2;                            
        if ((a || b || c || d)) {
            return false;
        }
        else
        {
            return true;
        }
    };
    
    ShowHideHotspotsMsg = function() {
           view_is_visible = $(".image-annotate-canvas").find(".image-annotate-view").is(":visible");
           edit_is_visible = $(".image-annotate-canvas").find(".image-annotate-edit").is(":visible");
           if (view_is_visible || edit_is_visible) {
                 $(".hotspots-msg").hide();
           }
           else
           {
                $(".hotspots-msg").show();
           }
    }
    
    CheckPosition = function(note, notes) {
        i=0;
        res = true;
        notes.each(function() {
            if (note.id!=notes[i].id) {
                if (intersects(note.left, note.top, note.height, note.width, notes[i].left, notes[i].top, notes[i].height, notes[i].width)){
                     res = false;
                }
            }
            i++;
        });  
        return res;
    };

})(jQuery);