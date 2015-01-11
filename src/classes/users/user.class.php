<?php
/**
* @package janitor.users
* This file contains simple user functionality
*
* Simple user is supposed to be a minimal interface to User maintenance and the user tables
* It is vital that this class does not expose anything but the current user's information
*
*
* Only for NON-Admin creation of users, like
* - signups on website
* - newsletter signup
* - placement of orders by unregistered users
*
*
* Creates a member user (user_group=2), with limited privileges
* - update profile
* - newsletter administration
* - own order view (on shops)
*
* - comments if allowed (not decided how that is to be implemented)
* - ratings if allowed (not decided how that is to be implemented)
*/

/**
* TODO
* compare functionality need with User class
* consider extending Simpleuser from User to avoid duplet functionality
* (only if resonable overlap)
* (requires the ability to overwrite funtions - test it)
*
*
* These updates will require rewriting of Shop class and shop functionality (there is no meaningful way around it)
*/

/**
* Simpleuser
*/
class User extends Model {


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		// basic usertables
		$this->db = SITE_DB.".users";
		$this->db_usernames = SITE_DB.".user_usernames";
		$this->db_addresses = SITE_DB.".user_addresses";
		$this->db_passwords = SITE_DB.".user_passwords";
		$this->db_newsletters = SITE_DB.".user_newsletters";



		// BASIC INFO

		// Nickname
		$this->addToModel("nickname", array(
			"type" => "string",
			"label" => "Nickname",
			"required" => true,
			"hint_message" => "Write your nickname or whatever you want us to use to greet you", 
			"error_message" => "Nickname must be filled out"
		));
		// Firstname
		$this->addToModel("firstname", array(
			"type" => "string",
			"label" => "Firstname",
			"hint_message" => "Write your first- and middlenames",
			"error_message" => "Write your first- and middlenames"
		));
		// Lastname
		$this->addToModel("lastname", array(
			"type" => "string",
			"label" => "Lastname",
			"hint_message" => "Write your lastname",
			"error_message" => "Write your lastname"
		));
		// Language
		$this->addToModel("language", array(
			"type" => "string",
			"label" => "Your preferred language",
			"hint_message" => "Select your preferred language",
			"error_message" => "Invalid language"
		));


		// USERNAMES AND PASSWORD

		// email
		$this->addToModel("email", array(
			"type" => "email",
			"label" => "Your email",
			"hint_message" => "You can log in using your email",
			"error_message" => "Invalid email"
		));

		// mobile
		$this->addToModel("mobile", array(
			"type" => "tel",
			"label" => "Your mobile",
			"hint_message" => "Write your mobile number",
			"error_message" => "Invalid number"
		));

		// password
		$this->addToModel("password", array(
			"type" => "password",
			"label" => "Password",
			"hint_message" => "Type your password - must be 8-20 characters",
			"error_message" => "Invalid password"
		));


		// username (for login form)
		$this->addToModel("username", array(
			"type" => "string",
			"label" => "Email or mobile",
			"pattern" => "[\w\.\-\_]+@[\w-\.]+\.\w{2,4}|([\+0-9\-\.\s\(\)]){5,18}", 
			"hint_message" => "Use your emailaddress or mobilenumber to log in.", 
			"error_message" => "The entered value is neither an email or a mobilenumber."
		));


		// ADDRESS INFO

