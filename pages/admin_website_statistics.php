<?php
User::check_permission(1);

initiate_html_columns();
?>

<div class="row">
	<div class="col-md-6">
		
		<?php
		$result = $database->query("
			SELECT 
				(SELECT COUNT(*) FROM `categories`) AS `categories_count`,
				(SELECT COUNT(*) FROM `comments`) AS `comments_count`,
				(SELECT COUNT(*) FROM `reports`) AS `reports_count`,
				(SELECT COUNT(*) FROM `servers`) AS `servers_count`,
				(SELECT COUNT(*) FROM `users`) AS `users_count`
			");
		$total_data = $result->fetch_object();
		?>

		<h4><?php echo $language['misc']['total_statistics']; ?></h4>

		<table class="table-fixed-full table-statistics">
			<tr>
				<td style="width:50%"><?php echo $language['misc']['statistics_categories']; ?></td>
				<td style="width:50%"><kbd><?php echo $total_data->categories_count; ?></kbd></td>
			</tr>
			<tr>
				<td style="width:50%"><?php echo $language['misc']['statistics_comments']; ?></td>
				<td style="width:50%"><kbd><?php echo $total_data->comments_count; ?></kbd></td>
			</tr>
			<tr>
				<td style="width:50%"><?php echo $language['misc']['statistics_reports']; ?></td>
				<td style="width:50%"><kbd><?php echo $total_data->reports_count; ?></kbd></td>
			</tr>
			<tr>
				<td style="width:50%"><?php echo $language['misc']['statistics_servers']; ?></td>
				<td style="width:50%"><kbd><?php echo $total_data->servers_count; ?></kbd></td>
			</tr>
			<tr>
				<td style="width:50%"><?php echo $language['misc']['statistics_users']; ?></td>
				<td style="width:50%"><kbd><?php echo $total_data->users_count; ?></kbd></td>
			</tr>
		</table>

	</div>

	<div class="col-md-6">
		
		<?php
		$result = $database->query("
			SELECT 
				(SELECT COUNT(*) FROM `comments` WHERE YEAR(`date_added`) = YEAR(CURDATE()) AND MONTH(`date_added`) = MONTH(CURDATE())) AS `comments_count`,
				(SELECT COUNT(*) FROM `reports` WHERE YEAR(`date`) = YEAR(CURDATE()) AND MONTH(`date`) = MONTH(CURDATE())) AS `reports_count`,
				(SELECT COUNT(*) FROM `servers` WHERE YEAR(`date_added`) = YEAR(CURDATE()) AND MONTH(`date_added`) = MONTH(CURDATE())) AS `servers_count`,
				(SELECT COUNT(*) FROM `users` WHERE YEAR(`date`) = YEAR(CURDATE()) AND MONTH(`date`) = MONTH(CURDATE())) AS `users_count`
			");
		$monthly_data = $result->fetch_object();
		?>

		<h4><?php echo $language['misc']['month_statistics']; ?></h4>

		<table class="table-fixed-full table-statistics">
			<tr>
				<td style="width:50%"><?php echo $language['misc']['statistics_comments']; ?></td>
				<td style="width:50%"><kbd><?php echo $monthly_data->comments_count; ?></kbd></td>
			</tr>
			<tr>
				<td style="width:50%"><?php echo $language['misc']['statistics_reports']; ?></td>
				<td style="width:50%"><kbd><?php echo $monthly_data->reports_count; ?></kbd></td>
			</tr>
			<tr>
				<td style="width:50%"><?php echo $language['misc']['statistics_servers']; ?></td>
				<td style="width:50%"><kbd><?php echo $monthly_data->servers_count; ?></kbd></td>
			</tr>
			<tr>
				<td style="width:50%"><?php echo $language['misc']['statistics_users']; ?></td>
				<td style="width:50%"><kbd><?php echo $monthly_data->users_count; ?></kbd></td>
			</tr>
		</table>

	</div>
</div>

<br />


<div class="row">
	<div class="col-md-6">

		<?php
		$result = $database->query("
			SELECT
				(SELECT COUNT(`user_id`) FROM `users` WHERE YEAR(`date`) = YEAR(CURDATE()) AND MONTH(`date`) = MONTH(CURDATE()) AND DAY(`date`) = DAY(CURDATE())) AS `new_users_today`,
				(SELECT COUNT(`user_id`) FROM `users` WHERE `type` = '2') AS `owner_users`,
				(SELECT COUNT(`user_id`) FROM `users` WHERE `type` = '1') AS `admin_users`,
				(SELECT COUNT(`user_id`) FROM `users` WHERE `private` = '1') AS `private_users`,
				(SELECT COUNT(`user_id`) FROM `users` WHERE `active` = '1') AS `confirmed_users`,
				(SELECT COUNT(`user_id`) FROM `users` WHERE `active` = '0') AS `unconfirmed_users`
			");
		$users_data = $result->fetch_object();
		?>

		<h4><?php echo $language['misc']['users_statistics']; ?></h4>

		<table class="table-fixed-full table-statistics">
			<tr>
				<td style="width:50%"><?php echo $language['misc']['new_users_today']; ?></td>
				<td style="width:50%"><kbd><?php echo $users_data->new_users_today; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['misc']['online_users']; ?></td>
				<td style="width:50%"><kbd><?php echo User::online_users(30); ?></kbd></td>
			</tr>
			<tr>
				<td colspan="2">
					<?php
					$result = $database->query("SELECT `name`, `username` FROM `users` WHERE `last_activity` > UNIX_TIMESTAMP() - 30");
					
					while($users = $result->fetch_object())
						echo '<a href="profile/' . $users->username . '">' . $users->name . '</a>, ';

					?>
				</td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['misc']['active_users_today']; ?></td>
				<td style="width:50%"><kbd><?php echo User::online_users(86400); ?></kbd></td>
			</tr>
			<tr>
				<td colspan="2">
					<?php
					$result = $database->query("SELECT `name`, `username` FROM `users` WHERE `last_activity` > UNIX_TIMESTAMP() - 86400");
					
					while($users = $result->fetch_object())
						echo '<a href="profile/' . $users->username . '">' . $users->name . '</a>, ';

					?>
				</td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['misc']['owner_users']; ?></td>
				<td style="width:50%"><kbd><?php echo $users_data->owner_users; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['misc']['admin_users']; ?></td>
				<td style="width:50%"><kbd><?php echo $users_data->admin_users; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['misc']['private_users']; ?></td>
				<td style="width:50%"><kbd><?php echo $users_data->private_users; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['misc']['confirmed_users']; ?></td>
				<td style="width:50%"><kbd><?php echo $users_data->confirmed_users; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['misc']['unconfirmed_users']; ?></td>
				<td style="width:50%"><kbd><?php echo $users_data->unconfirmed_users; ?></kbd></td>
			</tr>
		</table>

	</div>

	<div class="col-md-6">

		<?php
		$result = $database->query("
			SELECT
				(SELECT COUNT(`server_id`) AS `count` FROM `servers` WHERE YEAR(`date_added`) = YEAR(CURDATE()) AND MONTH(`date_added`) = MONTH(CURDATE()) AND DAY(`date_added`) = DAY(CURDATE())) AS `new_servers_today`,
				(SELECT COUNT(`server_id`) AS `count` FROM `servers` WHERE `cachetime` > UNIX_TIMESTAMP() - 86400) AS `active_servers_today`,
				(SELECT COUNT(`server_id`) AS `count` FROM `servers` WHERE `status` = '1') AS `online_servers`,
				(SELECT COUNT(`server_id`) AS `count` FROM `servers` WHERE `status` = '0') AS `offline_servers`,
				(SELECT COUNT(`server_id`) AS `count` FROM `servers` WHERE `active` = '1') AS `active_servers`,
				(SELECT COUNT(`server_id`) AS `count` FROM `servers` WHERE `private` = '1') AS `private_servers`
			");
		$servers_data = $result->fetch_object();
		?>

		<h4><?php echo $language['misc']['server_statistics']; ?></h4>

		<table class="table-fixed-full table-statistics">
			<tr>
				<td style="width:50%"><?php echo $language['misc']['new_servers_today']; ?></td>
				<td style="width:50%"><kbd><?php echo $servers_data->new_servers_today; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['misc']['active_servers_today']; ?></td>
				<td style="width:50%"><kbd><?php echo $servers_data->active_servers_today; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['misc']['online_servers']; ?></td>
				<td style="width:50%"><kbd><?php echo $servers_data->online_servers; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['misc']['offline_servers']; ?></td>
				<td style="width:50%"><kbd><?php echo $servers_data->offline_servers; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['misc']['active_servers']; ?></td>
				<td style="width:50%"><kbd><?php echo $servers_data->active_servers; ?></kbd></td>
			</tr>

			<tr>
				<td style="width:50%"><?php echo $language['misc']['private_servers']; ?></td>
				<td style="width:50%"><kbd><?php echo $servers_data->private_servers; ?></kbd></td>
			</tr>
		</table>

	</div>
</div>