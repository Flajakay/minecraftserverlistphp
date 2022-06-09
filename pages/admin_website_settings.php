<?php
User::check_permission(2);

if(!empty($_POST)) {
	/* Define some variables */
	$_POST['title']				 		= filter_var($_POST['title'], FILTER_SANITIZE_STRING);
	$_POST['meta_description']	 		= filter_var($_POST['meta_description'], FILTER_SANITIZE_STRING);
	$_POST['analytics_code']	 		= filter_var($_POST['analytics_code'], FILTER_SANITIZE_STRING);
	$_POST['banned_words']		 		= filter_var($_POST['banned_words'], FILTER_SANITIZE_STRING);
	$_POST['public_key']				= filter_var($_POST['public_key'], FILTER_SANITIZE_STRING);
	$_POST['private_key']				= filter_var($_POST['private_key'], FILTER_SANITIZE_STRING);
	$_POST['contact_email']				= filter_var($_POST['contact_email'], FILTER_SANITIZE_STRING);
	$_POST['servers_pagination']		= (int)$_POST['servers_pagination'];
	$_POST['avatar_max_size']	 		= (int)$_POST['avatar_max_size'];
	$_POST['cover_max_size']	 		= (int)$_POST['cover_max_size'];
	$_POST['email_confirmation']	 	= (isset($_POST['email_confirmation'])) ? 1 : 0;
	$_POST['cache_reset_time']			= (int)$_POST['cache_reset_time'];
	$_POST['display_offline_servers'] 	= (isset($_POST['display_offline_servers'])) ? 1 : 0;
	$_POST['new_servers_visibility'] 	= (isset($_POST['new_servers_visibility'])) ? 1 : 0;
	$_POST['paypal_email']				= filter_var($_POST['paypal_email'], FILTER_SANITIZE_STRING);
	$_POST['payment_currency']			= filter_var($_POST['payment_currency'], FILTER_SANITIZE_STRING);
	$_POST['maximum_slots']				= (int)$_POST['maximum_slots'];
	$_POST['per_day_cost']				= (is_numeric($_POST['per_day_cost'])) ? $_POST['per_day_cost'] : '1';
	$_POST['minimum_days']				= (int)$_POST['minimum_days'];
	$_POST['maximum_days']				= (int)$_POST['maximum_days'];
	$_POST['facebook']					= filter_var($_POST['facebook'], FILTER_SANITIZE_STRING);
	$_POST['twitter']					= filter_var($_POST['twitter'], FILTER_SANITIZE_STRING);
	$_POST['googleplus']				= filter_var($_POST['googleplus'], FILTER_SANITIZE_STRING);
	$_POST['smtp_host']					= filter_var($_POST['smtp_host'], FILTER_SANITIZE_STRING);
	$_POST['smtp_port']					= filter_var($_POST['smtp_port'], FILTER_SANITIZE_STRING);
	$_POST['smtp_user']					= filter_var($_POST['smtp_user'], FILTER_SANITIZE_STRING);
	$_POST['smtp_pass']					= filter_var($_POST['smtp_pass'], FILTER_SANITIZE_STRING);

	/* Prepare the statement and execute query */
	
	$stmt = $database->prepare("UPDATE `settings` SET `title` = ?, `meta_description` = ?, `analytics_code` = ?, `banned_words` = ?, `email_confirmation` = ?, `servers_pagination` = ?, `avatar_max_size` = ?, `cover_max_size` = ?, `contact_email` = ?, `cache_reset_time` = ?, `display_offline_servers` = ?, `new_servers_visibility` = ?, `top_ads` = ?, `bottom_ads` = ?, `side_ads` = ?, `public_key` = ?, `private_key` = ?, `paypal_email` = ?, `payment_currency` = ?, `maximum_slots` = ?, `per_day_cost` = ?, `minimum_days` = ?, `maximum_days` = ?, `facebook` = ?, `twitter` = ?, `googleplus` = ?, `smtphost` = ?, `smtpport` = ?, `smtpuser` = ?, `smtppass` = ?,`smtpsecure` = ?  WHERE `id` = 1");
	$stmt->bind_param('sssssssssssssssssssssssssssssss', $_POST['title'], $_POST['meta_description'], $_POST['analytics_code'], $_POST['banned_words'], $_POST['email_confirmation'], $_POST['servers_pagination'], $_POST['avatar_max_size'], $_POST['cover_max_size'], $_POST['contact_email'], $_POST['cache_reset_time'], $_POST['display_offline_servers'], $_POST['new_servers_visibility'], $_POST['top_ads'], $_POST['bottom_ads'], $_POST['side_ads'], $_POST['public_key'], $_POST['private_key'], $_POST['paypal_email'], $_POST['payment_currency'], $_POST['maximum_slots'], $_POST['per_day_cost'], $_POST['minimum_days'], $_POST['maximum_days'], $_POST['facebook'], $_POST['twitter'], $_POST['googleplus'], $_POST['smtp_host'], $_POST['smtp_port'], $_POST['smtp_user'], $_POST['smtp_pass'], $_POST['smtp_secure']);
	$stmt->execute();  
	$stmt->close();

	/* Set message & Redirect */
	$_SESSION['success'][] = $language['messages']['settings_updated'];
	redirect("admin/website-settings");
	
}

