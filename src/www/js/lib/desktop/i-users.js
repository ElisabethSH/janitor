// default new form
Util.Objects["usernames"] = new function() {
	this.init = function(div) {

//		u.bug("div usernames")
		var form = u.qs("form", div);
		u.f.init(form);

		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);

				if(response.cms_status == "error") {
					for(x in response.cms_message) {
						if(response.cms_message[x].match(/email/i)) {
							u.f.fieldError(this.fields["email"]);
						}
						if(response.cms_message[x].match(/mobile/i)) {
							u.f.fieldError(this.fields["mobile"]);
						}
					}
					
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}

	}
}

// default new form
Util.Objects["password"] = new function() {
	this.init = function(div) {

		var password_state = u.qs("div.password_state", div);
		var new_password = u.qs("div.new_password", div);

		var a_create = u.qs(".password_missing a");
		var a_change = u.qs(".password_set a");

		a_create.new_password = new_password;
		a_change.new_password = new_password;
		a_create.password_state = password_state;
		a_change.password_state = password_state;

		u.ce(a_create);
		u.ce(a_change);
		a_create.clicked = a_change.clicked = function() {
			u.as(this.password_state, "display", "none");
			u.as(this.new_password, "display", "block");
		}

		var form = u.qs("form", div);
		form.password_state = password_state;
		form.new_password = new_password;

		u.f.init(form);

		form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success") {
					u.ac(this.password_state, "set");
					u.as(this.password_state, "display", "block");
					u.as(this.new_password, "display", "none");
				}
				page.notify(response);
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
			this.fields["password"].val("");

		}

	}
}


// default new form
Util.Objects["formAddressNew"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		form.actions["cancel"].clicked = function(event) {
			location.href = this.url;
		}

		form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success" && response.cms_object) {


//					alert(response);
					location.href = this.actions["cancel"].url.replace("\/list", "/edit/"+response.cms_object.item_id);
				}
				else if(response.cms_message) {
					page.notify(response);
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}

	}
}


Util.Objects["accessEdit"] = new function() {
	this.init = function(div) {

		div._item_id = u.cv(div, "item_id");

		// primary form
		var form = u.qs("form", div);
		u.f.init(form);
		form.actions["cancel"].clicked = function(event) {
			location.href = this.url;
		}
		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}

		// enable select all on controller heading
		var i, group;
		var groups = u.qsa("li.action", form);
		for(i = 0; group = groups[i]; i++) {

			var h3 = u.qs("h3", group);
			h3.group = group;
			u.ce(h3)
			h3.clicked = function() {

				var i, input;
				var inputs = u.qsa("input[type=checkbox]", this.group);
				for(i = 0; input = inputs[i]; i++) {
					input.val(1);
				}

			}

		}


	}
}