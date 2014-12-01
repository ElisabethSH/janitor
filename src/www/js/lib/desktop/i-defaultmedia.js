// Add images form
Util.Objects["addMedia"] = new function() {
	this.init = function(div) {

		div.form = u.qs("form.upload", div);
		div.form.div = div;
		div.media_list = u.qs("ul.mediae", div);

		div.item_id = u.cv(div, "item_id");


		u.f.init(div.form);


		div.csrf_token = div.form.fields["csrf-token"].val();
		div.delete_url = div.getAttribute("data-media-delete");
		div.update_name_url = div.getAttribute("data-media-name");
		div.save_order_url = div.getAttribute("data-media-order");



		div.form.file_input = u.qs("input[type=file]", div.form);
		div.form.file_input.div = div;
		div.form.file_input.changed = function() {
			this.form.submit();
		}

		// upload form submitted
		div.form.submitted = function() {

			u.ac(this.file_input.field, "loading");
			u.rc(this.file_input.field, "focus");

			var form_data = new FormData(this);
			this.response = function(response) {
				page.notify(response);

				// inject/update image if everything went well
				if(response.cms_status == "success" && response.cms_object) {

					var i, media, li, image;
					for(i = 0; media = response.cms_object[i]; i++) {
						var li = u.ie(div.media_list, "li");

						li.media_list = this.div.media_list;

						u.ac(li, "media image");
						u.ac(li, "variant:"+media.variant);
						u.ac(li, "media_id:"+media.media_id);
						var image = u.ae(li, "img");
						image.src = "/images/"+media.item_id+"/"+media.variant+"/x"+li.offsetHeight+"."+media.format+"?"+u.randomString(4);

						// is name returned from upload
						if(media.name) {
							li.p_name = u.ae(li, "p", {"html":media.name});

							// Set p width to match li
							var n_w = media.width/media.height * li.offsetHeight;
							var p_p_l = parseInt(u.gcs(li.p_name, "padding-left"));
							var p_p_r = parseInt(u.gcs(li.p_name, "padding-right"));
							u.as(li.p_name, "width", (n_w - p_p_l - p_p_r)+"px");

							// add update form for image name
							if(this.div.update_name_url) {
								this.div.addUpdateNameForm(li);
							}
						}

						// add delete form for image
						if(this.div.delete_url) {
							this.div.addDeleteForm(li);
						}
					}

					if(this.div.save_order_url) {
						u.sortable(this.div.media_list);
					}
				}

				u.rc(this.file_input.field, "loading");
				this.file_input.val("");
			}
			u.request(this, this.action, {"method":"post", "params":form_data});
		}


		// add delete form
		div.addDeleteForm = function(li) {

			var delete_form = u.f.addForm(li, {"action":this.delete_url+"/"+this.item_id+"/"+u.cv(li, "variant"), "class":"delete"});
			delete_form.li = li;
			u.ae(delete_form, "input", {"type":"hidden", "name":"csrf-token", "value":this.csrf_token});

			var bn_delete = u.f.addAction(delete_form, {"class":"button delete"});

			delete_form.deleted = function() {
				this.li.parentNode.removeChild(this.li);
				u.sortable(div.media_list, {"targets":"mediae", "draggables":"media"});
			}
			u.o.deleteMedia.init(delete_form);
		}

		// add delete form
		div.addUpdateNameForm = function(li) {

			li.p_name.li = li;

			// enable edit state
			u.ce(li.p_name);
			// eliminate dragging if sorting is also enable
			li.p_name.inputStarted = function(event) {
				u.e.kill(event);
				this.li.media_list._sorting_disabled = true;
			}
			li.p_name.clicked = function(event) {
				u.ac(this.li, "edit");

				var input = this.li.update_name_form.fields["name"];
				var field = input.field;

				input.focus();

				// set specific input width to match image
				var f_w = field.offsetWidth;
				var f_p_l = parseInt(u.gcs(field, "padding-left"));
				var f_p_r = parseInt(u.gcs(field, "padding-right"));
				var i_p_l = parseInt(u.gcs(input, "padding-left"));
				var i_p_r = parseInt(u.gcs(input, "padding-right"));
				var i_m_l = parseInt(u.gcs(input, "margin-left"));
				var i_m_r = parseInt(u.gcs(input, "margin-right"));
				var i_b_l = parseInt(u.gcs(input, "border-left-width"));
				var i_b_r = parseInt(u.gcs(input, "border-right-width"));
				u.as(input, "width", (f_w - f_p_l - f_p_r - i_p_l - i_p_r - i_m_l - i_m_r - i_b_l - i_b_r)+"px");

			}

			// add update form
			li.update_name_form = u.f.addForm(li, {"action":this.update_name_url+"/"+this.item_id+"/"+u.cv(li, "variant"), "class":"edit"});
			li.update_name_form.li = li;
			var field = u.ae(li.update_name_form, "input", {"type":"hidden", "name":"csrf-token", "value":this.csrf_token});
			var field = u.f.addField(li.update_name_form, {"type":"string","name":"name", "value":li.p_name.innerHTML});

			// init form
			u.f.init(li.update_name_form);

			// submit on blur
			li.update_name_form.fields["name"].blurred = function() {
				u.bug("blurred")
				this.form.updateName();
			}

			// do nothing on submit - it is handled on blur
			li.update_name_form.submitted = function() {}

			// update name
			li.update_name_form.updateName = function() {

				u.rc(this.li, "edit");
				this.li.media_list._sorting_disabled = false;

				// submit new image name
				this.response = function(response) {

					page.notify(response);

					// inject/update image if everything went well
					if(response.cms_status == "success" && response.cms_object) {
						this.li.p_name.innerHTML = this.fields["name"].val();
					}
					else {
						this.fields["name"].val(this.li.p_name.innerHTML);
					}

				}
				u.request(this, this.action, {"method":this.method, "params":u.f.getParams(this)});

			}
		}

		// image list exists?
		if(!div.media_list) {
			u.ae(div, "ul", {"class":"mediae"});
		}

		// get media list nodes
		div.media_list.nodes = u.qsa("li.media", div.media_list);
		div.media_list.div = div;

		// inject delete forms in existing media list
		var i, node;
		for(i = 0; node = div.media_list.nodes[i]; i++) {
			node.media_list = div.media_list;

			// add delete form
			if(div.delete_url) {
				div.addDeleteForm(node);
			}

			// image name element
			node.p_name = u.qs("p", node);
			if(node.p_name) {

				// Set p width to match li
				var n_w = node.offsetWidth;
				var p_p_l = parseInt(u.gcs(node.p_name, "padding-left"));
				var p_p_r = parseInt(u.gcs(node.p_name, "padding-right"));
				u.as(node.p_name, "width", (n_w - p_p_l - p_p_r)+"px");

				// add update form for image name
				if(div.update_name_url) {
					div.addUpdateNameForm(node);
				}
			}
		}


		// sortable list
		if(u.hc(div, "sortable") && div.media_list && div.save_order_url) {

			u.sortable(div.media_list, {"targets":"mediae", "draggables":"media"});
			div.media_list.picked = function() {}
			div.media_list.dropped = function() {
				var order = new Array();
				this.nodes = u.qsa("li.media", this);
				for(i = 0; node = this.nodes[i]; i++) {
					order.push(u.cv(node, "media_id"));
				}
				this.response = function(response) {
					// Notify of event
					page.notify(response);
				}
				u.request(this, this.div.save_order_url+"/"+this.div.item_id, {"method":"post", "params":"csrf-token=" + this.div.csrf_token + "&order=" + order.join(",")});
			}
		}
		else {
			u.rc(div, "sortable");
		}

	}
}