		// address label
		$this->addToModel("address_label", array(
			"type" => "string",
			"label" => "Address label",
			"required" => true,
			"hint_message" => "Give this address a label (home, office, parents, etc.)",
			"error_message" => "Invalid label"
		));
		// address name
		$this->addToModel("address_name", array(
			"type" => "string",
			"label" => "Name/Company",
			"required" => true,
			"hint_message" => "Name on door at address, your name or company name",
			"error_message" => "Invalid name"
		));
		// att
		$this->addToModel("att", array(
			"type" => "string",
			"label" => "Att",
			"hint_message" => "Att for address",
			"error_message" => "Invalid att"
		));
		// address 1
		$this->addToModel("address1", array(
			"type" => "string",
			"label" => "Address",
			"required" => true,
			"hint_message" => "Address",
			"error_message" => "Invalid address"
		));
		// address 2
		$this->addToModel("address2", array(
			"type" => "string",
			"label" => "Additional address",
			"hint_message" => "Additional address info",
			"error_message" => "Invalid address"
		));
		// city
		$this->addToModel("city", array(
			"type" => "string",
			"label" => "City",
			"required" => true,
			"hint_message" => "Write your city",
			"error_message" => "Invalid city"
		));
		// postal code
		$this->addToModel("postal", array(
			"type" => "string",
			"label" => "Postal code",
			"required" => true,
			"hint_message" => "Postalcode of your city",
			"error_message" => "Invalid postal code"
		));
		// state
		$this->addToModel("state", array(
			"type" => "string",
			"label" => "State/region",
			"hint_message" => "Write your state/region, if applicaple",
			"error_message" => "Invalid state/region"
		));
		// country
		$this->addToModel("country", array(
			"type" => "string",
			"label" => "Country",
			"required" => true,
			"hint_message" => "Country",
			"error_message" => "Invalid country"
		));


		// newsletter
		$this->addToModel("newsletter", array(
			"type" => "string",
			"label" => "Newsletter",
			"required" => true,
			"hint_message" => "Newsletter",
			"error_message" => "Invalid newsletter"
		));


	}



	/**
	* Get current user
	*
	*/
	function getUser() {

		// default values

		$query = new Query();
		$user_id = session()->value("user_id");

		$sql = "SELECT * FROM ".$this->db." WHERE id = $user_id";
//			print $sql;
		if($query->sql($sql)) {
			$user = $query->result(0);


			$user["mobile"] = "";
			$user["email"] = "";


			$sql = "SELECT * FROM ".$this->db_usernames." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				$usernames = $query->results();
				foreach($usernames as $username) {
					$user[$username["type"]] = $username["username"];
				}
			}


			$user["addresses"] = $this->getAddresses();


			$user["newsletters"] = false;
			$sql = "SELECT * FROM ".$this->db_newsletters." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				$user["newsletters"] = $query->results();
			}


			return $user;
		}

		return false;
	}


	/**
	* Get user nickname
	* 
	* TODO: could be extended with email (if user permits)
	*/
	function getUserInfo($_options=false) {

		// default values
		$user_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "user_id"        : $user_id          = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific user
		if($user_id) {

			$sql = "SELECT nickname FROM ".$this->db." WHERE id = $user_id";
//			print $sql;
			if($query->sql($sql)) {
				$user = $query->result(0);
				return $user;
			}
		}

		return false;
	}



	// NOTE: All output should be kept in frontend logic because it might need to be served in different language
	// or with specific context


	// create new user
	// email is minimum info to create user at this point (signup to newsletter)
	function newUser($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$email = $this->getProperty("email", "value");


		// if user already exists, return error
		if($this->userExists(array("email" => $email))) {
			return array("status" => "USER_EXISTS");
		}


		// does values validate
		if(count($action) == 1 && $this->validateList(array("email")) && $email) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db);

			// get entities for current value
			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(nickname|firstname|lastname|language)$/", $name)) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			// if no nickname were posted, use email
			if(array_search("nickname", $names) == -1) {
				$values[] = "nickname='".$email."'";
			}

			// add member user group
			$values[] = "user_group_id=2";


			$sql = "INSERT INTO ".$this->db." SET " . implode(",", $values);
