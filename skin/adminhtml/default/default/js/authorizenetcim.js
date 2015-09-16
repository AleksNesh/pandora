function authorizenetcimuseSavedCC(ccSavedId) {     	

	document.getElementById('authorizenetcim_cc_new').checked = false;
		   
	var t = document.getElementById('authorizenetcim_cc_type');
		t.setAttribute("class","");
		t.value = document.getElementById('cctype'+ccSavedId).value;
	
	var cc = document.getElementById('authorizenetcim_cc_number');
		cc.setAttribute("class","");
		cc1 = document.getElementById('fullcc'+ccSavedId).value;
		cc.value = parseInt(cc1,10);

	var em = document.getElementById('authorizenetcim_expiration');
		em.setAttribute("class","");
		em.value= document.getElementById('expmonth'+ccSavedId).value;

	var ey= document.getElementById('authorizenetcim_expiration_yr');
		ey.setAttribute("class","");
		ey.value= document.getElementById('expyear'+ccSavedId).value;

	var cvn = document.getElementById('authorizenetcim_cc_cid');
		cvn.setAttribute("class","");

		if (t.value=='AE')
		{
			cvn.value='1111';
		}
		else
		{
			cvn.value='111';
		}

	var rb = document.getElementById('authorizenetcim_cc_new');
		rb.checked=false;
		rb.setAttribute("class","");				

	var li = document.getElementById('authorizenetcim_cc_entered');
		li.style.display = 'none';		
};

function authorizenetcimuseNewCC() {

	// This will uncheck the saved cc
	var payments = document.getElementsByName('payment[ccsave_id]');

	for(var i = 0; i < payments.length; i++){
		var element = payments[i].getAttribute('id');
		document.getElementById(element).checked = false;
	}
	
	var t = document.getElementById('authorizenetcim_cc_type');
		t.setAttribute("class","required-entry validate-cc-type-select");
		t.value='';	
		
	var cc = document.getElementById('authorizenetcim_cc_number');
		cc.setAttribute("class","input-text validate-cc-number");
		cc.value='';	

	var em = document.getElementById('authorizenetcim_expiration');
		em.setAttribute("class","month validate-cc-exp required-entry");
		em.value='';	

	var ey= document.getElementById('authorizenetcim_expiration_yr');
		ey.setAttribute("class","year required-entry");
		ey.value='';

	var cvn = document.getElementById('authorizenetcim_cc_cid');
		cvn.setAttribute("class","required-entry input-text validate-cc-cvn");
		cvn.value='';

	var rb = document.getElementsByName('payment[ccsave_id]');
		for(var i=0;i<rb.length;i++)
			rb[i].checked = false;
		//rb.checked=false;
		//rb.setAttribute("class","");

	var li = document.getElementById('authorizenetcim_cc_entered');
		li.style.display = 'block';
	
}; 