<?php
$page_title = 'Главная';

if(isset($_GET['page'])) {
	switch($_GET['page']) {
		case 'designs' 						:	$page_title = $language['titles']['designs'];				break;
		case 'login' 						:	$page_title = $language['titles']['login'];	 				break;
		case 'resendactivation'				:	$page_title = $language['titles']['resendactivation'];		break;
		case 'lostpassword'					:	$page_title = $language['titles']['lostpassword'];			break;
		case 'resetpassword'				:	$page_title = $language['titles']['resetpassword'];			break;
		case 'register'						:	$page_title = $language['titles']['register']; 				break;
		case 'notfound' 					:	$page_title = $language['titles']['notfound'];				break;
		case 'password' 					:	$page_title = $language['titles']['password'];				break;
		case 'settings' 					:	$page_title = $language['titles']['settings'];				break;
		case 'questions'					:	$page_title = $language['titles']['questions'];				break;
		case 'admin_users_management'		:	$page_title = $language['titles']['users_management'];		break;
		case 'admin_user_edit'				:	$page_title = $language['titles']['edit_user'];				break;
		case 'admin_categories_management'	:	$page_title = $language['titles']['categories_management'];	break;
		case 'admin_reports_management'		:	$page_title = $language['titles']['reports_management'];	break;
		case 'admin_servers_management'		:	$page_title = $language['titles']['servers_management'];	break;
		case 'admin_report_edit'			:	$page_title = $language['titles']['view_report'];			break;
		case 'admin_server_edit'			:	$page_title = $language['titles']['edit_server'];			break;
		case 'admin_category_edit'			:	$page_title = $language['titles']['edit_category'];			break;
		case 'admin_website_settings'		:	$page_title = $language['titles']['website_settings'];		break;
		case 'admin_website_statistics'		:	$page_title = $language['titles']['website_statistics'];	break;
		case 'servers'						:	$page_title = $language['titles']['servers'];				break;
		case 'my_servers'					:	$page_title = $language['titles']['my_servers'];			break;
		case 'my_favorites'					:	$page_title = $language['titles']['my_favorites'];			break;
		case 'server_edit'					:	$page_title = $language['titles']['edit_server'];			break;
		case 'submit'						:	$page_title = $language['titles']['submit'];				break;
		case 'purchase_highlight'			:	$page_title = $language['titles']['purchase_highlight'];	break;
		case 'server'						:	$page_title = ($server->exists) ? $server->data->name : $language['errors']['server_not_found'];	break;
		case 'category'						:	$page_title = ($category_exists) ? (empty($category->title) ? $category->name : $category->title) : $language['errors']['category_not_found'];		break;
		case 'profile'  					:	$page_title = ($user_exists) ? $profile_account->name : $language['errors']['user_not_found'];		break;
	}
}

$page_title .= ' - ' . $settings->title;
?>