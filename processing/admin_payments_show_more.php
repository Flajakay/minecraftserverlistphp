<?php
include '../core/init.php';

$_POST['limit'] = (int) $_POST['limit'];
$results_limit = 25;

$result = $database->query("SELECT * FROM `payments` ORDER BY `id` DESC LIMIT {$_POST['limit']}, {$results_limit}");
while($payment = $result->fetch_object()) {	

	/* Get some details of the server so we can generate the link */
	$server_result = $database->query("SELECT `address`, `connection_port`, `name` FROM `servers` WHERE `server_id` = {$payment->server_id}");
	$server = $server_result->fetch_object();

?>
<tr>
	<td><?php echo User::get_profile_link($payment->user_id); ?></td>
	<td><a href="server/<?php echo $server->address . ':' . $server->connection_port; ?>"><?php echo $server->name; ?></a></td>
	<td><?php echo $payment->highlighted_days; ?></td>
	<td><?php echo $payment->date; ?></td>
	<td><?php echo $payment->email; ?></td>
	<td><?php echo $payment->revenue . ' ' . $settings->payment_currency; ?></td>
	<td><a data-confirm="<?php echo $language['messages']['confirm_delete_payment']; ?>" href="admin/payments-management/delete/<?php echo $payment->id . '/' . $token->hash; ?>">Delete</a></td>
</tr>
<?php } ?>

<?php if($result->num_rows == $results_limit) { ?>
<tr id="showMorePayments">
	<td colspan="6">
		<div class="center">
			<button id="showMore" class="btn btn-default" onClick="showMore(<?php echo $_POST['limit'] + $results_limit; ?>, 'processing/admin_users_show_more.php', '#results', '#showMoreUsers');"><?php echo $language['misc']['show_more']; ?></button>
		</div>	
	</td>
</tr>
<?php } ?>
