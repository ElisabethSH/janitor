<?php
/**
* This file contains validation for building a model functionality
*/
class Model extends HTML {

	public $data_entities;
	public $data_errors;

	/**
	* Construct reference to data object
	*/
	function __construct() {

		// TODO: get base elements from Item (published_at, status, etc.?)

		// Get posted values to make them available for models
		$this->getPostedEntities();
	}

	/**
	* Validation types
	* optional => validation will be ignored if value is empty
	*
	* text => var has to contain text (or number)
	* optional extra arguments:
	* 1: minimum length
	* 2: maximum length
	*
	* num => var has to be a number
	* optional extra arguments:
	* 1: minimum value
	* 2: maximum value
	*
	* file => checking $_FILES[$element]["name"] and $_FILES[$element]["error"]
	* no extra arguments:
	*
	* image => checking $_FILES[$element]["name"] and $_FILES[$element]["error"]
	* optional extra arguments:
	* 1: width
	* 2: height
	*
	* email => var has to be valid formatted email
	* optional extra arguments:
	* 1: database to check for other appearances of value
	* 2: separate existance error message
	*
	* pwr => (password repeat) var has to be equal to pw
	* required extra arguments:
	* 1: password
	*
	* arr => var has to be an array
	* optional extra arguments:
	* 1: minimum length
	*
	* unik => var has to be unik value
	* required extra arguments:
	* 1: database to check for other appearances of value
	* 2: database field to check for other appearances of value (optional, default = element name)
	*
	* date => var has to be valid date DD[.-/]MM[.-/][YY]YY
	* optional extra arguments:
	* 1: after timestamp
	* 2: before timestamp
	*
	* timestamp => var has to be valid timestamp DD[.-/]MM[.-/][YY]YY hh:mm
	* optional extra arguments:
	* 1: after timestamp
	* 2: before timestamp
	*/
	function addToModel($name, $_options = false) {

		// Defining default values

		$label = false;
		$value = false;
		$type = "string";
		$options = false;


		$id = false;

		// validation
		$required = false;
		$unique = false;
		$pattern = false;

		// string lengt, file count, number value
		$min = false;
		$max = false;

		// files
		$allowed_formats = "gif,jpg,png,mp4,mov,m4v,pdf";
		$allowed_proportions = false;
		$allowed_sizes = false;

		// html
		$allowed_tags = "h1,h2,h3,h4,h5,h6,p,code";

		// dates
		$is_before = false;
		$is_after = false;

		// passwords
		$must_match = false;


		// messages
		$hint_message = "Must be " . $type;
		$error_message = "*";


		// currency
		$currencies = false;
		$vatrate = false;


		// only relates to frontend output, not really meaningful to include on model level
		// $class = false;
		// $readonly = false;
		// $disabled = false;



		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "label"                 : $label                 = $_value; break;
					case "type"                  : $type                  = $_value; break;
					case "value"                 : $value                 = $_value; break;
					case "options"               : $options               = $_value; break;

					case "id"                    : $id                    = $_value; break;

					case "required"              : $required              = $_value; break;
					case "unique"                : $unique                = $_value; break;
					case "pattern"               : $pattern               = $_value; break;


					case "min"                   : $min                   = $_value; break;
					case "max"                   : $max                   = $_value; break;

					case "allowed_formats"       : $allowed_formats       = $_value; break;
					case "allowed_proportions"   : $allowed_proportions   = $_value; break;
					case "allowed_sizes"         : $allowed_sizes         = $_value; break;

					case "allowed_tags"          : $allowed_tags          = $_value; break;

					case "is_before"             : $is_before             = $_value; break;
					case "is_after"              : $is_after              = $_value; break;

					case "must_match"            : $must_match            = $_value; break;

					case "error_message"         : $error_message         = $_value; break;
					case "hint_message"          : $hint_message          = $_value; break;

					case "currencies"            : $currencies            = $_value; break;
					case "vatrate"               : $vatrate               = $_value; break;

				}
			}
		}


		$this->data_entities[$name]["label"] = $label;
		$this->data_entities[$name]["type"] = $type;
		$this->data_entities[$name]["value"] = $value;
		$this->data_entities[$name]["options"] = $options;

