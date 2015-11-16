<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");

// script needs to be able to handle following extensions:

// IMAGE
// jpg
// png
// gif

// VIDEO
// mp4
// ogv
// mov
// 3gp

// AUDIO
// mp3
// ogg


// error handling
function conversionFailed($reason) {
	global $page;

	global $id;
	global $variant;
	global $request_type;
	global $format;

	global $width;
	global $height;

	global $bitrate;

	// get into the detail to make debugging as easy as possible
	$reason .= "\n".$_SERVER["REQUEST_URI"];
	$reason .= "\n\nRequest type: ".$request_type;
	$reason .= "\nFormat: ".$format;

	if($request_type == "audios") {
		if(!$bitrate) {
			$reason .= "\nBitrate: MISSING";
		}
		else {
			$reason .= "\nBitrate: ".$bitrate;
		}
	}
	else {
		if(!$width && !$height) {
			$reason .= "\nWidth and Height: MISSING";
		}
		else if($width) {
			$reason .= "\nWidth: ".$width;
		}
		else if($height) {
			$reason .= "\nHeight: ".$height;
		}
	}

	// specify reason
	if($id && !file_exists(PRIVATE_FILE_PATH."/$id")) {
		$reason .= "\n\nID does not exist: ".$id;
	}
	else if($id && $variant && !file_exists(PRIVATE_FILE_PATH."/$id$variant")) {
		$reason .= "\n\nVariant does not exist: ".$variant;
	}
	
	// show what we got
	if($id && file_exists(PRIVATE_FILE_PATH."/$id")) {
		$fs = new FileSystem();
		$files = $fs->files(PRIVATE_FILE_PATH."/$id");
		$reason .= "\n\nPrivate files:\n";
		foreach($files as $file) {
			$reason .= str_replace(PRIVATE_FILE_PATH, "", $file)."\n";
		}
		$files = $fs->files(PUBLIC_FILE_PATH."/$id");
		$reason .= "\nPublic files:\n";
		foreach($files as $file) {
			$reason .= str_replace(PUBLIC_FILE_PATH, "", $file)."\n";
		}
	}

	$page->mail(array(
		"subject" => "Autoconversion failed ($request_type)", 
		"message" => $reason,
		"template" => "system"
	));

	// TODO: implement fallback for audio and video
	// TODO: implement constraints to avoid media generation abuse

	// return missing image if it exists (and request is for image)
	//	print file_exists(PRIVATE_FILE_PATH."/0/missing/png")." && ". $width ."&&". $height;
	if($request_type == "images" && file_exists(PRIVATE_FILE_PATH."/0/missing/png") && ($width || $height)) {

//		header("Location: /images/0/missing/".$width."x".$height.".png");

	}

	// dangerous to return HTML - receiving JS will expect media, not HTML
	else {
//		header("Location: /janitor/admin/404");
	}
	exit();
}


$id = false;
$variant = "";

// Get conversion details
// parse file info from path

// IMAGE WITH VARIANT
// /images/{id}/{variant}/{width}x.{format}
// /images/{id}/{variant}/x{height}.{format}
// VIDEO WITH VARIANT
// /videos/{id}/{variant}/{width}x{height}.{format}
if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<variant>[^\/]+)\/(?P<width>\d*)x(?P<height>\d*)\.(?P<format>\w{3})/i", $_SERVER["REQUEST_URI"], $matches)) {
	$request_type = $matches["request_type"];

	if($request_type == "images" || $request_type == "videos") {

		$id = $matches["id"];
		$width = $matches["width"];
		$height = $matches["height"];
		$format = $matches["format"];
		$variant = "/".$matches["variant"];

		//	print "request:" . $request_type . " id:" . $id . " width:" . $width . " height:" . $height ." format:". $format ." variant:".$variant."<br>";


		// max size detection (2000x2000 or similar amount of pixels)
		$max_pixels = 4000000;
		
	}
}
// IMAGE
// /images/{id}/{width}x.{format}
// /images/{id}/x{height}.{format}
// VIDEO
// /videos/{id}/{width}x{height}.{format}
else if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<width>\d*)x(?P<height>\d*)\.(?P<format>\w{3})/i", $_SERVER["REQUEST_URI"], $matches)) {
	$request_type = $matches["request_type"];

	if($request_type == "images" || $request_type == "videos") {

		$id = $matches["id"];
		$width = $matches["width"];
		$height = $matches["height"];
		$format = $matches["format"];

		//	print "request:" . $request_type . " id:" . $id . " width:" . $width . " height:" . $height ." format:". $format ." variant:".$variant."<br>";


		// max size detection (2000x2000 or similar amount of pixels)
		$max_pixels = 4000000;

	}
}
// AUDIO
// IMAGE WITH VARIANT
// /audios/{id}/{variant}/{bitrate}.{format}
if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<variant>[^\/]+)\/(?P<bitrate>\d+)\.(?P<format>\w{3})/i", $_SERVER["REQUEST_URI"], $matches)) {
	$request_type = $matches["request_type"];

	if($request_type == "audios") {

		$id = $matches["id"];
		$bitrate = $matches["bitrate"];
		$format = $matches["format"];
		$variant = "/".$matches["variant"];

		//	print "request:" . $request_type . " id:" . $id . " bitrate:" . $bitrate ." format:". $format ." variant:".$variant."<br>";
	}
}
// /audios/{id}/{bitrate}.{format}
else if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<bitrate>\d+)\.(?P<format>\w{3})/i", $_SERVER["REQUEST_URI"], $matches)) {
	$request_type = $matches["request_type"];

	if($request_type == "audios") {

		$id = $matches["id"];
		$bitrate = $matches["bitrate"];
		$format = $matches["format"];

		// TODO: implement bitrate control in audio class first
		// $max_bitrate = 320;

		//	print "request:" . $request_type . " id:" . $id . " bitrate:" . $bitrate ." format:". $format ." variant:".$variant."<br>";
	}
}

