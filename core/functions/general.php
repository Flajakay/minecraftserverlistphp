<?php

use xPaw\MinecraftPing;
use xPaw\MinecraftPingException;

function send_mail($to, $from, $title, $message) {

	$headers = "From: " . strip_tags($from) . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

	mail($to, $title, $message, $headers);
}

function sendmail($to, $from, $title, $message) {
	global $settings;
	require 'PHPMailer_new/PHPMailer.php';
	require 'PHPMailer_new/SMTP.php';
	require 'PHPMailer_new/Exception.php';
	if(!empty($settings->smtphost) && !empty($settings->smtpport) && !empty($settings->smtpuser) && !empty($settings->smtppass)) {
		$mail = new PHPMailer\PHPMailer\PHPMailer();
		$mail->isSMTP();
		$mail->SMTPDebug = 0;
		$mail->SMTPSecure = $settings->smtpsecure;
		$mail->SMTPAuth = true;
		$mail->isHTML(true);

		$mail->Host = $settings->smtphost;
		$mail->Port = $settings->smtpport;
		$mail->Username = $settings->smtpuser;
		$mail->Password = $settings->smtppass;
		
		$mail->setFrom($settings->contact_email, $settings->title);
		$mail->addReplyTo($settings->contact_email, $settings->title);
		$mail->addAddress($to);
		$mail->Subject = $title;
		$mail->Body = $message;
		if (!$mail->send()) {
			send_mail($to, $settings->contact_email, $title, $message);
		}

	} else {
		send_mail($to, $settings->contact_email, $title, $message);
	}
	
}

function redirect($new_page = 'index') {
	global $settings;

	header('Location: ' . $settings->url . $new_page);
	die();
}

function Votifier($public_key, $ip, $port, $username) {
	$public_key = wordwrap($public_key, 65, "\n", true);

$public_key = <<<EOF
-----BEGIN PUBLIC KEY-----
$public_key
-----END PUBLIC KEY-----
EOF;

	/* Get user IP */
	$address = $_SERVER['REMOTE_ADDR'];

	/* set voting time */
	$timeStamp = time();

	/* create basic required string for Votifier */
	$string = "VOTE\nMinecraftServersList\n$username\n$address\n$timeStamp\n";

	/* fill blanks to make packet lenght 256 */
	$leftover = (256 - strlen($string)) / 2;
	while ($leftover > 0) {
	$string.= "\x0";
	$leftover--;
	}

	/* encrypt string before send */
	openssl_public_encrypt($string,$crypted,$public_key);

	/* try to connect to server */
	@$socket = fsockopen($ip, $port, $errno, $errstr, 3);
	if ($socket) {
		fwrite($socket, $crypted); //on success send encrypted packet to server
		return true; 
	}
	else return false; //on fail return false
}


function bind_object($stmt, &$row) {
	$row = new StdClass;
	$md = $stmt->result_metadata();

	$params = array();
	while($field = $md->fetch_field()) {
		$params[] = &$row->{$field->name};
	}

	call_user_func_array(array($stmt, 'bind_result'), $params);

	$stmt->fetch();

	if($row->{key($row)} == '' || $row->{key($row)} == '0') $row = null;
}

function trim_value(&$value) {
	$value = trim($value);
}

function filter_banned_words($value) {
	global $settings;

	$words = explode(',', $settings->banned_words);
	array_walk($words, 'trim_value');

	foreach($words as $word) {
		$value = str_replace($word, str_repeat('*', strlen($word)), $value);
	}

	return $value;
}