//		print "ADD TO MODEL:" . $this->data_entities[$name]["value"];

		$this->data_entities[$name]["id"] = $id;

		$this->data_entities[$name]["required"] = $required;
		$this->data_entities[$name]["unique"] = $unique;
		$this->data_entities[$name]["pattern"] = $pattern;

		$this->data_entities[$name]["min"] = $min;
		$this->data_entities[$name]["max"] = $max;

		$this->data_entities[$name]["allowed_formats"] = $allowed_formats;
		$this->data_entities[$name]["allowed_proportions"] = $allowed_proportions;
		$this->data_entities[$name]["allowed_sizes"] = $allowed_sizes;

		$this->data_entities[$name]["allowed_tags"] = $allowed_tags;

		$this->data_entities[$name]["is_before"] = $is_before;
		$this->data_entities[$name]["is_after"] = $is_after;

		$this->data_entities[$name]["must_match"] = $must_match;

		$this->data_entities[$name]["error_message"] = $error_message;
		$this->data_entities[$name]["hint_message"] = $hint_message;


		$this->data_entities[$name]["currencies"] = $currencies;
		$this->data_entities[$name]["vatrate"] = $vatrate;


		// $this->setValidationIndication($element);
	}

	function getModel() {
		return $this->data_entities;
	}

	function getModelNames() {
		$names = false;
		foreach($this->data_entities as $name) {
			$names[] = $name;
		}
		return $names;
	}

	/**
	* Getting all vars defined through the varnames array
	* Inserts the values of variables defined in vars-array
	*
	* @param array $varnames Array of variable names
	* @return array Vars array
	* @uses getVar
	*/
	function getPostedEntities() {
		if(count($this->data_entities)) {
			foreach($this->data_entities as $name => $entity) {

				// special case with files
				if($this->data_entities[$name]["type"] == "files") {

					// indicate value is present for file upload
					if(isset($_FILES[$name])) {
//						$this->data_entities[$name]["value"] = true;
						$this->data_entities[$name]["value"] = $_FILES[$name]["tmp_name"];
					}
				}

				// regular variable
				else {
					$value = getPost($name);
					if($value !== false) {
//						print $name."=".$value."\n";
						$this->data_entities[$name]["value"] = $value;
					}
					// else {
					// 	print "should be false:" . $name . "," . ($this->data_entities[$name]["value"] === false) . "\n";
					// }
				}
			}
		}
	}
	
	function getEntityProperty($name, $property) {
		return isset($this->data_entities[$name][$property]) ? $this->data_entities[$name][$property] : "";
	}

	/**
	* Execute defined validation rules for all elements (rules defined in data object)
	*
	* @param string Optional elements to skip can be passed as parameters
	* @return bool
	*/
	function validateAll($execpt = false, $item_id = false) {
		$this->data_errors = array();

//		print "<p>";
//		print_r($this->data_entities);
		if(count($this->data_entities)) {

			foreach($this->data_entities as $name => $entity) {

				if(!$execpt || array_search($name, $execpt) === false) {
//					print "validationg name: $name<br>";

					if(!$this->validate($name, $item_id)) {
//						print "error:<br>";
						$this->data_errors[$name] = true;
					}
				}
			}
		}
//		print "</p>";

		// prepare values to be returned to screen if errors exist
		if(count($this->data_errors)) {
			foreach($this->data_entities as $name => $entity) {
				if($this->data_entities[$name]["value"] !== false) {
					$this->data_entities[$name]["value"] = prepareForHTML($entity["value"]);
				}
			}
			return false;
		}
		else {
			return true;
		}
	}

	/**
	* Execute defined validation rules for listed elements (rules defined in data object)
	*
	* @param string Elements to validate
	* @return bool
	*/
	function validateList($list = false, $item_id = false) {
		$this->data_errors = array();

//		print_r($this->data_entities);
		foreach($list as $name) {
			if(isset($this->data_entities[$name])) {
				if(!$this->validate($name, $item_id)) {
					$this->data_errors[$name] = true;
				}
			}
		}

		// prepare values to be returned to screen if errors exist
		if(count($this->data_errors)) {
			foreach($this->data_entities as $name => $entity) {
				if($this->data_entities[$name]["value"] !== false) {
					$this->data_entities[$name]["value"] = prepareForHTML($entity["value"]);
				}
			}
			return false;
		}
		else {
			return true;
		}
	}

	/**
	* Execute validation rule (rules defined in data object)
	*
	* @param String $Element Element to validate
	* @param Integer $item_id Optional item_id to check aganist (in case of uniqueness)
	* @return bool
	*
	* TODO: some validation rules are not done!
	*/
	function validate($name, $item_id = false) {
//		print "validate:".$name."\n";

		// check uniqueness
		if($this->data_entities[$name]["unique"]) {
			if(!$this->isUnique($name, $item_id)) {
				$error_message = $this->data_entities[$name]["error_message"];
				$error_message = $error_message && $error_message != "*" ? $error_message : "An unknown validation error occured (uniqueness)";
				message()->addMessage($error_message, array("type" => "error"));
				return false;
			}
		}

//		print_r($this->data_entities[$name]);

		// is optional and empty?
		// if value is not empty - it needs to be validated even for optional entities
		if(!$this->data_entities[$name]["required"] && $this->data_entities[$name]["value"] == "") {
			return true;
		}

		// string or text field
		if($this->data_entities[$name]["type"] == "string" || $this->data_entities[$name]["type"] == "text"  || $this->data_entities[$name]["type"] == "html" || $this->data_entities[$name]["type"] == "select") {
			if($this->isString($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "files") {
			if($this->isFiles($name)) {
				return true;
			}
		}
		// else if($this->data_entities[$name]["type"] == "images") {
		// 	if($this->isImages($name)) {
		// 		return true;
		// 	}
		// }
		else if($this->data_entities[$name]["type"] == "number") {
			if($this->isNumber($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "integer") {
			if($this->isInteger($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "email") {
			if($this->isEmail($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "tel") {
			if($this->isTelephone($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "password") {
			if(isset($this->data_entities[$name]["compare_to"])) {
				if($this->comparePassword($name, $this->data_entities[$name]["compare_to"])) {
					return true;
				}
			}
			else if($this->isString($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "array") {
			if($this->isArray($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "date" || $this->data_entities[$name]["type"] == "datetime") {
			if($this->isDate($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "timestamp") {
			if($this->isTimestamp($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "prices") {
			if($this->isPrices($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "tags") {
			if($this->isTags($name)) {
				return true;
			}
		}

		// either type was not found or validation failed
		$error_message = $this->data_entities[$name]["error_message"];
		$error_message = $error_message && $error_message != "*" ? $error_message : "An unknown validation error occured";
		message()->addMessage($error_message, array("type" => "error"));
		return false;
	}


	/**
	* Check for other existance of value
	*
	* @param string $name Element identifier
	* @param Integer $item_id current item_id
	* @return bool
	*/
	function isUnique($name, $item_id) {
		$entity = $this->data_entities[$name];

		$query = new Query();
		$sql = "SELECT id FROM ".$entity["unique"]." WHERE $name = '".$entity["value"]."'".($item_id ? " AND item_id != ".$item_id : "");
		if($item_id) {
			
		}
		// does other value exist
		if($query->sql($sql)) {
			$this->data_entities[$name]["error"] = true;
			return false;
		}

		$this->data_entities[$name]["error"] = false;
		return true;
	}

	/**
	* Is file valid?
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isFiles($name) {
		// print "isFiles:<br>";
		// print "FILES:\n";
		// print_r($_FILES);

		$entity = $this->data_entities[$name];

		$value = $entity["value"];

		$min = $entity["min"];
		$max = $entity["max"];

		$formats = $entity["allowed_formats"];
		$proportions = $entity["allowed_proportions"];
		$sizes = $entity["allowed_sizes"];

		$uploads = $this->identifyUploads($name);

		// print "uploads:\n";
		// print_r($uploads);

		if(
			(!$min || count($value) >= $min) && 
			(!$max || count($value) <= $max) &&
			(!$proportions || $this->proportionTest($uploads, $proportions)) &&
			(!$sizes || $this->sizeTest($uploads, $sizes)) &&
			(!$formats || $this->formatTest($uploads, $formats))
		) {
			$this->data_entities[$name]["error"] = false;
			return true;
		}
		else {
			$this->data_entities[$name]["error"] = true;
			return false;
		}

	}

	// isFiles helper
	// test if proportions are valid
	function proportionTest($uploads, $proportions) {

		$proportion_array = explode(",", $proportions);
		foreach($uploads as $upload) {
			if(!isset($upload["proportion"]) || array_search($upload["proportion"], $proportion_array) === false) {
//				print "bad proportion";
				return false;
			}
		}
		return true;
	}

	// isFiles helper
	// test if sizes are valid
	function sizeTest($uploads, $sizes) {

		$size_array = explode(",", $sizes);
		foreach($uploads as $upload) {
			if(!isset($upload["width"]) || !isset($upload["height"]) || array_search($upload["width"]."x".$upload["height"], $size_array) === false) {
//				print "bad size";
				return false;
			}
		}
		return true;
	}

	// isFiles helper
	// test if formats are valid
	function formatTest($uploads, $formats) {

		$format_array = explode(",", $formats);
		foreach($uploads as $upload) {
			if(array_search($upload["format"], $format_array) === false) {
//				print "bad format";
				return false;
			}
		}
		return true;
	}

	// isFiles helper
	// upload identification helper
	// supports identification of:
	// - image
	// - video
	// - audio
	function identifyUploads($name) {

		$uploads = array();

//		print "input_name:" . $name;

		if(isset($_FILES[$name])) {
//			print_r($_FILES[$name]);

//			if($_FILES[$name]["name"])
			foreach($_FILES[$name]["name"] as $index => $value) {
				if(!$_FILES[$name]["error"][$index] && file_exists($_FILES[$name]["tmp_name"][$index])) {

					$upload = array();
					$upload["name"] = $value;

					$temp_file = $_FILES[$name]["tmp_name"][$index];
					$temp_type = $_FILES[$name]["type"][$index];
					$temp_extension = mimetypeToExtension($temp_type);


					// video upload (mp4)
					if(preg_match("/video/", $temp_type)) {

						include_once("class/system/video.class.php");
						$Video = new Video();

						// check if we can get relevant info about movie
						$info = $Video->info($temp_file);
						if($info) {

							// TODO: add extension to Video Class
							// TODO: add better bitrate detection to Video Class
							// TODO: add duration
							// $upload["bitrate"] = $info["bitrate"];
							$upload["type"] = "movie";
							$upload["filesize"] = filesize($temp_file);
							$upload["format"] = $temp_extension;
							$upload["width"] = $info["width"];
							$upload["height"] = $info["height"];
							$upload["proportion"] = round($upload["width"] / $upload["height"], 2);
							$uploads[] = $upload;
						}

					}

					// audio upload (mp3)
					else if(preg_match("/audio/", $temp_type)) {

						include_once("class/system/audio.class.php");
						$Audio = new Audio();

 						// check if we can get relevant info about audio
						$info = $Audio->info($temp_file);
						if($info) {
//							print_r($info);

							// TODO: add bitrate detection
							// TODO: add duration
							// $upload["bitrate"] = $info["bitrate"];
							$upload["type"] = "audio";
							$upload["filesize"] = filesize($temp_file);
							$upload["format"] = $temp_extension;
							$uploads[] = $upload;
						}

					}

					// image upload (gif/png/jpg)
					else if(preg_match("/image/", $temp_type)) {

						$image = new Imagick($temp_file);

 						// check if we can get relevant info about image
						$info = $image->getImageFormat();
						if($info) {

							$upload["type"] = "image";
							$upload["filesize"] = filesize($temp_file);
							$upload["format"] = $temp_extension;
							$upload["width"] = $image->getImageWidth();
							$upload["height"] = $image->getImageHeight();
							$upload["proportion"] = round($upload["width"] / $upload["height"], 2);
							$uploads[] = $upload;

						}
					}

					// application upload (pdf/zip)
					else if(preg_match("/application/", $temp_type)) {

						// PDF
						if($temp_extension == "pdf") {

							$upload["type"] = "pdf";
							$upload["filesize"] = filesize($temp_file);
							$upload["format"] = $temp_extension;
							$uploads[] = $upload;

						}
						// ZIP
						else if($temp_extension == "zip") {

							$upload["type"] = "zip";
							$upload["filesize"] = filesize($temp_file);
							$upload["format"] = $temp_extension;
							$uploads[] = $upload;

						}
					}
				}
			}

		}

		return $uploads;

	}



	/**
	* Is string string?
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isString($name) {
		$entity = $this->data_entities[$name];

		$value = $entity["value"];

		$min_length = $entity["min"];
		$max_length = $entity["max"];
		$pattern = $entity["pattern"];

		if(($value || $value === "0") && is_string($value) && 
			(!$min_length || strlen($value) >= $min_length) && 
			(!$max_length || strlen($value) <= $max_length) &&
			(!$pattern || preg_match("/^".$pattern."$/", $value))
		) {
			$this->data_entities[$name]["error"] = false;
			return true;
		}
		else {
			$this->data_entities[$name]["error"] = true;
			return false;
		}
	}

	/**
	* Is string numeric?
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isNumber($name) {
		$entity = $this->data_entities[$name];

		$value = $entity["value"];

		$min = $entity["min"];
		$max = $entity["max"];
		$pattern = $entity["pattern"];

		if(($value || $value == 0) && !($value%1) && 
			(!$min || $value >= $min) && 
			(!$max || $value <= $max) &&
			(!$pattern || preg_match("/^".$pattern."$/", $value))
		) {
			$this->data_entities[$name]["error"] = false;
			return true;
		}
		else {
//			$this->data_entities[$name]["error_message"] = "$name value: $value;";
			$this->data_entities[$name]["error"] = true;
			return false;
		}
	}

	/**
	* Is string integer?
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isInteger($name) {
		$entity = $this->data_entities[$name];

		$value = $entity["value"];

		$min = $entity["min"];
		$max = $entity["max"];
		$pattern = $entity["pattern"];

		if(($value || $value == 0) && !($value%1) && 
			(!$min || $value >= $min) && 
			(!$max || $value <= $max) &&
			(!$pattern || preg_match("/^".$pattern."$/", $value))
		) {
			$this->data_entities[$name]["error"] = false;
			return true;
		}
		else {
//			$this->data_entities[$name]["error_message"] = "$name value: $value;";
			$this->data_entities[$name]["error"] = true;
			return false;
		}
	}

	/**
	* Check if email is correctly formatted
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function isEmail($name) {
		$entity = $this->data_entities[$name];

		$value = $entity["value"];

		$pattern = stringOr($entity["pattern"], "[\w\.\-\_]+@[\w-\.]+\.\w{2,4}");

		if($value && is_string($value) && 
			(!$pattern || preg_match("/^".$pattern."$/", $value))
		) {
			$this->data_entities[$name]["error"] = false;
			return true;
		}
		else {
			$this->data_entities[$name]["error"] = true;
			return false;
		}
	}


	/**
	* Check if email is correctly formatted
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function isTelephone($name) {
		$entity = $this->data_entities[$name];

		$value = $entity["value"];

		$pattern = stringOr($entity["pattern"], "([\+0-9\-\.\s\(\)]){5,18}");

		if($value && is_string($value) && 
			(!$pattern || preg_match("/^".$pattern."$/", $value))
		) {
			$this->data_entities[$name]["error"] = false;
			return true;
		}
		else {
			$this->data_entities[$name]["error"] = true;
			return false;
		}
	}



	/**
	* Compare two passwords (to check if password and repeat password are identical)
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	* TODO: Faulty password validation
	*/
	function comparePassword($name) {

		$entity = $this->data_entities[$name];

		$repeated_password = $this->obj->vars[$element];
		$password = $this->obj->vars[$this->getRuleDetails($rule, 0)];
		if($repeated_password == $password) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	* Check if array is array
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	* TODO: Faulty Array validation
	*/
	function isArray($name) {
		$entity = $this->data_entities[$name];

		$array = $this->obj->vars[$element];
		$min_length = $this->getRuleDetails($rule, 0);
		if(is_array($array) && count(cleanArray($array)) && (!$min_length || count(cleanArray($array)) >= $min_length)) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	* Check if date is entered correctly
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*
	* TODO: Faulty date validation
	*/
	function isDate($name) {
		$entity = $this->data_entities[$name];


		return true;

		$after = $this->getRuleDetails($rule, 0);
		$before = $this->getRuleDetails($rule, 1);

		$this->obj->vars[$element] = preg_replace('/[\/\.-]/', '-', $this->obj->vars[$element]);
		$string = $this->obj->vars[$element];
		$date = explode('-', $string);
		if(count($date) == 3) {
			$timestamp = mktime(0,0,0,$date[1], $date[0], $date[2]);

			if(checkdate($date[1], $date[0], $date[2]) && (!$after || $timestamp > $after) && (!$before || $timestamp < $before)) {
				return true;
			}
		}
		return false;
	}

	/**
	* Check if GeoLocation is entered correctly
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*
	* TODO: faulty geolocation validation - maybe it should be deleted
	*/
	function isGeoLocation($name) {
		$entity = $this->data_entities[$name];


		return true;

	}


	/**
	* Check if timestamp is entered correctly
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	* TODO: Faulty timestamp validation
	*/
	function isTimestamp($name) {
		$entity = $this->data_entities[$name];

		$after = $this->getRuleDetails($rule, 0);
		$before = $this->getRuleDetails($rule, 1);

		list($date, $time) = explode(" ", $this->obj->vars[$element]);

		$date = preg_replace('/[\/\.-]/', '-', $date);
		$string = $this->obj->vars[$element];

		$date = explode('-', $date);
		$time = explode(':', $time);

		if(count($date) == 3 && count($time) == 2) {
			$timestamp = mktime($time[0], $time[1], 0, $date[1], $date[0], $date[2]);

			if(checkdate($date[1], $date[0], $date[2]) && (!$after || $timestamp > $after) && (!$before || $timestamp < $before)) {
				return true;
			}
		}
		return false;
	}

	// TODO: Faulty tags validation
	function isTags($name) {
		$entity = $this->data_entities[$name];

		return true;
	}

	// TODO: Faulty price validation
	function isPrices($name) {
		$entity = $this->data_entities[$name];

		return true;
	}

}

?>