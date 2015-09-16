
var is_check_giftwrap_form = false;
        function showEditForm(newUrl,id,hasgiftcard){
            if(id){
            	var url = newUrl+'id/'+id;
            	if(!hasgiftcard){                	
            		TINY.box.show(url, 1, 860, 640, 1);
            	}else{
                	
            		TINY.box.show(url, 1, 860, 980, 1);
            	}
            }else{
            	var url = newUrl;
            	TINY.box.show(url, 1, 860, 655, 1);
            }
            afterShowGiftWrapForm();
        }
		
        function deleteGiftbox(deleteUrl,id){			
        	var url = deleteUrl+'id/'+id;
        	var request = new Ajax.Request(
					url, 
					{
						method: 'post', 
						onComplete: '', 
						onSuccess: function(transport) {
							window.location.reload(true);
							response=transport.responseText;
							$('giftwrap-giftbox-additional').innerHTML=response;
							},
						onFailure: ""
					}	
				);	
        }
        function showGiftcardForm(){
    		if($('use_giftcard').checked){
    			$('giftwrap-giftbox-giftcard').style.display='block';
    			$('tinybox').style.height="auto";
    		}else{
    			$('giftwrap-giftbox-giftcard').style.display='none';
    			$('tinybox').style.height="auto";
    		}
    	}
    	function checkMaxLen(){
    		var elements=$$('input[name="giftbox_giftcard"][type="radio"]');
    		var max=0;
    		elements.each(function(el){
        		if(el.checked){
        				
            			max=$('max_len_'+el.value).value;
            		}
        		});
    		var element=$('giftcart-message-textarea');
    		if(max>0){
        	
	        	if(element.value.length>max){
	            	element.value=element.value.substring(0,max);
	            }else{
	                
	            }
    		} 
    	}

    	function viewMaxLen(max){
    		$('giftcard-message-maxlen').innerHTML = max;
    	}
		
		function afterShowGiftWrapForm(){
			if(is_check_giftwrap_form == true){
				return;
			}
			if($('giftwrap-form') != null){
				var giftwrapForm = new varienForm('giftwrap-form');											
				is_check_giftwrap_form = true;
				
				var nametool = document.getElementsByName('gift-cart-tooltip');
				//alert(nametool[0].id);
				var i=0;
				for( i=0; i < nametool.length; i++)
				{					
					new Tooltip(nametool[i].id, nametool[i].id+'-quick-view');
				}
			} else {
				setTimeout("afterShowGiftWrapForm()",200);
			}
		}
		
		function checkWrap(el,index){
		if(el.checked == true)
			$('qty_wrap_'+index).disabled = '';
		else 
			$('qty_wrap_'+index).disabled = 'disabled';
	}

	function checkWrapOne(el,index){
		if(el.checked == true)
			$('wrapall-item-'+index).disabled = true;
		else 
			$('wrapall-item-'+index).disabled = false;
	}

	function checkAll(){
		var selectedItem  = false;
		var wraps = $$("input[name='wrap[]']");
		if(wraps.length>0){
			for(var i=0;i<wraps.length;i++){
				if(wraps[i].checked){
					if(!checkNumber($('qty_wrap_'+wraps[i].value))){
						alert('Please fill numeric for quantity wrap!');
						return false;
					}
					if(!$('qty_wrap_'+wraps[i].value).value || (parseInt($('qty_wrap_'+wraps[i].value).value) == 0)){
						alert('Please fill quantity of item !');
						return false;
					}
					selectedItem = true;
					var qtycanwrap = $('qty_can_wrapped_'+ wraps[i].value).value;
					var qtywrap = $('qty_wrap_'+wraps[i].value).value;
					if(parseInt(qtycanwrap) < parseInt(qtywrap)){
						alert("Can't save gift box because qty wrap is too large !");
						return false;
					}
				}	
			}
		}
		if(!selectedItem){
			alert("Please select item to wrap !");
			return false;
		}
		
		return true;
	}

	function checkNumber(el){
		var check = true;
		var value = el.value;
		for(var i=0;i < value.length; ++i)
        {
             var new_key = value.charAt(i); //cycle through characters
             if(((new_key < "0") || (new_key > "9")) && 
                  !(new_key == ""))
             {
                  check = false;
                  break;
             }
        }
        return check;
	}