<?php
error_reporting(0);
$errors = array();
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Installation</title>
		    <meta charset="UTF-8">
			<link href="template/css/bootstrap.min.css" rel="stylesheet" media="screen">
			<script src="template/js/jquery.js"></script>
		    <script src="template/js/bootstrap.min.js"></script>
	</head>
	<body>
		<div class="container">
			<h2>Installation</h2>

			<div class="panel panel-default">
				<div class="panel-body">
					<?php
					if(!empty($_POST)) {
						/* Define some variables */
						$database_server 	= $_POST['database_server'];
						$database_user	 	= $_POST['database_user'];
						$database_password  = $_POST['database_password'];
						$database_name		= $_POST['database_name'];

						$database = new mysqli($database_server, $database_user, $database_password, $database_name);
						$connect_file = "core/database/connect.php";

						/* Check for any errors */
						if($database->connect_error) {
							$errors[] = 'Failed to connect to database!';
						}
						if(!is_readable($connect_file) || !is_writable($connect_file)) {
							$errors[] = '<u><strong>core/database/connect.php</strong></u> does not have CHMOD 777';
						}
						if(filter_var($_POST['settings_url'], FILTER_VALIDATE_URL) == false) {
							$errors[] = 'Wrong link!';
						}
/* 						if(version_compare(phpversion(), '7.1', '=')) {
							$errors[] = 'PHP Version != 7.1!';
						} */

						if(empty($errors)) {
							/* add "/" if the user didnt added it */
							if(substr($_POST['settings_url'], -1) !== "/") {
								$_POST['settings_url'] .= "/";
							}

							/* Define the connect.php content */
							$connect_content = <<<PHP
<?php
// Connection parameters
\$DatabaseServer = "$database_server";
\$DatabaseUser   = "$database_user";
\$DatabasePass   = "$database_password";
\$DatabaseName   = "$database_name";

// Connecting to the database
\$database = new mysqli(\$DatabaseServer, \$DatabaseUser, \$DatabasePass, \$DatabaseName);

?>
PHP;
							/* open, write and close */
							$command = fopen($connect_file, w);
							fwrite($command, $connect_content);
							fclose($command);

							/* Add the tables to the database */
							$database->query("
								CREATE TABLE IF NOT EXISTS `categories` (
								  `category_id` int(11) NOT NULL AUTO_INCREMENT,
								  `parent_id` int(11) NOT NULL,
								  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `description` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
								  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `url` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `image` varchar(38) COLLATE utf8_unicode_ci NOT NULL,
								  PRIMARY KEY (`category_id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
							");

							$database->query("
								CREATE TABLE IF NOT EXISTS `comments` (
								  `id` int(11) NOT NULL AUTO_INCREMENT,
								  `server_id` int(11) NOT NULL,
								  `user_id` int(11) NOT NULL,
								  `type` int(11) NOT NULL DEFAULT '0',
								  `comment` varchar(512) NOT NULL,
								  `date_added` varchar(32) NOT NULL,
								  PRIMARY KEY (`id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
							");

							$database->query("
								CREATE TABLE IF NOT EXISTS `favorites` (
								  `id` int(11) NOT NULL AUTO_INCREMENT,
								  `user_id` int(11) NOT NULL,
								  `server_id` int(11) NOT NULL,
								  PRIMARY KEY (`id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
							");

							$database->query("
								CREATE TABLE IF NOT EXISTS `points` (
								  `id` int(11) NOT NULL AUTO_INCREMENT,
								  `type` int(11) NOT NULL,
								  `server_id` int(11) NOT NULL,
								  `ip` varchar(32) NOT NULL,
								  `timestamp` int(11) NOT NULL,
								  PRIMARY KEY (`id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
							");
							$database->query("
								CREATE TABLE IF NOT EXISTS `reports` (
								  `id` int(11) NOT NULL AUTO_INCREMENT,
								  `user_id` int(11) NOT NULL,
								  `type` int(11) NOT NULL,
								  `reported_id` int(11) NOT NULL,
								  `message` varchar(512) NOT NULL,
								  `date` varchar(32) NOT NULL,
								  PRIMARY KEY (`id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
							");
							$database->query("
								CREATE TABLE IF NOT EXISTS `servers` (
								  `server_id` int(11) NOT NULL AUTO_INCREMENT,
								  `user_id` int(11) NOT NULL,
								  `category_id` int(11) NOT NULL,
								  `address` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `connection_port` int(11) NOT NULL,
								  `query_port` int(11) NOT NULL,
								  `private` int(11) NOT NULL,
								  `active` int(11) NOT NULL,
								  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `description` varchar(2560) COLLATE utf8_unicode_ci NOT NULL,
								  `image` varchar(38) COLLATE utf8_unicode_ci NOT NULL,
								  `website` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
								  `youtube_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `date_added` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `highlight` int(11) NOT NULL,
								  `votes` int(11) NOT NULL,
								  `favorites` int(11) NOT NULL,
								  `status` int(11) NOT NULL,
								  `online_players` int(11) NOT NULL,
								  `maximum_online_players` int(11) NOT NULL,
								  `motd` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
 								  `server_version` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
								  `details` mediumtext COLLATE utf8_unicode_ci NOT NULL,
								  `custom` varchar(5120) COLLATE utf8_unicode_ci NOT NULL,
								  `cachetime` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
								  PRIMARY KEY (`server_id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
							");
							$database->query("
								CREATE TABLE IF NOT EXISTS `settings` (
								  `id` int(11) NOT NULL,
								  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `url` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `meta_description` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
								  `banned_words` varchar(2560) COLLATE utf8_unicode_ci NOT NULL,
								  `analytics_code` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `email_confirmation` int(11) NOT NULL DEFAULT '0',
								  `servers_pagination` int(11) NOT NULL DEFAULT '10',
								  `avatar_max_size` int(11) NOT NULL DEFAULT '250000',
								  `cover_max_size` int(11) NOT NULL DEFAULT '300000',
								  `contact_email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
								  `cache_reset_time` int(11) NOT NULL,
								  `display_offline_servers` int(11) NOT NULL DEFAULT '1',
								  `new_servers_visibility` int(11) NOT NULL,
								  `top_ads` varchar(2560) COLLATE utf8_unicode_ci NOT NULL,
								  `bottom_ads` varchar(2560) COLLATE utf8_unicode_ci NOT NULL,
								  `side_ads` varchar(2560) COLLATE utf8_unicode_ci NOT NULL,
								  `public_key` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `private_key` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `paypal_email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
								  `payment_currency` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
								  `maximum_slots` int(11) NOT NULL,
								  `per_day_cost` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
								  `minimum_days` int(11) NOT NULL,
								  `maximum_days` int(11) NOT NULL,
								  `facebook` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `twitter` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `googleplus` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `smtphost` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `smtpport` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `smtpuser` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `smtppass` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  PRIMARY KEY (`id`)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
							");
							$database->query("
								CREATE TABLE IF NOT EXISTS `users` (
								  `user_id` int(11) NOT NULL AUTO_INCREMENT,
								  `username` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `password` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
								  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
								  `email_activation_code` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `lost_password_code` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `about` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
								  `website` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `location` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `avatar` varchar(38) COLLATE utf8_unicode_ci NOT NULL,
								  `cover` varchar(38) COLLATE utf8_unicode_ci NOT NULL,
								  `facebook` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `twitter` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `googleplus` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
								  `type` int(11) NOT NULL DEFAULT '0',
								  `active` int(11) NOT NULL DEFAULT '0',
								  `private` int(11) NOT NULL DEFAULT '0',
								  `ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `date` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  `last_activity` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								  PRIMARY KEY (`user_id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
							");
							$database->query("
								CREATE TABLE IF NOT EXISTS `payments` (
								  `id` int(11) NOT NULL AUTO_INCREMENT,
								  `user_id` int(11) NOT NULL,
								  `server_id` int(11) NOT NULL,
								  `highlighted_days` int(11) NOT NULL,
								  `date` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
								  `revenue` int(11) NOT NULL,
								  `email` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
								  PRIMARY KEY (`id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
							");
							$database->query("
								INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `email_activation_code`, `lost_password_code`, `name`, `about`, `website`, `location`, `avatar`, `cover`, `facebook`, `twitter`, `googleplus`, `type`, `active`, `private`, `ip`, `date`, `last_activity`) VALUES
								(1, 'admin', '365a4a0e748d76932d03cd46e62e4c3b4ca426c00c87bdf6ca9e692a0dc797224d151c3c9156a57c624e5bef533f0af9b8059726987c7929281a6b7acf7af8d4', 'admin@admin.com', '', '0', 'Admin', '', 'http://', '', '', '', 'test', 'test', 'test', 2, 1, 1, '-hax-', '-hax-', '')
							");
							$database->query("
								INSERT INTO `settings` (`id`, `title`, `url`, `meta_description`, `banned_words`, `analytics_code`, `email_confirmation`, `servers_pagination`, `avatar_max_size`, `cover_max_size`, `contact_email`, `cache_reset_time`, `display_offline_servers`, `new_servers_visibility`, `top_ads`, `bottom_ads`, `side_ads`, `public_key`, `private_key`, `paypal_email`, `payment_currency`, `maximum_slots`, `per_day_cost`, `minimum_days`, `maximum_days`, `facebook`, `twitter`, `googleplus`, `smtphost`, `smtpport`, `smtpuser`, `smtppass`) VALUES
								(1, '" . $_POST['settings_title'] . "', '" . $_POST['settings_url'] . "', '', '', '', 1, 15, 1000000, 1000000, 'no-reply@domain.com', 600, 1, 0, '', '', '', '6Le43tISAAAAADni-XsMzvEaStTluh6vSFmbhpfC', '6Le43tISAAAAANP9dDZb-ConEQRFxdyTpNFo09Q3', '', '', 0, '', 0, 0, '', '', '', '', '', '', '');
							");

							/* Display a success message */
							echo '<div class="alert alert-success"><strong>Installation completed!</strong>Now delete install.php!</div>';
						} else {

							/* Display all the errors if needed */
							foreach($errors as $nr => $error) {
								echo '<div class="alert alert-warning">' . $error . '</div>';
							}

							echo '<a href="install.php"><button class="btn btn-primary">Back</button></a>';
						}
					} else {
					?>
					<div class="alert alert-info">Make sure file <u><strong>core/database/connect.php</strong></u> has CHMOD 777 !</div>

					<form action="" method="post" role="form">
						<div class="form-group">
							<label> Database server </label>
							<input type="text" class="form-control" name="database_server" value="localhost" />
						</div>
						<div class="form-group">
							<label> User </label>
							<input type="text" class="form-control" name="database_user" />
						</div>
						<div class="form-group">
							<label> Password </label>
							<input type="text" class="form-control" name="database_password" />
						</div>
						<div class="form-group">
							<label> Database name </label>
							<input type="text" class="form-control" name="database_name" />
						</div>

						<div class="form-group">
							<label> URL </label>
							<p class="help-block">e.g: http://domain.com/directory/</p>
							<input type="text" class="form-control" name="settings_url" />
						</div>
						<div class="form-group">
							<label> Title </label>
							<input type="text" class="form-control" name="settings_title" />
						</div>

						<div class="form-group">
							<button type="submit" name="submit" class="btn btn-primary col-lg-4">Done</button>
						</div>
					</form>
					<?php } ?>
				</div>

				<div class="panel-footer">
					<span>Developed by: <a href="dev.mine-craft.net">Flajakay</a></span>
				</div>

			</div>

		</div>
	</body>
</html>