//			print $sql;
			if($query->sql($sql)) {

				$user_id = $query->lastInsertId();

				// add email to user_usernames
				$sql = "INSERT INTO $this->db_usernames SET username = '$email', verified = 0, type = 'email', user_id = $user_id";
				$query->sql($sql);


				// TODO: user can send password on signup
				$raw_password = $this->getProperty("password", "value");
				$mail_password = "******** (password is encrypted)";

				if(!$raw_password) {
					// add temp password
					$raw_password = randomKey(8);
					$mail_password = $raw_password." (autogenerated password)";
				}

				// encrypt password
				$password = sha1($raw_password);
				$sql = "INSERT INTO ".$this->db_passwords." SET user_id = $user_id, password = '$password'";
				$query->sql($sql);


				// maybe this is not always a good idea, but it is for now :)
				// let the new user be logged in
				session()->value("user_id", $user_id);
				session()->value("user_group_id", 1);

				// send welcome email
				global $page;
				$page->mail(array("subject" => "signup", "message" => "EMAIL:$email\nPASSWORD:$mail_password", "recipients" => $email, "template" => "signup"));


				// return enough information to the frontend
				return array("user_id" => $user_id, "email" => $email);
			}
		}

		return false;
	}


	// update current profile
	// /janitor/admin/profile/update (values in POST)
	function update($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		if(count($action) == 1 && $user_id) {

			$query = new Query();

			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(firstname|lastname|nickname|language)$/", $name)) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($this->validateList($names, $user_id)) {
				if($values) {
					$sql = "UPDATE ".$this->db." SET ".implode(",", $values).",modified_at=CURRENT_TIMESTAMP WHERE id = ".$user_id;
//					print $sql;
				}

				if(!$values || $query->sql($sql)) {

					return true;
				}
			}
		}
		return false;
	}



	// xxx/(email|mobile)/#email|mobile#
	// 
	// this can be used to re-enable user (if user has been disabled for some reason)
	// will only make alterations if username is not already verified
	function confirmUser($action) {

		// does values validate
		if(count($action) == 3) {

			$query = new Query();
			$type = $action[1];
			$username = $action[2];

			// only make alterations if not already verified
			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = '$type' AND username = '$username' AND verified = 0";
			if($query->sql($sql)) {

				$user_id = $query->result(0, "user_id");

				$sql = "UPDATE ".$this->db_usernames." SET verified = 1 WHERE type = '$type' AND username = '$username'";
				$query->sql($sql);

				$query->sql("UPDATE ".$this->db." SET status = 1 WHERE id = $user_id");
				$query->sql($sql);

			}

		}
	}



	// check if user exists
	// checks if email or mobile already exists for different user_id
	// TODO: could be expanded to cover names and addresses as well
	// Consider if expanded "search" should be kept elsewhere
	function userExists($_options) {

		$email = false;
		$mobile = false;

		// user_id to check is user is same user
		$user_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "email"      : $email        = $_value; break;
					case "mobile"     : $mobile       = $_value; break;

					case "user_id"    : $user_id      = $_value; break;
				}
			}
		}

		$query = new Query();

		// check for users with same email
		if($email) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = 'email' AND username = '$email'".($user_id ? " AND user_id != $user_id" : "");
//			print $sql;
			if($query->sql($sql)) {
				return true;
			}
		}

		// check for users with same mobile
		if($mobile) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = 'mobile' AND username = '$mobile'".($user_id ? " AND user_id != $user_id" : "");
//			print $sql;
			if($query->sql($sql)) {
				return true;
			}
		}

		return false;
	}




	// USERNAMES


	// Update email from posted values
	// /janitor/admin/profile/updateEmail
	function updateEmail($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		// does action match expected
		if(count($action) == 1 && $user_id) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_usernames);

			$email = $this->getProperty("email", "value");

			// check if email exists
			if($this->userExists(array("email" => $email, "user_id" => $user_id))) {
				return array("status" => "USER_EXISTS");
				// message()->addMessage("Email already exists", array("type" => "error"));
				// return false;
			}


			$current_user = $this->getUser();
			$current_email = $current_user["email"];

			// email is sent
			if($email) {

				// email has not been set before
				if(!$current_email) {

					$sql = "INSERT INTO $this->db_usernames SET username = '$email', verified = 0, type = 'email', user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
//						message()->addMessage("Email added");
						return true;
					}
				}

				// email is changed
				else if($email != $current_email) {

					$sql = "UPDATE $this->db_usernames SET username = '$email', verified = 0 WHERE type = 'email' AND user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
//						message()->addMessage("Email updated");
						return true;
					}
				}

				// email is NOT changed
				else if($email == $current_email) {

//					message()->addMessage("Email unchanged");
					return true;
				}
			}

			// email is not sent
			else if(!$email && $current_email !== false) {

				$sql = "DELETE FROM $this->db_usernames WHERE type = 'email' AND user_id = $user_id";
//				print $sql."<br>";
				if($query->sql($sql)) {
//					message()->addMessage("Email deleted");
					return true;
				}
			}

		}

