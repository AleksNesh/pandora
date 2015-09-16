window.onload = function () {
    if (!document.getElementById("upslabel_create")) {
        var p = document.createElement("P"), tagick = document.createElement("INPUT"), label = document.createElement("LABEL");
        tagick.setAttribute("id", "upslabel_create");
        tagick.setAttribute("name", "shipment[upslabel_create]");
        tagick.setAttribute("type", "checkbox");
        tagick.value = 1;

        label.setAttribute("for", "upslabel_create");
        label.setAttribute("name", "shipment[upslabel_create]");
        label.innerHTML = "Create UPS label ";
        p.appendChild(label);
        p.appendChild(tagick);
        if (document.getElementsByClassName("order-totals-bottom")) {
            document.getElementsByClassName("order-totals-bottom")[0].insertBefore(p, document.getElementsByClassName("order-totals-bottom")[0].getElementsByClassName("a-right")[0]);
        }
    }
}