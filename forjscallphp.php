<?php
if (isset($_POST["get_num_page"])) {
	require_once ('inc/Product.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');
	require_once ('inc/WishList.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	
	$cat = $_POST["c"];
	$str = $_POST["s"];
	
	$count;
	if ($str == "") {
		if ($cat == "Category" || $cat == "All") {
			$count = Product::GetAllProductCount();
		}
		else {
			$count = ProductDescription::SearchByTagsCount(array($cat));
		}
	}
	else {
		$str = array_map( 'trim', explode(",", $str) );
	
		if (!($cat == "Category" || $cat == "All")) {
			array_push($str, $cat);
			$count = ProductDescription::SearchByTagsCount($str);
		}
		else {
			$count = ProductDescription::SearchByTagsCount($str);
		}
	}
	echo $count;
	
}


if (isset($_POST["search_product_for_shopping"])) {
	require_once ('inc/Product.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');
	require_once ('inc/WishList.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	
	$cat = $_POST["category"];
	$str = $_POST["search_product_for_shopping"];
	$pages = isset($_POST["page"]) ? $_POST["page"] : 1;
	
	if ($_POST["customer_id"] != 0)
		$customerId = $_POST["customer_id"];
	
	$productdescs = array();
	if ($str == "") {
		if ($cat == "Category" || $cat == "All") {
			$product = Product::GetAllProductWithLimit(30, $pages);
			foreach ($product as $p){
				array_push($productdescs, $p->productDescription);
			}
		}
		else {
			$productdescs = ProductDescription::SearchByTagsWithLimit(array($cat), 30, $pages);
		}
	}
	else {
		$str = array_map( 'trim', explode(",", $str) );
		
		if (!($cat == "Category" || $cat == "All")) {
			array_push($str, $cat);
			$productdescs = ProductDescription::SearchByTagsWithLimit($str, 30, $pages);
		}
		else {
			$productdescs = ProductDescription::SearchByTagsWithLimit($str, 30, $pages);
		}
	}
	
	if ($_POST["customer_id"] != 0) {
		$wishList = WishList::GetWishListFromCustomer(Customer::GetCustomer($customerId));
		foreach ($productdescs as $p) {
			$product = Product::GetEnabledProductByProductDescriptionId($p->id);
			
			$isWish = $wishList->isWish($product);
			
			createProductBoxForShopping($product, $isWish);
		}
	}
	else {
		foreach ($productdescs as $p) {
			$product = Product::GetEnabledProductByProductDescriptionId($p->id);
								
			createProductBoxForShopping($product, 0);
		}
	
	}
}

if (isset($_POST["search_product_for_inventory"])) {
	require_once ('inc/Product.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');

	$cat = $_POST["category"];
	$str = $_POST["search_product_for_inventory"];
	$pages = $_POST["page"];
	
	$productdescs = array();
	if ($str == "") {
// 			echo("test2");
		if ($cat == "Category" || $cat == "All") {
				$product = Product::GetAllProductWithLimit(30, $pages);
				foreach ($product as $p) {
				array_push($productdescs, $p->productDescription);
			}		
		}
		else{
			$productdescs = ProductDescription::SearchByTagsWithLimit(array($cat), 30, $pages);
		}
	}
	else {
//		$str = explode(",", $str);
		$str = array_map( 'trim', explode(",", $str) );
//		array_map( 'trim', $tags_str );
		if (!($cat == "Category" || $cat == "All")) {
			array_push($str, $cat);
			$productdescs = ProductDescription::SearchByTagsWithLimit($str, 30, $pages);
		}
		else {
			$productdescs = ProductDescription::SearchByTagsWithLimit($str, 30, $pages);
		}
	}

	foreach ($productdescs as $p) {
		$product = Product::GetEnabledProductByProductDescriptionId($p->id);
		createProductBoxForInventory($product);
	}
}

function createProductBoxForShopping($product, $isWish) {
	if (!($product -> id))
		return "";
	require_once ('inc/Inventory.php');
	include_once ('inc/InventoryDao.php');
	
	$productId = $product->id;
	$productName = $product->productDescription->productName;
	$price = $product->price;
	$imageLen = count($product->productDescription->images);
	$maxQuan = Inventory::getQuntity($productId);
	
	$buttonStatus = $maxQuan > 0 ? "" : "disabled";
	
	if ($isWish)
	echo "
			
	<div class=\"thumbnail productbox\">
	
	   	<a href=\"?page=detail&id=$productId\">
	   		<!-- May change link of pic to product desc page -->
	   		<img src=\"{$product->productDescription->images[$imageLen-1]}\">
	   		<span style=\"position: relative; top: -20px; left: 70px;\" class=\"glyphicon glyphicon-heart\">$isWish</span> 
			<div class=\"name\" id=\"name\">$productName</div>
		</a>
	
		<div id=\"price\">&#3647;$price</div>
	
		<button type=\"button\" class=\"btn btn-success\" onclick=\"addToCart($productId, '$productName', $price, 1/*, $maxQuan*/);\" $buttonStatus>ADD</button>
	</div>

	";

	else
	echo "
		
	<div class=\"thumbnail productbox\">
	
	<a href=\"?page=detail&id=$productId\">
		<!-- May change link of pic to product desc page -->
		<img src=\"{$product->productDescription->images[$imageLen-1]}\">
		<div class=\"name\" id=\"name\">$productName</div>
	</a>

		<div id=\"price\">&#3647;$price</div>

		<button type=\"button\" class=\"btn btn-success\" onclick=\"addToCart($productId, '$productName', $price, 1/*, $maxQuan*/);\" $buttonStatus>ADD</button>
	</div>
	
	";
	
}

function createProductBoxForInventory($product) {
	if (!($product -> id))
		return "";
	$productId = $product->id;
	$productName = $product->productDescription->productName;
	$price = $product->price;
	
	$images = $product->productDescription->images;
	$imageLen = count($product->productDescription->images);
	
	$coverImage;
	if ( $images == null || count ( $images ) < 1 ) {
		$coverImage = 'image/logo.png';
	} else {
		$coverImage = $product->productDescription->images[$imageLen-1];
	}
	echo "
	<div class=\"thumbnail productbox\">

		<!-- May change link of pic to product desc page -->
		<img src=\"{$coverImage}\">
	
		<div class=\"name\" id=\"name\">$productName</div>
	
		<div id=\"price\">&#3647;$price</div>
	
		<button type=\"button\" class=\"btn btn-info\" onclick=\"editProduct('$productId');\">EDIT</button>
		<button type=\"button\" class=\"btn btn-Danger\" onclick=\"removeProduct('$productId');\">REMOVE</button>
	</div>

	";
}

if (isset($_POST["submit"])) {
	if ($_POST["submit"] == "add") {
		include_once ('inc/Product.php');
		include_once ('inc/ProductDescription.php');
		include_once ('inc/Category.php');
		include_once ('inc/Brand.php');
		include_once ('inc/Inventory.php');
		include_once ('inc/InventoryDao.php');
		$brand = Brand::CreateBrand($_POST['brand']);
		$category = Category::CreateCategory($_POST['category']);
		$productDesc = ProductDescription::CreateProductDescription( $category, $brand, $_POST['desc'], $_POST['code'], array_map( 'trim', explode(',', $_POST['tag']) ), $_POST['name'] ,$_POST['weight']);
		$productDesc->addImages( array( $_POST['image'] ) );
		$product = Product::CreateProduct($productDesc, $_POST['price']);
		Inventory::addProduct($product, $_POST['quan'] );
	}
	if ($_POST["submit"] == "edit") {
		include_once ('inc/ProductDescription.php');
		include_once ('inc/Product.php');
		include_once ('inc/Category.php');
		include_once ('inc/Brand.php');
		include_once ('inc/Inventory.php');
		include_once ('inc/InventoryDao.php');
		
		$id = $_POST["id"];
		$product = Product::GetProduct($id);
		$productdescs = $product->productDescription;
		$product = Product::ReplaceActiveProduct($productdescs, $_POST["price"]);
		Inventory::addProduct($product, $_POST["quan"]);
		$productdescs -> productName = $_POST["name"];
		$productdescs -> modelCode = $_POST["code"];
		$productdescs -> description = $_POST["desc"];
		$productdescs -> addImages(array($_POST["image"]));
		$productdescs -> category = Category::CreateCategory($_POST["category"]);
		$productdescs -> additionTags = array_map( 'trim', explode(',', $_POST['tag']) );
		$productdescs -> brand = Brand::CreateBrand($_POST["brand"]);
		$productdescs -> weight = $_POST['weight'];
		$productdescs -> updateData();
		
		//print_r($productdescs);
		//echo ("Product Edited");
	}
	
}

if (isset($_POST["get_product_detail_by_id"])) {

	require_once ('inc/Product.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');
	require_once ('inc/Inventory.php');
	include_once ('inc/InventoryDao.php');
	
	$product = Product::GetProduct($_POST["get_product_detail_by_id"]);
	$productdescs = $product->productDescription;
	$quan = Inventory::getQuntity($product->id);
	$desc = str_replace('"', '\"', $productdescs->description);
	
// 	print_r($product);
// 	print_r($productdescs);
// 	print_r($quan);
// 	echo($desc);
	
	$tags_str = implode("\", \"", $productdescs->additionTags);
	$imageLen = count($product->productDescription->images);
	
	echo "
	{
		\"id\" : {$_POST["get_product_detail_by_id"]},
		\"name\" : \"{$productdescs->productName}\",
		\"code\" : \"{$productdescs->modelCode}\",
		\"price\" : {$product->price},
		\"description\" : \"{$desc}\",
		\"image\" : \"{$product->productDescription->images[$imageLen-1]}\",
		\"category\" : \"{$productdescs->category->value}\",
		\"tag\" : [\"$tags_str\"],
		\"weight\" : $productdescs->weight,
		\"quantity\" : $quan,
		\"brand\" : \"{$productdescs->brand->value}\"
	}";

}

if (isset($_POST["sign_up"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Customer.php');
	
	$result = Customer::CreateCustomer($_POST["firstname"], $_POST["lastname"], $_POST["email"], $_POST["password"], "");
	echo "
	{
		\"id\" : {$result->id},
		\"username\" : \"{$result->username}\",
		\"firstname\" : \"{$result->firstName}\",
		\"lastname\" : \"{$result->lastName}\"
	}";
	
	confirmemail($_POST["email"], $_POST["firstname"]." ".$_POST["lastname"]);
}

if (isset($_POST["sign-in"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/Admin.php');
	
// 	var_dump($_POST);
	
	$result = Customer::Authenticate($_POST["email"], $_POST["password"]);
	if ($result->id != 0) {
		echo "
		{
			\"id\" : {$result->id},
			\"username\" : \"{$result->username}\",
			\"firstname\" : \"{$result->firstName}\",
			\"lastname\" : \"{$result->lastName}\"
		}";
		return;
	}
	else {
		$result = Admin::Authenticate($_POST["email"], $_POST["password"]);
		echo "
		{
		\"id\" : {$result->id},
		\"username\" : \"{$result->username}\",
		\"firstname\" : \"{$result->firstName}\",
		\"lastname\" : \"{$result->lastName}\",
		\"adminlevel\" : \"{$result->level}\"
		}";
		return;
	}
}

if (isset($_POST["remove"])) {
	require_once('inc/Product.php');
	require_once('inc/ProductDao.php');
	
	Product::GetProduct($_POST["remove"])->disable();
}

if (isset($_POST["get_all_product_in_cart"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');
	
	$customer = Customer::GetCustomer($_POST["get_all_product_in_cart"]);
	$products = $customer->getCart()->GetProducts();
	
	echo json_encode($products);
}

if (isset($_POST["add_to_cart"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');
	require_once ('inc/Inventory.php');

	$customer = Customer::GetCustomer($_POST["add_to_cart"]);
	$cart = $customer->getCart();
	
	$product = Product::GetProduct($_POST["product_id"]);
	$amount = $_POST["quantity"];
	
	if ($amount > 0) {
		if (Inventory::getQuntity($product->id) < $amount) {}
		else
			$cart->AddProduct($product, $amount);
	}
	else if ($amount < 0) {
		$cart->RemoveProduct($product, -1 * $amount);
	}
}

if (isset($_POST["clear-cart"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');

	$customer = Customer::GetCustomer($_POST["clear-cart"]);
	$cart = $customer->getCart();
	$cart->ClearProducts();
}

if (isset($_POST["confirm-payment"])) {
	include_once 'inc/CreditCard.php';
	include_once 'inc/Customer.php';
	include_once 'inc/CustomerDao.php';
	include_once 'inc/Cart.php';
	include_once 'inc/Sale.php';
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	require_once ('inc/Promotion.php');
	require_once ('inc/PaymentDao.php');
	require_once ('inc/Payment.php');
	$card = CreditCard::CreateCreditCard($_POST["name"], $_POST["number"], $_POST["cvv"], $_POST["expYear"], $_POST["expMonth"]);
	$customer = Customer::GetCustomer($_POST['customerid']);	
	$customerDetail = $_POST["customerDetail"];
	$cart = $customer->getCart();
	$cart->setFee($_POST["fee"]);
	$sale = $cart->purchase($card, $customerDetail);
	
	if($sale !== null){
		echo("Pass");
	}else{
		echo("Fail");
	}
}

if (isset($_POST["get_wishlist_product"])) {
	require_once ('inc/WishList.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	
	$customerId = $_POST["get_wishlist_product"];
	$pages = $_POST["page"];
	$wishlist = Customer::GetCustomer($customerId)->getWishList();
	$products = $wishlist -> GetProductsWithLimit(30, $pages);
	foreach ($products as $p) {
		createProductBoxForWishList($p["Product"], $p["Quantity"]);
	}
	
}

if (isset($_POST["get_number_wishlist"])) {
	require_once ('inc/WishList.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');

	$customerId = $_POST["get_number_wishlist"];
	
	$wishlist = Customer::GetCustomer($customerId)->getWishList();
	echo $wishlist -> GetProductsCount();

}

if (isset($_POST["add_to_wishlist"])) {
	require_once ('inc/WishList.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	
	$customerId = $_POST["add_to_wishlist"];
	$productId = $_POST["product_id"];
	$quan = $_POST["quantity"];
	$wishlist = Customer::GetCustomer($customerId) -> getWishList();
	$wishlist -> addProduct(Product::GetProduct($productId), $quan);
}

function createProductBoxForWishList($product, $wishQuan) {
	if (!($product -> id))
		return "";
	require_once ('inc/Inventory.php');
	include_once ('inc/InventoryDao.php');

	$productId = $product->id;
	$productName = $product->productDescription->productName;
	$price = $product->price;
	$imageLen = count($product->productDescription->images);
	$maxQuan = Inventory::getQuntity($productId);

	$buttonStatus = $maxQuan > 0 ? "" : "disabled";

	
	echo "
		
	<div class=\"thumbnail productbox\">

	<a href=\"?page=detail&id=$productId\">
	<!-- May change link of pic to product desc page -->
	<img src=\"{$product->productDescription->images[$imageLen-1]}\">
	 
	<div class=\"name\" id=\"name\">$wishQuan $productName</div>
	</a>

	<div id=\"price\">&#3647;$price</div>

	<button type=\"button\" class=\"btn btn-success\" onclick=\"addToCart($productId, $wishQuan);\" $buttonStatus>ADD</button>
	<button type=\"button\" class=\"btn btn-Danger\" onclick=\"removeWishProduct('$productId');\">REMOVE</button>
	</div>

	";

}

if (isset($_POST["remove_wish"])) {
	require_once ('inc/WishList.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	
	$customerId = $_POST["remove_wish"];
	$productId = $_POST["product_id"];
	$wishlist = WishList::GetWishListFromCustomer(Customer::GetCustomer($customerId));
	$wishlist -> RemoveProduct(Product::GetProduct($productId),1);
}

if (isset($_POST["sign_up_admin"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Admin.php');
	$result = Admin::CreateAdmin($_POST["firstname"], $_POST["lastname"], $_POST["email"], $_POST["password"],1);
	echo "
	{
	\"id\" : {$result->id},
	\"username\" : \"{$result->username}\",
	\"firstname\" : \"{$result->firstName}\",
	\"lastname\" : \"{$result->lastName}\",
	\"adminlevel\" : {$result->level}
	}";
}

if (isset($_POST["get_all_transaction"])) {
	require_once ('inc/Sale.php');
	require_once ('inc/PaymentDao.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Payment.php');
	require_once ('inc/Creditcard.php');
	$pages = $_POST["page"];
	$allSale = Sale::GetAll();
	echo json_encode($allSale);
}

if (isset($_POST["get_product_in_transaction"])) {
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	require_once ('inc/Sale.php');
	require_once ('inc/PaymentDao.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Payment.php');
	require_once ('inc/Creditcard.php');
	
	$cartId = $_POST["get_product_in_transaction"];
	$pages = $_POST["page"];
	$products = Cart::GetCart($cartId)->GetProducts();
	echo json_encode($products);
}




if (isset($_POST["get_all_customer"])) {
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	
	$customers = Customer:: getAllCustomers();
	
	echo json_encode($customers);
}

if (isset($_POST["get_all_blocked_customer"])) {
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');

	$customers = Customer:: getAllBlockedCustomers();

	echo json_encode($customers);
}


if (isset($_POST["get_all_admin"])) {
	require_once ('inc/Admin.php');
	require_once ('inc/CustomerDao.php');

	$admins = Admin::getAllAdmins();
	
	echo json_encode($admins);
}

if(isset($_POST["get_cartid_by_customerid"])){
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	
	$customer = Customer::GetCustomer($_POST["get_cartid_by_customerid"]);
	echo( $customer->getCart()->cartId );
}

if (isset($_POST["get_customer_detail"])) {
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	
	$customerId = $_POST["get_customer_detail"];
	
	$customer = Customer::GetCustomer($customerId);
	$address = explode("¿", $customer->Address);
	
	echo "
	{
	\"id\": $customerId,
	\"username\" : \"{$customer->username}\",
	\"firstname\" : \"{$customer->firstName}\",
	\"lastname\" : \"{$customer->lastName}\",
	\"address\" : \"{$address[0]}\",
	\"address2\" : \"{$address[1]}\",
	\"district\" : \"{$address[2]}\",
	\"province\" : \"{$address[3]}\",
	\"country\" : \"{$address[4]}\",
	\"zip\" : \"{$address[5]}\",
	\"phone\" : \"{$address[6]}\",
	\"isblocked\" : $customer->isBlocked
	}";
}

if (isset($_POST["save_customer_detail"])) {
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	
	$customerId = $_POST["save_customer_detail"];
	$customer = Customer::GetCustomer($customerId);
	$customer -> username = $_POST["email"];
	$customer -> firstName = $_POST["firstname"];
	$customer -> lastName = $_POST["lastname"];
	$customer ->  Address = $_POST["address"] . "¿" . $_POST["address2"] . "¿" . $_POST["district"] . "¿" . $_POST["province"] . "¿" . $_POST["country"] . "¿" . $_POST["zip"] . "¿" . $_POST["phone"];
	$customer->updateCustomer();
	if ($_POST["password"] != "")
		$customer ->updatePassword( $_POST["password"] );
}

if (isset($_POST["get_all_promotion_for_calendar"])) {
	require_once 'inc/Admin.php';
	require_once 'inc/CustomerDao.php';
	require_once 'inc/Promotion.php';
	require_once 'inc/PaymentDao.php';
	
	$pros = Promotion::GetAllPromotions();
	$jstr = "[";
	
	for ($i = 0; $i < count($pros); $i++) {
		$sdate = strtotime($pros[$i]->startDate->format("Y-m-d H:i:s"));
		$edate = strtotime($pros[$i]->endDate->format("Y-m-d H:i:s"));
		
		while ($sdate <= $edate) {
			$jstr .= "{\"date\": \"" . (int)date("d", $sdate) . "/" .(int)date("m", $sdate) . "/" . (int)date("Y", $sdate) . "\", \"title\": \"" . $pros[$i]->value . "% " . $pros[$i]->title ."\", \"color\": \"" . ($pros[$i]->value >= 50 ? "red" : ($pros[$i]->value >= 25 ? "green" : "blue")) ."\", \"class\": \"miclasse\", \"content\": \"" . $pros[$i]->description ." by " .$pros[$i]->admin->firstName . " " .$pros[$i]->admin->lastName ."\"}, ";
			$sdate += 86400;
		}
	}
	$jstr = substr($jstr, 0, $jstr.length-2) . "]";
	
	echo $jstr;
}

if (isset($_POST["get_all_promotion"])) {
	require_once 'inc/Admin.php';
	require_once 'inc/CustomerDao.php';
	require_once 'inc/Promotion.php';
	require_once 'inc/PaymentDao.php';

	echo json_encode(Promotion::GetAllPromotions());
}


// StatusType :: 'ORDER_RETRIEVE','ORDER_PICK','ORDER_READY','ORDER_SCAN','ORDER_TRANSIT'
if (isset($_POST["bind_cartid"])) {
	
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	 
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$date = date("Y-m-d h:i:sa");
	$STH = $db->prepare("INSERT INTO `OrderTrackings` (`CartId`, `StatusType`, `Date`, `Description`, `UpdatedBy`) VALUES (:cid, 'ORDER_RETRIEVE', '$date', 'Undefined', 'System')");
	$STH->bindParam(':cid', $_POST["cartId"]);
	$STH->execute();
}

if (isset($_POST["get_lastest_order_status_by_cartid"])) {
	$cartid = $_POST["get_lastest_order_status_by_cartid"];
	
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("SELECT * FROM `OrderTrackings` WHERE `CartId`=:cid ORDER BY `Key` DESC LIMIT 1");
	$STH->bindParam(':cid', $cartid);
	$STH->execute();
	echo json_encode($STH->fetchAll());
}

if (isset($_POST["get_order_status_by_cartid"])) {
	$cartid = $_POST["get_order_status_by_cartid"];
	
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("SELECT * FROM `OrderTrackings` WHERE `CartId`=:cid ORDER BY `Key`");
	$STH->bindParam(':cid', $cartid);
	$STH->execute();
	echo json_encode($STH->fetchAll());
}

if (isset($_POST["is_cartid_exists"])) {
	$cartid = $_POST["is_cartid_exists"];
	
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("SELECT COUNT(*) AS num FROM `OrderTrackings` WHERE `CartId`=:cid");
	$STH->bindParam(':cid', $cartid);
	$STH->execute();
	
	echo $STH->fetch()["num"];
}

if (isset($_POST["update_order_status_by_cartid"])) {
	$cartid = $_POST["update_order_status_by_cartid"];
	$status = $_POST["status"];
	$description = $_POST["description"];
	$by = $_POST["by"];

	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	 
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("INSERT INTO `OrderTrackings` (`CartId`, `StatusType`, `Date`, `Description`, `UpdatedBy`) VALUES (:cid, :type, :date, :desc, :by)");
	$STH->bindParam(':cid', $cartid);
	$STH->bindParam(':type', $status);
	$STH->bindParam(':date', date("Y/m/d h:i:s A"));
	$STH->bindParam(':desc', $description);
	$STH->bindParam(':by', $by);
	$STH->execute();
}

if (isset($_POST["get_enum_values"])) {
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("SHOW COLUMNS FROM `OrderTrackings` WHERE `Field`='StatusType'");
	$STH->execute();
	$row = $STH->fetch();
	$type = $row['Type'];
	preg_match('/enum\((.*)\)$/', $type, $matches);
	$vals = explode(',', $matches[1]);
	echo json_encode($vals);
}

if (isset($_POST["get_customer_detail_by_cartid"])) {
	$cartId = $_POST["get_customer_detail_by_cartid"];
	
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("SELECT `CustomerDetail` FROM `Sales` WHERE `CartId`=:cid");
	$STH->bindParam(':cid', $cartId);
	$STH->execute();
	echo $STH->fetch()[0];
}

if (isset($_POST["check_overlap"])) {
	require_once 'inc/Promotion.php';
	require_once 'inc/PaymentDao.php';
	
	return Promotion::checkOverlapPromotion($_POST["check_overlap"]);
}

if (isset($_POST["create_promotion"])) {
	require_once 'inc/Promotion.php';
	require_once 'inc/PaymentDao.php';
	require_once 'inc/Admin.php';
	require_once 'inc/CustomerDao.php';
	//[{"date":"7/10/2014","title":"test3","color":"blue","class":"miclasse ","content":"contingut popover"}]
	//echo json_encode(Promotion::GetAllPromotions());

	$adminid = $_POST["adminid"];
	$startDate = $_POST["start"];
	$endDate = $_POST["end"];
	$value = $_POST["value"];
	$title = $_POST["title"];
	$description = $_POST["description"];
	Promotion::CreatePercentPromotion($value, $startDate, $endDate, Admin::GetAdmin($adminid), $title, $description);
}

if (isset($_POST["get_news"])) {
	require_once 'inc/Product.php';
	require_once 'inc/ProductDao.php';
	
	$products = Product::GetAllProduct();
	
	$j = 5;
	for ($i = count($products) - 1; $i >= 0; $i--) {
		if ($j == 0)
			return;
		
		createBoxForNews($products[$i]);
		$j--;
	}
}

function createBoxForNews($product) {
	require_once ('inc/Inventory.php');
	include_once ('inc/InventoryDao.php');
	
	$productId = $product->id;
	$productName = $product->productDescription->productName;
	$price = $product->price;
	$imageLen = count($product->productDescription->images);
	$pic = $product->productDescription->images[$imageLen-1];
	$brand = $product->productDescription->brand->value;
	$catgory = $product->productDescription->category->value;
	$quan = Inventory::getQuntity($product->id);
	$date = $product->createDate->format('Y-m-d H:i:s');
	
	echo "
	<div class=\"list-group\">
		<a href=\"?page=detail&id={$product->productDescription->id}\" class=\"list-group-item\">
			<div class=\"row\">
				<div class=\"col-md-3\">
					<img src=\"$pic\" width=\"256px\">
				</div>
				<div class=\"col-md-5\">
	    			<h4 class=\"list-group-item-heading\">$productName</h4>
	    			<h3 id=\"price\">&#3647;$price</h3>
	    			<br>
	    			<h4>Brand: $brand<h4	
					<h5>Category: $catgory</h5>
					<h5>Stock: $quan</h5>
					<h5>Created: $date</h5>
	    		</div>
	  		</div>
	  	</a>
	</div>
	";	
}

if (isset($_POST["current_promotion"])) {
	require_once 'inc/Promotion.php';
	require_once 'inc/PaymentDao.php';
	require_once 'inc/Admin.php';
	require_once 'inc/CustomerDao.php';
	
	echo json_encode(Promotion::GetCurrentPromotions());
}

if (isset($_POST["block"])) {
	require_once 'inc/Customer.php';
	require_once 'inc/CustomerDao.php';
	
	$cid = $_POST["block"];
	$customer = Customer::GetCustomer($cid);
	$customer->isBlocked = 1;
	$customer->updateCustomer();
}

if (isset($_POST["unblock"])) {
	require_once 'inc/Customer.php';
	require_once 'inc/CustomerDao.php';

	$cid = $_POST["unblock"];
	$customer = Customer::GetCustomer($cid);
	$customer->isBlocked = 0;
	$customer->updateCustomer();
}

if (isset($_POST["upgrade"])) {
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	
	$cid = $_POST["upgrade"];
	
	$STH = $db->prepare( "UPDATE `Admins` SET `Level` = 2 WHERE `AdminId` = " . $cid);
	$STH->execute();
}

if (isset($_POST["downgrade"])) {
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	
	$cid = $_POST["downgrade"];

	$STH = $db->prepare( "UPDATE `Admins` SET `Level` = 1 WHERE `AdminId` = " . $cid);
	$STH->execute();
}

if (isset($_POST["add_to_cart_by_wish"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');
	require_once ('inc/WishList.php');
	require_once ('inc/Inventory.php');
	
	$cid = $_POST["add_to_cart_by_wish"];
	$pid = $_POST["product_id"];
	$wishQuan = $_POST["quantity"];

	$customer = Customer::GetCustomer($cid);
	$cart = $customer->getCart();
	$product = Product::GetProduct($pid);
	
	WishList::GetWishListFromCustomer($customer)->RemoveProduct($product, $wishQuan);
	
	$cart->AddProduct($product, $wishQuan);
}

if (isset($_POST["is_wish"])) {
	require_once ('inc/WishList.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');

	$customerId = $_POST["is_wish"];
	$pid = $_POST["productId"];

	$wishlist = Customer::GetCustomer($customerId)->getWishList();
	
	echo $wishlist -> isWish(Product::GetProduct($pid));

}

function confirmemail($email, $name) {
	$strTo = $email;
	$strSubject = "=?UTF-8?B?".base64_encode("Welcome to XTremeSportShop")."?=";
	$strHeader .= "MIME-Version: 1.0' . \r\n";
	$strHeader .= "Content-type: text/html; charset=utf-8\r\n";
	$strHeader .= "From: Super admin<admin@xtremesportshop.com>\r\nReply-To: admin@xtremesportshop.com";
	
	$strMessage = "
	<h1>XTreme Sport Shop confirm your register.</h1><br>
	<br>
	<h3>Welcome, $name<br>
	<p>This email is sent to confirm your register to our e-commerce website XTreme Sport Shop.
	You can signin to our website and edit your profile including your address, used for shipment
	your items later.</p>
	<br>
	<br>
	Thanks!<br>
	Xtreme Sport Shop<br>
	";
	
	$flgSend = @mail($strTo,$strSubject,$strMessage,$strHeader);  // @ = No Show Error //
// 	if($flgSend)
// 	{
// 		echo "Email Sending. " .$flgSend;
// 	}
// 	else
// 	{
// 		echo "Email Can Not Send. " .$flgSend;
// 	}
}

if (isset($_POST["recovery"])) {
	$email = $_POST["recovery"];
	
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare( "SELECT `CustomerId` FROM `Customers` WHERE `UserName` = " .$email);
	$cid = $STH->execute()->fetch();
	if ($cid == 0) {
		echo "Email not found.";
		return;	
	}
	else {
		$STH = $db->prepare( "SELECT `Tag` FROM `Recoveries` WHERE `CustomerId` = :cid AND `Date` < DATE_SUB(CURDATE(), INTERVAL 1 DAY)" );
		$STH->bindParam(':cid', $cid );
		$tag = $STH->execute()->fetch();
		
		if ($tag == "") {
			$STH = $db->prepare("INSERT INTO `Recoveries` (`CustomerId`, `Date`, `Tag`) VALUES (:cid, :d, :t) ");
	    	$STH->bindParam(':cid', $cid );
	    	$STH->bindParam(':d', new Date() );
	    	$STH->bindParam(':t', md5($email) );
	    	$STH->execute();
		}
		
		$STH = $db->prepare( "SELECT `Tag` FROM `Recoveries` WHERE `CustomerId` = :cid AND `Date` < DATE_SUB(CURDATE(), INTERVAL 1 DAY)" );
		$STH->bindParam(':cid', $cid );
		$tag = $STH->execute()->fetch();
		recovery($email, $tag);
		echo "Email is sent.";
	}
}

function recovery($email, $tag) {
	$strTo = $email;
	$strSubject = "=?UTF-8?B?".base64_encode("Recovery password for XTremeSportShop")."?=";
	$strHeader .= "MIME-Version: 1.0' . \r\n";
	$strHeader .= "Content-type: text/html; charset=utf-8\r\n";
	$strHeader .= "From: Super admin<admin@xtremesportshop.com>\r\nReply-To: admin@xtremesportshop.com";
	
	$strMessage = "
	<h3>Welcome, $email<br>
	<p>This email is sent for recovery your password fro our e-commerce website XTremeSportShop.
	Click a link below to reset your password within 24 hours since you get this email.</p>
	<br>
	<a href=\"http://128.199.145.53/tsp/?page=recovery?tag=$tag\">Recovery</a>
	<br>
	Thanks!<br>
	Xtreme Sport Shop<br>
	";
	
	$flgSend = @mail($strTo,$strSubject,$strMessage,$strHeader);
}

if (isset($_POST["get_transaction_by_customerid"])) {
	require_once 'inc/Sale.php';
	require_once 'inc/PaymentDao.php';
	require_once 'inc/Cart.php';
	require_once 'inc/InventoryDao.php';
	require_once 'inc/Customer.php';
	require_once 'inc/CustomerDao.php';
	require_once 'inc/Payment.php';
	require_once 'inc/CreditCard.php';
	
	$cid = $_POST["get_transaction_by_customerid"];
	echo json_encode(Sale::GetSaleByCustomer($cid, 400, 1));
	
}

if (isset($_POST["get_transaction_by_daterange"])) {
	require_once 'inc/Sale.php';
	require_once 'inc/PaymentDao.php';
	require_once 'inc/Cart.php';
	require_once 'inc/InventoryDao.php';
	require_once 'inc/Customer.php';
	require_once 'inc/CustomerDao.php';
	require_once 'inc/Payment.php';
	require_once 'inc/CreditCard.php';

	$start = $_POST["start"];
	$end = $_POST["end"];
	
	echo json_encode(Sale::GetSaleByDateTime($start, $end, 30, 1));

}

if (isset($_POST["get_transaction_by_cartid"])) {
	require_once 'inc/Sale.php';
	require_once 'inc/PaymentDao.php';
	require_once 'inc/Cart.php';
	require_once 'inc/InventoryDao.php';
	require_once 'inc/Customer.php';
	require_once 'inc/CustomerDao.php';
	require_once 'inc/Payment.php';
	require_once 'inc/CreditCard.php';

	$cartId = $_POST["get_transaction_by_cartid"];
	echo json_encode(Sale::GetSaleByCartId($cartId));

}

if (isset($_POST["get_promotion_by_datetime"])) {
	require_once 'inc/Promotion.php';
	require_once 'inc/PaymentDao.php';
	require_once 'inc/Admin.php';
	require_once 'inc/CustomerDao.php';

	$start = $_POST["start"];
	$end = $_POST["end"];
	
	echo json_encode(Promotion::GetPromotionsByDateTime($start, $end, 30, 1));

}

if (isset($_POST["confirm-payment_only_outer_fullfill"])) {
	include_once 'inc/CreditCard.php';
	include_once 'inc/Customer.php';
	include_once 'inc/CustomerDao.php';
	include_once 'inc/Cart.php';
	include_once 'inc/Sale.php';
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	require_once ('inc/Promotion.php');
	require_once ('inc/PaymentDao.php');
	require_once ('inc/Payment.php');

	$card = CreditCard::CreateCreditCard($_POST["name"], $_POST["number"], $_POST["cvv"], $_POST["expYear"], $_POST["expMonth"]);
	$customer = Customer::GetCustomer($_POST['customerid']);	
	$customerDetail = $_POST["customerDetail"];
	$cart = $customer->getCart();
	$cart->setFee($_POST["fee"]);
	$sale = $cart->purchase($card, $customerDetail);

	if($sale !== null){
		echo("Pass");
	}else{
		echo("Fail");
	}
}