initiate_html_columns();

?>
<h3><?php echo $language['headers']['website_settings']; ?></h3>

<ul class="nav nav-pills">
	<li class="active"><a href="#main" data-toggle="tab"><?php echo $language['forms']['main_settings']; ?></a></li>
	<li><a href="#servers" data-toggle="tab"><?php echo $language['forms']['servers_settings']; ?></a></li>
	<li><a href="#ads" data-toggle="tab"><?php echo $language['forms']['ads_settings']; ?></a></li>
	<li><a href="#automatic_payment_system" data-toggle="tab"><?php echo $language['forms']['automatic_payment_system']; ?></a></li>
	<li><a href="#social" data-toggle="tab"><?php echo $language['forms']['social_settings']; ?></a></li>
	<li><a href="#smtp" data-toggle="tab"><?php echo $language['forms']['smtp_settings']; ?></a></li>
</ul>


<form action="" method="post" role="form">
	<div class="tab-content">
 		<div class="tab-pane fade in active" id="main">
			<div class="form-group">
				<label><?php echo $language['forms']['settings_title']; ?></label>
				<input type="text" name="title" class="form-control" value="<?php echo $settings->title; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_meta_description']; ?></label>
				<input type="text" name="meta_description" class="form-control" value="<?php echo $settings->meta_description; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_analytics_code']; ?></label>
				<p class="help-block"><?php echo $language['forms']['settings_analytics_code_help']; ?></p>
				<input type="text" name="analytics_code" class="form-control" value="<?php echo $settings->analytics_code; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_banned_words']; ?></label>
				<p class="help-block"><?php echo $language['forms']['settings_banned_words_help']; ?></p>
				<input type="text" name="banned_words" class="form-control" value="<?php echo $settings->banned_words; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_avatar_max_size']; ?></label>
				<input type="text" name="avatar_max_size" class="form-control" value="<?php echo $settings->avatar_max_size; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_cover_max_size']; ?></label>
				<input type="text" name="cover_max_size" class="form-control" value="<?php echo $settings->cover_max_size; ?>" />
			</div>
			
			<div class="form-group">
				<label><?php echo $language['forms']['settings_contact_email']; ?></label>
				<p class="help-block"><?php echo $language['forms']['settings_contact_email_help']; ?></p>
				<input type="text" name="contact_email" class="form-control" value="<?php echo $settings->contact_email; ?>" />
			</div>

			<div class="checkbox">
				<label>
					<?php echo $language['forms']['settings_email_confirmation']; ?><input type="checkbox" name="email_confirmation" <?php if($settings->email_confirmation) echo 'checked'; ?>>
				</label>
			</div>

		</div>

		<div class="tab-pane fade" id="servers">
			<div class="form-group">
				<label><?php echo $language['forms']['settings_cache_reset_time']; ?></label>
				<p class="help-block"><?php echo $language['forms']['settings_cache_reset_time_help']; ?></p>
				<input type="text" name="cache_reset_time" class="form-control" value="<?php echo $settings->cache_reset_time; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_servers_pagination']; ?></label>
				<p class="help-block"><?php echo $language['forms']['settings_servers_pagination_help']; ?></p>
				<input type="text" name="servers_pagination" class="form-control" value="<?php echo $settings->servers_pagination; ?>" />
			</div>
			
			
			<div class="checkbox">
				<label>
					<?php echo $language['forms']['settings_new_servers_visibility']; ?><input type="checkbox" name="new_servers_visibility" <?php if($settings->new_servers_visibility) echo 'checked'; ?>>
				</label>
			</div>

			<div class="checkbox">
				<label>
					<?php echo $language['forms']['settings_display_offline_servers']; ?><input type="checkbox" name="display_offline_servers" <?php if($settings->display_offline_servers) echo 'checked'; ?>>
				</label>
			</div>
		</div>

		<div class="tab-pane fade" id="ads">
			<div class="form-group">
				<label><?php echo $language['forms']['settings_top_ads']; ?></label>
				<textarea class="form-control" name="top_ads"><?php echo $settings->top_ads; ?></textarea>
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_bottom_ads']; ?></label>
				<textarea class="form-control" name="bottom_ads"><?php echo $settings->bottom_ads; ?></textarea>
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_side_ads']; ?></label>
				<textarea class="form-control" name="side_ads"><?php echo $settings->side_ads; ?></textarea>
			</div>
		</div>


		<div class="tab-pane fade" id="automatic_payment_system">
			<div class="form-group">
				<label><?php echo $language['forms']['settings_paypal_email']; ?></label>
				<input type="text" name="paypal_email" class="form-control" value="<?php echo $settings->paypal_email; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_payment_currency']; ?></label>
				<p class="help-block"><?php echo $language['forms']['settings_payment_currency_help']; ?></p>
				<input type="text" name="payment_currency" class="form-control" value="<?php echo $settings->payment_currency; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_maximum_slots']; ?></label>
				<p class="help-block"><?php echo $language['forms']['settings_maximum_slots_help']; ?></p>
				<input type="text" name="maximum_slots" class="form-control" value="<?php echo $settings->maximum_slots; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_per_day_cost']; ?></label>
				<input type="text" name="per_day_cost" class="form-control" value="<?php echo $settings->per_day_cost; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_minimum_days']; ?></label>
				<p class="help-block"><?php echo $language['forms']['settings_minimum_days_help']; ?></p>
				<input type="text" name="minimum_days" class="form-control" value="<?php echo $settings->minimum_days; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_maximum_days']; ?></label>
				<p class="help-block"><?php echo $language['forms']['settings_maximum_days_help']; ?></p>
				<input type="text" name="maximum_days" class="form-control" value="<?php echo $settings->maximum_days; ?>" />
			</div>
		</div>

		<div class="tab-pane fade" id="social">
			<p class="help-block"><?php echo $language['forms']['settings_social_help']; ?></p>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_facebook']; ?></label>
				<input type="text" name="facebook" class="form-control" value="<?php echo $settings->facebook; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_twitter']; ?></label>
				<input type="text" name="twitter" class="form-control" value="<?php echo $settings->twitter; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_googleplus']; ?></label>
				<input type="text" name="googleplus" class="form-control" value="<?php echo $settings->googleplus; ?>" />
			</div>

		</div>

		<div class="tab-pane fade" id="smtp">
			<p class="help-block"><?php echo $language['forms']['smtp_settings_help']; ?></p>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_smtp_host']; ?></label>
				<input type="text" name="smtp_host" class="form-control" value="<?php echo $settings->smtphost; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_smtp_port']; ?></label>
				<input type="text" name="smtp_port" class="form-control" value="<?php echo $settings->smtpport; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_smtp_user']; ?></label>
				<input type="text" name="smtp_user" class="form-control" value="<?php echo $settings->smtpuser; ?>" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['settings_smtp_pass']; ?></label>
				<input type="text" name="smtp_pass" class="form-control" value="<?php echo $settings->smtppass; ?>" />
			</div>
			
			<div class="form-group">
				<label><?php echo $language['forms']['settings_smtp_secure']; ?></label>
				<input type="text" name="smtp_secure" class="form-control" value="<?php echo $settings->smtpsecure; ?>" />
			</div>
		</div>

		<div class="form-group">
			<button type="submit" name="submit" class="btn btn-default col-lg-4"><?php echo $language['forms']['submit']; ?></button><br /><br />
		</div>
	</div>
</form>

