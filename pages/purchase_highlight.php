<?php
User::check_permission(0);

/* Check for any errors */
if(User::get_servers($account_user_id) < 1) {
	$_SESSION['error'][] = $language['messages']['no_my_servers'];
}

$result = $database->query("SELECT COUNT(*) AS `count` FROM `payments` WHERE `date` + INTERVAL `highlighted_days` DAY > CURDATE()");
$data = $result->fetch_object();

if($data->count >= $settings->maximum_slots) {
	$_SESSION['error'][] = $language['messages']['no_highlighted_slots_available'];
}

if(!empty($_SESSION['error'])) User::get_back();

initiate_html_columns();

include 'template/includes/modals/purchase_highlight.php';
?>

<h3><?php echo $language['headers']['purchase_highlight']; ?></h3>

<form action="" method="post" role="form">

	<div class="form-group">
		<label><?php echo $language['forms']['select_server']; ?></label>
		<select name="server_id" class="form-control">
			<?php
			$result = $database->query("SELECT `server_id`, `name` FROM `servers` WHERE `user_id` = {$account_user_id}");
			while($server = $result->fetch_object()) {
				echo '<option value="' . $server->server_id . '">' . $server->name . '</option>';
			}
			?>
		</select>
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['highlighted_days']; ?></label>
		<input type="text" name="highlighted_days" class="form-control" value="<?php echo $settings->minimum_days; ?>" />
	</div>


	<table class="table-fixed-full table-statistics">
		<tbody>

			<tr>
				<td style="width:50%"><?php echo $language['forms']['settings_minimum_days']; ?>:</td>
				<td style="width:50%"><kbd><?php echo $settings->minimum_days; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['forms']['settings_maximum_days']; ?>:</td>
				<td style="width:50%"><kbd><?php echo $settings->maximum_days; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['forms']['settings_payment_currency']; ?>:</td>
				<td style="width:50%"><kbd><?php echo $settings->payment_currency; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['forms']['settings_per_day_cost']; ?>:</td>
				<td style="width:50%"><kbd><?php echo $settings->per_day_cost; ?></kbd></td>
			</tr>

		</tbody>
	</table>

	<br />

	<div class="form-group">
        <button id="generate_payment" class="btn btn-default col-lg-4"><?php echo $language['forms']['generate_payment']; ?></button><br /><br />
    </div>

</form>

<script>
	$(document).ready(function(){

		$('#generate_payment').on('click', function(event){

			/* Store some variables */
			var server_id = $('[name=server_id]').val();
			var highlighted_days = $('[name=highlighted_days]').val();
			var per_day_cost = <?php echo $settings->per_day_cost; ?>;
			var final_cost = (per_day_cost * highlighted_days).toFixed(2);

			/* Check for any small "errors" */
			if(<?php echo $settings->minimum_days; ?> > highlighted_days) {
				alert('<?php echo $language['errors']['minimum_days']; ?>');
			} else
			if(<?php echo $settings->maximum_days; ?> < highlighted_days) {
				alert('<?php echo $language['errors']['maximum_days']; ?>');
			}
			else {

				/* Change the details of the payment */
				$('[name=amount]').val(final_cost);
				$('[name=custom]').val(server_id + '|' + highlighted_days);

				/* Show the modal */
				$('#purchase_highlight').modal('show');
			}

			event.preventDefault();
		});

	});
</script>