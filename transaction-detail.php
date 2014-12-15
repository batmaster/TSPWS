
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">Transaction Confirm</h3>
	</div>
	<div class="panel-body">
		<p>CartId = <?php echo $_GET["cartId"] ?><p>
		<p>Status = <span id="status"></span><p>
	</div>
</div>




<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">Receipt</h3>
	</div>
	<div class="panel-body" id="reciept-body">
		
		<table class="table table-striped table-bordered">
			<tbody id="orders-table">
				<tr>
					<td>#</td>
					<td>Product</td>
					<td>Quantity</td>
					<td>Unit Price</td>
					<td>Total</td>
				</tr>
			</tbody>
		</table>
		
		<p>Customer Name: <p id="customer-name"></p></p>
		<p>Customer Address: <p id="customer-address"></p></p>
		
		
		<div class="row" style="padding: 10px">
			<div class="col-md-6">
				<button type="button" class="btn btn-success" id="button-reciept" style="width: 100%">Reciept</button>
			</div>
		</div>
		
	</div>
</div>


<script type="text/javascript">

	$(document).ready(function() {
		$.ajax({
			url: 'forjscallphp.php',
			type: 'POST',
			data: {
				'get_product_in_transaction': <?php echo $_GET["cartId"];?>
			},
			success: function(json_str2) {
				console.log(json_str2);
				var products = JSON.parse(json_str2);
				var totalQuan = 0;
				var total = 0;
				for (var i = 0; i < products.length; i++) {
					$("#orders-table").append("\
							<tr>\
								<td>" + (i+1) + "</td>\
								<td>" + products[i].Product.productDescription.productName + "</td>\
								<td>" + products[i].Quantity + "</td>\
								<td>" + products[i].Product.price + "</td>\
								<td>" + (products[i].Quantity * products[i].Product.price) + "</td>\
							</tr>");
					totalQuan += Number(products[i].Quantity);
					total += (products[i].Quantity * products[i].Product.price);
				}
				$("#orders-table").append("\
						<tr>\
							<td></td>\
							<td>Total</td>\
							<td>" + totalQuan + "</td>\
							<td></td>\
							<td>" + total + "</td>\
						</tr>");
			}
		});




		
		// 2 get lastest status #ORDER_RETRIEVE
		$.ajax({
			url: 'http://128.199.145.53:11111/orders/' + <?php echo $_GET["orderId"] ?>,
			type: "GET"
		}).done(function(orders_json) {
			var order = JSON.parse(orders_json);
			$("#status").text(order.status);
		});


		

		$("#button-reciept").click(function() {
			alert("Will be ok");
		});

		$.ajax({
			url: 'forjscallphp.php',
			type: "POST",
			data : {
				"get_customer_detail_by_cartid": <?php echo $_GET["cartId"] ?>
			}
		}).done(function(customer_detail) {
			console.log(customer_detail);
			var res = customer_detail.split("**");
 			$("#customer-name").text(res[0]);
 			$("#customer-address").text(res[1] + " " + res[2] + "<br>District: " + res[3] + " Province: " + res[4] + "<br>Country: " + res[5] + " ZIP: " + res[6] + " Phone: " + res[7]);
		});
		
	});

</script>