function country_check($type, $value) {
	// Type 0: Verify whether the country exists or not
	// Type 1: Return the country list
	// Type 2: Key to Value
	$list = array("AF" => "Afghanistan", "AL" => "Albania", "DZ" => "Algeria", "AS" => "American Samoa", "AD" => "Andorra", "AO" => "Angola", "AI" => "Anguilla", "AQ" => "Antarctica", "AG" => "Antigua and Barbuda", "AR" => "Argentina", "AM" => "Armenia", "AW" => "Aruba", "AU" => "Australia", "AT" => "Austria", "AZ" => "Azerbaijan", "AX" => "Åland Islands", "BS" => "Bahamas", "BH" => "Bahrain", "BD" => "Bangladesh", "BB" => "Barbados", "BY" => "Belarus", "BE" => "Belgium", "BZ" => "Belize", "BJ" => "Benin", "BM" => "Bermuda", "BT" => "Bhutan", "BO" => "Bolivia", "BA" => "Bosnia and Herzegovina", "BW" => "Botswana", "BV" => "Bouvet Island", "BR" => "Brazil", "BQ" => "British Antarctic Territory", "IO" => "British Indian Ocean Territory", "VG" => "British Virgin Islands", "BN" => "Brunei", "BG" => "Bulgaria", "BF" => "Burkina Faso", "BI" => "Burundi", "KH" => "Cambodia", "CM" => "Cameroon", "CA" => "Canada", "CV" => "Cape Verde", "KY" => "Cayman Islands", "CF" => "Central African Republic", "TD" => "Chad", "CL" => "Chile", "CN" => "China", "CX" => "Christmas Island", "CC" => "Cocos [Keeling] Islands", "CO" => "Colombia", "KM" => "Comoros", "CG" => "Congo - Brazzaville", "CD" => "Congo - Kinshasa", "CK" => "Cook Islands", "CR" => "Costa Rica", "HR" => "Croatia", "CU" => "Cuba", "CY" => "Cyprus", "CZ" => "Czech Republic", "CI" => "Côte d’Ivoire", "DK" => "Denmark", "DJ" => "Djibouti", "DM" => "Dominica", "DO" => "Dominican Republic", "EC" => "Ecuador", "EG" => "Egypt", "SV" => "El Salvador", "GQ" => "Equatorial Guinea", "ER" => "Eritrea", "EE" => "Estonia", "ET" => "Ethiopia", "FK" => "Falkland Islands", "FO" => "Faroe Islands", "FJ" => "Fiji", "FI" => "Finland", "FR" => "France", "GF" => "French Guiana", "PF" => "French Polynesia", "TF" => "French Southern Territories", "GA" => "Gabon", "GM" => "Gambia", "GE" => "Georgia", "DE" => "Germany", "GH" => "Ghana", "GI" => "Gibraltar", "GR" => "Greece", "GL" => "Greenland", "GD" => "Grenada", "GP" => "Guadeloupe", "GU" => "Guam", "GT" => "Guatemala", "GN" => "Guinea", "GW" => "Guinea-Bissau", "GY" => "Guyana", "HT" => "Haiti", "HM" => "Heard Island and McDonald Islands", "HN" => "Honduras", "HK" => "Hong Kong SAR China", "HU" => "Hungary", "IS" => "Iceland", "IN" => "India", "ID" => "Indonesia", "IR" => "Iran", "IQ" => "Iraq", "IE" => "Ireland", "IL" => "Israel", "IT" => "Italy", "JM" => "Jamaica", "JP" => "Japan", "JO" => "Jordan", "KZ" => "Kazakhstan", "KE" => "Kenya", "KI" => "Kiribati", "KW" => "Kuwait", "KG" => "Kyrgyzstan", "LA" => "Laos", "LV" => "Latvia", "LB" => "Lebanon", "LS" => "Lesotho", "LR" => "Liberia", "LY" => "Libya", "LI" => "Liechtenstein", "LT" => "Lithuania", "LU" => "Luxembourg", "MO" => "Macau SAR China", "MK" => "Macedonia", "MG" => "Madagascar", "MW" => "Malawi", "MY" => "Malaysia", "MV" => "Maldives", "ML" => "Mali", "MT" => "Malta", "MH" => "Marshall Islands", "MQ" => "Martinique", "MR" => "Mauritania", "MU" => "Mauritius", "YT" => "Mayotte", "MX" => "Mexico", "FM" => "Micronesia", "MD" => "Moldova", "MC" => "Monaco", "MN" => "Mongolia", "ME" => "Montenegro", "MS" => "Montserrat", "MA" => "Morocco", "MZ" => "Mozambique", "MM" => "Myanmar [Burma]", "NA" => "Namibia", "NR" => "Nauru", "NP" => "Nepal", "NL" => "Netherlands", "AN" => "Netherlands Antilles", "NC" => "New Caledonia", "NZ" => "New Zealand", "NI" => "Nicaragua", "NE" => "Niger", "NG" => "Nigeria", "NU" => "Niue", "NF" => "Norfolk Island", "KP" => "North Korea", "MP" => "Northern Mariana Islands", "NO" => "Norway", "OM" => "Oman", "PK" => "Pakistan", "PW" => "Palau", "PS" => "Palestinian Territories", "PA" => "Panama", "PG" => "Papua New Guinea", "PY" => "Paraguay", "PE" => "Peru", "PH" => "Philippines", "PN" => "Pitcairn Islands", "PL" => "Poland", "PT" => "Portugal", "PR" => "Puerto Rico", "QA" => "Qatar", "RO" => "Romania", "RU" => "Russia", "RW" => "Rwanda", "RE" => "R?ion", "SH" => "Saint Helena", "KN" => "Saint Kitts and Nevis", "LC" => "Saint Lucia", "PM" => "Saint Pierre and Miquelon", "VC" => "Saint Vincent and the Grenadines", "WS" => "Samoa", "SM" => "San Marino", "SA" => "Saudi Arabia", "SN" => "Senegal", "RS" => "Serbia", "CS" => "Serbia and Montenegro", "SC" => "Seychelles", "SL" => "Sierra Leone", "SG" => "Singapore", "SK" => "Slovakia", "SI" => "Slovenia", "SB" => "Solomon Islands", "SO" => "Somalia", "ZA" => "South Africa", "GS" => "South Georgia and the South Sandwich Islands", "KR" => "South Korea", "ES" => "Spain", "LK" => "Sri Lanka", "SD" => "Sudan", "SR" => "Suriname", "SJ" => "Svalbard and Jan Mayen", "SZ" => "Swaziland", "SE" => "Sweden", "CH" => "Switzerland", "SY" => "Syria", "ST" => "S?Tom?nd Pr?ipe", "TW" => "Taiwan", "TJ" => "Tajikistan", "TZ" => "Tanzania", "TH" => "Thailand", "TL" => "Timor-Leste", "TG" => "Togo", "TK" => "Tokelau", "TO" => "Tonga", "TT" => "Trinidad and Tobago", "TN" => "Tunisia", "TR" => "Turkey", "TM" => "Turkmenistan", "TC" => "Turks and Caicos Islands", "TV" => "Tuvalu", "UM" => "U.S. Minor Outlying Islands", "VI" => "U.S. Virgin Islands", "UG" => "Uganda", "UA" => "Ukraine", "SU" => "Union of Soviet Socialist Republics", "AE" => "United Arab Emirates", "GB" => "United Kingdom", "US" => "United States", "UY" => "Uruguay", "UZ" => "Uzbekistan", "VU" => "Vanuatu", "VA" => "Vatican City", "VE" => "Venezuela", "VN" => "Vietnam", "WF" => "Wallis and Futuna", "EH" => "Western Sahara", "YE" => "Yemen", "ZM" => "Zambia", "ZW" => "Zimbabwe");

	if($type == 1) {

		foreach($list as $code => $name) {
			if($code == $value) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			echo '<option value="'.$code.'"'.$selected.'>'.$name.'</option>';
		}

	} elseif($type == 0) {

		return (array_key_exists($value, $list));

	} else {

		return $list[$value];

	}
}

