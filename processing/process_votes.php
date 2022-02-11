<?php
include '../core/init.php';
include '../core/functions/recaptchalib.php';

/* Define the captcha variable */
$captcha = recaptcha_check_answer ($settings->private_key, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);

/* Check for any errors */
$result = $database->query("SELECT `id` FROM `points` WHERE `type` = 1 AND `server_id` = {$_SESSION['server_id']} AND `ip` = '{$_SERVER['REMOTE_ADDR']}' AND `timestamp` > UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY)");

if($result->num_rows) {
	$errors[] = $language['errors']['already_voted'];
}
/* if(!$captcha->is_valid) {
	$errors[] = $language['errors']['captcha_not_valid'];
}
 */
if(empty($errors)) {

	/* Check for custom fields */
	$server = new Server('', '', $_SESSION['server_id']);

	if($server->category->name = 'minecraft' && !empty($server->data->custom->votifier_public_key) && !empty($_POST['username'])) {
		$votifier_ip = (!empty($server->data->custom->votifier_ip)) ? $server->data->custom->votifier_ip : $server->data->address;

		if(!Votifier($server->data->custom->votifier_public_key, $votifier_ip, $server->data->custom->votifier_port, $_POST['username'])) {
			$errors[] = $language['errors']['votifier_vote'];
		}
	}

	
	/* Update the votes in the database */
	$database->query("INSERT INTO `points` (`type`, `server_id`, `ip`, `timestamp`) VALUES (1, {$_SESSION['server_id']}, '{$_SERVER['REMOTE_ADDR']}', UNIX_TIMESTAMP())");
	$database->query("UPDATE `servers` SET `votes` = `votes` + 1 WHERE `server_id` = {$_SESSION['server_id']}");

	echo "success";
} else echo output_errors($errors);
?>