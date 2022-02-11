<?php
include 'core/classes/Query.php';
include 'core/functions/recaptchalib.php';
include 'template/includes/modals/comment.php';
include 'template/includes/modals/blog.php';
include 'template/includes/modals/report.php';
include 'template/includes/modals/vote.php';

/* Check if server exists and the GET variables are not empty */
if(empty($_GET['address']) || empty($_GET['port']) || !$server->exists) {
	$_SESSION['error'][] = $language['errors']['server_not_found'];
} else {

	/* Check if server is disabled */
	if(!$server->data->active) {
		$_SESSION['error'][] = $language['errors']['server_not_active'];
	}

	if(
		($server->data->private && !User::logged_in()) || 
		($server->data->private && User::logged_in() && $account_user_id != $server->data->user_id)
	) {
		/* Set error message and redirect */
		$_SESSION['error'][] = $language['errors']['server_private'];
	}

}

if(!empty($_SESSION['error'])) User::get_back();

/* If its private but the owner is viewing it, display a notice message */
if($server->data->private) echo output_notice($language['server']['private']);

/* Check if we should add another hit to the server or not */
$result = $database->query("SELECT `id` FROM `points` WHERE `type` = 0 AND `server_id` = {$server->data->server_id} AND `ip` = '{$_SERVER['REMOTE_ADDR']}' AND `timestamp` > UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY)");
if(!$result->num_rows) $database->query("INSERT INTO `points` (`type`, `server_id`, `ip`, `timestamp`) VALUES (0, {$server->data->server_id}, '{$_SERVER['REMOTE_ADDR']}', UNIX_TIMESTAMP())");

/* Check the cache timer, so we don't query the server
everytime we load the page */
if($server->data->cachetime > time() - $settings->cache_reset_time) {

	$query = new StdClass;
	$query->status = $server->data->status;

	/* Decode the details content into an array */
	$server->data->details = json_decode($server->data->details, true);

	$info = array(
				'general' => array(
					'online_players' => array(
						'name' => $language['server']['general_online_players'],
						'icon' => 'user',
						'value' => $server->data->online_players
					),

					'maximum_online_players' => array(
						'name' => $language['server']['general_maximum_online_players'],
						'icon' => 'user',
						'value' => $server->data->maximum_online_players
					),

					'motd' => array(
						'name' => $language['server']['motd'],
						'icon' => 'tasks',
						'value' => $server->data->motd
					),

					'server_version' => array(
						'name' => $language['server']['server_version'],
						'icon' => 'wrench',
						'value' => $server->data->server_version
					)
				),

				'players' => $server->data->details['players'],
			);

	$information = $info;

} else {

	/* Query the server with a specific protocol */
	$query = new Query($server->data->address, $server->data->query_port);
	$information  = $query->query();

	if(!$information) {
		$info = $query->return_false();
	} else {
		$info = $information;
	}


	/* JSON Encode the Players & Details so they can be inserted into the database */
	$details = array(
		'players' => $info['players'],
	);
	$details = json_encode($details);

	/* Update the cache depending on the  status */
	if($query->status){
		$stmt = $database->prepare("UPDATE `servers` SET `status` = ?, `online_players` = ?, `maximum_online_players` = ?, `motd` = ?, `server_version` = ?, `details` = ?, `cachetime` = unix_timestamp() WHERE `server_id` = {$server->data->server_id}");
		$stmt->bind_param('ssssss', $query->status, $info['general']['online_players']['value'], $info['general']['maximum_online_players']['value'], $info['general']['motd']['value'], $info['general']['server_version']['value'], $details);
	} else {
		$stmt = $database->prepare("UPDATE `servers` SET `status` = ?, `online_players` = ?, `maximum_online_players` = ?, `details` = ?, `cachetime` = unix_timestamp() WHERE `server_id` = {$server->data->server_id}");
		$stmt->bind_param('ssss', $query->status, $info['general']['online_players']['value'], $info['general']['maximum_online_players']['value'], $details);
	}
	$stmt->execute();

	/* Decode the MOTD */
	$info['general']['motd']['value'] = minecraft::decodeMotd($info['general']['motd']['value']);
}

initiate_html_columns();

?>

