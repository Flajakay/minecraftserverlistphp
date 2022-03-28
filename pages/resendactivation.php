<?php
User::logged_in_redirect();

if(!empty($_POST)) {
	/* Clean the posted variable */
	$_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);


	/* Check for any errors */
	if(!User::x_exists('email', $_POST['email'])) {
		$_SESSION['error'][] = $language['errors']['email_doesnt_exist'];
	} else 
	if(User::user_active(User::x_to_y('email', 'username', $_POST['email']))) {
		$_SESSION['error'][] = $language['errors']['user_already_active'];
	}
	/* If there are no errors, resend the activation link */
	if(empty($_SESSION['error'])) {
		/* Define some variables */
		$user_id 	= User::x_to_y('email', 'user_id', $_POST['email']);
		$email_code = md5($_POST['email'] + microtime());

		/* Update the current activation email */
		$database->query("UPDATE `users` SET `email_activation_code` = '{$email_code}' WHERE `user_id` = {$user_id}");

		/* Send the email */
		sendmail($_POST['email'], $settings->contact_email, $language['misc']['activate_account'], sprintf($language['misc']['activation_email'], $settings->url, $_POST['email'], $email_code));
		//printf($language['misc']['activation_email'], $settings->url, $_POST['email'], $email_code);

		/* Store success message */
		$_SESSION['success'][] = $language['messages']['resendactivation'];
	}

	display_notifications();
	
}

initiate_html_columns();

?>

<h3><?php echo $language['headers']['resendactivation']; ?></h3>

<form action="" method="post" role="form">
	<div class="form-group">
		<label><?php echo $language['forms']['email']; ?></label>
		<input type="text" name="email" class="form-control" />
	</div>

	<div class="form-group">
		<button type="submit" name="submit" class="btn btn-default col-lg-4"><?php echo $language['forms']['submit']; ?></button><br /><br />
	</div>

</form>