function youtube_convert($id, $width = 400, $height = 250) {

	$output = '<iframe width="' . $width . '" height="' . $height . '" src="//www.youtube.com/embed/' . $id . '" frameborder="0" allowfullscreen></iframe>';

	return $output;

}

function youtube_url_to_id($url) {

	$output = preg_replace(
		"/(http:\/\/|https:\/\/)?(www.)?(youtube.com){1}(\/watch\?v=){1}([a-zA-Z0-9\-_]+)/",
		'$5', 
		$url
	);

	return $output;
}

function bbcode($data){

	$search = array(
		'/\[b\](.*?)\[\/b\]/is',
		'/\[i\](.*?)\[\/i\]/is',
		'/\[u\](.*?)\[\/u\]/is',
		'/\[li\](.*?)\[\/li\]/is',
		'/\[br\]/is'
		);
	$replace = array(
		'<strong>$1</strong>',
		'<em>$1</em>',
		'<u>$1</u>',
		'<li>$1</li>',
		'<br />'
		);

	/* Check for multiple [br] tags */
	$data = preg_replace('/(\[br\])+/', '[br]', trim($data));

	/* Replace the codes */
	$data = preg_replace($search, $replace, $data);

	return $data;
}

function generateSlug($string, $delimiter = "-") {

		/* Convert accents characters */
		$string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

		/* Replace all non words characters with the specified $delimiter */
		$string = preg_replace('/\W/', $delimiter, $string);

		/* Check for double $delimiters and remove them so it only will be 1 delimiter */
		$string = preg_replace('/-+/', '-', $string);

		/* Remove the $delimiter character from the start and the end of the string */
		$string = trim($string, $delimiter);

		/* Make all the remaining words lowercase */
		$string = strtolower($string);

		return $string;
}

