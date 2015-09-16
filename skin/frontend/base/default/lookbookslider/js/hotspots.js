
jQuery.extend({
        setHotspots : function(slide, hotspots) {
           
                if (!hotspots) return;
                var i=0;
                hotspots.each(function() {
                   if (!document.getElementById(hotspots[i].id)) {
                       var imgwidth = slide.width();
                       var scale= imgwidth/hotspots[i].imgW;
                       if(typeof hotspots[i].imgW == 'undefined' || hotspots[i].imgW === null){scale = 1;hotspots[i].imgH = slide.height();}
                       var offsetH = parseInt((hotspots[i].imgH*scale - slide.height())/2);
                       var left_pos = hotspots[i].left/(hotspots[i].imgW/100); //procent
                       var top_pos = hotspots[i].top/(hotspots[i].imgH/100);
                       var width_pos = hotspots[i].width/(hotspots[i].imgW/100);
                       var height_pos = hotspots[i].height/(hotspots[i].imgH/100);
                       slide.append('<div class="hotspot" id="'+hotspots[i].id+'" style="left:'+left_pos+'%; top:'+top_pos+'%; width:'+width_pos+'%; height:'+height_pos+'%;">'+hotspots[i].text+'</div>');
                       var infoblock = slide.find('#'+hotspots[i].id +' .product-info');
                       var infowidth = infoblock.width();             
                       var hspt_width_hf = parseInt(hotspots[i].width*scale/2);
                       var leftposition = hotspots[i].left*scale+hspt_width_hf+7;                
                       infoblock.find('.info-icon').css('left',hspt_width_hf+'px'); 
                       if (((leftposition + infowidth + 10)> imgwidth) && (leftposition>(imgwidth-leftposition))) 
                       {
                          if ( jQuery.browser.msie && jQuery.browser.version=='8.0') {
                              if (leftposition-5<infowidth) {
                              infoblock.css('width', leftposition-20 +'px');
                              infowidth = infoblock.width();      
                              }
                              infoblock.css('left', '50%');
                              infoblock.css('margin-left','-'+infowidth-2*parseInt(infoblock.css('padding-left'))+'px');
                          }
                          else
                          {
                              infoblock.css('left', '');
                              infoblock.css('right', '50%');               
                          } 
                          
                          if (leftposition-5<infowidth) {
                              infoblock.css('width', leftposition-20 +'px');
                              infowidth = infoblock.width();
                          }      
                        }
                        else
                        {
                             infoblock.css('left', '50%');
                             if ((imgwidth-leftposition-5)<infowidth) {
                                  infoblock.css('width', imgwidth-leftposition-20 +'px');
                                  infowidth = infoblock.width();      
                              }  
                        }
                        var imgheight = slide.height();
                        var infoheight = infoblock.height(); 

                        var hspt_height_hf = parseInt(hotspots[i].height*scale/2);
                        var topposition = hotspots[i].top*scale+hspt_height_hf; 
                        if (((topposition + infoheight + 5)> imgheight) && (topposition>(imgheight-topposition))) 
                        {
                          if ( jQuery.browser.msie && jQuery.browser.version=='8.0') {
                              if (topposition-5<infoheight) {
                               infoblock.css('height', topposition-10 +'px');
                               infoheight = infoblock.height();      
                              }
                              infoblock.css('top', '50%');
                              infoblock.css('margin-top', '-'+infoheight-2*parseInt(infoblock.css('padding-top'))+'px');
                          }
                          else
                          {
                              infoblock.css('top', '');
                              infoblock.css('bottom', hspt_height_hf+'px');                 
                          }
                          if (topposition-5<infoheight) {
                              infoblock.css('height', '50%');
                              infoheight = infoblock.height();      
                          }
                        }
                        else
                        {
                             infoblock.css('top', '50%');
                             if ((imgheight-topposition-5)<infoheight) {
                                  infoblock.css('height', imgheight-topposition-10 +'px');
                                  infoheight = infoblock.height();      
                             }
                        }   
                        /////// set position for hotspot-icon /////////////
                        var icon = slide.find('#'+hotspots[i].id +' .hotspot-icon');
                        icon.css('left','50%');
                        icon.css('top','50%');
                        icon.css('margin-left','-'+hotspots[i].icon_width/2+'px');
                        icon.css('margin-top','-'+hotspots[i].icon_height/2+'px');
                        


                        i++;
                  }          
              });
        },
        updateHotspots : function(slide, hotspots) {
            if (!hotspots) return;
                            var i=0;
                hotspots.each(function() {
                if (document.getElementById(hotspots[i].id)) {
                       var imgwidth = slide.width();
                       var scale= imgwidth/hotspots[i].imgW;
                       if(typeof hotspots[i].imgW == 'undefined' || hotspots[i].imgW === null){scale = 1;hotspots[i].imgH = slide.height();}
                       var offsetH = parseInt((hotspots[i].imgH*scale - slide.height())/2);
                       jQuery('#'+hotspots[i].id).css('left',parseInt(hotspots[i].left*scale)+'px');
                       jQuery('#'+hotspots[i].id).css('top',parseInt(hotspots[i].top*scale - offsetH)+'px');
                        i++;
                  }             
              });
        }
        
});




function LookbookAddToCart(button, url) {
       var form = button.up('form');
       var oldUrl = form.action;
      if (url) {form.action = url;}
      var e = null;
      try {
           form.submit();
      } catch (e) {
      }
      if (e) {
        throw e;
      }
      if (button && button != 'undefined') {
         button.disabled = true;
      }
};
          
jQuery(document).ready(function() {
    jQuery('.product-info a').on('click touchend', function(e) {
        var el = jQuery(this);
        var link = el.attr('href');
        window.location = link;
    });
    var handleClick = 'ontouchstart' in document.documentElement ? 'touchstart' : 'click';
    
   if ("ontouchstart" in document.documentElement) {
       jQuery(document).on('touchstart', 'body', function(e) {
        console.log('del');
        jQuery(".hotspot").removeClass('hover');
       });
       jQuery(document).on('touchstart', '.hotspot', function(e){
        console.log('add');
        jQuery(this).addClass('hover'); 
        e.stopPropagation();
       });
       jQuery(document).on('touchstart', '.hotspot .product-info', function(event){
        event.stopPropagation(); 
       });
        
   } 
    
});              