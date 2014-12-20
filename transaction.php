<div class="panel panel-default" id="redzone" style="display: none">
	<div class="panel-heading">
		<h3 class="panel-title">Transaction</h3>
	</div>
	<div class="panel-body">
		<table class="table table-striped table-bordered">
			<tbody id="orders-table">
				<tr>
					<th>ID</th>
					<th>Date</th>
					<th>Customer</th>
					<th>Total</th>
					<th>Promotion</th>
					<th>Order Status</th>
					<th>Payment Status</th>
					<th>Shipping Status</th>
				</tr>
			</tbody>
		</table>
		
	</div>
</div>


<script type="text/javascript">

	$(document).ready(function() {
		if ($.cookie("adminlevel") != undefined) {
			$("#redzone").show();
		}
		else
			document.location.href = "?page=notfound"
	});

	$.ajax({
		url: 'forjscallphp.php',
		type: "POST",
		data: {
			"get_enum_values": ""
		}
	}).done(function(response) {
	    var types = JSON.parse(response);
		console.log(response)
		var current = JSON.parse(response)[0].StatusType;
// 		$("#types-dropdown").empty();
// 		$("#dropdown qq").text("");
		
	    for (var i = 0; i < types.length; i++) {
			$("#types-dropdown").append("<li><a>" + types[i].substring(1, types[i].length-1) + "</a></li>");
	    }
	    
	    $("#types-dropdown li").click(function() {
	    	$("#dropdown qq").text($(this).text());
	    	$("#button-get").removeAttr('disabled');
	    });
	});

	$(document).ready(function() {
		getTransaction("All");
	});


	$("#button-get").click(function() {
		getTransaction($("#dropdown qq").text());
	});

	function getTransaction(str) {
		
		$.ajax({
			url: 'forjscallphp.php',
			type: 'POST',
			async: false,
			data: {
				'get_all_transaction': '',
			}
		}).done(function(json_str) {
			var ts = JSON.parse(json_str);
// 			console.log(ts);

			$("#orders-table").empty();
			$("#orders-table").append("\
					<tr>\
						<th>ID</th>\
						<th>Date</th>\
						<th>Customer</th>\
						<th>Total</th>\
						<th>Promotion</th>\
						<th>Order Status</th>\
						<th>Payment Status</th>\
						<th>Shipping Status</th>\
					</tr>");

			for (var i = 0; i < ts.length; i++) {
// 				console.log(ts[i]);
				var row = "\
						<tr>\
							<td><a href=\"?page=transaction-detail&cartId=" + ts[i].cart.cartId + "\">" + ts[i].cart.cartId + "</a></td>\
							<td>" + ts[i].payment.timeDate.date + "</td>\
							<td>" + ts[i].cart.customer.firstName + " " + ts[i].cart.customer.lastName + "</td>\
							<td>" + ts[i].payment.amount + "</td>";

							(function(date, amount) {
								$.ajax({
									url: 'forjscallphp.php',
									type: "POST",
									async: false,
									data : {
										"get_promotion_by_datetime": "",
										"start": date,
										"end": date
									}
								}).done(function(tran) {
									try {
						 				console.log(".......V");
										var val = JSON.parse(tran)[0].value;
	
						 				console.log(val);
						 				console.log(".......^");
										var pro = JSON.parse(tran)[0];
										row += "<td>" + ((100.0-val)/100*amount).toFixed(2) + " </td>"
									} catch (err) {
										row += "<td></td>"
									}
									
								});
							})(ts[i].payment.timeDate.date, ts[i].payment.amount);

				

							$.ajax({
						        url: 'http://localhost:11111/orders/' + ts[i].cart.cartId,
						        async: false,
						        type: "GET"
						    }).done(function(s) {
						    	row += '<td>' + s.order.order_status + '</td><td>' + s.order.payment_status + '</td><td>' + s.order.shipping_Status + '</td>';
						    });

				$("#orders-table").append(row);
				
			}
		});
	}

	function post(path, params) {
	    var method = "POST";
	    
	    var form = document.createElement("form");
	    form.setAttribute("method", method);
	    form.setAttribute("action", path);

	    for(var key in params) {
	        if(params.hasOwnProperty(key)) {
	            var hiddenField = document.createElement("input");
	            hiddenField.setAttribute("type", "hidden");
	            hiddenField.setAttribute("name", key);
	            hiddenField.setAttribute("value", params[key]);

	            form.appendChild(hiddenField);
	         }
	    }

	    document.body.appendChild(form);
	    form.submit();
	}
</script>
