
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">Transaction Confirm</h3>
	</div>
	<div class="panel-body">
		<p>CartId = <?php echo $_GET["cartId"] ?><p>
		<!-- <p>Status = <span id="status"></span><p> -->
		<p id="pay"></p>
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
		<p><b>Date:</b> <p id="date"></p></p>
		
		<div class="row" style="padding: 10px">
			<div class="col-md-6">
				<button type="button" class="btn btn-success" id="button-reciept" style="width: 100%">Reciept</button>
			</div>
			<div class="col-md-6">
                <button type="button" class="btn btn-info" id="button-back" style="width: 100%">Shopping</button>
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
				'get_product_in_transaction': "<?php echo $_GET["cartId"];?>";
			},
			success: function(json_str2) {
// 				console.log(json_str2);
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
							<td id=\"tot\">" + total + "</td>\
						</tr>");
			}				
		});

		<?php 
		if (isset($_GET["paymentId"])) {
			echo "$(\"#pay\").html('<a href=\"http://128.199.212.108:8000/payment/{$_GET["paymentId"]}/accept\">KUPaypal</a>');";
		}
		
		if (isset($_GET["pass"])) {
			echo 'fullfill();';
		}
		?>		
		


		
// 		// 2 get lastest status #ORDER_RETRIEVE
// 		$.ajax({
//			url: 'http://128.199.145.53:11111/orders/' + <?php echo $_GET["orderId"] ?>,
// 			type: "GET"
// 		}).done(function(orders_json) {
// 			var order = JSON.parse(orders_json);
// 			$("#status").text(order.status);
// 		});


// 			console.log(customer_detail);
			var res = $.cookie("customer_detail").split("**");
 			$("#customer-name").text(res[0]);
 			$("#customer-address").html("<p>" + res[1] + " " + res[2] + "<br>District: " + res[3] + " Province: " + res[4] + "<br>Country: " + res[5] + " ZIP: " + res[6] + " Phone: " + res[7] + "/<p>");

	});

	$("#button-reciept").click(function() {
		 window.print();
	});

	$("#button-back").click(function() {
        window.location.href = "?page=shopping";
    });

	function fullfill() {
		$.ajax({
			url: 'forjscallphp.php',
			type: "POST",
			data: {
				"confirm-payment_only_outer_fullfill" : "",
				"name" : "Poramate Homprakob",
				"number" : "1909253600008099",
				"cvv" : "199",
				"expMonth" : "11",
				"expYear" : "15",
				"customerid" : '"' + $.cookie("customerid") + '"',
				"fee" : "0",
				"customerDetail": $.cookie("customer_detail")
			}
		}).done(function(response) {
			$.removeCookie("customer_detail");

			var j = '{"order": {"site": "kurel","products": [';
			
			$.ajax({
				url: 'forjscallphp.php',
				type: 'POST',
				data: {
					'get_product_in_transaction': "<?php echo $_GET["cartId"]; ?>"
				},
				success: function(json_str2) {
					var products = JSON.parse(json_str2);
					
					var total = 0;
					for (var i = 0; i < products.length; i++) {
						j += '{"product_id":"' + products[i].Product.id + '","product_name":"' + products[i].Product.productDescription.productName + '","price":"' + products[i].Product.price + '","quantity":"' + products[i].Quantity + '","weight":"' + products[i].Product.productDescription.weight + '"},';
						total += (products[i].Quantity * products[i].Product.price);
					}
					if (products.length > 0)
						j = j.substring(0, j.length-1);

					j += '],"totalprice":"' + total + '","customer_id":"' + $.cookie("customerid") + '","customer_name":"' + $.cookie("firstname") + " " + $.cookie("lastname") + '","email":"' + $.cookie("email") + '",';

					$.ajax({
						url: 'forjscallphp.php',
						type: "POST",
						data : {
							"get_customer_detail_by_cartid": "<?php echo $_GET["cartId"] ?>"
						}
					}).done(function(customer_detail) {
						var res = customer_detail.split("**");
						j += '"phone_number":"' + res[7] + '","address":"' + res[1] + " " + res[2] + " " + res[3] + " " + res[4] + " " + res[5] + " " + res[6] + '"';
						j += ',"payment_type":"credit","id":"<?php echo $_GET["cartId"];?>"}}';
						console.log(j);
						
						$.ajax({
							url: 'http://localhost:11111/orders/',
							type: "POST",
							contentType: 'application/json',
							dataType: 'json',
							async: false,
							data : j
						}).done(function(resp) {
							console.log(resp);
// 							getStatus();
						});
						
					});
				}				
			});
		});

	}

	// 1 make or ordertrackingid
    $.ajax({
        url: 'forjscallphp.php',
        type: "POST",
        async: false,
        data : {
            "is_cartid_exists": "<?php echo $_GET["cartId"] ?>"
        }
    }).done(function(response) {
        if (response == 0) {
            $.ajax({
                url: 'forjscallphp.php',
                type: "POST",
                async: false,
                data : {
                    "bind_cartid": "",
                    "cartId": "<?php echo $_GET["cartId"] ?>"
                }
            }).done(function(response) {
                console.log("just added");
            });
        }

    });

//     function getStatus() {
//         alert(1);
// 	    // 2 get lastest status #ORDER_RETRIEVE
// 	    $.ajax({
//	        url: 'http://localhost:11111/orders/<?php //echo $_GET["cartId"]; ?>',
// 	        async: false,
// 	        type: "GET"
// 	    }).done(function(lasteststatus) {
// 	        var obj = JSON.parse(lasteststatus);
// 		    console.log(obj)
// 	//      console.log(lasteststatus);
//	        $("#status").html('<a href="?page=tracking&id=<?php //echo $_GET["cartId"] ?>">' + obj[0].StatusType + '</a>');
// 	    });
//     }

    $.ajax({
        url: 'forjscallphp.php',
        type: "POST",
        data : {
            "get_transaction_by_cartid": "<?php echo $_GET["cartId"] ?>"
        }
    }).done(function(tran) {
//         console.log(tran);
        var date = JSON.parse(tran).payment.timeDate.date
//         console.log(JSON.parse(tran));
        $("#date").text(date);
        
        $.ajax({
            url: 'forjscallphp.php',
            type: "POST",
            data : {
                "get_promotion_by_datetime": "",
                "start": date,
                "end": date
            }
        }).done(function(tran) {
//          console.log(tran);
            var pro = JSON.parse(tran)[0];
            $("#orders-table").append("\
                    <tr>\
                        <td></td>\
                        <td>Promotion " + pro.title + "</td>\
                        <td>Discount " + pro.value + "%</td>\
                        <td>-" + (pro.value/100.0 * Number($("#tot").text())).toFixed(2) + "</td>\
                        <td><b>" + (Number($("#tot").text()) * (100-pro.value)/100).toFixed(2) + "</b></td>\
                    </tr>");
            
        });
    });

</script>
