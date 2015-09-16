/*
 * alchemyQuery.js
 * Author: Logan Wilkerson
 *
 * The jquery plugin of things I find useful
 */
(function($) {
    $.fn.toggleHeight = function(time, shrink) {
        this.toggleDimension({
            time: time,
            shrink: shrink
        });
    };

    $.fn.toggleWidth = function(time, shrink) {
        this.toggleDimension({
            time: time,
            shrink: shrink,
            dimension: width
        });
    };

    /*
     * toggleDimension
     */
    $.fn.toggleDimension = function(options) {
        options = $.extend({
            dimension: "height",
            shrink: false,
            time: 500
        }, options);

        return this.each(function() {
            var minDim = parseInt($(this).css('min-' + options.dimension));
            var shrink = typeof options.shrink != 'undefined' ? options.shrink : $(this)[options.dimension]() != minDim;

            var obj = {};
            if (shrink) {
                obj[options.dimension] = minDim;
                $(this).animate(obj, options.time);
            } else {
                var maxDim = parseInt($(this).css('max-' + options.dimension));
                obj[options.dimension] = maxDim;
                $(this).animate(obj, options.time);
            }
        });
    };

    /*
     * rotates an elements
     */
    $.fn.rotate = function(degrees) {
        var rotateCSS = 'rotate(' + degrees + 'deg)';
        var css = {
            '-webkit-transform': rotateCSS,
            '-moz-transform': rotateCSS,
            '-ms-transform': rotateCSS,
            'transform': rotateCSS
        };
        return this.each(function() {
            $(this).css(css);
        });
    };

    /*
     * pagify
     * Converts a containing element so that its contents can be paged through.
     * Allows for the content to be loaded in page chunks so that not all the pages need be loaded at
     * once. Calling pagify on an element will EMPTY THAT ELEMENT before setting it up to be paged.
     *
     * options
     * width: width of a single page
     * default: 420
     *
     * height: width of a single page
     * default: 350
     *
     * pagesPerChunk: How many pages are loaded in at once (Do not change once set)
     * default: 2
     *
     * curPageNum: the current page number (should probably always be zero at the start)
     * default: 0
     *
     * time: the animation time of a page turn. (in ms)
     * default: 250
     *
     * numPages: the current number of pages
     * default: 0
     *
     * buffer: the number of pages before the user reaches the end to load the next pageChunk
     * default: 0
     *
     * getPages: a function that returns the next chunk of pages, should return null if there are
     * no more chunks
     *
     * pagify adds a number of custom events to the element
     * forward.pagify - called when the element should attempt to page forward one page
     * backward.pagify - called when the element should attempt to page backward one page
     * pageturn.pagify - called whenever a pageturn occurs.
     *
     * forward and backward should be triggered by whatever page turning element you want
     * pageturn is called by forward/backward and can be used to update your page when a page
     * turn occurs. (such as to say that the user is on page X of Y)
     */
    $.fn.pagify = function(options) {
        return this.each(function() {
            var ele = $(this);
            ele.off('foward.pagify backward.pagify');
            ele.empty();
            /*
             * Stores pagifyOptions on the element so in custom pagify events
             * the pagify state can be accessed.
             */
            this.pagifyOptions = $.extend({
                width: ele.width(),
                height: ele.height(),
                pagesPerChunk: 2,
                curPageNum: 0,
                time: 250,
                numPages: 0,
                buffer: 0,
                getPages: function() {
                    if (this.numPages == 10) {
                        return null;
                    }
                    this.numPages += this.pagesPerChunk;
                    var div = $('<div>');
                    div.css('background-color', alchemy.randomColor()).css('overflow', 'hidden');
                    return div;
                }
            }, options);

            ele.css({
                overflow: 'hidden',
                width: this.pagifyOptions.width,
                height: this.pagifyOptions.height
            });

            //Add Div to hold page chunks
            var pageContainer = $('<div>');
            pageContainer.css({
                width: '100%',
                height: '100%'
            });
            ele.append(pageContainer);

            //Loads the first page chunk
            var firstChunk = getChunk(this.pagifyOptions);
            pageContainer.append(firstChunk);

            (function(pageContainer, options) {

                /*
                 * If false turned will prevent any page turning from occuring.
                 * This is to ensure that if a page turn is made the animation is
                 * complete before another turn occurs. This is because the plugin
                 * relies on the current margin of a page-chunk and turning too fast can
                 * make that unreliable.
                 */
                var turned = true;

                //Returns the chunkNumber of a page
                var getChunkNum = function(pageNum) {
                    if (pageNum === 0)
                        return 0;
                    return parseInt(pageNum / options.pagesPerChunk);
                };

                var changePage = function(mult) {
                    if (!turned)
                        return;

                    if (options.curPageNum + mult < 0) {
                        return;
                    }
                    //Prevent further page turning until done.
                    turned = false;

                    options.curPageNum += mult;

                    //Determine if new pages should be loaded
                    if (options.curPageNum > (options.numPages - 1 - options.buffer)) {
                        var nextChunk = getChunk(options);
                        if (nextChunk) {
                            pageContainer.append(nextChunk);

                            var newWidth = pageContainer.width() + (parseInt(options.width) * options.pagesPerChunk);
                            pageContainer.width(newWidth);

                            /** Cam added
adjustProductSize();**/
                        }
                    }

                    //Determine if outside page boundary
                    if (options.curPageNum == options.numPages) {
                        turned = true;
                        options.curPageNum = options.numPages - 1;
                        return;
                    }


                    var chunkNum = getChunkNum(options.curPageNum);
                    var prevChunkNum;
                    if (mult == 1) {
                        prevChunkNum = getChunkNum(options.curPageNum - 1);
                    } else {
                        prevChunkNum = getChunkNum(options.curPageNum);
                    }

                    var tarChunk = $(pageContainer.children()[prevChunkNum]);
                    /** Cam removed **/
                    var margin = Math.ceil(parseInt(tarChunk.css('margin-left')) / options.width) * options.width - (options.width * mult);
                    /** end Cam removed **/

                    /** Cam added **/
                    //var pageWidth = $('.page', tarChunk).first().width();
                    //var margin = Math.ceil(parseInt(tarChunk.css('margin-left')) / pageWidth) * pageWidth - (pageWidth*mult);
                    /** end Cam added **/
                    tarChunk.animate({
                        'margin-left': margin
                    }, options.time, function() {
                        turned = true;
                    });
                    pageContainer.parent().trigger('pageturn.pagify');
                };

                //Sets up custom events
                pageContainer.parent().on('forward.pagify', function() {
                    changePage(1);
                });
                pageContainer.parent().on('backward.pagify', function() {
                    changePage(-1);
                });

            })(pageContainer, this.pagifyOptions);
        });
    };

    var getChunk = function(o) {
        var nextChunk = o.getPages();
        if (!nextChunk)
            return null;
        nextChunk.css({
            'float': 'left',
            'width': o.width * o.pagesPerChunk,
            'height': o.height
        });
        $(nextChunk).children().each(function() {
            $(this).css({
                'height': '100%',
                'width': o.width,
                'float': 'left'
            });
        });
        return nextChunk;
    };

    /*
     * Creates a message bubble on the mouse
     */
    $.fn.bubble = function(o) {
        o = $.extend({
            events: 'click',
            delay: 100,
            data: '<div style="padding:5px;">Hey! This is a bubble box</div>',
            'background-color': 'white',
            'border-color': 'black'
        }, o);

        var createBubble = function(e) {
            $(this).off(o.events, createBubble);
            var container = $('<div>').addClass('bubble-container');
            var arrow = $('<div>').addClass('bubble-arrow');
            var infobox = $('<div>').addClass('bubble-infoBox');

            if (Object.prototype.toString.call(o.data) === '[object Function]') {
                infobox.html(o.data());
            } else {
                infobox.html(o.data);
            }

            $('body').append(container);
            container.append(infobox);
            container.append(arrow);
            infobox.css('margin-left', arrow.width() - parseInt(infobox.css('border-width')));

            container.css({
                top: e.pageY - arrow.height() / 2 - arrow.position().top + 1,
                left: e.pageX - arrow.width() / 2
            });



            (function(ele, container, o) {
                var hover = true;
                container.children().hover(
                    function(evt) {
                        hover = true;
                        clearTimeout(container.timer);
                    },
                    function(evt) {
                        hover = false;
                        container.timer = setTimeout(function() {
                            if (!hover) {
                                container.remove();
                                ele.on(o.events, createBubble);
                            }
                        }, o.delay);
                    }
                );
            }($(this), container, o));
        };

        return this.on(o.events, createBubble);


    };

}(jQuery));
