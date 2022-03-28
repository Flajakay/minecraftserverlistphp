<?php
User::check_permission(1);

if(isset($_GET['delete'])) {

	/* Check for errors */
	if(!$token->is_valid()) {
		$_SESSION['error'][] = $language['errors']['invalid_token'];
	}

	if(empty($_SESSION['error'])) {
		/* Get the $server_id from the $category_id */
		$server_id = User::x_to_y('category_id', 'server_id', $_GET['delete'], 'servers');

		/* Delete category and all servers from that category */
		$database->query("DELETE FROM `categories` WHERE `category_id` = {$_GET['delete']}");

		$result = $database->query("SELECT `server_id` FROM `servers` WHERE `category_id` = {$_GET['delete']}");
		while($servers = $result->fetch_object()) Server::delete_server($servers->server_id);


		/* Delete the cover image */
		$result = $database->query("SELECT `image` FROM `categories` WHERE `category_id` = {$_GET['delete']}");
		$category = $result->fetch_object();
		@unlink('user_data/category_covers/' . $category->image);

		/* Set the success message & redirect*/
		$_SESSION['success'][] = $language['messages']['success'];
		User::get_back('admin/categories-management');
	}
}

if(!empty($_POST)) {
	/* Define some variables */
	$_POST['name']				 		= filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$_POST['title']				 		= filter_var($_POST['title'], FILTER_SANITIZE_STRING);
	$_POST['description']		 		= filter_var($_POST['description'], FILTER_SANITIZE_STRING);
	$_POST['url']						= generateSlug(filter_var($_POST['url'], FILTER_SANITIZE_STRING));
	$_POST['parent_id']					= (int)$_POST['parent_id'];
	$image = (!empty($_FILES['image']['name'])) ? true : false;
	$allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
	$required_fields = array('name', 'url');

	/* Check for any errors on the cover image */
	if($image == true) {
		$image_file_name		= $_FILES['image']['name'];
		$image_file_extension	= explode(".", $image_file_name);
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

		if($image == true) {
			/* Generate new name for cover */
			$image_new_name = md5(time().rand()) . '.' . $image_file_extension;

			/* Resize if needed & upload the image */
			if($image_width != '970' || $image_height != '170') {
				resize($image_file_temp, 'user_data/category_covers/' . $image_new_name, '970', '170', true);
			} else {
				move_uploaded_file($image_file_temp, 'user_data/category_covers/' . $image_new_name);	
			}

		} else $image_new_name = '';

		$stmt = $database->prepare("INSERT INTO `categories` (`parent_id`, `name`, `title`, `description`, `url`, `image`) VALUES ( ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param('ssssss', $_POST['parent_id'], $_POST['name'], $_POST['title'], $_POST['description'], $_POST['url'], $image_new_name);
		$stmt->execute();
		$stmt->close();

		$_SESSION['success'][] = $language['messages']['success'];
	}

	display_notifications();

}


initiate_html_columns();

?>
<div class="panel panel-default">
	<div class="panel-body">
		<div class="table-responsive">
			<table class="table">
				<thead>
					<tr>
						<th><? echo $language['forms']['name'] ?></th>
						<th>Url</th>
						<th><? echo $language['forms']['tools'] ?></th>
					</tr>
				</thead>
				<tbody>

					<?php
					$result = $database->query("SELECT `category_id`, `parent_id`, `name`, `url` FROM `categories` WHERE `parent_id` = '0' ORDER BY `category_id` ASC");
					while($category = $result->fetch_object()) {	
					?>
						<tr class="bg-primary">
							<td><?php echo $category->name; ?></td>
							<td><a href="category/<?php echo $category->url; ?>" class="white"><?php echo $category->url; ?></td>
							<td><?php category_admin_buttons($category->category_id, $token->hash); ?></td>
						</tr>
							<?php 
							$subcategories_result = $database->query("SELECT `category_id`, `parent_id`, `name`, `url` FROM `categories` WHERE `parent_id` = {$category->category_id} ORDER BY `category_id` ASC");
							while($subcategory = $subcategories_result->fetch_object()) {	
							?>
							<tr>
								<td><?php echo $subcategory->name; ?></td>
								<td><a href="category/<?php echo $subcategory->url; ?>"><?php echo $subcategory->url; ?></td>
								<td><?php category_admin_buttons($subcategory->category_id, $token->hash); ?></td>
							</tr>
							<?php } ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>




<div class="panel panel-default">
	<div class="panel-heading">
		<?php echo $language['forms']['admin_add_category_header']; ?>
	</div>
	<div class="panel-body">

		<form action="" method="post" role="form" enctype="multipart/form-data">
			<div class="form-group">
				<label><?php echo $language['forms']['admin_add_category_name']; ?> *</label>
				<input type="text" name="name" class="form-control" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['admin_add_category_title']; ?></label>
				<p class="help-block"><?php echo $language['forms']['admin_add_category_title_help']; ?></p>
				<input type="text" name="title" class="form-control" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['admin_add_category_description']; ?></label>
				<p class="help-block"><?php echo $language['forms']['admin_add_category_description_help']; ?></p>
				<input type="text" name="description" class="form-control" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['admin_add_category_url']; ?></label>
				<p class="help-block"><?php echo $language['forms']['admin_add_category_url_help']; ?></p>
				<input type="text" name="url" class="form-control" />
			</div>

			<div class="form-group">
				<label><?php echo $language['forms']['admin_add_category_parent']; ?> *</label>
				<select name="parent_id" class="form-control">
					<option value="0">None</option>
					<?php 
					$result = $database->query("SELECT `category_id`, `name` FROM `categories` WHERE `parent_id` = '0' ORDER BY `name` ASC");
					while($category = $result->fetch_object()) echo '<option value="' . $category->category_id . '">' . $category->name . '</option>'; 
					?>
				</select>
			</div>


			<button type="submit" name="submit" class="btn btn-default"><?php echo $language['forms']['submit']; ?></button>
		</form>
	</div>
</div>