<?php
User::check_permission(0);
include 'core/functions/recaptchalib.php';

$address =  $name = $country_code = $youtube_link = $website = $description = null;
$connection_port = $query_port = 25565;

if(!empty($_POST)) {

	/* Define some variables */
	$address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
	$connection_port = (int) $_POST['connection_port'];
	$query_port = (int) $_POST['query_port'];
	$date = new DateTime();
	$date = $date->format('Y-m-d H:i:s');
	$active = $status = '1';
	$private = ($settings->new_servers_visibility) ? '0' : '1';
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$image = (empty($_FILES['image']['name']) == false) ? true : false;
	$country_code = (country_check(0, $_POST['country_code'])) ? $_POST['country_code'] : 'US';
	$youtube_link = filter_var($_POST['youtube_id'], FILTER_SANITIZE_STRING);
	$youtube_id = youtube_url_to_id($youtube_link);
	$website = filter_var($_POST['website'], FILTER_VALIDATE_URL);
	$description = $_POST['description'];
	
	//$captcha = recaptcha_check_answer ($settings->private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
	$allowed_extensions = array('jpg', 'jpeg', 'gif');
	$required_fields = array('address', 'connection_port', 'query_port', 'category_id');

	/* Get category data */
	$category = new StdClass;

	$stmt = $database->prepare("SELECT `category_id`, `name`, `url` FROM `categories` WHERE `category_id` = ?");
	$stmt->bind_param('s', $_POST['category_id']);
	$stmt->execute();
	bind_object($stmt, $category);
	$stmt->fetch();
	$stmt->close(); 
	
	/* Determine if category exists */
	if($category !== NULL) {
		$category->exists = true;
	} else {
		$category = new StdClass;
		$category->exists = false;
	}

	/* If the category doesn't exist, set an error message.If it exists, continue with the checks */
	if(!$category->exists) {
		$_SESSION['error'][] = $language['errors']['category_not_found'];
	} else {

		/* Query the server with a specific protocol */
		$query = new Query($address, $query_port);
		$info  = $query->query();

		if(!$info && $query->status) {
			$_SESSION['error'][] = $language['errors']['server_no_data'];
		} else 
		if(!$info && !$query->status) {
			$_SESSION['error'][] = $language['errors']['server_offline'];
		}

	}

	/* Check for the required fields */
	foreach($_POST as $key=>$value) {
		if(empty($value) && in_array($key, $required_fields) == true) {
			$_SESSION['error'][] = $language['errors']['marked_fields_empty'];
			break 1;
		}
	}

	/* Check for banner image errors */
	if($image == true) {
		$image_file_name		= $_FILES['image']['name'];
		$image_file_extension	= explode('.', $image_file_name);
		$image_file_extension	= strtolower(end($image_file_extension));
		$image_file_temp		= $_FILES['image']['tmp_name'];
		$image_file_size		= $_FILES['image']['size'];
		list($image_width, $image_height)	= getimagesize($image_file_temp);

		if(in_array($image_file_extension, $allowed_extensions) !== true) {
			$_SESSION['error'][] = $language['errors']['incorrect_file_type'];
		}
		if($image_file_size > $settings->cover_max_size) {
			$_SESSION['error'][] = sprintf($language['errors']['image_size'], formatBytes($settings->cover_max_size));
		}
	}

	/* More checks */
/* 	if(!$captcha->is_valid) {
		$_SESSION['error'][] = $language['errors']['captcha_not_valid'];
	} */
	if(strlen($name) > 64 || strlen($name) < 3) {
		$_SESSION['error'][] = $language['errors']['server_name_length'];
	}
	if(strlen($description) > 2560) {
		$_SESSION['error'][] = $language['errors']['description_too_long'];
	}
	$server = new Server($address, $connection_port);
	if($server->exists) {
		$_SESSION['error'][] = $language['errors']['server_already_exists'];
	}


	/* If there are no errors, add the server to the database */
	if(empty($_SESSION['error'])) {

		/* Banner process */
		if($image == true) {

			/* Generate new name for image */
			$image_new_name = md5(time().rand()) . '.' . $image_file_extension;

			/* Resize if needed & upload the image */
			if($image_width != '468' || $image_height != '60') {
				resize($image_file_temp, 'user_data/server_banners/' . $image_new_name, '468', '60');
			} else {
				move_uploaded_file($image_file_temp, 'user_data/server_banners/' . $image_new_name);	
			}

		}

		$image_name = ($image == true) ? $image_new_name : '';

		/* Add the server to the database as private */
		//$stmt = $database->prepare("INSERT INTO `servers` (`user_id`, `category_id`, `address`, `connection_port`, `query_port`, `private`, `active`, `status`, `date_added`, `image`, `name`, `country_code`, `youtube_id`, `website`, `description`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		
		$stmt = $database->prepare("INSERT INTO `servers` (`server_id`, `user_id`, `category_id`, `address`, `connection_port`, `query_port`, `private`, `active`, `name`, `description`, `image`, `website`, `country_code`, `youtube_id`, `date_added`, `highlight`, `votes`, `favorites`, `status`, `online_players`, `maximum_online_players`, `motd`, `server_version`, `details`, `custom`, `cachetime`)
		VALUES (NULL, $account_user_id, $category->category_id, '$address', $connection_port, $query_port, '1', $active, '$name', '$description', '$image_name', '$website', '$country_code', '$youtube_id', '$date', '0', '0', '0', '1', '0', '0', $status, '', '', '', '')");
		//$stmt->bind_param('sssssssssssssss',  $account_user_id, $category->category_id, $address, $connection_port, $query_port, $private, $active, $status, $date, $image_name, $name, $country_code, $youtube_id, $website, $description);
		$test = $stmt->execute();
		$stmt->close();
		
		/* Set the success message and redirect */
		$_SESSION['success'][] = $language['messages']['server_added'];
		redirect('my-servers');
	}

display_notifications();

}


initiate_html_columns();

?>


<h3><?php echo $language['headers']['submit']; ?></h3>

<form action="" method="post" role="form" enctype="multipart/form-data">
	<div class="form-group">
		<label><?php echo $language['forms']['server_address']; ?> *</label>
		<input type="text" name="address" class="form-control" value="<?php echo $address; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['server_connection_port']; ?> *</label>
		<p class="help-block"><?php echo $language['forms']['server_connection_port_help']; ?></p>
		<input type="text" name="connection_port" class="form-control" value="<?php echo $connection_port; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['server_query_port']; ?> *</label>
		<p class="help-block"><?php echo $language['forms']['server_query_port_help']; ?></p>
		<input type="text" name="query_port" class="form-control" value="<?php echo $query_port; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['server_category']; ?> *</label>
		<select name="category_id" class="form-control">
			<?php 
			$result = $database->query("SELECT `category_id`, `name` FROM `categories` WHERE `parent_id` = '0'  ORDER BY `name` ASC");
			while($category = $result->fetch_object()) {
				echo '<option value="' . $category->category_id . '">' . $category->name . '</option>'; 

				$subcategory_result = $database->query("SELECT `category_id`, `name` FROM `categories` WHERE `parent_id` = {$category->category_id} ORDER BY `name` ASC");
				while($subcategory = $subcategory_result->fetch_object()) {
					echo '<option value="' . $subcategory->category_id . '">--' . $subcategory->name . '</option>'; 

				}
			}
			?>	
		</select>
	</div>

	<hr />

	<div class="form-group">
		<label><?php echo $language['forms']['server_banner']; ?></label><br />
		<p class="help-block"><?php echo $language['forms']['server_banner_help']; ?></p>
		<input type="file" name="image" class="form-control" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['server_name']; ?></label>
		<input type="text" name="name" class="form-control" value="<?php echo $name; ?>" />
	</div>


	<div class="form-group">
		<label><?php echo $language['forms']['server_country']; ?></label>
		<select name="country_code" class="form-control">
			<?php country_check(1, $country_code); ?>
		</select>
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['server_youtube_id']; ?></label>
		<p class="help-block"><?php echo $language['forms']['server_youtube_id_help']; ?></p>
		<input type="text" name="youtube_id" class="form-control" value="<?php echo $youtube_link; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['server_website']; ?></label>
		<input type="text" name="website" class="form-control" value="<?php echo $website; ?>"/>
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['server_description']; ?></label>
		<textarea id="editorincluded" name="description" class="form-control" rows="6"></textarea>
	</div>


	<div class="form-group">
		<button type="submit" name="submit" class="btn btn-default col-lg-4"><?php echo $language['forms']['submit']; ?></button><br /><br />
	</div>

</form>
<script type="text/javascript">
	$('#editorincluded').each(function () {
		var editor = new Jodit(this, {"buttons": "bold,italic,underline,strikethrough,eraser,ul,ol,indent,outdent,left,font,fontsize,paragraph,brush,superscript,subscript,image,video"});
	}); 
</script>