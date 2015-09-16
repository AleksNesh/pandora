var AlpineQZ = Class.create();
AlpineQZ.prototype = {
    initialize: function(qzDir, options) {
        this.qzDir   = qzDir;
        this.options = options || {};
    },
    deploy: function() {
        var self = this;
        function deployQZ() {
            var attributes = {id: "qz", code:'qz.PrintApplet.class',
                archive: self.qzDir + '/qz-print.jar', width:1, height:1};
            var parameters = {jnlp_href: self.qzDir + '/qz-print_jnlp.jnlp',
                cache_option:'plugin', disable_logging:'false',
                initial_focus:'false'};
            if (deployJava.versionCheck("1.7+") == true) {}
            else if (deployJava.versionCheck("1.6+") == true) {
                delete parameters['jnlp_href'];
            }
            deployJava.runApplet(attributes, parameters, '1.5');
        }

        deployQZ();
    },
    runFail: function(err) {
        console.log(err);
    },
    runSuccess: function(qz) {
        this.qz = qz;
    },
    donePrinting: function() {
        // Alert error, if any
        if (qz.getException()) {
            this.donePrintingFail();
        } else {
            this.donePrintingSuccess();
        }
    },
    donePrintingFail: function() {
        // Alert error, if any
        if (qz.getException()) {
            alert('Error printing:\n\n\t' + qz.getException().getLocalizedMessage());
            qz.clearException();
        }
    },
    donePrintingSuccess: function() {

    },
    findPrinters: function (callback) {
        if (isLoaded()) {
            // Searches for a locally installed printer with a bogus name
            qz.findPrinter('\\{bogus_printer\\}');

            // Automatically gets called when "qz.findPrinter()" is finished.
            window['qzDoneFinding'] = function() {
                // Get the CSV listing of attached printers
                var printers = qz.getPrinters().split(',');

                if (callback) {
                    callback(printers);
                }

                // Remove reference to this function
                window['qzDoneFinding'] = null;
            };
        }
    },
    findPrinter: function(name, callback) {
        if (isLoaded()) {
            // Searches for locally installed printer with specified name
            qz.findPrinter(name);

            // Automatically gets called when "qz.findPrinter()" is finished.
            window['qzDoneFinding'] = function() {
                var printer = qz.getPrinter();

                if (callback) {
                    callback();
                }
                // Remove reference to this function
                window['qzDoneFinding'] = null;
            };
        }
    },
    printPDF: function(printerName, pathToPdf) {

        var self = this;

        var callback = function(){

            if (notReady()) {
                return;
            }
            qz.appendPDF(pathToPdf);
            qz.setCopies(1);

            if (self.options.paperSizeX && self.options.paperSizeY) {
                qz.setPaperSize(self.options.paperSizeX, self.options.paperSizeY);
            } else {
                qz.setPaperSize('210mm', '297mm'); // A4 size
            }

            // Automatically gets called when "qz.appendPDF()" is finished.
            window['qzDoneAppending'] = function() {
                // Tell the applet to print PostScript.
                qz.printPS();

                // Remove reference to this function
                window['qzDoneAppending'] = null;
            };
        };

        this.findPrinter(printerName, callback);
    }
};

/**
* Automatically gets called when applet has loaded.
*/
function qzReady() {
    // Setup our global qz object
    window["qz"] = document.getElementById('qz');
    if (qz) {
        try {
            qz.getVersion();
            if (alpineQz) {
                if (!alpineQz.qz) {
                    alpineQz.runSuccess(qz);
                }
            }
        } catch (err) {
            if (alpineQz) {
                if (!alpineQz.qz) {
                    alpineQz.runFail(err);
                }
            }
        }
    } else {
        if (alpineQz) {
            if (!alpineQz.qz) {
                alpineQz.runFail(err);
            }
        }
    }
}

/**
* Returns whether or not the applet is not ready to print.
* Displays an alert if not ready.
*/
function notReady() {
    // If applet is not loaded, display an error
    if (!isLoaded()) {
        return true;
    }
    // If a printer hasn't been selected, display a message.
    else if (!qz.getPrinter()) {
        alert('Printer isn\'t configured properly.');
        return true;
    }
    return false;
}

/**
* Returns is the applet is not loaded properly
*/
function isLoaded() {
    if (!qz) {
        alert('Error:\n\n\tPrint plugin is NOT loaded!');
        return false;
    } else {
        try {
            if (!qz.isActive()) {
                alert('Error:\n\n\tPrint plugin is loaded but NOT active!');
                return false;
            }
        } catch (err) {
            alert('Error:\n\n\tPrint plugin is NOT loaded properly!');
            return false;
        }
    }
    return true;
}

/**
* Automatically gets called when "qz.print()" is finished.
*/
function qzDonePrinting() {
    if (alpineQz) {
        alpineQz.donePrinting();
    }
}


/***************************************************************************
****************************************************************************
* *                          HELPER FUNCTIONS                             **
****************************************************************************
***************************************************************************/


/***************************************************************************
* Gets the current url's path, such as http://site.com/example/dist/
***************************************************************************/
function getPath() {
    var path = window.location.href;
    return path.substring(0, path.lastIndexOf("/")) + "/";
}

/**
* Fixes some html formatting for printing. Only use on text, not on tags!
* Very important!
*   1.  HTML ignores white spaces, this fixes that
*   2.  The right quotation mark breaks PostScript print formatting
*   3.  The hyphen/dash autoflows and breaks formatting
*/
function fixHTML(html) {
    return html.replace(/ /g, "&nbsp;").replace(/’/g, "'").replace(/-/g,"&#8209;");
}

/**
* Equivelant of VisualBasic CHR() function
*/
function chr(i) {
    return String.fromCharCode(i);
}

/***************************************************************************
* Prototype function for allowing the applet to run multiple instances.
* IE and Firefox may benefit from this setting if using heavy AJAX to
* rewrite the page.  Use with care;
* Usage:
*    qz.allowMultipleInstances(true);
***************************************************************************/
function allowMultiple() {
    if (isLoaded()) {
        var multiple = qz.getAllowMultipleInstances();
        qz.allowMultipleInstances(!multiple);
        alert('Allowing of multiple applet instances set to "' + !multiple + '"');
    }
}