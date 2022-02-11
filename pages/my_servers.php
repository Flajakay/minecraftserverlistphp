<?php
User::check_permission(0);

initiate_html_columns();

/* Initiate the servers list class */
$servers = new Servers;

/* Set a custom no servers message */
$servers->no_servers = $language['messages']['no_my_servers'];

/* Add additional condition to show only the users servers */
$servers->additional_where("AND `user_id` = {$account_user_id}");

/* Try and display the server list */
$servers->display();

/* Display any notification if there are any ( no servers ) */
display_notifications();

/* Display the pagination if there are servers */
$servers->display_pagination('my-servers');
?>