// default delete form
Util.Objects["deleteMedia"] = new function() {
	this.init = function(form) {
//		u.bug("deleteMedia init:" + u.nodeId(form));

		u.f.init(form);

		var bn_delete = u.qs("input.delete", form);
		if(bn_delete) {

			bn_delete.org_value = bn_delete.value;

			u.e.click(bn_delete);
			bn_delete.restore = function(event) {
				this.value = this.org_value;
				u.rc(this, "confirm");
			}

			bn_delete.inputStarted = function(event) {
				u.e.kill(event);
			}

			bn_delete.clicked = function(event) {
				u.e.kill(event);

				// first click
				if(!u.hc(this, "confirm")) {
					u.ac(this, "confirm");
					this.value = "Confirm";
					this.t_confirm = u.t.setTimer(this, this.restore, 3000);
				}
				// confirm click
				else {
					u.t.resetTimer(this.t_confirm);

					this.response = function(response) {
						page.notify(response);

						if(response.cms_status == "success") {
							// check for constraint error preventing row from actually being deleted
							if(response.cms_object && response.cms_object.constraint_error) {
								this.value = this.org_value;
								u.ac(this, "disabled");
							}
							else {
								// look for callback method on form
								if(typeof(this.form.deleted) == "function") {
									this.form.deleted();
								}
								else {
									location.reload();
								}
							}
						}
						else {
							this.restore();
						}
					}
					u.request(this, this.form.action, {"method":"post", "params" : u.f.getParams(this.form)});
				}
			}
		}

	}
}



