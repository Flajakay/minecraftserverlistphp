<h4><?php echo $language['misc']['categories']; ?></h4>

<div class="list-group">
	<?php

	$result = $database->query("SELECT `name`, `url`, `category_id` FROM `categories` WHERE `parent_id` = 0");
	while($categories = $result->fetch_object()) {

		/* Determine the active category */
		$active = (isset($category) && $category->category_id == $categories->category_id);

		/* Display categories */
		echo '<a href="category/' . $categories->url . '" class="list-group-item ' . ($active ? "active" : null) . '">' . $categories->name . '</a>';

		/* Display subcategories if any, only for the active category */
		if($active) {

			$subcategory_result = $database->query("SELECT `name`, `url`, `category_id` FROM `categories` WHERE `parent_id` = {$categories->category_id}");
			while($subcategories = $subcategory_result->fetch_object()) echo '<a href="category/' . $subcategories->url . '" class="list-group-item"> <span class="glyphicon glyphicon-minus"></span> ' . $subcategories->name . '</a>';

		} else

		/* If the curent category is a subcategory then display the other subcategories of the parent category */
		if(isset($category) && $category->parent_id == $categories->category_id) {

			$subcategory_result = $database->query("SELECT `name`, `url`, `category_id` FROM `categories` WHERE `parent_id` = {$category->parent_id}");
			while($subcategories = $subcategory_result->fetch_object()) {

			/* Determine the active subcategory */
			$subcategory_active = ($category->category_id == $subcategories->category_id);

			/* Display categories */
			echo '<a href="category/' . $subcategories->url . '" class="list-group-item ' . ($subcategory_active ? "active" : null) . '"> <span class="glyphicon glyphicon-minus"></span> ' . $subcategories->name . '</a>';
			}

		}

	}
	?>
</div>