<?php
include '../core/init.php';

$_POST['limit'] = (int) $_POST['limit'];
$results_limit = 10;

$result = $database->query("SELECT * FROM `comments` WHERE `server_id` = {$_SESSION['server_id']} AND `type` = 1 ORDER BY `id` DESC LIMIT {$_POST['limit']}, {$results_limit}");
if(!$result->num_rows && $_POST['limit'] == "0") output_notice($language['server']['no_blog_posts']);

while($comment = $result->fetch_object()) {
	/* Get user data */
	$user_result = $database->query("SELECT `username`, `name`, `email`, `avatar` FROM `users` WHERE `user_id` = {$comment->user_id}");
	
	$user_data = $user_result->fetch_object();
	if(empty($user_data->avatar)) {
		$avatarThumb = get_gravatar($user_data->email, 100); 
		$avatar 	 = get_gravatar($user_data->email, 250);
	} else {
		$avatarThumb = 'user_data/avatars/thumb/'.$user_data->avatar;
		$avatar = 'user_data/avatars/'.$user_data->avatar;
	}
?>

<div class="media">
	<span class="pull-left">
		<img class="media-object" style="width:64px;height:64px;" data-target="#avatar" src="<?php echo $avatar; ?>" alt="Avatar">
	</span>

	<div class="media-body">
		<h4 class="media-heading">
			<a href="profile/<?php echo $user_data->username; ?>"><?php echo $user_data->name; ?></a>
			
			<?php if(User::logged_in() && User::is_admin($account_user_id) || User::logged_in() && $comment->user_id == $account_user_id) { ?>
			<span class="glyphicon glyphicon-remove pull-right clickable media-span-opacity delete" data-id="<?php echo $comment->id; ?>" data-type="<?php echo $comment->type; ?>"></span>
			<?php } ?>

			<span class="glyphicon glyphicon-exclamation-sign pull-right clickable media-span-opacity" onclick="report(<?php echo $comment->id; ?>, <?php echo $comment->type; ?>);"></span>
		</h4>

		<?php echo filter_banned_words($comment->comment); ?>

		<br />
		<span class="text-muted"><?php echo $comment->date_added; ?></span>

	</div>
</div>
<?php } ?>

<?php if($result->num_rows == $results_limit) { ?>
<div id="showMoreBlogPosts" class="center">
	<button id="showMore" class="btn btn-default" onClick="showMore(<?php echo $_POST['limit'] + $results_limit; ?>, 'processing/blog_show_more.php', '#blog_posts', '#showMoreBlogPosts');"><?php echo $language['misc']['show_more']; ?></button>
</div>
<?php } ?>