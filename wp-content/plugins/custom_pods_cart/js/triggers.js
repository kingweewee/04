jQuery(document).ready(function ($) {
	
	var $miniCart = $("#mini-cart"),
	$displayCart = $("#display-cart"),
	$content = $(".content"),
	$main = $(".main");
	
	//intitialise carts
	$miniCart.load(cartObj.ajaxURL, {"action": cartObj.miniCartAction});
	$displayCart.load(cartObj.ajaxURL, {"action": cartObj.cartAction});
	
	//on submit of the add-to-cart-form, retrieve the data and load the php function "loadMinicart"
	$content.on("submit",".add-to-cart-form",function(e){
		e.preventDefault();
		var qty = $(this).find(".qty").val(),
		id = $(this).data('id'),
		type = $(this).data('type');
		//reload mini cart
		$miniCart.load(cartObj.ajaxURL, { "action": cartObj.miniCartAction, "mode" : "add", "id" : id, "qty" : qty, "type" : type });
			
	});
	
	//on change of the qty field on the cart page, catch vars and load the php function "load_minicart" to update the cart then re-load the cart:
	$displayCart.on("keyup",".qty-input", function() {
		
		var id = $(this).data("id"),
		type = $(this).data("type"),
		qty = $(this).val();

		$miniCart.load(cartObj.ajaxURL, { "action": cartObj.miniCartAction, "mode" : "update", "id" : id, "type" : type, "qty" : qty }, function() {
			$displayCart.load(cartObj.ajaxURL, {"action": cartObj.cartAction});	
		});
		
	});
	
	//on click of the delete image, run delete function and re-load cart functions:
	$main.on("click","img.delete-icon", function() {
		
		var id = $(this).data("id"),
		type = $(this).data("type");

		$miniCart.load(cartObj.ajaxURL, { "action": cartObj.miniCartAction, "mode" : "delete", "id" : id, "type" : type }, function() {
			$displayCart.load(cartObj.ajaxURL, {"action": cartObj.cartAction});	
		});
	});
	
	//on click of the remove all link, run function to remove all items then re-load cart:
	$main.on("click",".delete-all", function() {

		$miniCart.load(cartObj.ajaxURL, {"action": cartObj.miniCartAction, "mode" : "delete-all" }, function() {
			$displayCart.load(cartObj.ajaxURL, {"action": cartObj.cartAction});	
		});
		
		return false;
	});
	
	 
	//on submit of the order form, run form validation, then send order ajax:
	$main.on("submit","#cart-form", function(e) {
		
		e.preventDefault();
		
		var $form = $(this),	
		name = $form.find("#name"),
		email = $form.find("#email"),
		credit = $form.find("#credit-num"),
		message = $form.find("#message"),
		spam = $form.find("#spam"),
		formFields = $form.find("input:text, textarea"),
		status = $form.find("#status");
		
		formFields.removeClass("error-focus");
		
		//check required fields are not empty and that the email address is valid
		if(name.val()==""){
			
			errorMessage("Please Enter Your Name");
			name.focus().addClass("error-focus");
			
		}else if(email.val()==""){
			
			errorMessage("Please Enter Your Email Address");
			email.focus().addClass("error-focus");
			
		}else if(!isValidEmail(email.val())){
			
			errorMessage("Please Enter a Correct Email Address");
			email.focus().addClass("error-focus");
			
		}else if(!isValidCrediCardNum(credit.val())){
			
			errorMessage("Please Enter Your Credit Card Number");
			credit.focus().addClass("error-focus");
			
		}else if(message.val()==""){
			
			errorMessage("Please Enter Your Message");
			message.focus().addClass("error-focus");
			
		}else if(!spam.val()==""){
			
			errorMessage("Spam Attack!!");

		}else{
			
			//if all fields are valid then send data to the server for processing
			successMessage("Email being sent... please wait");				
	
			var formData = $form.serialize();
			
			$.post(cartObj.ajaxURL, formData, function(sent) {

				 if(sent=="success"){ 
				 	
					successMessage("Thanks "+name.val()+", your message has been successfully sent");
					  
				} else if(sent=="error"){
					
					 errorMessage("Opps, something went wrong - message not sent");	
					
					//clear form fields
					formFields.val("");	
					
				}else{
					alert("else: "+ sent);
				}
				
			  });
			  
		}//end else
		
		function errorMessage(message){
			status.html(message).attr("class", "error").slideDown("fast");
		}
		
		function successMessage(message){
			status.html(message).attr("class", "success").slideDown("fast");
		}
		
		function isValidEmail(email) {
			var emailRx = /^[\w\.-]+@[\w\.-]+\.\w+$/;
			return  emailRx.test(email);
		}
		
		function isValidCrediCardNum(ccnum) {
			var ccNumRx = /^\d{16}$/;
			return  ccNumRx.test(ccnum);
		}
	});
	
	
});//ready