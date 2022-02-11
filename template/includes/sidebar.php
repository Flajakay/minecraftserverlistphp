<?php 

if(isset($_GET['page']) && (($_GET['page'] == 'category' && $category_exists) || $_GET['page'] == 'servers')) {
	include 'widgets/servers_filter.php';
	include 'widgets/categories.php';
}

if(User::logged_in() && User::get_servers($account_user_id) && (isset($_GET['page']) && $_GET['page'] != 'server')) include 'widgets/my_servers_status.php';

if(!empty($settings->side_ads)) echo $settings->side_ads;

?>