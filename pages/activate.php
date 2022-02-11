<?php
//User::logged_in_redirect();
if(empty($_GET['email']) || empty($_GET['activation_code'])) redirect();

/* Check if the activation code is correct */
$stmt = $database->prepare("SELECT `user_id` FROM `users` WHERE `email` = ? AND `email_activation_code` = ?");
$stmt->bind_param('ss', $_GET['email'], $_GET['activation_code']);
$stmt->execute();
$stmt->store_result();
$num_rows = $stmt->num_rows;
$stmt->fetch();
$stmt->close();

if($num_rows > 0) {
	$stmt = $database->prepare("UPDATE `users` SET `active` = 1 WHERE `email` = ?");
	$stmt->bind_param('s', $_GET['email']);
	$stmt->execute();
	$stmt->close();

	$_SESSION['success'][] = $language['messages']['activation_successful'];
} else {
	$_SESSION['error'][] = $language['errors']['invalid_activation'];
}

redirect();
?>