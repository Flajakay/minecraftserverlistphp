<?php
User::check_permission(2);

/* Delete and reset the vote & hit logs */
$database->query("DELETE FROM `points`");
$database->query("UPDATE `servers` SET `votes` = '0'");

/* Set the success message & redirect*/
$_SESSION['success'][] = $language['messages']['success'];
User::get_back('index');

?>