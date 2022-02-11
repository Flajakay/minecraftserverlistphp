<?php
User::check_permission(0);

if(!empty($_POST)) {
	/* Clean some posted variables */
	$_POST['name']		= filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$_POST['email']		= filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
	$_POST['website']	= filter_var($_POST['website'], FILTER_VALIDATE_URL);
	$_POST['location']	= filter_var($_POST['location'], FILTER_SANITIZE_STRING);
	$_POST['about']		= filter_var($_POST['about'], FILTER_SANITIZE_STRING);
	$_POST['facebook']	= filter_var($_POST['facebook'], FILTER_SANITIZE_STRING);
	$_POST['twitter']	= filter_var($_POST['twitter'], FILTER_SANITIZE_STRING);
	$_POST['googleplus']= filter_var($_POST['googleplus'], FILTER_SANITIZE_STRING);
	$_POST['private']	= (isset($_POST['private'])) ? 1 : 0;

	/* Check for any errors */
	if(strlen($_POST['name']) < 3 || strlen($_POST['name']) > 32) {
		$_SESSION['error'][] = $language['errors']['name_length'];
	}
	if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) == false) {
		$_SESSION['error'][] = $language['errors']['invalid_email'];
	}
	if(User::x_exists('email', $_POST['email']) == true && $_POST['email'] !== $account->email) {
		$_SESSION['error'][] = $language['errors']['email_used'];
	}
	if(strlen($_POST['about']) > 128) {
		$_SESSION['error'][] = $language['errors']['about_too_big'];
	}
	if(strlen($_POST['location']) > 64) {
		$_SESSION['error'][] = $language['errors']['location_too_big'];
	}

	/* If there are no errors continue the updating process */
	if(empty($_SESSION['error'])) {
		/* Prepare the statement and execute query */
		$stmt = $database->prepare("UPDATE `users` SET `name` = ?, `email` = ?, `website` = ?, `location` = ?, `about` = ?, `facebook` = ?, `twitter` = ?, `googleplus` = ?, `private` = ? WHERE `user_id` = {$account_user_id}");
		$stmt->bind_param('sssssssss', $_POST['name'], $_POST['email'], $_POST['website'], $_POST['location'], $_POST['about'], $_POST['facebook'], $_POST['twitter'], $_POST['googleplus'], $_POST['private']);
		$stmt->execute(); 
		$stmt->close();

		/* Set the success message & Refresh users data */
		$_SESSION['success'][] = $language['messages']['settings_updated'];
		$account = new User($account_user_id); 
	}
	
	display_notifications();
	
}

initiate_html_columns();

?>

<h3><?php echo $language['headers']['change_details']; ?></h3>

<form action="" method="post" role="form">
	<div class="form-group">
		<label><?php echo $language['forms']['name']; ?></label>
		<input type="text" name="name" class="form-control" value="<?php echo $account->name; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['email']; ?></label>
		<input type="text" name="email" class="form-control" value="<?php echo $account->email; ?>" />
	</div>

	<div class="form-group">
    	<label><?php echo $language['forms']['website']; ?></label>
    	<input type="text" name="website" class="form-control" value="<?php echo $account->website; ?>" />
    </div>

    <div class="form-group">
    	<label><?php echo $language['forms']['location']; ?></label>
    	<input type="text" name="location" class="form-control" value="<?php echo $account->location; ?>" />
    </div>

    <div class="form-group">
   		<label><?php echo $language['forms']['about']; ?></label>
    	<input type="text" name="about" class="form-control" value="<?php echo $account->about; ?>" />
    </div>

    <hr />

    <h3><?php echo $language['headers']['social_data']; ?></h3>
    <p class="help-block"><?php echo $language['forms']['social_help']; ?></p>

	<div class="form-group">
   		<label><?php echo $language['forms']['facebook']; ?></label>
    	<input type="text" name="facebook" class="form-control" value="<?php echo $account->facebook; ?>" />
    </div>

    <div class="form-group">
   		<label><?php echo $language['forms']['twitter']; ?></label>
    	<input type="text" name="twitter" class="form-control" value="<?php echo $account->twitter; ?>" />
    </div>

    <div class="form-group">
   		<label><?php echo $language['forms']['googleplus']; ?></label>
    	<input type="text" name="googleplus" class="form-control" value="<?php echo $account->googleplus; ?>" />
    </div>

	<hr />

	<div class="checkbox">
		<label>
			<?php echo $language['forms']['private_profile']; ?><input type="checkbox" name="private" <?php if($account->private == 1) echo 'checked'; ?>>
		</label>
	</div>

	<div class="form-group">
		<button type="submit" name="submit" class="btn btn-default col-lg-4"><?php echo $language['forms']['submit']; ?></button><br /><br />
	</div>


</form>