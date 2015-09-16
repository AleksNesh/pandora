function authorizenetcimuseSavedCC(ccSavedId) {
    document.getElementById('authorizenetcim_cc_new').checked = false;

    var t      = document.getElementById('authorizenetcim_cc_type')
      , cc     = document.getElementById('authorizenetcim_cc_number')
      , cc1    = document.getElementById('fullcc'+ccSavedId).value
      , em     = document.getElementById('authorizenetcim_expiration')
      , ey     = document.getElementById('authorizenetcim_expiration_yr')
      , cvn    = document.getElementById('authorizenetcim_cc_cid')
      , rb     = document.getElementById('authorizenetcim_cc_new')
      , li     = document.getElementById('authorizenetcim_cc_entered');

    // credit card type
    t.setAttribute("class","");
    t.value = document.getElementById('cctype'+ccSavedId).value;

    // credit card number
    cc.setAttribute("class","");
    cc.value = parseInt(cc1,10);

    // credit card expiry month
    em.setAttribute("class","");
    em.value = document.getElementById('expmonth'+ccSavedId).value;

    // credit card expiry year
    ey.setAttribute("class","");
    ey.value = document.getElementById('expyear'+ccSavedId).value;

    // credit card CVV
    cvn.setAttribute("class","");

    if (t.value == 'AE') {
        cvn.value = '1111';
    } else {
        cvn.value = '111';
    }


    rb.checked = false;
    rb.setAttribute("class","");

    li.style.display = 'none';
}

function authorizenetcimuseNewCC() {

    // This will uncheck the saved cc
    var payments = document.getElementsByName('payment[ccsave_id]');

    for(var i = 0; i < payments.length; i++){
        var element = payments[i].getAttribute('id');
        document.getElementById(element).checked = false;
    }

    var t      = document.getElementById('authorizenetcim_cc_type')
      , cc     = document.getElementById('authorizenetcim_cc_number')
      , em     = document.getElementById('authorizenetcim_expiration')
      , ey     = document.getElementById('authorizenetcim_expiration_yr')
      , cvn    = document.getElementById('authorizenetcim_cc_cid')
      , rb     = document.getElementsByName('payment[ccsave_id]')
      , li     = document.getElementById('authorizenetcim_cc_entered');

    // credit card type
    t.setAttribute("class","required-entry validate-cc-type-select");
    t.value ='';

    // credit card number
    cc.setAttribute("class","input-text validate-cc-number");
    cc.value ='';

    // credit card expiry month
    em.setAttribute("class","month validate-cc-exp required-entry");
    em.value ='';

    // credit card expiry year
    ey.setAttribute("class","year required-entry");
    ey.value ='';

    // credit card CVV
    cvn.setAttribute("class","required-entry input-text validate-cc-cvn");
    cvn.value ='';

    for(var i=0;i<rb.length;i++) {
        rb[i].checked = false;
    }
    //rb.checked=false;
    //rb.setAttribute("class","");

    li.style.display = 'block';
}
