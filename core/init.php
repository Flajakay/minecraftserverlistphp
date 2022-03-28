<?php
/* Script version 1.4 */
ob_start();
session_start();
error_reporting(E_ALL);

include 'classes/Query.php';
include 'database/connect.php';
include 'functions/language.php';
include 'functions/general.php';
include 'classes/User.php';
include 'classes/Pagination.php';
include 'classes/Server.php';
include 'classes/Servers.php';
include 'classes/Csrf.php';
include 'classes/PHPMailer/PHPMailerAutoload.php';

/* Initialize variables */
$errors 	= array();
$settings 	= settings_data();
$token 		= new CsrfProtection();
$query 		= new StdClass;

/* Set the default timezone if its not set in the ini file */
if (!date_default_timezone_get()) 
	date_default_timezone_set('America/New_York');

/* If user is logged in get his data */
if(User::logged_in()) {
	$account_user_id = (isset($_SESSION['user_id']) == true) ? $_SESSION['user_id'] : $_COOKIE['user_id'];
	$account = new User($account_user_id);

	/* Update last activity */
	$database->query("UPDATE `users` SET `last_activity` = unix_timestamp() WHERE `user_id` = {$account_user_id}");
}


/* Get server data if needed */
if(!empty($_GET['address']) && !empty($_GET['port']) && $_GET['page'] == 'server') {
	$server = new Server($_GET['address'], $_GET['port']);
	if($server->exists) $_SESSION['server_id'] = $server->data->server_id;
}

/* Get profile data if needed */
if(!empty($_GET['username']) && $_GET['page'] == 'profile') {
	/* Fetch the users data & Set a session with the profile id for the form */
	$_SESSION['profile_user_id'] = $profile_user_id = User::x_to_y('username', 'user_id', $_GET['username']);

	/* Check if user exists */
	$user_exists = ($profile_user_id !== NULL);

	/* If user exists -> get his profile data */
	if($user_exists) {
		$profile_account = new User($profile_user_id);
	}

}

/* If the page is category do: */
if(!empty($_GET['url']) && $_GET['page'] == 'category') {
	
	/* Get $category data from the database */
	$stmt = $database->prepare("SELECT * FROM `categories` WHERE BINARY `url` = ?");
	$stmt->bind_param('s', $_GET['url']);
	$stmt->execute();
	bind_object($stmt, $category);
	$stmt->fetch();
	$stmt->close();

	$category_exists = ($category !== NULL);
}


include 'functions/titles.php';

?>
