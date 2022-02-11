<h4><?php echo $language['misc']['my_servers_status']; ?></h4>

<div class="list-group">
	<?php
	$result = $database->query("SELECT `server_id`, `address`, `connection_port`, `status`,`cachetime` FROM `servers` WHERE `user_id` = {$account_user_id}");
	while($my_servers = $result->fetch_object()) {
	?>
	<a class="list-group-item" href="server/<?php echo $my_servers->address . ':' . $my_servers->connection_port; ?>">
		<?php echo $my_servers->address; ?><br />

		<?php echo ($my_servers->status ? '<span class="glyphicon glyphicon-ok green"></span>' : '<span class="glyphicon glyphicon-remove red"></span>'); ?>
		<span class="timeago text-muted" title="<?php echo @date('c', $my_servers->cachetime); ?>"></span> 
	</a>
	<?php } ?>
</div>

