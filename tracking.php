
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">Tracking</h3>
	</div>
	<div class="panel-body">
	
	
					<div class="input-group">
						<span class="input-group-addon">Tracking ID</span>
						<input type="text" class="form-control" id="id" value="<?php echo $_GET["id"]; ?>">
					</div>
					<br>
					<button class="btn btn-lg btn-success btn-block" id="button-get">GET</button>
					<br>
					<table class="table table-striped table-bordered">
						<tbody id="orders-table">
						</tbody>
					</table>
					
	
	</div>
</div>


<script type="text/javascript">

	$(document).ready(function() {
		<?php if (isset($_GET["id"])) echo "get();"; ?>
	});

	$("#button-get").click(function() {
		document.location.href = "?page=tracking&id=" + $("#id").val();
	});

	$("#id").keypress(function(event) {
		if (event.which == 13)
			document.location.href = "?page=tracking&id=" + $("#id").val();
	});

	function get() {
		$.ajax({
	        url: 'http://128.199.145.53:11111/orders/' + $("#id").val(),
	        async: false,
	        type: "GET"
	    }).done(function(s) {
			$("#orders-table").append('<tr><td>Order Status</td><td>' + s.order.order_status + '</td></tr><tr><td>Payment Status</td><td>' + s.order.payment_status + '</td></tr><tr><td>Shipping Status</td><td>' + s.order.shipping_Status + '</td></tr>');
	    });
	}
	


</script>
