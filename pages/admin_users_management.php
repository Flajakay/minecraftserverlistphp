<?php
User::check_permission(1);

if(isset($_GET['status'])) {
	$user_data = new User($_GET['status']);

	/* Check for errors and permissions */
	if(!$token->is_valid()) {
		$_SESSION['error'][] = $language['errors']['invalid_token'];
	}
	if($_GET['status'] == $account_user_id) {
		$_SESSION['error'][] = $language['errors']['status_yourself'];
	}
	if(User::get_type($_GET['status']) > 0 && User::get_type($account_user_id) < 2) {
		$_SESSION['error'][] = $language['errors']['command_denied'];
	}

	if(empty($_SESSION['error'])) {
		if($user_data->active == true) $new_value = 0; else $new_value = 1;

		$database->query("UPDATE `users` SET `active` = {$new_value} WHERE `user_id` = {$_GET['status']}");
		$_SESSION['success'][] = $language['messages']['success'];
	} 
	
	display_notifications();

}

if(isset($_GET['delete'])) {
	$user_data = new User($_GET['delete']);

	/* Check for errors and permissions */
	if(!$token->is_valid()) {
		$_SESSION['error'][] = $language['errors']['invalid_token'];
	}
	if($_GET['delete'] == $account_user_id) {
		$_SESSION['error'][] = $language['errors']['delete_yourself'];
	}
	if(User::get_type($account_user_id) < 2) {
		$_SESSION['error'][] = $language['errors']['command_denied'];
	}

	if(empty($_SESSION['error'])) {
		$database->query("DELETE FROM `users` WHERE `user_id` = {$_GET['delete']}");
		
		$_SESSION['success'][] = $language['messages']['success'];
	}
	
	display_notifications();
	
}

initiate_html_columns();

?>

<div class="table-responsive">
	<table class="table">
		<thead>
			<tr>
				<th><? echo $language['forms']['username'] ?></th>
				<th><? echo $language['forms']['name'] ?></th>
				<th><? echo $language['forms']['email'] ?></th>
				<th>IP</th>
				<th><? echo $language['forms']['date'] ?></th>
				<th><? echo $language['forms']['tools'] ?></th>
			</tr>
		</thead>
		<tbody id="results">
			
		</tbody>
	</table>
</div>

<script>
$(document).ready(function() {
	/* Load first answers */
	showMore(0, 'processing/admin_users_show_more.php', '#results', '#showMoreUsers');
});
</script>