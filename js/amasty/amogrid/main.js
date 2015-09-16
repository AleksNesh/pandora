/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
function orderLoadMore(link, code){
    var showDialog = function (html){
        var title = 'Order Detailed Information';
        var dialog = Dialog.info(html, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            width: 700,
            height: 600,
            zIndex: 1000,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            id: 'viewDialog'
        });
        
        window.setTimeout(function(){
            dialog.content.scrollTop = dialog.content.select("#col_" + code)[0].offsetTop
        }, 100)

    }
    
    new Ajax.Request(link.href, {
        method:'get',
        onSuccess: function(transport) {
            var response = transport.responseText || "no response text";
            showDialog(response);
        },
        onFailure: function() { alert('Something went wrong...'); }
    });
    return false;
}