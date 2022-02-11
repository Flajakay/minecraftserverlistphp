<?php

/* Check if category exists and the GET variable is not empty*/
if(empty($_GET['url']) || !$category_exists) {
	$_SESSION['error'][] = $language['errors']['category_not_found'];
	User::get_back();
}

initiate_html_columns();
?>



<?php
/* Initiate the servers list class */
$servers = new Servers($category->category_id);

/* Set a custom no servers message */
$servers->no_servers = $language['messages']['no_category_servers'];

/* Make it so it will display only the active and the servers which are not private */
$servers->additional_where("AND `private` = '0' AND `active` = '1'");

/* Try and display the server list */
$servers->display();

/* Display any notification if there are any ( no servers ) */
display_notifications();

/* Display the pagination if there are servers */
$servers->display_pagination('category/' . $category->url);
?>
