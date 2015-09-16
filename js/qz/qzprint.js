AlpineQZ.addMethods({
    runPrinting: function() {
        this.printPDF(this.options.printer, this.options.pdfUrl);
    },
    runSuccess: function(qz) {
        this.qz = qz;
        this.runPrinting();
    },
    donePrintingSuccess: function() {
        if (this.options.closeWindow) {
            window.close();
        }
    }
});
