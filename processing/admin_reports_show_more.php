<?php
include '../core/init.php';

$_POST['limit'] = (int) $_POST['limit'];
$results_limit = 25;

$result = $database->query("SELECT * FROM `reports` ORDER BY `id` ASC LIMIT {$_POST['limit']}, {$results_limit}");
while($reports_data = $result->fetch_object()) {
	switch($reports_data->type) {
		case '0' : $reports_data->type = 'Comment'; break;
		case '1' : $reports_data->type = 'Blog Post'; break;
		case '2' : $reports_data->type = 'Server'; break;
		case '3' : $reports_data->type = 'User'; break;
	}
?>
<tr>
	<td><?php echo $reports_data->type; ?></td>
	<td><?php echo User::get_profile_link($reports_data->user_id); ?></td>
	<td><?php echo $reports_data->reported_id; ?></td>
	<td><?php echo $reports_data->date; ?></td>
	<td><a href="admin/edit-report/<?php echo $reports_data->id; ?>">Open</a></td>
</tr>
<?php } ?>

<?php if($result->num_rows == $results_limit) { ?>
<tr id="showMoreReports">
	<td colspan="6">
		<div class="center">
			<button id="showMore" class="btn btn-default" onClick="showMore(<?php echo $_POST['limit'] + $results_limit; ?>, 'processing/admin_users_show_more.php', '#results', '#showMoreReports');"><?php echo $language['misc']['show_more']; ?></button>
		</div>	
	</td>
</tr>
<?php } ?>
