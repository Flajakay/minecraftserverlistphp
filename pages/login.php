<?php
User::logged_in_redirect();


if(!empty($_POST)) {
	/* Clean username and encrypt the password */
	$_POST['username'] = filter_var($_POST['username'], FILTER_SANITIZE_STRING);;
	$_POST['password'] = User::encrypt_password($_POST['username'], $_POST['password']);

	/* Check for any errors */
	if(empty($_POST['username']) || empty($_POST['password'])) {
		$_SESSION['error'][] = $language['errors']['empty_fields'];
	}
	if(User::x_exists('username', $_POST['username']) == false) {
		$_SESSION['error'][] = sprintf($language['errors']['user_doesnt_exist'], $_POST['username']);
	}
	if(User::user_active($_POST['username']) == false) {
		$_SESSION['error'][] = $language['errors']['user_not_active'];
	}
	if(User::login($_POST['username'], $_POST['password']) == false) {
		$_SESSION['error'][] = $language['errors']['login_failed'];
	}

	if(!empty($_POST) && empty($_SESSION['error'])) {
		/* If remember me is checked, log the user with cookies for 30 days else, remember just with a session */
		if(isset($_POST['rememberme'])) {
			setcookie("username", $_POST['username'], time()+60*60*24*30);
			setcookie("password", $_POST['password'], time()+60*60*24*30);
			setcookie("user_id", User::login($_POST['username'], $_POST['password']), time()+60*60*24*30);
		}else{
			$_SESSION['user_id'] = User::login($_POST['username'], $_POST['password']);
		}
		$_SESSION['info'][] = $language['messages']['loggedin'];
		redirect();
	}

	display_notifications();

}

initiate_html_columns();

?>

<h3><?php echo $language['headers']['login']; ?></h3>

<form action="" method="post" role="form">
	<div class="form-group">
		<label><?php echo $language['forms']['username']; ?></label>
		<input type="text" name="username" class="form-control" placeholder="<?php echo $language['forms']['username']; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['password']; ?></label>
		<input type="password" name="password" class="form-control" placeholder="<?php echo $language['forms']['password']; ?>" />
	</div>

	<div class="checkbox">
		<label>
			<?php echo $language['misc']['remember_me']; ?><input type="checkbox" name="rememberme">
		</label>
    </div>
	
	<div class="form-group">
		<button type="submit" name="submit" class="btn btn-default col-lg-4"><?php echo $language['forms']['signin']; ?></button><br /><br />
	</div>

	<a href="lost-password"  role="button"><?php echo $language['forms']['lost_password']; ?></a><br />

</form>