// ERROR - MISSING ID - STOP IMMEDIATELY
// id can be 0, but not false
if($id === false || !file_exists(PRIVATE_FILE_PATH."/$id$variant")) {
//	print "missing info";
	conversionFailed("Missing or bad path info - request ignored");
}



// images
if($request_type == "images" && ($width || $height) && ($format == "jpg" || $format == "png" || $format == "gif")) {
	include_once("classes/system/image.class.php");

	$Image = new Image();

	// check for sources

	// jpg, and source is available
	if($format == "jpg" && file_exists(PRIVATE_FILE_PATH."/$id$variant/jpg")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/jpg";
	}
	// png, and source is available
	else if($format == "png" && file_exists(PRIVATE_FILE_PATH."/$id$variant/png")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/png";
	}
	// gif, and source is available
	else if($format == "png" && file_exists(PRIVATE_FILE_PATH."/$id$variant/gif")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/gif";
	}
	// jpg available
	else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/jpg")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/jpg";
	}
	// png available
	else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/png")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/png";
	}
	// gif available
	else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/gif")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/gif";
	}
	// no valid source available
	else {
		conversionFailed("no valid source available");
	}

	$output_file = PUBLIC_FILE_PATH."/".$id.$variant."/".$width."x".$height.".".$format;

//	print $input_file . ":" . $output_file . "<br>";

	// scale image (will autoconvert)
	if($Image->convert($input_file, $output_file, array("compression" => 93, "allow_cropping" => true, "width" => $width, "height" => $height, "format" => $format, "max_pixels" => $max_pixels))) {

		// collect log autoconvertion for bundled notification
		$page->collectNotification($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");

		// redirect to new image
		header("Location: /".$request_type."/".$id.$variant."/".$width."x".$height.".".$format);
		exit();

	}
	else {
		conversionFailed("Image->convert failed");
	}


}

// video
else if($request_type == "videos" && ($width || $height) && ($format == "mp4" || $format == "ogv" || $format == "mov" || $format == "3gp")) {
		include_once("classes/system/video.class.php");

		$Video = new Video();

		// check for sources

		// mov, and source is available
		if($format == "mov" && file_exists(PRIVATE_FILE_PATH."/$id$variant/mov")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/mov";
		}
		// mp4, and source is available
		else if($format == "mp4" && file_exists(PRIVATE_FILE_PATH."/$id$variant/mp4")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/mp4";
		}
		// ogv, and source is available
		else if($format == "ogv" && file_exists(PRIVATE_FILE_PATH."/$id$variant/ogv")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/ogv";
		}
		// 3gp, and source is available
		else if($format == "3gp" && file_exists(PRIVATE_FILE_PATH."/$id$variant/3gp")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/3gp";
		}
		// mov available
		else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/mov")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/mov";
		}
		// mp4 available
		else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/mp4")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/mp4";
		}
		// ogv available
		else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/ogv")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/ogv";
		}
		// 3gp available
		else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/3gp")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/3gp";
		}
		// no valid source available
		else {
			conversionFailed("no valid source available");
		}

		$output_file = PUBLIC_FILE_PATH."/".$id.$variant."/".$width."x".$height.".".$format;


		// scale image (will autoconvert)
		if($Video->convert($input_file, $output_file, array("allow_cropping" => true, "width" => $width, "height" => $height, "format" => $format, "max_pixels" => $max_pixels))) {

			// collect log autoconvertion for bundled notification
			$page->collectNotification($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");

			// redirect to new image
			header("Location: /".$request_type."/".$id.$variant."/".$width."x".$height.".".$format);
			exit();

		}
		else {
			conversionFailed("Video->convert failed");
		}

}

// audio
else if($request_type == "audios" && $bitrate && ($format == "mp3" || $format == "ogg")) {
	include_once("classes/system/audio.class.php");

	$Audio = new Audio();

	// mp3, and source is available
	if($format == "mp3" && file_exists(PRIVATE_FILE_PATH."/$id$variant/mp3")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/mp3";
	}
	// ogg, and source is available
	else if($format == "ogg" && file_exists(PRIVATE_FILE_PATH."/$id$variant/ogg")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/ogg";
	}
	else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/mp3")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/mp3";
	}
	else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/ogg")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/ogg";
	}
	else {
		conversionFailed("no valid source available");
	}

	$output_file = PUBLIC_FILE_PATH."/".$id.$variant."/".$bitrate.".".$format;


	// scale image (will autoconvert)
	if($Audio->convert($input_file, $output_file, array("bitrate" => $bitrate, "format" => $format))) {
		// TODO: implement bit rate control in audio class first, "max_bitrate" => $max_bitrate
		// redirect to new image

		// collect log autoconvertion for bundled notification
		$page->collectNotification($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");

		header("Location: /".$request_type."/".$id.$variant."/".$bitrate.".".$format);
		exit();

	}
	else {
		conversionFailed("Audio->convert failed");
	}

}

// something weren't as expected
conversionFailed("Missing or bad path info - request not completed");

?>
