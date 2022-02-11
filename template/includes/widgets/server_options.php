<div class="btn-group btn-group-justified" id="server_options">

	<?php
	if(User::logged_in() && $server->data->user_id != $account_user_id) {
		$query_favorite = $database->query("SELECT `id` FROM `favorites` WHERE `user_id` = {$account_user_id} AND `server_id` = {$_SESSION['server_id']}");

		echo '<div class="btn-group">';

		if($query_favorite->num_rows)
			echo '<a class="btn btn-danger favorite"><span class="glyphicon glyphicon-heart"></span> <span class="text">' . $language['server']['sidebar_unfavorite'] . '</span></a>';
			else
			echo '<a class="btn btn-default favorite"><span class="glyphicon glyphicon-heart-empty"></span> <span class="text">' . $language['server']['sidebar_favorite'] . '</span></a>';
		
		echo '</div>';
	} 
	?>

	<div class="btn-group">
		<a class="btn btn-default" data-toggle="modal" data-target="#vote">
			<span class="glyphicon glyphicon-stats"></span> <?php echo $language['server']['sidebar_vote']; ?>
		</a>
	</div>

	<div class="btn-group">
		<a class="btn btn-default" data-toggle="modal" data-target="#comment">
			<span class="glyphicon glyphicon-plus"></span> <?php echo $language['server']['sidebar_add_comment']; ?>
		</a>
	</div>

	<?php if(User::logged_in() && $account_user_id == $server->data->user_id) { ?>
	<div class="btn-group">
		<a class="btn btn-default" data-toggle="modal" data-target="#blog">
			<span class="glyphicon glyphicon-pencil"></span> <?php echo $language['server']['sidebar_add_blog_post']; ?>
		</a>
	</div>
	<?php } ?>

	<div class="btn-group">
		<a class="btn btn-default" onclick="report(<?php echo $server->data->server_id; ?>, 2);">
			<span class="glyphicon glyphicon-exclamation-sign"></span> <?php echo $language['misc']['report']; ?>
		</a>
	</div>

	<?php if(User::logged_in() && $account_user_id == $server->data->user_id) { ?>
	<div class="btn-group">
		<a href="edit-server/<?php echo $server->data->server_id; ?>" class="btn btn-default">
			<span class="glyphicon glyphicon-pencil"></span> <?php echo $language['forms']['server_edit']; ?>
		</a>
	</div>
	<?php } ?>

	<?php if(User::logged_in() && User::is_admin($account_user_id)) { ?>
	<div class="btn-group">
		<a href="admin/edit-server/<?php echo $server->data->server_id; ?>" class="btn btn-default">
			<span class="glyphicon glyphicon-pencil"></span> <?php echo $language['forms']['server_admin_edit']; ?>
		</a>
	</div>
	<?php } ?>

</div>

<br />

<script>
$(document).ready(function() {

	/* Favorite handler */
	$('#server_options').on('click', '.favorite', function() {
		var $div = $(this);

		/* Post and get reponse */
		$.post("processing/process_favorites.php", function(data) {
			$div.fadeOut('fast');
			setTimeout(function() {
				if(data == "favorited") {
					$div.removeClass('btn-default').addClass('btn-danger').children('.text').html("<?php echo $language['server']['sidebar_unfavorite']; ?>");
					$div.children('.glyphicon').removeClass('glyphicon-heart-empty').addClass('glyphicon-heart');
				} else 
				if(data == "unfavorited") {
					$div.removeClass('btn-danger').addClass('btn-default').children('.text').html("<?php echo $language['server']['sidebar_favorite']; ?>");
					$div.children('.glyphicon').removeClass('glyphicon-heart').addClass('glyphicon-heart-empty');
				} else {
					//console.log(data);
					alert("<?php echo $language['messages']['logged_in_action']; ?>");
				}
				$div.fadeIn('fast');
			}, 1500);
			
		});

	});

});
</script>