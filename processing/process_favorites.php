<?php
include '../core/init.php';

/* Additional check to see if server is not coming from the same account */
$result = $database->query("SELECT `user_id` FROM `servers` WHERE `server_id` = {$_SESSION['server_id']}");
$data = $result->fetch_object();

if(User::logged_in() && $data->user_id !== $account_user_id) {
	$query = $database->query("SELECT `id` FROM `favorites` WHERE `user_id` = {$account_user_id} AND `server_id` = {$_SESSION['server_id']}");
	if($query->num_rows > 0) {
		$database->query("DELETE FROM `favorites` WHERE `user_id` = {$account_user_id} AND `server_id` = {$_SESSION['server_id']}");
		$database->query("UPDATE `servers` SET `favorites` = `favorites` - 1 WHERE `server_id` = {$_SESSION['server_id']}");
		
		echo "unfavorited";
	} else {
		$database->query("INSERT INTO `favorites` (`user_id`, `server_id`) VALUES ({$account_user_id}, {$_SESSION['server_id']})");
		$database->query("UPDATE `servers` SET `favorites` = `favorites` + 1 WHERE `server_id` = {$_SESSION['server_id']}");

		echo "favorited";
	}
} else echo "not logged in";
?>