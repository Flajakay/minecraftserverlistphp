<?php
include '../core/init.php';

$_POST['limit'] = (int) $_POST['limit'];
$results_limit = 25;

$result = $database->query("SELECT `user_id`, `username`, `name`, `email`, `ip`, `date`, `type`, `active` FROM `users` ORDER BY `username` ASC LIMIT {$_POST['limit']}, {$results_limit}");
while($users_data = $result->fetch_object()) {	
?>
<tr>
	<td>
		<?php if($users_data->type == 1) { ?>
		<a href="#" data-toggle="tooltip" title="<?php echo $language['misc']['admin']; ?>"  class="tooltipz"><span class="glyphicon glyphicon-bookmark"></span></a>
		<?php } elseif($users_data->type == 2) { ?>
		<a href="#" data-toggle="tooltip" title="<?php echo $language['misc']['owner']; ?>"  class="tooltipz"><span class="glyphicon glyphicon-bookmark"></span></a>
		<?php } ?>

		<?php echo $users_data->username; ?>
	</td>
	<td><?php echo $users_data->name; ?></td>
	<td><?php echo $users_data->email; ?></td>
	<td><?php echo $users_data->ip; ?></td>
	<td><?php echo $users_data->date; ?></td>
	<td><?php profile_admin_buttons($users_data->user_id, $users_data->active, $token->hash); ?></td>
</tr>
<?php } ?>

<?php if($result->num_rows == $results_limit) { ?>
<tr id="showMoreUsers">
	<td colspan="6">
		<div class="center">
			<button id="showMore" class="btn btn-default" onClick="showMore(<?php echo $_POST['limit'] + $results_limit; ?>, 'processing/admin_users_show_more.php', '#results', '#showMoreUsers');"><?php echo $language['misc']['show_more']; ?></button>
		</div>	
	</td>
</tr>
<?php } ?>
