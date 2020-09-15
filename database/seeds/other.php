<!DOCTYPE html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Ensures optimal rendering on mobile devices. -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge" /> <!-- Optimal Internet Explorer compatibility -->
</head>

<body>

<div style="text-align: center"> Descripcion: <input type="text" id="description" placeholder=Descripción value="LCC 052020"></div>
<div style="text-align: center"> NRO NOTA: <input type="text" id="invoice_id" placeholder=Descripción value="123456654321"></div>
<div style="text-align: center"> Precio: <input type="text" id="amount" placeholder=Precio value="100" > USD</div> 
<br><br>


  <script
    src="https://www.paypal.com/sdk/js?client-id=Ab8frqGsF4rlmjIH9mS9kTdaGo2-vLh-v0PK5G1ZxeKBSTbAkygWF3eRCPYydHRtQBGlRJyLPDY4v5Aw"> // Required. Replace SB_CLIENT_ID with your sandbox client ID.
  </script>

<center>
<script>
  paypal.Buttons({
    createOrder: function(data, actions) {
      // This function sets up the details of the transaction, including the amount and line item details.
      return actions.order.create({
			purchase_units: [{
				description: document.getElementById("description").value,
				invoice_id : document.getElementById("invoice_id").value,
				custom_id  : document.getElementById("invoice_id").value,
				amount: {
					value: document.getElementById("amount").value
				}
				
			}]
		});
    },
    onApprove: function(data, actions) {
      // This function captures the funds from the transaction.
      return actions.order.capture().then(function(details) {
        // This function shows a transaction success message to your buyer.
		//alert('Transaction completed by ' + details.payer.name.given_name );
		alert(' orderID '  +  data.orderID );
		alert(' id '  +  data.id );
		alert(' status  '  +  data.status  );
		//console.log('data'); console.log(data); 
		//console.log('details'); console.log(details); 
        //alert(' id '  +  details.autorization.id + ' invoice_id  '  +  details.autorization.invoice_id  );
		//alert('Errors ' + details.error.name  +  '  '  +  details.error.message );
		//alert('Order ' + details.order_status );
		
      });
    }
  }).render('#paypal-button-container');
  //This function displays Smart Payment Buttons on your web page.
</script>
  
  
<div id="paypal-button-container"></div>

  <script>
    //paypal.Buttons().render('#paypal-button-container');
    // This function displays Smart Payment Buttons on your web page.
  </script>  
</center>
  
</body>