//		message()->addMessage("Could not update email", array("type" => "error"));
		return false;

	}

	// Update mobile from posted values
	// /janitor/admin/profile/updateMobile
	function updateMobile($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		// does action match expected
		if(count($action) == 1 && $user_id) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_usernames);

			$mobile = $this->getProperty("mobile", "value");

			// check if mobile exists
			if($this->userExists(array("mobile" => $mobile, "user_id" => $user_id))) {
				return array("status" => "USER_EXISTS");
				// message()->addMessage("Mobile already exists", array("type" => "error"));
				// return false;
			}


			$current_user = $this->getUser();
			$current_mobile = $current_user["mobile"];

			// mobile is sent
			if($mobile) {

				// mobile has not been set before
				if(!$current_mobile) {

					$sql = "INSERT INTO $this->db_usernames SET username = '$mobile', verified = 0, type = 'mobile', user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
//						message()->addMessage("Mobile added");
						return true;
					}
				}

				// mobile is changed
				else if($mobile != $current_mobile) {

					$sql = "UPDATE $this->db_usernames SET username = '$mobile', verified = 0 WHERE type = 'mobile' AND user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
//						message()->addMessage("Mobile updated");
						return true;
					}
				}

				// mobile is NOT changed
				else if($mobile == $current_mobile) {

//					message()->addMessage("Mobile unchanged");
					return true;
				}
			}

			// mobile is not sent
			else if(!$mobile && $current_mobile !== false) {

				$sql = "DELETE FROM $this->db_usernames WHERE type = 'mobile' AND user_id = $user_id";
//				print $sql."<br>";
				if($query->sql($sql)) {
//					message()->addMessage("Mobile deleted");
					return true;
				}
			}

		}

//		message()->addMessage("Could not update mobile", array("type" => "error"));
		return false;

	}




	// PASSWORD


	// set new password for current user
	// /janitor/admin/profile/setPassword
	function setPassword($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		if(count($action) == 1 && $user_id) {

			// does values validate
			if($this->validateList(array("password"))) {

				$query = new Query();

				// make sure type tables exist
				$query->checkDbExistance($this->db_passwords);

				$password = sha1($this->getProperty("password", "value"));

				// DELETE OLD PASSWORD
				$sql = "DELETE FROM ".$this->db_passwords." WHERE user_id = $user_id";
				if($query->sql($sql)) {

					// SAVE NEW PASSWORD
					$sql = "INSERT INTO ".$this->db_passwords." SET user_id = $user_id, password = '$password'";
					if($query->sql($sql)) {

//						message()->addMessage("Password saved");
						return true;
					}
				}
			}
		}

//		message()->addMessage("Password could not be saved", array("type" => "error"));
		return false;
	}




	// ADDRESSES


	// return addresses for current user
	// can return all addresses for current user, or a specific address
	// Adds country_name for stored country ISO value
	function getAddresses($_options = false) {

		$user_id = session()->value("user_id");
		$address_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "address_id"  : $address_id    = $_value; break;
				}
			}
		}

		$query = new Query();
		global $page;
		$countries = $page->countries();

		// get specific address
		if($address_id) {
			$sql = "SELECT * FROM ".$this->db_addresses." WHERE id = $address_id && user_id = $user_id";
//			print $sql;

			if($query->sql($sql)) {
				$result = $query->result(0);
				$result["country_name"] = $countries[arrayKeyValue($countries, "id", $result["country"])]["name"];
				return $result;
			}
		}

		// get alle addresses for user
		else {

			$sql = "SELECT * FROM ".$this->db_addresses." WHERE user_id = $user_id";
//			print $sql;

			if($query->sql($sql)) {
				$results = $query->results();
				foreach($results as $index => $result) {
					$results[$index]["country_name"] = $countries[arrayKeyValue($countries, "id", $result["country"])]["name"];
				}
				return $results;
			}

		}
	}

	// create a new address
	// /janitor/admin/profile/addAddress (values in POST)
	function addAddress($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");


		if(count($action) == 1 && $user_id && $this->validateList(array("address_label","address_name","address1","postal","city","country"))) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_addresses);

			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(address_label|address_name|att|address1|address2|city|postal|state|country)$/", $name)) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "INSERT INTO ".$this->db_addresses." SET user_id=$user_id," . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
