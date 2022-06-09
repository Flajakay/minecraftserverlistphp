<?php
//User::logged_in_redirect();
if(empty($_GET['email']) || empty($_GET['activation_code'])) redirect();

/* Check if the activation code is correct */
$stmt = $database->prepare("SELECT * FROM `users` WHERE `email` = ? AND `email_activation_code` = ?");
$stmt->bind_param('ss', $_GET['email'], $_GET['activation_code']);
$stmt->execute();
bind_object($stmt, $data);
$stmt->fetch();
$stmt->close();

if($data->user_id > 0) {
	$stmt = $database->prepare("UPDATE `users` SET `active` = 1 WHERE `email` = ?");
	$stmt->bind_param('s', $_GET['email']);
	$stmt->execute();
	$stmt->close();

	$_SESSION['success'][] = $language['messages']['activation_successful'];	
	$_SESSION['user_id'] = User::login($data->username, $data->password);
} else {
	$_SESSION['error'][] = $language['errors']['invalid_activation'];
}

redirect();
?>