function profile_admin_buttons($user_id, $active, $hash) {
	global $language;

	echo '<a href="admin/edit-user/' . $user_id . '" class="no-underline"><span class="label label-info">Edit <span class="glyphicon glyphicon-wrench white"></span></span></a>';
	
	echo '&nbsp;<a href="admin/users-management/status/' . $user_id . '/' . $hash . '" class="no-underline">';
		if($active == true) { 
			echo '<span class="label label-warning">Disable <span class="glyphicon glyphicon-ban-circle white"></span></span>';
		} else {
			echo '<span class="label label-success">Enable <span class="glyphicon glyphicon-ok white"></span></span>';
		}
	echo '</a>';
	
	echo '&nbsp;<a data-confirm="' . $language['messages']['confirm_delete'] . '" href="admin/users-management/delete/' . $user_id . '/' . $hash . '" class="no-underline"><span class="label label-danger">' . $language['misc']['delete'] . ' <span class="glyphicon glyphicon-remove white"></span></span></a>&nbsp;';

}

function category_admin_buttons($category_id,  $hash) {
	global $language;

	echo '<a href="admin/edit-category/' . $category_id . '" class="no-underline"><span class="label label-info">Edit <span class="glyphicon glyphicon-wrench white"></span></span></a>';
	
	echo '&nbsp;<a data-confirm="' . $language['messages']['category_confirm_delete'] . '" href="admin/categories-management/delete/' . $category_id . '/' . $hash . '" class="no-underline"><span class="label label-danger">' . $language['misc']['delete'] . ' <span class="glyphicon glyphicon-remove white"></span></span></a>&nbsp;';

}

function gifResize($file_origin,$file_dest,$size_w,$size_h){
   $crop_w = 0;
   $crop_h = 0;
   $crop_x = 0;
   $crop_y = 0;
   $image = new Imagick($file_origin);
   $image = $image->coalesceImages();

   foreach ($image as $frame) {
       $frame->cropImage($crop_w, $crop_h, $crop_x, $crop_y);
       $frame->thumbnailImage($size_h, $size_w);
       $frame->setImagePage($size_h, $size_w, 0, 0);
   }
   $imageContent = $image->getImagesBlob();
   $fp = fopen($file_dest,'w');
   fwrite($fp,$imageContent);
   fclose($fp);
}

function resize($file_name, $path, $width, $height, $center = false) {
	/* Get original image x y*/
	list($w, $h) = getimagesize($file_name);

	/* calculate new image size with ratio */
	$ratio = max($width/$w, $height/$h);
	$h = ceil($height / $ratio);
	$x = ($w - $width / $ratio) / 2;
	$w = ceil($width / $ratio);
	$y = 0;
	if($center) $y = 250 + $h/1.5;

	/* read binary data from image file */
	$imgString = file_get_contents($file_name);

	/* create image from string */
	$image = imagecreatefromstring($imgString);
	$tmp = imagecreatetruecolor($width, $height);
	imagecopyresampled($tmp, $image,
	0, 0,
	$x, $y,
	$width, $height,
	$w, $h);

	/* Save image */
	imagejpeg($tmp, $path, 100);

	return $path;
	/* cleanup memory */
	imagedestroy($image);
	imagedestroy($tmp);
}

function get_description($data)
{
	$answer = strip_tags($data);
	return (strlen($answer) > 150) ? substr($answer, 0, 150) . '...' : $answer;
}

function formatBytes($bytes, $precision = 2) {  
    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;
    $terabyte = $gigabyte * 1024;
   
    if (($bytes >= 0) && ($bytes < $kilobyte)) {
        return $bytes . ' B';
 
    } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
        return round($bytes / $kilobyte, $precision) . ' KB';
 
    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
        return round($bytes / $megabyte, $precision) . ' MB';
 
    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
        return round($bytes / $gigabyte, $precision) . ' GB';
 
    } elseif ($bytes >= $terabyte) {
        return round($bytes / $terabyte, $precision) . ' TB';
    } else {
        return $bytes . ' B';
    }
}

