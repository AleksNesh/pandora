/**
 * alchemy-image
 * Author: Logan Wilkerson
 *
 * Image related javascript tools to go into the alchemy suite.
 */

var alchemy = alchemy || {};
(function(A) {

    /*
     * loadImages
     * Loads image or images in sources. Once all the images are loaded
     * it will call the function success with the Image objects. If
     * there is an error the function failure can be called instead
     *
     * @param {String/Array} Single src string or array of src strings
     * @param {Function/Object} Success function or Object containing
     * the callback functions and options
     * options.success(images) - success function that will be called
     * options.error - failure function to call
     * options.cache - whether the images should be caches
     */
    A.loadImages = function(sources, options) {
        sources = A.isArray(sources) ? sources : [sources];
        options = !A.isFunction(options) ? options : {
            success: options
        };
        A.loadImages._cache = A.loadImages._cache || {};
        var images = [];
        var imagesLoaded = 0;
        var onload = function() {
            imagesLoaded += 1;
            if (sources.length == 1) {
                options.success(image);
            } else if (imagesLoaded == sources.length && options.success) {
                options.success(images);
            }
        };
        var onerror = function() {
            if (options.error) {
                options.error();
            }
        };
        for (var i = 0; i < sources.length; i++) {
            if (options.cache && (sources[i] in A.loadImages._cache)) {
                images.push(A.loadImages._cache[sources[i]]);
                imagesLoaded += 1;
                if (sources.length == 1) {
                    options.success(A.loadImages._cache[sources[i]]);
                } else if (imagesLoaded == sources.length && options.success) {
                    options.success(images);
                }
                continue;
            }
            var image = new Image();
            images.push(image);
            if (options.cache) {
                A.loadImages._cache[sources[i]] = image;
            }
            if (options.crossOrigin) {
                image.crossOrigin = 'anonymous';
            }
            image.onload = onload;
            image.onerror = onerror;
            image.src = sources[i];
        }
    };

    // Clears the image cache
    A.clearImageCache = function() {
        for (var key in A.loadImages._cache) {
            if (A.loadImage._cache.hasOwnProperty(key)) {
                delete A.loadImages._cache[key];
            }
        }
    };

    /*
     * editImage
     * Requires the canvas element
     * Edits a given source image based on the settings in options.
     * Once the edit is complete a base64 encoded string of the image
     * is passed to the callback function. This function does NOT
     * edit the original image, and will not work with cross-domain
     * images
     *
     * @param {String} source - the source image
     * @param {Object} options - the options for editing the image
     * It can have the following attributes
     * options.callback {Function} - the callback function to pass
     * the edit image url too.
     * options.width - the width of the final image, defaults to
     * original image width
     * options.height - the height of the final image, defaults to
     * original image height
     * option.destX - The destination x cord on the final canvas
     * option.destY - The destination y cord on the final canvas
     * option.
     * option.toDataURLArgs - This function uses the canvas method
     * toDataURL to get out the image data. This option is an
     * array of arguments to pass to that function. Defaults
     * to ['image/png']
     */
    A.editImage = function(source, options, callback) {
        var opt = options || {};
        var sourceImage = new Image();
        sourceImage.onload = function() {
            opt.width = typeof opt.width != 'undefined' ? opt.width : sourceImage.width;
            opt.height = typeof opt.height != 'undefined' ? opt.height : sourceImage.height;
            opt.srcX = typeof opt.srcX != 'undefined' ? opt.srcX : 0;
            opt.srcY = typeof opt.srcY != 'undefined' ? opt.srcY : 0;
            opt.srcWidth = typeof opt.srcWidth != 'undefined' ? opt.srcWidth : sourceImage.width;
            opt.srcHeight = typeof opt.srcHeight != 'undefined' ? opt.srcHeight : sourceImage.height;
            opt.destX = typeof opt.destX != 'undefined' ? opt.destX : 0;
            opt.destY = typeof opt.destY != 'undefined' ? opt.destY : 0;
            opt.destWidth = typeof opt.destWidth != 'undefined' ? opt.destWidth : opt.width;
            opt.destHeight = typeof opt.destHeight != 'undefined' ? opt.destHeight : opt.height;
            opt.toDataURLArgs = typeof opt.toDataURLArgs != 'undefined' ? opt.toDataURLArgs : ['image/png'];
            var canvas = document.createElement('canvas');
            canvas.width = opt.width;
            canvas.height = opt.height;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(sourceImage, opt.srcX, opt.srcY, opt.srcWidth, opt.srcHeight, opt.destX, opt.destY, opt.destWidth, opt.destHeight);

            if (opt.background) {
                ctx.globalCompositeOperation = 'destination-over';
                ctx.fillStyle = opt.background;
                ctx.fillRect(0, 0, opt.width, opt.height);
            }
            callback(canvas.toDataURL.apply(canvas, opt.toDataURLArgs));
        };
        sourceImage.src = source;
    };

    A.addImage = function(source, image, options, callback) {
        var opt = options || {};
        var sourceImage = new Image();
        sourceImage.onload = function() {
            opt.destX = typeof opt.destX != 'undefined' ? opt.destX : 0;
            opt.destY = typeof opt.destY != 'undefined' ? opt.destY : 0;
            opt.destWidth = typeof opt.destWidth != 'undefined' ? opt.destWidth : image.width;
            opt.destHeight = typeof opt.destHeight != 'undefined' ? opt.destHeight : image.height;
            opt.toDataURLArgs = typeof opt.toDataURLArgs != 'undefined' ? opt.toDataURLArgs : ['image/png'];
            var canvas = document.createElement('canvas');
            canvas.width = sourceImage.width;
            canvas.height = sourceImage.height;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(sourceImage, 0, 0);
            ctx.drawImage(image, opt.destX, opt.destY, opt.destWidth, opt.destHeight);
            callback(canvas.toDataURL.apply(canvas, opt.toDataURLArgs));
        };
        sourceImage.src = source;
    };

    A.getWhitespaceBoundary = function(source, callback) {
        var sourceImage = new Image();
        sourceImage.onload = function() {
            var canvas = document.createElement('canvas');
            canvas.width = sourceImage.width;
            canvas.height = sourceImage.height;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(sourceImage, 0, 0, sourceImage.width, sourceImage.height);
            var imageData = ctx.getImageData(0, 0, sourceImage.width, sourceImage.height);
            var top = sourceImage.height / 2,
                left = sourceImage.width / 2,
                right = left,
                bottom = top;
            var count = 0;
            for (var tempY = 0; tempY < sourceImage.height; tempY++) {
                for (var tempX = 0; tempX < sourceImage.width; tempX++) {
                    var pixel = A.getPixelData(imageData, sourceImage.width, tempX, tempY);
                    if (!A.isWhitePixel(pixel)) {
                        if (tempX < left) {
                            left = tempX;
                        }
                        if (tempX > right) {
                            right = tempX + 1;
                        }
                        if (tempY < top) {
                            top = tempY;
                        }
                        if (tempY > bottom) {
                            bottom = tempY + 1;
                        }
                    }
                }
            }
            callback({
                top: top,
                left: left,
                right: right,
                bottom: bottom
            });
        };
        sourceImage.src = source;
    };


    //http://blogs.windows.com/windows/archive/b/developers/archive/2011/02/15/canvas-direct-pixel-manipulation.aspx
    A.colorOffset = {
        red: 0,
        green: 1,
        blue: 2,
        alpha: 3
    };
    A.getPixelData = function(imageData, width, x, y) {
        return {
            red: imageData.data[(4 * y * width) + (4 * x) + A.colorOffset.red],
            green: imageData.data[(4 * y * width) + (4 * x) + A.colorOffset.green],
            blue: imageData.data[(4 * y * width) + (4 * x) + A.colorOffset.blue],
            alpha: imageData.data[(4 * y * width) + (4 * x) + A.colorOffset.alpha]
        };
    };

    A.isWhitePixel = function(pixel) {
        return pixel.red === 255 && pixel.green === 255 && pixel.blue === 255 || pixel.alpha === 0;
    };
}(alchemy));
