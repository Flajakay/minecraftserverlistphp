<?php
include '../core/init.php';
include '../core/functions/recaptchalib.php';

/* Process variables */
//@$_POST['comment'] = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);
//$encode = $_POST['comment'];
//echo utf8_encode($encode);
//$_POST['comment'] = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);

$type = (isset($_POST['type'])) ? (int) $_POST['type'] : '0';

/* Define the captcha variable */
if(!isset($_POST['delete']))
//$captcha = recaptcha_check_answer ($settings->private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

/* Check for errors */
if(!isset($_POST['delete'])) {

/* 	if(!$captcha->is_valid) {
		$errors[] = $language['errors']['captcha_not_valid'];
	} */
	if(isset($_POST['type']) && $account_user_id != User::x_to_y('server_id', 'user_id', $_SESSION['server_id'], 'servers')) {
		$errors[] = $language['errors']['command_denied'];
	}
/* 	if(!$token->is_valid()) {
	$errors[] = $language['errors']['invalid_token'];
	} */
	if(strlen($_POST['comment']) > 512) {
		$errors[] = $language['errors']['message_too_long'];
	}
	if(strlen($_POST['comment']) < 5) {
		$errors[] = $language['errors']['message_too_short'];
	}

} else {
	if(!User::is_admin($account_user_id)) {
		$errors[] = $language['errors']['command_denied'];
	}
}


if(empty($errors)) {
	$date = new DateTime();
	$date = $date->format('Y-m-d H:i:s');

	if(!isset($_POST['delete'])) {
		$stmt = $database->prepare("INSERT INTO `comments` (`server_id`, `user_id`, `type`, `comment`, `date_added`) VALUES (?, ?, ?, ?, ?)");
		$stmt->bind_param('sssss',  $_SESSION['server_id'], $account_user_id, $type, $_POST['comment'], $date);
		$stmt->execute();
		$stmt->close();
	} else {
		$stmt = $database->prepare("DELETE FROM `comments` WHERE `type` = ? AND `id` = ?");
		$stmt->bind_param('ss', $type, $_POST['reported_id']);
		$stmt->execute();
		$stmt->close();
	}

	echo "success";
} else echo output_errors($errors);
?>