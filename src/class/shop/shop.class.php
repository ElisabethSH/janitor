<?php
/**
* @package janitor.shop
*/



/**
* Cart and Order helper class
*
*/

class Shop {

	/**
	*
	*/
	function __construct() {
		$this->db_carts = SITE_DB.".shop_carts";
		$this->db_items = SITE_DB.".shop_cart_items";
		$this->db_orders = SITE_DB.".shop_orders";
		$this->db_order_items = SITE_DB.".shop_order_items";

	}



	// get carts
	// - optional multiple carts, based on content match
	function getCarts($_options=false) {

		// get specific cart
		$cart_id = false;

		// get all carts containing $item_id
		$item_id = false;

		// get carts based on timestamps
		$before = false;
		$after = false;

		$order = "status DESC, id DESC";

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "cart_id"  : $cart_id    = $_value; break;
					case "item_id"  : $item_id    = $_value; break;
					case "before"   : $before     = $_value; break;
					case "after"    : $after      = $_value; break;

					case "order"    : $order      = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific cart
		if($cart_id) {

//			print "SELECT * FROM ".$this->db_carts." as carts WHERE carts.id = ".$cart_id." LIMIT 1";
			$sql = "SELECT * FROM ".$this->db_carts." WHERE id = ".$cart_id." LIMIT 1";
			if($query->sql($sql)) {
				$cart = $query->result(0);
				$cart["items"] = false;

				$sql = "SELECT * FROM ".$this->db_items." as items WHERE items.cart_id = ".$cart_id;
//				print $sql;
				if($query->sql($sql)) {
					$cart["items"] = $query->results();
				}
				return $cart;
			}
		}

		// get all carts with item_id in it
		if($item_id) {

			if($query->sql("SELECT * FROM ".$this->db_items." WHERE item_id = $item_id GROUP BY cart_id")) {
				$results = $query->results();
				$carts = false;
				foreach($results as $result) {
					$carts[] = $this->getCarts(array("cart_id" => $result["cart_id"]));
				}
				return $carts;
			}
		}

		// return all carts
		if(!$cart_id && !$item_id) {
			if($query->sql("SELECT * FROM ".$this->db_carts." ORDER BY $order")) {
				$carts = $query->results();

				foreach($carts as $i => $cart) {
					$carts[$i]["items"] = false;
					if($query->sql("SELECT * FROM ".$this->db_items." WHERE cart_id = ".$cart["id"])) {
						$carts[$i]["items"] = $query->results();
					}
				}
				return $carts;
			}
		}

		return false;
	}


	// if no cart in session - create new cart

