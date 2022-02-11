<?php
User::check_permission(0);

initiate_html_columns();

/* Initiate the servers list class */
$servers = new Servers;

/* Set a custom no servers message */
$servers->no_servers = $language['messages']['no_favorite_servers'];

/* Add additional condition to show only the users servers */
$servers->additional_where("AND `servers`.`private` = '0' AND `servers`.`active` = '1'");

/* Add aditional join conditions so we show only the favorite servers */
$servers->additional_join("
		INNER JOIN `favorites` ON `servers`.`server_id` = `favorites`.`server_id`
		AND `favorites`.`user_id` = {$account_user_id}
	");

/* Try and display the server list */
$servers->display();

/* Display any notification if there are any ( no servers ) */
display_notifications();

/* Display the pagination if there are servers */
$servers->display_pagination('my-favorites');
?>