// Add images form
Util.Objects["addMediaSingle"] = new function() {
	this.init = function(div) {

		div.form = u.qs("form.upload", div);
		div.form.div = div;

		div.image = u.qs("img", div);

		div.is_media = u.hc(div, "media");
		div.is_audio = u.hc(div, "audio");
		div.is_video = u.hc(div, "video");


		div.item_id = u.cv(div, "item_id");
		div.media_variant = u.cv(div, "variant");
		div.media_format = u.cv(div, "format");


		u.f.init(div.form);


		div.csrf_token = div.form.fields["csrf-token"].val();
		div.delete_url = div.getAttribute("data-media-delete");


		div.form.file_input = u.qs("input[type=file]", div.form);
		div.form.file_input.div = div;
		div.form.file_input.changed = function() {
			this.form.submit();
		}

		div.form.submitted = function() {

			u.ac(this.file_input.field, "loading");
			u.rc(this.file_input.field, "focus");

			if(this.div.image) {
				u.as(this.div.image, "display", "none");
			}

			var form_data = new FormData(this);
			this.response = function(response) {
				page.notify(response);

				// hide existing image while waiting for response
				if(this.div.image) {
					u.as(this.div.image, "display", "block");
				}

				// inject/update image if everything went well
				if(response.cms_status == "success" && response.cms_object) {

					this.div._format = response.cms_object.format;
					u.rc(this.div, "format:[a-z]*");
					u.ac(this.div, "format:"+this.div._format);

					if(this.div.is_audio) {
						this.div.addAudioPreview();
					}
					else if(this.div.is_video) {
						this.div.addVideoPreview();
					}
					else {
						this.div.addImagePreview();
					}

				}

				u.rc(this.file_input.field, "loading");
				this.file_input.val("");
			}
			u.request(this, this.action, {"method":"post", "params":form_data});
		}

		// add delete form
		div.addDeleteForm = function() {

			if(!this.delete_form) {
				this.delete_form = u.f.addForm(this, {"action":this.delete_url+"/"+this.item_id+"/"+this.media_variant, "class":"delete"});
				this.delete_form.div = this;
				u.ae(this.delete_form, "input", {"type":"hidden", "name":"csrf-token", "value":this.csrf_token});
				this.bn_delete = u.f.addAction(this.delete_form, {"class":"button delete"});

				this.delete_form.deleted = function() {
					this.div.image.parentNode.removeChild(this.div.image);
					this.div.image = false;
					this.parentNode.removeChild(this);
				}
				u.o.deleteMedia.init(this.delete_form);
			}
		}


		div.addImagePreview = function() {

			if(!this.image && this.media_format) {
				this.image = u.ae(this, "img");
			}

			if(this.media_format) {

				this.addDeleteForm();

				if(this.media_format == "pdf") {
					this.image.src = "/images/0/pdf/x"+this.div.image.offsetHeight+".png?"+u.randomString(4);
				}
				else if(this.media_format == "zip") {
					this.image.src = "/images/0/zip/x"+this.div.image.offsetHeight+".png?"+u.randomString(4);
				}
				else if(this.media_format.match(/^(jpg|png)$/)) {
					this.image.src = "/images/"+this.item_id+"/"+this.media_variant+"/x"+this.image.offsetHeight+"."+this.media_format+"?"+u.randomString(4);
				}
			}
			
		}

		div.addAudioPreview = function() {
			
			if(!this.audio && this.media_format) {

				if(!page.audioplayer) {
					page.audioplayer = u.audioPlayer();
				}
				this.audio = u.ae(div.form, "div", {"class":"audio"});
				this.audio.div = this;

			}

			if(this.media_format) {

				this.addDeleteForm();

				this.audio.url = "/audios/"+this.item_id+"/"+this.media_variant+"/128."+this.media_format;

				u.e.click(this.audio);
				this.audio.clicked = function(event) {
				if(!u.hc(this, "playing")) {
						page.audioplayer.loadAndPlay(this.url);
						u.ac(this, "playing");
					}
					else {
						page.audioplayer.stop();
						u.rc(this, "playing");
					}
				}

			}

		}

		div.addVideoPreview = function() {}


		// add initial delete form if image exists
		if(div.is_audio) {
			div.addAudioPreview();
		}
		else if(div.is_audio) {
			div.addVideoPreview();
		}
		else if(div.is_media) {
			div.addImagePreview();
		}


//		u.bug("div.audio:" + div.audio)

	}
}