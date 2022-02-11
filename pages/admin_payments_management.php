<?php
User::check_permission(2);


if(isset($_GET['delete'])) {

	/* Check for errors */
	if(!$token->is_valid()) {
		$_SESSION['error'][] = $language['errors']['invalid_token'];
	}

	if(empty($_SESSION['error'])) {
		/* Get the $server_id from the $payment_id */
		$server_id = User::x_to_y('id', 'server_id', $_GET['delete'], 'payments');

		/* Remove Highlight from the specific server */
		$database->query("UPDATE `servers` SET `highlight` = '0' WHERE `server_id` = {$server_id}");

		/* Delete the payment logs */
		$database->query("DELETE FROM `payments` WHERE `id` = {$_GET['delete']}");

		/* Set the success message & redirect*/
		$_SESSION['success'][] = $language['messages']['success'];
		User::get_back('admin/payments-management');
	}
}

initiate_html_columns();

?>

<div class="table-responsive">
	<table class="table">
		<thead>
			<tr>
				<th><? echo $language['forms']['username'] ?></th>
				<th><? echo $language['forms']['server_address'] ?></th>
				<th><? echo $language['forms']['highlighted_days'] ?></th>
				<th><? echo $language['forms']['date'] ?></th>
				<th><? echo $language['forms']['email'] ?></th>
				<th><? echo $language['forms']['income'] ?></th>
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
	showMore(0, 'processing/admin_payments_show_more.php', '#results', '#showMorePayments');
});
</script>