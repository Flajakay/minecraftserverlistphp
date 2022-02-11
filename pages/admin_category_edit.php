<?php
User::check_permission(1);

/* Check if category exists */
if(!User::x_exists('category_id', $_GET['category_id'], 'categories')) {
	$_SESSION['error'][] = $language['errors']['category_not_found'];
	User::get_back('admin/categories-management');
}

if(!empty($_POST)) {
	/* Define some variables */
	$_POST['name']				 		= filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$_POST['title']				 		= filter_var($_POST['title'], FILTER_SANITIZE_STRING);
	$_POST['description']				= filter_var($_POST['description'], FILTER_SANITIZE_STRING);
	$_POST['url']						= generateSlug(filter_var($_POST['url'], FILTER_SANITIZE_STRING));
	$_POST['parent_id']					= (int)$_POST['parent_id'];
	$_GET['category_id']				= (int)$_GET['category_id'];
	$image = (!empty($_FILES['image']['name'])) ? true : false;
	$allowed_extensions = array('jpg', 'jpeg');
	$required_fields = array('name', 'url');

	/* Check for any errors on the cover image */
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
		if($image_width < 970 || $image_height < 170) {
			$_SESSION['error'][] = $language['errors']['category_cover_res'];
		}
		if($image_file_size > $settings->cover_max_size) {
			$_SESSION['error'][] = sprintf($language['errors']['image_size'], formatBytes($settings->cover_max_size));
		}
	}

	/* Check for the required fields */
	foreach($_POST as $key=>$value) {
		if(empty($value) && in_array($key, $required_fields) == true) {
			$_SESSION['error'][] = $language['errors']['marked_fields_empty'];
			break 1;
		}
	}
	

	/* Check if the select value was changed for categories */
	$categories = array();
	$result = $database->query("SELECT `category_id` FROM `categories` WHERE `parent_id` = '0' ORDER BY `category_id` ASC");
	while($category = $result->fetch_object()) $categories[] = $category->category_id; $categories[] = "0";
	if(!in_array($_POST['parent_id'], $categories)) {
		$_SESSION['error'][] = $language['errors']['category_doesnt_exist'];
	}

	/* If there are no errors continue the updating process */
	if(empty($_SESSION['error'])) {

		/* Get the current image */
		$result = $database->query("SELECT `image` FROM `categories` WHERE `category_id` = {$_GET['category_id']}");
		$category = $result->fetch_object();

		if($image == true) {
			/* Delete the current image */
			@unlink('user_data/category_covers/' . $category->image);

			/* Generate new name for cover */
			$image_new_name = md5(time().rand()) . '.' . $image_file_extension;

			/* Resize if needed & upload the image */
			if($image_width != '970' || $image_height != '170') {
				resize($image_file_temp, 'user_data/category_covers/' . $image_new_name, '970', '170', true);
			} else {
				move_uploaded_file($image_file_temp, 'user_data/category_covers/' . $image_new_name);	
			}

		} else $image_new_name = $category->image;

		$stmt = $database->prepare("UPDATE `categories` SET `parent_id` = ?, `name` = ?, `title` = ?, `description` = ?, `url` = ?, `image` = ? WHERE `category_id` = ?");
		$stmt->bind_param('sssssss', $_POST['parent_id'], $_POST['name'], $_POST['title'], $_POST['description'], $_POST['url'], $image_new_name, $_GET['category_id']);
		$stmt->execute();
		$stmt->close();

		/* Set a success message */
		$_SESSION['success'][] = $language['messages']['success'];
	}

	display_notifications();

}

/* Get $category data from the database */
$stmt = $database->prepare("SELECT * FROM `categories` WHERE `category_id` = ?");
$stmt->bind_param('s', $_GET['category_id']);
$stmt->execute();
bind_object($stmt, $category);
$stmt->fetch();
$stmt->close();

initiate_html_columns();

?>


<h3><?php echo $language['headers']['edit_category']; ?></h3>

<form action="" method="post" role="form" enctype="multipart/form-data">
	<div class="form-group">
		<label><?php echo $language['forms']['admin_add_category_name']; ?></label>
		<input type="text" name="name" class="form-control" value="<?php echo $category->name; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['admin_add_category_title']; ?></label>
		<p class="help-block"><?php echo $language['forms']['admin_add_category_title_help']; ?></p>
		<input type="text" name="title" class="form-control"value="<?php echo $category->title; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['admin_add_category_description']; ?></label>
		<p class="help-block"><?php echo $language['forms']['admin_add_category_description_help']; ?></p>
		<input type="text" name="description" class="form-control" value="<?php echo $category->description; ?>"/>
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['admin_add_category_url']; ?></label>
		<p class="help-block"><?php echo $language['forms']['admin_add_category_url_help']; ?></p>
		<input type="text" name="url" class="form-control" value="<?php echo $category->url; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['admin_add_category_parent']; ?></label>
		<select name="parent_id" class="form-control">
			<?php echo '<option value="' . $category->parent_id . '">Current parent: ' . User::x_to_y('category_id', 'name', $category->parent_id, 'categories') . '</option>'; ?>
			<option value="0">None</option>
			<?php 
			$result = $database->query("SELECT `category_id`, `name` FROM `categories` WHERE `parent_id` = '0' AND `category_id` != {$category->parent_id} AND `category_id` != {$category->category_id} ORDER BY `name` ASC");
			while($category_parent = $result->fetch_object()) echo '<option value="' . $category_parent->category_id . '">' . $category_parent->name . '</option>'; 
			?>
		</select>
	</div>



	<button type="submit" name="submit" class="btn btn-default"><?php echo $language['forms']['submit']; ?></button>
</form>



