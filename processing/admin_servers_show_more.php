<?php
include '../core/init.php';

$_POST['limit'] = (int) $_POST['limit'];
$results_limit = 25;

$result = $database->query("SELECT `server_id`, `category_id`, `address`, `connection_port`, `query_port`, `date_added`, `private`, `active`, `highlight` FROM `servers` ORDER BY `server_id` DESC LIMIT {$_POST['limit']}, {$results_limit}");
while($servers_data = $result->fetch_object()) {	
?>
<tr>
	<td>
		<?php
		if($servers_data->active) 
			echo '<span data-toggle="tooltip" title="' . $language['server']['status_active'] . '" class="glyphicon glyphicon-ok green tooltipz"></span>&nbsp;';
		else
			echo '<span data-toggle="tooltip" title="' . $language['server']['status_disabled'] . '" class="glyphicon glyphicon-remove red tooltipz"></span>&nbsp;';		
		if($servers_data->highlight) echo '<span data-toggle="tooltip" title="' . $language['server']['status_highlighted'] . '" class="glyphicon glyphicon-star tooltipz"></span>&nbsp;';
		if($servers_data->private) echo '<span data-toggle="tooltip" title="' . $language['server']['status_private'] . '" class="glyphicon glyphicon-off tooltipz"></span>';
		?>
	</td>
	<td><?php echo $servers_data->address; ?></td>
	<td><?php echo $servers_data->connection_port; ?></td>
	<td><?php echo $servers_data->query_port; ?></td>
	<td><?php echo Server::get_category($servers_data->category_id); ?></td>
	<td><?php echo $servers_data->date_added; ?></td>
	<td><a href="admin/edit-server/<?php echo $servers_data->server_id; ?>">Edit</a></td>
</tr>
<?php } ?>

<?php if($result->num_rows == $results_limit) { ?>
<tr id="showMoreServers">
	<td colspan="6">
		<div class="center">
			<button id="showMore" class="btn btn-default" onClick="showMore(<?php echo $_POST['limit'] + $results_limit; ?>, 'processing/admin_servers_show_more.php', '#results', '#showMoreServers');"><?php echo $language['misc']['show_more']; ?></button>
		</div>	
	</td>
</tr>
<?php } ?>