	// add product to cart - 4 parameters exactly
	// /cart-controller/addToCart/
	// posting: item_id, quantity, cart_id
	function addToCart() {

		$query = new Query();
		$IC = new Item();
		global $page;

		$query->checkDbExistance($this->db_carts);
		$query->checkDbExistance($this->db_items);
		$query->checkDbExistance($this->db_orders);
		$query->checkDbExistance($this->db_order_items);

		// if cart_id from form, prioritize it
		// TODO: Is cart_id ever sent as post?
		$cart_id = stringOr(getPost("cart_id"), Session::value("cart_id"));

		// no cart id - create new cart
		if(!$cart_id) {
			// TODO: add user id to cart creation when users are implemented
			$currency = $page->currency();
			$query->sql("INSERT INTO ".$this->db_carts." VALUES(DEFAULT, '".$page->country()."', '".$currency["id"]."', DEFAULT, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
			$cart_id = $query->lastInsertId();

			Session::value("cart_id", $cart_id);
			// save cookie
			setcookie("cart_id", $cart_id, time()+60*60*24*60, "/");
		}
		// update cart modified at
		else {
			$query->sql("UPDATE ".$this->db_carts." SET modified_at = CURRENT_TIMESTAMP, status = 1 WHERE id = ".$cart_id);
		}

		$item_id = getPost("item_id");
		// add item to cart, if item exists
		if($item_id && $IC->getItem($item_id)) {

			// item already exists in cart, update quantity
			if($query->sql("SELECT * FROM ".$this->db_items." items WHERE items.cart_id = ".$cart_id." AND item_id = ".$item_id)) {
				$cart_item = $query->result(0);

				// INSERT current quantity+1
				$query->sql("UPDATE ".$this->db_items." SET quantity = ".($cart_item["quantity"]+1)." WHERE id = ".$cart_item["id"]);
			}
			// just add item to cart
			else {

				$query->sql("INSERT INTO ".$this->db_items." VALUES(DEFAULT, '".$item_id."', '".$cart_id."', 1)");
			}
		}
	}


	// update cart quantity
	// posting item_id + quantity
	function updateQuantity() {

		$query = new Query();
		$IC = new Item();
		global $page;

		// if cart_id from form, prioritize it
		$cart_id = stringOr(getPost("cart_id"), Session::value("cart_id"));

		$item_id = getPost("item_id");
		$quantity = getPost("quantity");

		// update quantity if item exists in cart
		if($query->sql("SELECT * FROM ".$this->db_items." as items WHERE items.cart_id = ".$cart_id." AND item_id = ".$item_id)) {
			$cart_item = $query->result(0);

			if($quantity) {
				// INSERT current quantity+1
				$query->sql("UPDATE ".$this->db_items." SET quantity = ".$quantity." WHERE id = ".$cart_item["id"]);
			}
			else {
				// DELETE
				$query->sql("DELETE FROM ".$this->db_items." WHERE id = ".$cart_item["id"]);
			}
		}
	}

	// delete cart - 2 parameters exactly
	// /deleteCart/#cart_id#
	function deleteCart($action) {

		if(count($action) == 2) {

			$query = new Query();
			if($query->sql("DELETE FROM ".$this->db_carts." WHERE id = ".$action[1])) {
				message()->addMessage("Cart deleted");
				return true;
			}

		}

		message()->addMessage("Cart could not be deleted - refresh your browser", array("type" => "error"));
		return false;

	}

	function getOrders($_options=false) {

		$user = new User();

		// get specific cart
		$cart_id = false;

		$order_id = false;

		// get all orders containing $user_id
		$user_id = false;

		// get all orders containing $item_id
		$item_id = false;

		// get carts based on timestamps
		$before = false;
		$after = false;

		$order = "status DESC, id DESC";

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "cart_id"    : $cart_id    = $_value; break;
					case "user_id"    : $user_id    = $_value; break;
					case "order_id"   : $order_id    = $_value; break;
					case "item_id"    : $item_id    = $_value; break;
					case "before"     : $before     = $_value; break;
					case "after"      : $after      = $_value; break;

					case "order"      : $order      = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific order
		if($order_id) {

//			print "SELECT * FROM ".$this->db_orders." WHERE id = ".$order_id." LIMIT 1";
			if($query->sql("SELECT * FROM ".$this->db_orders." WHERE id = ".$order_id." LIMIT 1")) {
				$order = $query->result(0);
				$order["items"] = false;

				if($query->sql("SELECT * FROM ".$this->db_order_items." as items WHERE items.order_id = ".$order_id)) {
					$order["items"] = $query->results();
				}

				$email = $user->getUsernames(array("user_id" => $order["user_id"], "type" => "email"));
				if($email) {
					$order["email"] = $email["username"];
				}
				else {
					$order["email"] = "n/a";
				}

				$mobile = $user->getUsernames(array("user_id" => $order["user_id"], "type" => "mobile"));
				if($mobile) {
					$order["mobile"] = $mobile["username"];
				}
				else {
					$order["mobile"] = "n/a";
				}

				return $order;
			}
		}

		// get specific cart
		if($cart_id) {

//			print "SELECT * FROM ".UT_CARTS." as carts WHERE carts.id = ".$cart_id." LIMIT 1";
			if($query->sql("SELECT * FROM ".$this->db_orders." WHERE cart_id = ".$cart_id." LIMIT 1")) {
				$order = $query->result(0);
				$order["items"] = false;

				if($query->sql("SELECT * FROM ".$this->db_order_items." as items WHERE items.order_id = ".$order["id"])) {
					$order["items"] = $query->results();
				}

				// get email and mobile from user
				$email = $user->getUsernames(array("user_id" => $order["user_id"], "type" => "email"));
				if($email) {
					$order["email"] = $email["username"];
				}
				else {
					$order["email"] = "n/a";
				}

				$mobile = $user->getUsernames(array("user_id" => $order["user_id"], "type" => "mobile"));
				if($mobile) {
					$order["mobile"] = $mobile["username"];
				}
				else {
					$order["mobile"] = "n/a";
				}

				return $order;
			}
		}

		// TODO: get all carts with item_id in it
		if($item_id) {

			// if($query->sql("SELECT * FROM ".$this->db_order_items." WHERE item_id = $item_id GROUP BY cart_id")) {
			// 	$results = $query->results();
			// 	$carts = false;
			// 	foreach($results as $result) {
			// 		$carts[] = $this->getCarts(array("cart_id" => $result["cart_id"]));
			// 	}
			// 	return $carts;
			// }
		}

		// return all carts
		if(!$cart_id && !$item_id && !$user_id && !$order_id) {
			if($query->sql("SELECT * FROM ".$this->db_orders." ORDER BY $order")) {
				$orders = $query->results();

				foreach($orders as $i => $order) {
					$orders[$i]["items"] = false;
					if($query->sql("SELECT * FROM ".$this->db_order_items." WHERE order_id = ".$order["id"])) {
						$orders[$i]["items"] = $query->results();
					}

					// get email and mobile from user
					$email = $user->getUsernames(array("user_id" => $orders[$i]["user_id"], "type" => "email"));
					if($email) {
						$orders[$i]["email"] = $email["username"];
					}
					else {
						$orders[$i]["email"] = "n/a";
					}

					$mobile = $user->getUsernames(array("user_id" => $orders[$i]["user_id"], "type" => "mobile"));
					if($mobile) {
						$orders[$i]["mobile"] = $mobile["username"];
					}
					else {
						$orders[$i]["mobile"] = "n/a";
					}
					
				}
				return $orders;
			}
		}

		return false;
	}

	// calculate total order price
	function getTotalOrderPrice($order_id) {
		$order = $this->getOrders(array("order_id" => $order_id));
		$total = 0;

		if($order["items"]) {
			foreach($order["items"] as $item) {
				$total += ($item["total_price"] + $item["total_vat"]);
			}
		}
		return $total;
	}

	// ;
	// $prices[$index]["formatted_with_vat"] = ($price["abbreviation_position"] == "before" ? $price["abbreviation"]." " : "") . number_format($price["price"]* (1 + ($price["vatrate"]/100)), $price["decimals"], $price["decimal_separator"], $price["grouping_separator"]) . ($price["abbreviation_position"] == "after" ? " ".$price["abbreviation"] : "");
	// $prices[$index]["price_with_vat"] = number_format($price["price"]* (1 + ($price["vatrate"]/100)), $price["decimals"], $price["decimal_separator"], $price["grouping_separator"]);

	function updateOrder() {

		$user = new User();


		$cart_id = getPost("cart_id");
		// does values validate
		if($cart_id && $user->validateList(array("nickname","email","mobile","att","address1","address2","city","address1"))) {

			$query = new Query();
			$entities = $user->data_entities;

			// make sure type tables exist
			$query->checkDbExistance($user->db);
			$query->checkDbExistance($user->db_usernames);
			$query->checkDbExistance($user->db_addresses);
			$query->checkDbExistance($user->db_newsletters);

			$query->checkDbExistance($this->db_orders);
			$query->checkDbExistance($this->db_order_items);

			// check for existing order
			$order = $this->getOrders(array("cart_id" => $cart_id));
			if($order) {
				$user_id = $order["user_id"];
				$order_id = $order["id"];
			}


			// if order do not exist
			if(!$order_id) {

				// enough info to create user
				if($entities["nickname"]["value"] && $entities["email"]["value"] && $entities["mobile"]["value"]) {

					// create user
					$sql = "INSERT INTO ".$user->db." SET id=DEFAULT, nickname='".$entities["nickname"]["value"]."'";
					if($query->sql($sql)) {
						$user_id = $query->lastInsertId();

						$order_no = randomKey(10);
						// create order
						$sql = "INSERT INTO ".$this->db_orders." SET id=DEFAULT,order_no='$order_no', user_id=$user_id, cart_id=$cart_id";
						if($query->sql($sql)) {

							$order_id = $query->lastInsertId();
//							Session::value("order_id", $order_id);
						}
					}
				}
			}

			// we have enough info to update order
			if($order_id && $user_id && $cart_id) {

				Session::value("order_id", $order_id);

				$cart = $this->getCarts(array("cart_id" => $cart_id));
//				print_r($cart);

				// update modified timestamp for order
				$sql = "UPDATE ".$user->db_orders." SET modified_at=CURRENT_TIMESTAMP WHERE id = $order_id";
				$query->sql($sql);


				// remove existing order items
				$sql = "DELETE FROM ".$this->db_order_items." WHERE order_id = $order_id";
				$query->sql($sql);

				// update user nickname
				$sql = "UPDATE ".$user->db." SET nickname='".$entities["nickname"]["value"]."' WHERE id = ".$user_id;
				$query->sql($sql);

				// update usernames (email+mobile)
				$email = $user->getUsernames(array("user_id" => $user_id, "type" => "email"));
				if($email) {
					$sql = "UPDATE ".$user->db_usernames." SET username='".$entities["email"]["value"]."' WHERE id = ".$email["id"];
				}
				else {
					$sql = "INSERT INTO ".$user->db_usernames." SET id=DEFAULT, user_id=$user_id, username='".$entities["email"]["value"]."', type='email'";
				}
				$query->sql($sql);

				$mobile = $user->getUsernames(array("user_id" => $user_id, "type" => "mobile"));
				if($mobile) {
					$sql = "UPDATE ".$user->db_usernames." SET username='".$entities["mobile"]["value"]."' WHERE id = ".$mobile["id"];
				}
				else {
					$sql = "INSERT INTO ".$user->db_usernames." SET id=DEFAULT, user_id=$user_id, username='".$entities["mobile"]["value"]."', type='mobile'";
				}
				$query->sql($sql);

				// add newsletter
				$newsletter = getPost("newsletter");
				if($newsletter) {
					if(!$query->sql("SELECT * FROM ".$user->db_newsletters." WHERE user_id = $user_id AND newsletter = 'general'")) {
						$sql = "INSERT INTO ".$user->db_newsletters." SET id=DEFAULT, user_id=$user_id,newsletter='general'";
						$query->sql($sql);
					}
				}


				// TODO: add address as user_addresses if it does not exist already


				// update general order info
				$sql = "UPDATE ".$this->db_orders." SET ";
				$sql .= "country='".$cart["country"]."',";
				$sql .= "currency='".$cart["currency"]."',";
				$sql .= "delivery_name='".$entities["nickname"]["value"]."',";
				$sql .= "delivery_address1='".$entities["address1"]["value"]."',";
				$sql .= "delivery_address2='".$entities["address2"]["value"]."',";
				$sql .= "delivery_city='".$entities["city"]["value"]."',";
				$sql .= "delivery_postal='".$entities["postal"]["value"]."',";
//				$sql .= "delivery_country='".$entities["country"]["value"]."'";
				// TODO: make country dynamic
				$sql .= "delivery_country='Danmark'";
				$sql .= " WHERE id=$order_id";
//				print $sql."<br>";
				$query->sql($sql);

				// update order item
				if($cart["items"]) {
					$IC = new Item();
					foreach($cart["items"] as $cart_item) {
						$item = $IC->getCompleteItem($cart_item["item_id"]);

						$name = $item["name"];
						$quantity = $cart_item["quantity"];

						// TODO: update price handling
						foreach($item["prices"] as $price_in_currency) {
							if($price_in_currency["currency"] == $cart["currency"]) {
								$price = $price_in_currency["price"];
								$vat = $price*($price_in_currency["vatrate"]/100);

								$total_price = $price*$quantity;
								$total_vat = $vat*$quantity;
							}
						}

						// no price??
						if(!$price) {
							$price = 0;
							$vat = 0;
							
							$total_price = 0;
							$total_vat = 0;
						}

						$sql = "INSERT INTO ".$this->db_order_items." SET ";
						$sql .= "id=DEFAULT, ";
						$sql .= "order_id=$order_id, ";
						$sql .= "item_id=".$cart_item["item_id"].",";
						$sql .= "name='".$name."',";
						$sql .= "quantity='".$quantity."',";
						$sql .= "price='".$price."',";
						$sql .= "vat='".$vat."',";
						$sql .= "total_price='".$total_price."',";
						$sql .= "total_vat='".$total_vat."'";
						
//						print $sql."<br>";
						$query->sql($sql);
					}
				}


				return true;
			}
		}

		return false;
	}


	// // delete cart - 2 parameters exactly
	// // /deleteOrder/#order_id#
	// function deleteOrder($action) {
	// 
	// 	print_r($action);
	// 
	// 	if(count($action) == 2) {
	// 
	// 		$query = new Query();
	// 		print "DELETE FROM ".$this->db_orders." WHERE id = ".$action[1];
	// 		if($query->sql("DELETE FROM ".$this->db_orders." WHERE id = ".$action[1])) {
	// 			message()->addMessage("Order deleted");
	// 			return true;
	// 		}
	// 
	// 	}
	// 
	// 	message()->addMessage("Order could not be deleted - refresh your browser", array("type" => "error"));
	// 	return false;
	// 
	// }

}

?>