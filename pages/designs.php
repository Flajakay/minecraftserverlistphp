<?php
User::check_permission(0);

if(!empty($_POST)) {
	/* Define some variables */
	$allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
	$avatar = (empty($_FILES['avatar']['name']) == false) ? true : false;
	$cover = (empty($_FILES['cover']['name']) == false) ? true : false;

	/* Check for any errors on the avatar image */
	if($avatar == true) {
		$avatar_file_name		= $_FILES['avatar']['name'];
		$avatar_file_extension	= explode('.', $avatar_file_name);
		$avatar_file_extension	= strtolower(end($avatar_file_extension));
		$avatar_file_temp		= $_FILES['avatar']['tmp_name'];
		$avatar_file_size		= $_FILES['avatar']['size'];
		list($avatar_width, $avatar_height)	= getimagesize($avatar_file_temp);

		if(in_array($avatar_file_extension, $allowed_extensions) !== true) {
			$_SESSION['error'][] = $language['errors']['incorrect_file_type'];
		}
		if($avatar_width < 80 || $avatar_height < 80) {
			$_SESSION['error'][] = $language['errors']['avatar_res'];
		}
		if($avatar_file_size > $settings->avatar_max_size) {
			$_SESSION['error'][] = sprintf($language['errors']['image_size'], formatBytes($settings->avatar_max_size));
		}
	}

	/* Check for any errors on the cover image */
	if($cover == true) {
		$cover_file_name		= $_FILES['cover']['name'];
		$cover_file_extension	= explode('.', $cover_file_name);
		$cover_file_extension	= strtolower(end($cover_file_extension));
		$cover_file_temp		= $_FILES['cover']['tmp_name'];
		$cover_file_size		= $_FILES['cover']['size'];
		list($cover_width, $cover_height)	= getimagesize($cover_file_temp);

		if(in_array($cover_file_extension, $allowed_extensions) !== true) {
			$_SESSION['error'][] = $language['errors']['incorrect_file_type'];
		}
		if($cover_width < 1200 || $cover_height < 150) {
			$_SESSION['error'][] = $language['errors']['cover_res'];
		}
		if($cover_file_size > $settings->cover_max_size) {
			$_SESSION['error'][] = sprintf($language['errors']['image_size'], formatBytes($settings->cover_max_size));
		}
	}

	/* If there are no errors continue the updating process */
	if(empty($_SESSION['error'])) {

		/* Avatar update process */
		if($avatar == true) {
			/* Delete current avatar & thumbnail */
			@unlink('user_data/avatars/'.$account->avatar);
			@unlink('user_data/avatars/thumb/'.$account->avatar);

			/* Generate new name for avatar */
			$avatar_new_name = md5(time().rand()) . '.' . $avatar_file_extension;

			/* Make a thumbnail and upload the original */
			if ($avatar_file_extension != 'gif') {
				resize($avatar_file_temp, 'user_data/avatars/thumb/'.$avatar_new_name, '100', '100');
			} else {
				gifResize($avatar_file_temp, 'user_data/avatars/thumb/'.$avatar_new_name, '100', '100');
			}
			move_uploaded_file($avatar_file_temp, "user_data/avatars/".$avatar_new_name);

			/* Execute query */
			$database->query("UPDATE `users` SET `avatar` = '{$avatar_new_name}' WHERE `user_id` = {$account_user_id}");

			
		} 

		/* Cover update process */
		if($cover == true) {
			/* Delete current cover */
			@unlink('user_data/covers/'.$account->cover);

			/* Generate new name for cover */
			$cover_new_name = md5(time().rand()) . "." . $cover_file_extension;
			
			/* Resize */
			if ($cover_file_extension != 'gif') {
				resize($cover_file_temp, 'user_data/covers/'.$cover_new_name, '180', '1200');
			} else {
				gifResize($cover_file_temp, 'user_data/covers/'.$cover_new_name, '180', '1200');
			}
			/* Execute query */
			$database->query("UPDATE `users` SET `cover` = '{$cover_new_name}' WHERE `user_id` = {$account_user_id}");
		}

		/* Set success message and refresh users data */
		$_SESSION['success'][] = $language['messages']['settings_updated'];
		$account = new User($account_user_id); 

	}

	display_notifications();

}

$avatar = (empty($account->avatar)) ? get_gravatar($account->email, 100) : 'user_data/avatars/thumb/' . $account->avatar;

initiate_html_columns();

?>

<h3><?php echo $language['headers']['change_design']; ?></h3>

<form action="" method="post" role="form" enctype="multipart/form-data">
	<div class="form-group">
		<label><?php echo $language['forms']['avatar']; ?></label>
		<br>
		<img src="<?php echo $avatar; ?>" class="img-rounded" alt="Avatar" />
		<input type="file" name="avatar" class="form-control" />
		<p class="help-block"><?php echo $language['forms']['help_avatar']; ?></p>
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['cover']; ?></label>
		<br>
		<img src="user_data/covers/<?php echo $account->cover; ?>" style="max-width: 800px;" class="img-rounded" alt="Avatar" />
		<input type="file" name="cover" class="form-control" />
		<p class="help-block"><?php echo $language['forms']['help_avatar']; ?></p>
	</div>

	<div class="form-group">
		<button type="submit" name="submit" class="btn btn-default col-lg-4"><?php echo $language['forms']['submit']; ?></button><br /><br />
	</div>
</form>