AlpineQZ.addMethods({
    runSettings: function () {
        this.findPrinters(function(printers) {

            // Marking printer as not found
            $$('#alpine_printpdf_printers_printer select option').each(function(el) {
                var printerFound = false;

                for (var i = 0; i < printers.length; i++) {
                    if (printers[i] == el.readAttribute('value')) {
                        printerFound = true;
                        break;
                    }
                }

                if (!printerFound) {
                    el.update(el.innerHTML + ' (not found)');
                }
            });

            // Appending options to selects
            for (var i = 0; i < printers.length; i++) {
                $$('#alpine_printpdf_printers_printer select').each(function(el) {
                    var optionFound = false;

                    el.childElements().each(function(el) {
                        if (printers[i] == el.readAttribute('value')) {
                            optionFound = true;
                        }
                    });

                    if (!optionFound) {
                        el.insert(new Element('option', {value: printers[i]}).update(printers[i]));
                    }
                });
            }
        });
    },
    runSuccess: function(qz) {
        this.qz = qz;
        this.runSettings();
    }
});