<div id="response" style="display:none;"><?php output_success($language['messages']['success']); ?></div>

<div class="panel panel-default">
	<div class="panel-body">
		<h3 class="no-margin">
			<?php echo $server->data->address . ":" . $server->data->connection_port; ?>
			<span class="pull-right">
				<?php if($query->status && @$information) 
					echo '<span class="glyphicon glyphicon-ok green" style="font-size: 20px;"></span>' . $language['server']['status_online'];
				else 
					echo '<span class="glyphicon glyphicon-remove red" style="font-size: 20px;"></span>' . $language['server']['status_offline'];
				?>
			</span>
		</h3>
	</div>
</div>

<?php include 'template/includes/widgets/server_options.php'; ?>

<div class="panel panel-default">
	<div class="panel-body">

		<ul class="nav nav-pills">
			<li class="active"><a href="#general" data-toggle="tab"><?php echo $language['server']['tab_general']; ?></a></li>
			<?php if($info['players'] !== 'false' && !empty($info['players'][0])) { ?>
			<li>
				<a href="#players" data-toggle="tab"><?php echo $language['server']['tab_players']; ?></a>
			</li>
			<?php } ?>
			<li><a href="#statistics" data-toggle="tab"><?php echo $language['server']['tab_statistics']; ?></a></li>
			<li><a href="#blog_section" data-toggle="tab"><?php echo $language['server']['tab_blog']; ?></a></li>
			<li><a href="#banners" data-toggle="tab"><?php echo $language['server']['tab_banners']; ?></a></li>
		</ul><br />


		<div class="tab-content">
			<div class="tab-pane fade in active" id="general">
					
				<table class="table">
					<tbody>
						<tr>
							<td style="width: 40%;"><span class="glyphicon glyphicon-time"></span> <strong><?php echo $language['server']['general_status']; ?></strong></td>
							<td>
								<?php 
								if($query->status && @$information) 
									echo '<span class="label label-success"><span class="glyphicon glyphicon-ok glyphicon glyphicon-white"></span></span> ' . $language['server']['status_online'];
								else
									echo '<span class="label label-danger"><span class="glyphicon glyphicon-remove glyphicon glyphicon-white"></span></span> ' . $language['server']['status_offline'];
								?>					
							</td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-random"></span> <strong><?php echo $language['server']['general_address']; ?></strong></td>
							<td><?php echo $server->data->address ?></td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-tasks"></span> <strong><?php echo $language['server']['general_connection_port']; ?></strong></td>
							<td><?php echo $server->data->connection_port; ?></td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-bell"></span> <strong><?php echo $language['server']['general_last_check']; ?></strong></td>
							<td class="timeago" title="<?php if($server->data->cachetime > time() - $settings->cache_reset_time) echo @date("c", $server->data->cachetime); else echo date("c", time()); ?>"><?php if($server->data->cachetime > time() - $settings->cache_reset_time) echo @date("c", $server->data->cachetime); else echo date("c", time()); ?></td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-bell"></span> <strong><?php echo $language['server']['general_previous_check']; ?></strong></td>
							<td class="timeago" title="<?php echo @date('c', $server->data->cachetime); ?>"><?php echo @date("c", $server->data->cachetime); ?></td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-cog"></span> <strong><?php echo $language['server']['general_category']; ?></strong></td>
							<td><?php echo '<a href="category/' . $server->category->url . '">' . $server->category->name . '</a>'; ?></td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-tower"></span> <strong><?php echo $language['server']['general_owner']; ?></strong></td>
							<td><?php echo User::get_profile_link($server->data->user_id); ?></td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-arrow-up"></span> <strong><?php echo $language['server']['general_votes']; ?></strong></td>
							<td id="votes_value"><?php echo $server->data->votes; ?></td>
						</tr>	
						<tr>
							<td><span class="glyphicon glyphicon-star"></span> <strong><?php echo $language['server']['general_favorites']; ?></strong></td>
							<td><?php echo $server->data->favorites; ?></td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-upload"></span> <strong><?php echo $language['server']['general_hits']; ?></strong></td>
							<td><?php echo $server->hits; ?></td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-globe"></span> <strong><?php echo $language['server']['general_country']; ?></strong></td>
							<td><?php echo country_check(2, $server->data->country_code); ?> <img src="template/images/locations/<?php echo $server->data->country_code; ?>.png" alt="<?php echo $server->data->country_code; ?>" /></td>
						</tr>
						<?php if(!empty($server->data->website)) { ?>
						<tr>
							<td><span class="glyphicon glyphicon-link"></span> <strong><?php echo $language['forms']['server_website']; ?></strong></td>
							<td><a href="<?php echo $server->data->website; ?>"><?php echo $server->data->website; ?></a></td>
						</tr>
						<?php } ?>

						<?php
						/* Dynamic data for each server */
						foreach($info['general'] as $key => $array) {
							if($array['value'] !== 'false')
								echo '
								<tr>
									<td>
										<span class="glyphicon glyphicon-' . $array['icon'] . '"></span>
										<strong>' . $array['name'] . '</strong>
									</td>
									<td>' . $array['value'] . '</td>
								</tr>
								';
						}
						?>
					</tbody>
				</table>


			</div>

			<?php if($info['players'] !== 'false' && !empty($info['players'][0])) { ?>
			<div class="tab-pane fade" id="players">
				<table class="table table-bordered">
				<thead>
					<tr>
						<?php
						/* Get the available fields */
						$fields = array_keys($info['players'][0]);

						/* Display the fields */
						foreach($fields as $field) {
							echo '<td><strong>' . $field . '</strong></td>';
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					/* Display the players based on the fields */
					foreach($info['players'] as $key => $value) {
						echo '<tr>';
						foreach($fields as $field) {
							echo '<td>' . $value[$field] . '</td>';
						}
						echo '</tr>';
					}
					?>
				</tbody>
				</table>
			</div>

			<?php } ?>

			<!-- Statistics -->
			<div class="tab-pane fade" id="statistics">
				<?php
				$result = $database->query("
					SELECT
						FROM_UNIXTIME(`points`.`timestamp`, '%Y-%m-%d') AS `date`,
						(SELECT COUNT(`points`.`id`) FROM `points` WHERE `type` = 0 AND `server_id` = {$server->data->server_id} AND  FROM_UNIXTIME(`points`.`timestamp`, '%Y-%m-%d') = `date`) AS `hits_count`,
						(SELECT COUNT(`points`.`id`) FROM `points` WHERE `type` = 1 AND `server_id` = {$server->data->server_id} AND FROM_UNIXTIME(`points`.`timestamp`, '%Y-%m-%d') = `date`) AS `votes_count`
					FROM `points`
					WHERE `points`.`timestamp` > UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY) AND `server_id` = {$server->data->server_id}
					GROUP BY `date`
					ORDER BY `date`
					");
				?>
				<script type="text/javascript" src="https://www.google.com/jsapi"></script>
				<script type="text/javascript">
					google.load("visualization", "1", {packages:["corechart"]});
					google.setOnLoadCallback(drawChart);
					function drawChart() {
						var data = google.visualization.arrayToDataTable([
							['Date', 'Hits', 'Votes'],
							<?php
							while($data = $result->fetch_object())
							echo "['" . $data->date . "', " . $data->hits_count . ", " . $data->votes_count . "],";
							?>
						]);

						var options = {
							title: <?php echo '\'' . $language['server']['tab_statistics'] . '\''; ?>
						};

						var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
						chart.draw(data, options);
					}

					$(window).resize(function(){
						drawChart();
					});

					$('[href=#statistics]').on('shown.bs.tab', function() {
						drawChart();
					});
				</script>

				<div id="chart_div" style="width: 100%; height: 500px;"></div>
			</div>


			<!-- Blog Posts -->
			<div class="tab-pane fade" id="blog_section">				

				<div id="blog_posts"></div>

			</div>

			<!-- Banners -->
			<div class="tab-pane fade" id="banners">				
				<?php 
				$linki = $settings->url . "server/" . $server->data->address . ":" . $server->data->connection_port;
				?>
				<a target="_blank" href="<?php echo $linki; ?>"><img src="<?echo $settings->url?>/template/images/votebuttons/vote_orange.png"></a>
				<p> </p>
				<div class="form-group">
					<textarea name="html_code" class="form-control" rows="3" cols="40"><a target="_blank" href="<?php echo $linki ?>"><img src="<?echo $settings->url?>/template/images/votebuttons/vote_orange.png"></a></textarea>
				</div>
				<p> </p>
				<a target="_blank" href="<?php echo $linki ?>"><img src="<?echo $settings->url?>/template/images/votebuttons/vote_blue.png"></a>
				<p> </p>
				<div class="form-group">
					<textarea name="html_code" class="form-control" rows="3" cols="40"><a target="_blank" href="<?php echo $linki ?>"><img src="<?echo $settings->url?>/template/images/votebuttons/vote_blue.png"></a></textarea>
				</div>
				<p> </p>
				<a target="_blank" href="<?php echo $linki ?>"><img src="<?echo $settings->url?>/template/images/votebuttons/vote_for_server_blue.png"></a>
				<p> </p>
				<div class="form-group">
					<textarea name="html_code" class="form-control" rows="3" cols="40"><a target="_blank" href="<?php echo $linki ?>"><img src="<?echo $settings->url?>/template/images/votebuttons/vote_for_server_blue.png"></a></textarea>
				</div>
				<p> </p>
				<a target="_blank" href="<?php echo $linki ?>"><img src="<?echo $settings->url?>/template/images/votebuttons/vote_for_server_orange.png"></a>
				<p> </p>
				<div class="form-group">
					<textarea name="html_code" class="form-control" rows="3" cols="40"><a target="_blank" href="<?php echo $linki ?>"><img src="<?echo $settings->url?>/template/images/votebuttons/vote_for_server_orange.png"></a></textarea>
				</div>
				<p> </p>
				<a target="_blank" href="<?php echo $linki ?>"><img src="<?echo $settings->url?>/template/images/votebuttons/vote.png"></a>
				<p> </p>
				<div class="form-group">
					<textarea name="html_code" class="form-control" rows="3" cols="40"><a target="_blank" href="<?php echo $linki ?>"><img src="<?echo $settings->url?>/template/images/votebuttons/vote.png"></a></textarea>
				</div>

			</div>
		</div>
	</div>
</div>
<!-- Description -->
<?php if(!empty($server->data->description)) { ?>
<div class="panel panel-default">
	<div class="panel-body">
		<h3>
			<?php echo $language['server']['description']; ?>
		</h3>

		<?php echo bbcode($server->data->description); ?>

	</div>
</div>
<?php } ?>

<!-- Video -->
<?php if(!empty($server->data->youtube_id)) { ?>

<div class="panel panel-default">
	<div class="panel-body">
		<h3>
			<?php echo $language['server']['video']; ?>
		</h3>

		<div class="video-container">
			<?php echo youtube_convert($server->data->youtube_id); ?>
		</div>
	</div>
</div>
<?php } ?>

<!-- Comments -->
<div class="panel panel-default">
	<div class="panel-body">
		<h3>
			<?php echo $language['server']['comments']; ?>
		</h3>

		<div id="comments"></div>

	</div>
</div>

<!-- Recaptcha base -->

<script>
$(document).ready(function() {

	/* Initialize the success message variable */
	var SuccessMessage = $('#response').html();

	/* Load the first comments results */
	showMore(0, 'processing/comments_show_more.php', '#comments', '#showMoreComments');

	/* Load the first blog results */
	showMore(0, 'processing/blog_show_more.php', '#blog_posts', '#showMoreBlogPosts');
	
	/* Delete system */
	$('#comments, #blog_posts').on('click', '.delete', function() {
		/* selector = div to be removed */
		var answer = confirm("<?php echo $language['messages']['confirm_delete']; ?>");
		
		if(answer) {
			$('html, body').animate({scrollTop:0},'slow');

			var $div = $(this).closest('.media');
			var reported_id = $(this).attr('data-id');
			var type = $(this).attr('data-type');

			/* Post and get response */
			$.post("processing/process_comments.php", "delete=true&reported_id="+reported_id+"&type="+type, function(data) {

				if(data == "success") {
					$("#response").html(SuccessMessage).fadeIn('slow');
					$div.fadeOut('slow');
				} else {
					$("#response").html(data).fadeIn('slow');
				}
				setTimeout(function() {
					$("#response").fadeOut('slow');
				}, 5000);
			});
		}
	});


});
</script>