//					message()->addMessage("Address created");
					return array("item_id" => $user_id);
				}
			}
		}

		return false;
	}

	// update an address
	// /janitor/admin/profile/updateAddress/#address_id# (values in POST)
	function updateAddress($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		if(count($action) == 2 && $user_id) {

			$query = new Query();
			$address_id = $action[1];

			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "UPDATE ".$this->db_addresses." SET ".implode(",", $values).",modified_at=CURRENT_TIMESTAMP WHERE id = $address_id AND user_id = $user_id";
//				print $sql;
			}

			if(!$values || $query->sql($sql)) {
//				message()->addMessage("Address updated");
				return true;
			}

		}

//		message()->addMessage("Address could not be updated", array("type" => "error"));
		return false;
	}

	// Delete address
	// /janitor/admin/user/deleteAddress/#address_id#
	function deleteAddress($action) {
		
		$user_id = session()->value("user_id");

		if(count($action) == 2 && $user_id) {
			$query = new Query();
			$address_id = $action[1];

			$sql = "DELETE FROM $this->db_addresses WHERE id = $address_id AND user_id = $user_id";
//			print $sql;
			if($query->sql($sql)) {
				message()->addMessage("Address deleted");
				return true;
			}

		}

		return false;
	}




	// NEWSLETTER

	// get newsletter info
	// get all newsletters (list of available newsletters)
	// get newsletters for user
	// get state of specific newsletter for specific user
	function getNewsletters($_options = false) {

		$newsletter = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "newsletter"     : $newsletter       = $_value; break;
				}
			}
		}

		$query = new Query();


		// check for specific newsletter for specific user
		if($newsletter) {
			$sql = "SELECT * FROM ".$this->db_newsletters." WHERE user_id = $user_id AND newsletter = '$newsletter'";
			if($query->sql($sql)) {
				return true;
			}
		}

		// get list of all newsletters
		else {
			$sql = "SELECT * FROM ".$this->db_newsletters." GROUP BY newsletter";
			if($query->sql($sql)) {
				return $query->results();
			}
		}

	}


	// updateNewsletter/#newsletter#/#state#
	function updateNewsletter($action) {

		$user_id = session()->value("user_id");

		// does values validate
		if(count($action) == 3 && $user_id) {

			$query = new Query();
			$newsletter = urldecode($action[1]);
			$state = $action[2];


			// make sure type tables exist
			$query->checkDbExistance($this->db_newsletters);

			if($state) {
				// already signed up
				$sql = "SELECT id FROM $this->db_newsletters WHERE user_id = $user_id AND newsletter = '$newsletter'";
				if(!$query->sql($sql)) {
					$sql = "INSERT INTO $this->db_newsletters SET user_id = $user_id, newsletter = '$newsletter'";
					$query->sql($sql);
				}
			}
			else {
				$sql = "DELETE FROM $this->db_newsletters WHERE user_id = $user_id AND newsletter = '$newsletter'";
				$query->sql($sql);
			}

			return true;
		}

		return false;
	}



}

?>