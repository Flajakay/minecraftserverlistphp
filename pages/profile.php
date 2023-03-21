<?php
/* Check if user exists, if not -> display error */
if(!$user_exists) {
	$_SESSION['error'][] = $language['errors']['user_not_found'];
} else
/* Check if profile is private */
if(
	($profile_account->private && !User::logged_in()) ||
	($profile_account->private && User::logged_in() && $account_user_id != $profile_user_id)
) {
	/* Set error message and redirect */
	$_SESSION['error'][] = $language['errors']['profile_private'];
	User::get_back('index');
}


if(empty($profile_account->avatar)) {
	$avatarThumb = get_gravatar($profile_account->email, 100); 
	$avatar 	 = get_gravatar($profile_account->email, 250);
} else {
	$avatarThumb = 'user_data/avatars/thumb/'.$profile_account->avatar;
	$avatar = 'user_data/avatars/'.$profile_account->avatar;
}
initiate_html_columns();

?>
<div class="panel panel-default">
 	<div class="panel-body" style="position:relative; <?php if(!empty($profile_account->cover)) echo 'max-height:180px;background: url(\'/user_data/covers/' . $profile_account->cover . '\');"'; ?>>

 		<table class="table-fixed-full" cellpadding="5">
 			<tr>
 				<td class="hidden-xs" rowspan="2" style="width: 15%;text-align:center;">
 					<img data-toggle="modal" data-target="#avatar" src="<?php echo $avatarThumb; ?>"  alt="Avatar" class="clickable img-circle" />
 				</td>
 				<td>
 					<h1 class="shadow white"><?php echo $profile_account->name; ?></h1>
 					
				 	<div class="navbar-social pull-right">
			 			<?php 
			 			if(!empty($profile_account->facebook))
						echo '&nbsp;<a href="http://facebook.com/' . $profile_account->facebook . '"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-facebook fa-stack-1x fa-inverse"></i></span></a>';

			 			if(!empty($profile_account->twitter))
						echo '<a href="http://twitter.com/' . $profile_account->twitter . '"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-twitter fa-stack-1x fa-inverse"></i></span></a>';
						
						if(!empty($profile_account->googleplus))
						echo '&nbsp;<a href="http://plus.google.com/' . $profile_account->googleplus . '"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-google-plus fa-stack-1x fa-inverse"></i></span></a>';
						
						
						?>
			 		</div>
 				</td>
 			</tr>
 			<tr style="vertical-align: top;">
 				<td class="shadow white">
 					<?php echo $profile_account->about; ?>
 					<?php if(!empty($profile_account->website)) echo '<br /><span class="glyphicon glyphicon-globe"></span> <a href="' . $profile_account->website . '">' . $profile_account->website . '</a>'; ?>
 					<?php if(!empty($profile_account->location)) echo '<span class="glyphicon glyphicon-home"></span> ' . $profile_account->location ; ?>
 				</td>
 			</tr>
 		</table>
 		<?php if(User::logged_in() && $account_user_id != $profile_user_id) { ?>
 		<div class="followW" style="position: absolute;right: 10px;bottom: 10px;">
 			<?php if(User::is_admin($account_user_id)) profile_admin_buttons($profile_user_id, $profile_account->active, $token->hash); ?>
		</div>
		<?php } ?>
 	</div>
 </div>

<?php
/* Initiate the servers list class */
$servers = new Servers;

/* Set a custom no servers message */
$servers->no_servers = $language['messages']['no_my_servers'];

/* Add additional condition to show only the users servers */
$servers->additional_where("AND `user_id` = {$profile_user_id} AND `active` = 1 AND `private` = 0");

/* Remove pagination */
$servers->remove_pagination();

/* Try and display the server list */
$servers->display();

/* Display any notification if there are any ( no servers ) */
display_notifications();
?>


<div class="modal fade" id="avatar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-body center">
				<img src="<?php echo $avatar; ?>" alt="Avatar" />
			</div>

		</div>
	</div>
</div>
