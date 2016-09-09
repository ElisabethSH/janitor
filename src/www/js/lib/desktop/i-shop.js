
// edit Cart/Order data section (basics, contact, billing address etc.)
Util.Objects["editDataSection"] = new function() {
	this.init = function(form) {

		var header = u.qs("h2", form.parentNode);

		var action = u.ae(header, "span", {"html":"edit"});
		
		action.change_form = form;
		u.ce(action);


		u.f.init(form);


		action.clicked = function(event) {

			if(this.change_form.is_open) {
				this.change_form.is_open = false;
				this.innerHTML = "Edit";
				this.change_form.reset();
				u.ass(this.change_form, {
					"display":"none"
				})
			}
			else {
				this.change_form.is_open = true;
				this.innerHTML = "Cancel";
				u.ass(this.change_form, {
					"display":"block"
				})
				u.f.init(this.change_form);
			}
		}


		form.submitted = function() {

			this.response = function(response) {
				page.notify(response);

				if(response && response.cms_status == "success") {
					location.reload(true);
				}
			}
			
			u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
		}

	}
}



// newOrderFromCart
Util.Objects["newOrderFromCart"] = new function() {
	this.init = function(div) {

		var bn_convert = u.qs("li.convert", div);
		if(bn_convert) {

			bn_convert.confirmed = function(response) {
				u.bug("confirmed checkout")

				if(response.cms_status == "success") {
//					u.bug("location: " + location.href.replace(/\/cart\/edit\/.+/, "/order/edit/"+response.cms_object["id"]));

					location.href = location.href.replace(/\/cart\/edit\/.+/, "/order/edit/"+response.cms_object["id"]);
				}
			}
		}
	}
}


//
Util.Objects["cartItemsList"] = new function() {
	this.init = function(div) {
		u.bug("cartItemsList");

		div.total_cart_price = u.qs("dd.total_cart_price");


		var i, node;
		for(i = 0; node = div.nodes[i]; i++) {

			node.unit_price = u.qs("span.unit_price", node);
			node.total_price = u.qs("span.total_price", node);

			// look for quantity update form
			var quantity_form = u.qs("form.updateCartItemQuantity", node)

			// initialize quantity form
			if(quantity_form) {
				quantity_form.node = node;

				u.f.init(quantity_form);


				quantity_form.fields["quantity"].updated = function() {
					u.ac(this._form.actions["update"], "primary");

					this._form.submit();
				}


				quantity_form.submitted = function() {

					this.response = function(response) {
						page.notify(response);

						if(response && response.cms_status == "success") {

							this.node.unit_price.innerHTML = response.cms_object["unit_price_formatted"];
							this.node.total_price.innerHTML = response.cms_object["total_price_formatted"];
							this.node.div.total_cart_price.innerHTML = response.cms_object["total_cart_price_formatted"];

				 			u.rc(this.actions["update"], "primary");

						}
					}

					u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
				}
			}


			

			var bn_delete = u.qs("ul.actions li.delete", node);
			if(bn_delete) {
				bn_delete.node = node;
				bn_delete.deletedFromCart = function(response) {
					if(response && response.cms_status == "success") {

						// update total price
						this.node.div.total_cart_price.innerHTML = response.cms_object["total_cart_price_formatted"];

					}

					this.confirmed(response);
				}
				
			}


			//
			// node.li_unsubscribe = u.qs("li.unsubscribe", node);
			// node.li_subscribe = u.qs("li.subscribe", node);
			//
			// // init if form is available
			// if(node.li_unsubscribe) {
			//
			// 	node.li_unsubscribe.node = node;
			// 	// callback from oneButtonForm
			// 	node.li_unsubscribe.confirmed = function(response) {
			//
			// 		if(response.cms_status == "success") {
			// 			page.notify({"isJSON":true, "cms_status":"success", "cms_message":"Unsubscribed from newsletter"});
			// 			u.rc(this.node, "subscribed");
			// 		}
			// 		else {
			// 			page.notify({"isJSON":true, "cms_status":"error", "cms_message":"Could not unsubscribe"});
			// 		}
			//
			// 	}
			//
			// }
			

		}

	}
}



// New cart
Util.Objects["orderItemsList"] = new function() {
	this.init = function(div) {

		u.bug("orderItemsList");

		div.total_order_price = u.qs("dd.total_order_price");
		div.order_status = u.qs("dd.status");
		div.payment_status = u.qs("dd.payment_status");
		div.shipping_status = u.qs("dd.shipping_status");


		var i, node;
		for(i = 0; node = div.nodes[i]; i++) {

			node.unit_price = u.qs("span.unit_price", node);
			node.total_price = u.qs("span.total_price", node);

			// look for quantity update form
			var quantity_form = u.qs("form.updateOrderItemQuantity", node)

			// initialize quantity form
			if(quantity_form) {
				quantity_form.node = node;

				u.f.init(quantity_form);


				quantity_form.fields["quantity"].updated = function() {
					u.ac(this._form.actions["update"], "primary");

					this._form.submit();
				}


				quantity_form.submitted = function() {

					this.response = function(response) {
						page.notify(response);

						if(response && response.cms_status == "success") {

							this.node.unit_price.innerHTML = response.cms_object["unit_price_formatted"];
							this.node.total_price.innerHTML = response.cms_object["total_price_formatted"];
							this.node.div.total_order_price.innerHTML = response.cms_object["total_order_price_formatted"];

				 			u.rc(this.actions["update"], "primary");

						}
					}

					u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
				}
			}


			

			var bn_delete = u.qs("ul.actions li.delete", node);
			if(bn_delete) {
				bn_delete.node = node;
				bn_delete.deletedFromOrder = function(response) {
					if(response && response.cms_status == "success") {

						// update total price
						this.node.div.total_order_price.innerHTML = response.cms_object["total_order_price_formatted"];

					}

					this.confirmed(response);
				}
				
			}


			node.li_shipped = u.qs("ul.actions li.shipped", node);

			u.bug("node.li_shipped:" + node.li_shipped)
			// init if form is available
			if(node.li_shipped) {

				node.li_shipped.node = node;

				u.o.oneButtonForm.init(node.li_shipped);

				// callback from oneButtonForm
				node.li_shipped.confirmed = function(response) {

					if(response.cms_status == "success") {

						if(this.node.div.order_status.innerHTML != response.cms_object["order_status_text"]) {
//							location.reload(true);
						}

						this.node.div.order_status.innerHTML = response.cms_object["order_status_text"];
						this.node.div.shipping_status.innerHTML = response.cms_object["shipping_status_text"];
						this.node.div.payment_status.innerHTML = response.cms_object["payment_status_text"];

						u.rc(this.node, "shipped");
					}

				}

			}

			node.not_shipped = u.qs("ul.actions li.not_shipped", node);

			// init if form is available
			if(node.not_shipped) {

				node.not_shipped.node = node;

				u.o.oneButtonForm.init(node.not_shipped);

				// callback from oneButtonForm
				node.not_shipped.confirmed = function(response) {


					if(response.cms_status == "success") {

						if(this.node.div.order_status.innerHTML != response.cms_object["order_status_text"]) {
//							location.reload(true);
						}

						this.node.div.order_status.innerHTML = response.cms_object["order_status_text"];
						this.node.div.shipping_status.innerHTML = response.cms_object["shipping_status_text"];
						this.node.div.payment_status.innerHTML = response.cms_object["payment_status_text"];

						u.ac(this.node, "shipped");

					}

				}

			}
			
		}
	}
}
