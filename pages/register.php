<?php
User::logged_in_redirect();

if(!empty($_POST)) {
	/* Clean some posted variables */
	$_POST['username']	= filter_var($_POST['username'], FILTER_SANITIZE_STRING);
	$_POST['name']		= filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$_POST['email']		= filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

	/* Define some variables */
	$fields = array('username', 'name', 'email' ,'password', 'repeat_password');

	/* Check for any errors */
	foreach($_POST as $key=>$value) {
		if(empty($value) && in_array($key, $fields) == true) {
			$_SESSION['error'][] = $language['errors']['fields_required'];
			break 1;
		}
	}
	if(strlen($_POST['username']) > 32 || strlen($_POST['username']) < 3) {
		$_SESSION['error'][] = $language['errors']['username_length'];
	}
	if(strlen($_POST['name']) < 3 || strlen($_POST['name']) > 32) {
		$_SESSION['error'][] = $language['errors']['name_length'];
	}
	if(User::x_exists('username', $_POST['username'])) {
		$_SESSION['error'][] = sprintf($language['errors']['user_exists'], $_POST['username']);
	}
	if(User::x_exists('email', $_POST['email'])) {
		$_SESSION['error'][] = $language['errors']['email_used'];
	}
	if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) == false) {
		$_SESSION['error'][] = $language['errors']['invalid_email'];
	}
	if(strlen(trim($_POST['password'])) < 6) {
        $_SESSION['error'][] = $language['errors']['password_too_short'];
    }
    if($_POST['password'] !== $_POST['repeat_password']) {
        $_SESSION['error'][] = $language['errors']['passwords_doesnt_match'];
    }


	/* If there are no errors continue the registering process */
	if(empty($_SESSION['error'])) {
		/* Define some needed variables */ 
	    $password 	= User::encrypt_password($_POST['username'], $_POST['password']);
	    $active 	= ($settings->email_confirmation == 0) ? "1" : "0";
	    $email_code = md5($_POST['email']);
		$date = new DateTime();
		$date = $date->format('Y-m-d H:i:s');
		$param1 = $_POST['username'];
		$param2 = $_POST['email'];
		$param3 = $_POST['name'];
		/* Add the user to the database */

//		$stmt = $database->prepare("INSERT INTO `users` (`username`, `password`, `email`, `email_activation_code`, `name`, `active`, `ip`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt = $database->prepare("INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `email_activation_code`, `lost_password_code`, `name`, `about`, `website`, `location`, `avatar`, `cover`, `facebook`, `twitter`, `googleplus`, `type`, `active`, `private`, `ip`, `date`, `last_activity`) 
		VALUES (NULL, '$param1', '$password', '$param2', '$email_code', '', '$param3', '', '', '', '', '', '', '', '', '0', '0', '0', '', '$date', '')");
		
//		$stmt->bind_param('ssssssss', $_POST['username'], $password, $_POST['email'], $email_code, $_POST['name'], $active, $_SERVER['REMOTE_ADDR'], $date);
		$stmt->execute();
		$stmt->close();

		/* If active = 1 then login the user, else send the user an activation email */
		if($active == "1") {
			$_SESSION['user_id'] = User::login($_POST['username'], $password);
			redirect("status/loggedin");
		} else {
			$_SESSION['success'][] = $language['messages']['registered_successfuly'];
			sendmail($_POST['email'], $settings->contact_email, $language['misc']['activate_account'], sprintf($language['misc']['activation_email'], $settings->url, $_POST['email'], $email_code));
			//printf($language['misc']['activation_email'], $settings->url, $_POST['email'], $email_code);
		}
	}

	display_notifications();

}

initiate_html_columns();

?>

<h3><?php echo $language['headers']['register']; ?></h3>

<form action="" method="post" role="form">
	<div class="form-group">
		<label><?php echo $language['forms']['username']; ?></label>
		<input type="text" name="username" class="form-control" placeholder="<?php echo $language['forms']['username']; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['name']; ?></label>
		<input type="text" name="name" class="form-control" placeholder="<?php echo $language['forms']['name']; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['email']; ?></label>
		<input type="text" name="email" class="form-control" placeholder="<?php echo $language['forms']['email']; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['password']; ?></label>
		<input type="password" name="password" class="form-control" placeholder="<?php echo $language['forms']['password']; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['repeat_password']; ?></label>
		<input type="password" name="repeat_password" class="form-control" placeholder="<?php echo $language['forms']['repeat_password']; ?>" />
	</div>


	<div class="form-group">
		<button type="submit" name="submit" class="btn btn-default col-lg-4"><?php echo $language['forms']['submit']; ?></button><br /><br />
	</div>
	<a href="resend-activation" role="button"><?php echo $language['forms']['resend_activation']; ?></a>

</form>
