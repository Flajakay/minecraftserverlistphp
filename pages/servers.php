<?php
initiate_html_columns();

/* Initiate the servers list class */
$servers = new Servers;

/* Make it so it will display only the active and the servers which are not private */
$servers->additional_where("AND `private` = '0' AND `active` = '1'");

/* Try and display the server list */
$servers->display();

/* Display any notification if there are any ( no servers ) */
display_notifications();

/* Display the pagination if there are servers */
$servers->display_pagination('servers');
?>