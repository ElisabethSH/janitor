<?php
/**
* Start session
*/

session_start();

// for safety - known bugs in old devices, should be tested
session_regenerate_id();

/**
* This class contains session value exchange functionality
*/
class Session {

	/**
	* Set/Get value - omit value to get - state value to set
	*
	* @param String $key Key
	* @param String $value Value to save - Optional
	*/
	function value($key, $value = false) {
		if($value !== false) {
			$_SESSION["SV"][$key] = $value;
		}
		else {
			if(!isset($_SESSION["SV"]) || !isset($_SESSION["SV"][$key])) {
				return false;
			}
			return $_SESSION["SV"][$key];
		}
	}

	/**
	* Reset value and all sub values - or plain reset all values if key is omitted
	*
	* @param String $key Key
	*/
	function reset($key = false) {
		if($key) {
			unset($_SESSION["SV"][$key]);
		}
		else {
			session_unset();
		}
	}

}

?>