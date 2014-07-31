<?php
/**
* This file contains settings for mailer connection
*
*
* @package Config
*/
define("ADMIN_MAIL", SITE_EMAIL);

$this->mail_connection(
	array(
		"host" => "###HOST###", 
		"port" => "###PORT###", 
		"username" => "###USERNAME###", 
		"password" => "###PASSWORD###", 
		"secure" => "ssl", 
		"smtpauth" => true,
		"from_email" => "###SITE_EMAIL###",
		"from_name" => "###SITE_NAME###"
	)
);

?>
