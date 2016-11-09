<?php

/**
* This file contains definitions
*
* @package Config Dummy file
*/
header("Content-type: text/html; charset=UTF-8");
error_reporting(E_ALL);

/**
* Site name
*/
define("SITE_UID", "JNT");
define("SITE_NAME", "Janitor");
define("SITE_URL", (isset($_SERVER["HTTPS"]) ? "https" : "http")."://".$_SERVER["SERVER_NAME"]);
define("SITE_EMAIL", "mail@domain.com");

/**
* Optional constants
*/
define("DEFAULT_PAGE_DESCRIPTION", "");
define("DEFAULT_PAGE_IMAGE", "");

define("DEFAULT_LANGUAGE_ISO", "EN");
define("DEFAULT_COUNTRY_ISO", "DK");
define("DEFAULT_CURRENCY_ISO", "DKK");


define("DEFAULT_PAGE_IMAGE", "/touchicon.png");

// ENABLE ITEMS MODEL
define("SITE_ITEMS", false);
define("SITE_SIGNUP", "/path_to_signup_page");

define("SITE_SUBSCRIPTIONS", true);
define("SITE_MEMBERS", true);

// Enable shop model
define("SITE_SHOP", true);
define("SHOP_ORDER_NOTIFIES", "email@domain.dk,anotheremail@domain.dk");

// Enable notifications (send collection email after N notifications)
define("SITE_COLLECT_NOTIFICATIONS", 50);

?>