function string_resize($string, $maxchar) {
	$length = strlen($string);
	if($length > $maxchar) {
		$cutsize = -($length-$maxchar);
		$string  = substr($string, 0, $cutsize);
		$string  = $string . "..";
	}
	return $string;
}

function get_gravatar($email, $size) {
	$grav_url = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( "http://www.gravatar.com/avatar/" ) . "&s=" . $size;
	return $grav_url;
}

/* Function to return all the settings table */
function settings_data() {
	global $database;

	$result = $database->query("SELECT * FROM `settings` WHERE `id` = 1");
	$data   = $result->fetch_object();

	return $data;
}

/* Initiate html columns */
function initiate_html_columns() {
	include 'template/includes/initiate_main_column.php';
}

/* Custom die function */
function close($message, $error = false) {
	global $languages;
	global $language;
	global $database;
	global $account_user_id;
	global $server;
	global $category_exists;
	
	echo $message;

	include 'template/overall_footer.php';
	//include 'core/deinit.php';
	die();
}

function display_notifications() {
	global $language;

	$types = array("error", "success", "info");
	foreach($types as $type) {
		if(isset($_SESSION[$type]) && !empty($_SESSION[$type])) {
			if(!is_array($_SESSION[$type])) $_SESSION[$type] = array($_SESSION[$type]);

			foreach($_SESSION[$type] as $message) {
				echo '
					<div class="alert alert-' . $type . '">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong>' . $language['alerts'][$type] . '</strong> ' . $message . '
					</div>
				';
			}
			unset($_SESSION[$type]);
		}
	}

}

//Functions to quick output stylish errors, notices and success messages ;)
function output_errors($errors) {
	global $language;
	
	//if its not an array (just a single message), transform it into an array so it can be displayed properly 
	if(!is_array($errors)) $errors = array($errors);
	
	//display the errors
	foreach($errors as $error) {
		echo '
			<div class="alert alert-danger">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<strong>' . $language['alerts']['error'] . '</strong> ' . $error . '
			</div>
		';
	}
}

function output_success($messages) {
	global $language;

	//if its not an array (just a single message), transform it into an array so it can be displayed properly 
	if(!is_array($messages)) $messages = array($messages);
	
	//display the success messages
	foreach($messages as $message) {
		echo '
			<div class="alert alert-success">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<strong>' . $language['alerts']['success'] . '</strong> ' . $message . '
			</div>
		';
	}
}

function output_notice($messages) {
	global $language;

	//if its not an array (just a single message), transform it into an array so it can be displayed properly 
	if(!is_array($messages)) $messages = array($messages);
	
	//display the notice messages
	foreach($messages as $message) {
		echo '
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<strong>' . $language['alerts']['info'] . '</strong> ' . $message . '
			</div>
		';
	}
}


function server_status($address, $port) {
	global $database;
	
	require_once('core/classes/MinecraftPing.php');
	require_once('core/classes/MinecraftPingException.php');
	
	$status = new MinecraftPing($address, $port);
	$response = $status->Query( );
	
	return ($response !== false) ? true : false;

	
}
function server_update($server) {
	global $database;
	
	require_once('core/classes/MinecraftPing.php');
	require_once('core/classes/MinecraftPingException.php');
	
	$status = new MinecraftPing($server->address, $server->connection_port);
	$response = $status->Query( );
	
	if ($response !== false) {
		foreach( $response as $InfoKey => $InfoValue ) {
			if($InfoKey == 'version') {
				$must_be_replaced = array("Waterfall", "BotFilter", "Requires MC");
				$server_version = str_replace($must_be_replaced, "", $InfoValue['name']);
				
			}
			else if($InfoKey == 'players') {
				$players_max = $InfoValue['max'];
				$players_online = $InfoValue['online'];
			}
		
		}
		$server_status = 1;
	
	} else {
		$server_status = 0;
		$players_online = 0;
		$players_max = 0;
		$server_version = "offline";
		
	}
	$server->status = $server_status;
	$server->online_players = $players_online;
	$server->maximum_online_players = $players_max;
	$server->server_version = $server_version;	
		
	$stmt = $database->prepare("UPDATE `servers` SET `status` = ?, `online_players` = ?, `maximum_online_players` = ?, `server_version` = ?, `cachetime` = unix_timestamp() WHERE `server_id` = {$server->server_id}");
	$stmt->bind_param('ssss', $server_status, $players_online, $players_max, $server_version);
	$stmt->execute();
	
}

?>