<?php
User::check_permission(1);

/* Check if report exists */
if(!User::x_exists('id', $_GET['report_id'], 'reports')) {
	$_SESSION['error'][] = $language['errors']['report_not_found'];
	User::get_back('admin/reports-management');
}

/* Get $report data from the database */
$stmt = $database->prepare("SELECT * FROM `reports` WHERE `id` = ?");
$stmt->bind_param('s', $_GET['report_id']);
$stmt->execute();
bind_object($stmt, $report);
$stmt->fetch();
$stmt->close();

/* Convert the type of the report into text */
switch($report->type) {
	case '0' : $report->type_text = 'Comment';		$report->type_db_from = 'comments';	$report->type_db_where = 'id'; break;
	case '1' : $report->type_text = 'Blog Post';	$report->type_db_from = 'comments';	$report->type_db_where = 'id'; break;
	case '2' : $report->type_text = 'Server';		$report->type_db_from = 'servers';	$report->type_db_where = 'server_id'; break;
	case '3' : $report->type_text = 'User';			$report->type_db_from = 'users';	$report->type_db_where = 'user_id'; break;
}

/* Check if the admin wants to delete the report or the reported */
if(isset($_GET['delete']) && ($_GET['delete'] == 'reported' || $_GET['delete'] == 'report')) {

	if($_GET['delete'] == 'reported') {

		/* Delete the reported $type and also remove the report */
		$database->query("DELETE FROM `{$report->type_db_from}` WHERE `{$report->type_db_where}` = {$report->reported_id}");
		$database->query("DELETE FROM `reports` WHERE `id` = {$report->id}");

	} else

	if($_GET['delete'] == 'report') {

		/* Delete the report */
		$database->query("DELETE FROM `reports` WHERE `id` = {$report->id}");

	}
	
	/* Set a success message and redirect */
	$_SESSION['success'][] = $language['messages']['success'];
	redirect('admin/reports-management');
}





initiate_html_columns();

?>

<h3><?php echo $language['headers']['view_report']; ?></h3>


<div class="form-group">
	<label><?php echo $language['forms']['admin_report_edit_user']; ?></label>
	<p class="form-control-static"><?php echo User::get_profile_link($report->user_id); ?></p>
</div>

<div class="form-group">
	<label><?php echo $language['forms']['admin_report_edit_date']; ?></label>
	<input type="text" class="form-control" value="<?php echo $report->date; ?>" disabled="true" />
</div>

<div class="form-group">
	<label><?php echo $language['forms']['admin_report_edit_type']; ?></label>
	<input type="text" class="form-control" value="<?php echo $report->type_text; ?>" disabled="true" />
</div>

<div class="form-group">
	<label><?php echo $language['forms']['admin_report_edit_reported_id']; ?></label>
	<input type="text" name="email" class="form-control" value="<?php echo $report->reported_id; ?>" disabled="true" />
</div>

<div class="form-group">
	<label><?php printf($language['forms']['admin_report_edit_reported_x'], $report->type_text); ?></label>
	<?php
	switch($report->type) {
		case '0' :
		case '1' :
			$result = $database->query("SELECT * FROM `comments` WHERE `id` = {$report->reported_id} AND `type` = {$report->type}");
			$data = $result->fetch_object();
		?>
		<div class="panel panel-default">
			<div class="panel-body">
				<div class="media">
					<div class="media-body">
						<h4 class="media-heading">
							<?php echo User::get_profile_link($data->user_id); ?>
						</h4>

						<?php echo $data->comment; ?>

						<br />
						<span class="text-muted"><?php echo $data->date_added; ?></span>

					</div>
				</div>
			</div>
		</div>
		<?php
		break;

		case '2' :
			$result = $database->query("SELECT `name`, `address`, `connection_port` FROM `servers` WHERE `server_id` = {$report->reported_id}");
			$server = $result->fetch_object();
			echo '<p class="form-control-static"><a href="server/' . $server->address . ':' . $server->connection_port . '">' . $server->name . '</a></p>';
		break;

		case '3' :
			echo '<p class="form-control-static">' . User::get_profile_link($report->user_id) . '</p>';
		break;
	}
	?>		
</div>

<div class="form-group">
	<label><?php echo $language['forms']['admin_report_edit_reason']; ?></label>
	<textarea class="form-control" rows="4" style="resize:none"; disabled="true"><?php echo $report->message; ?></textarea>
</div>



<div class="form-group">
	<a href="admin/edit-report/<?php echo $_GET['report_id']; ?>?delete=reported" data-confirm="<?php echo $language['messages']['confirm_delete']; ?>" >
		<button type="button" class="btn btn-default"><?php printf($language['forms']['admin_report_edit_delete_x'], $report->type_text); ?></button>
	</a>

	<a href="admin/edit-report/<?php echo $_GET['report_id']; ?>?delete=report" data-confirm="<?php echo $language['messages']['confirm_delete']; ?>">
		<button type="button" class="btn btn-default"><?php printf($language['forms']['admin_report_edit_delete_x'], 'report'); ?></button>
	</a>

	<br /><br />
</div>
