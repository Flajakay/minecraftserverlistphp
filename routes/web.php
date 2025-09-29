<?php

$router->get('/', 'HomeController@index');
$router->get('/servers', 'ServerController@index');
$router->get('/servers/{page}', 'ServerController@index');
$router->get('/server/{address}:{port}', 'ServerController@show');

$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

$router->get('/profile/{username}', 'UserController@show');

$router->get('/submit', 'ServerController@showSubmit');
$router->post('/submit', 'ServerController@submit');

$router->get('/my-servers', 'ServerController@userServers');
$router->get('/my-favorites', 'ServerController@userFavorites');

$router->get('/edit-server/{id}', 'ServerController@edit');
$router->post('/edit-server/{id}', 'ServerController@update');
$router->post('/server-action/{id}', 'ServerController@action');

$router->get('/settings/profile', 'UserController@editProfile');
$router->post('/settings/profile', 'UserController@updateProfile');

$router->get('/settings/password', 'UserController@changePassword');
$router->post('/settings/password', 'UserController@updatePassword');

$router->post('/vote', 'VoteController@vote');
$router->post('/favorite', 'FavoriteController@toggle');
$router->post('/comment', 'CommentController@store');
$router->post('/comment/delete', 'CommentController@delete');
$router->get('/comment/load-more', 'CommentController@loadMore');
$router->post('/report', 'ReportController@store');
$router->get('/banner', 'BannerController@generate');

$router->get('/premium', 'PaymentController@showPurchase');
$router->post('/paypal/create-order', 'PaymentController@createOrder');
$router->post('/paypal/capture-payment', 'PaymentController@capturePayment');
$router->get('/paypal/cancel', 'PaymentController@cancelPayment');

$router->post('/blog', 'BlogController@store');
$router->get('/blog/edit/{id}', 'BlogController@edit');
$router->post('/blog/edit/{id}', 'BlogController@update');
$router->post('/blog/delete', 'BlogController@delete');
$router->get('/blog/load-more', 'BlogController@loadMore');

$router->get('/admin/users', 'Admin\UserController@index');
$router->get('/admin/users/edit/{id}', 'Admin\UserController@edit');
$router->post('/admin/users/edit/{id}', 'Admin\UserController@update');
$router->post('/admin/users/action/{id}', 'Admin\UserController@action');
$router->post('/admin/users/delete/{id}', 'Admin\UserController@delete');

$router->get('/admin/servers', 'Admin\ServerController@index');
$router->get('/admin/servers/edit/{id}', 'Admin\ServerController@edit');
$router->post('/admin/servers/edit/{id}', 'Admin\ServerController@update');
$router->post('/admin/servers/action/{id}', 'Admin\ServerController@action');
$router->post('/admin/servers/delete/{id}', 'Admin\ServerController@delete');

$router->get('/admin/categories', 'Admin\CategoryController@index');
$router->post('/admin/categories', 'Admin\CategoryController@create');
$router->get('/admin/categories/edit/{id}', 'Admin\CategoryController@edit');
$router->post('/admin/categories/edit/{id}', 'Admin\CategoryController@update');
$router->post('/admin/categories/delete/{id}', 'Admin\CategoryController@delete');

$router->get('/admin/reports', 'Admin\ReportController@index');
$router->get('/admin/reports/view/{id}', 'Admin\ReportController@view');
$router->post('/admin/reports/action/{id}', 'Admin\ReportController@action');
$router->post('/admin/reports/delete/{id}', 'Admin\ReportController@delete');

$router->get('/admin/payments', 'Admin\PaymentController@index');
$router->post('/admin/payments/delete/{id}', 'Admin\PaymentController@delete');

$router->get('/admin/audit', 'Admin\AuditController@index');

$router->get('/admin/blog-posts', 'Admin\BlogController@index');
$router->post('/admin/blog-posts/delete/{id}', 'Admin\BlogController@delete');

$router->get('/admin/settings', 'Admin\SettingController@index');
$router->post('/admin/settings', 'Admin\SettingController@update');
$router->post('/admin/reset-votes', 'Admin\SettingController@resetVotes');

$router->get('/activate/{email}/{code}', 'AuthController@activate');
$router->get('/reset-password/{email}/{code}', 'AuthController@resetPassword');

$router->get('/lost-password', 'AuthController@showLostPassword');
$router->post('/lost-password', 'AuthController@sendResetLink');

$router->get('/contact', 'ContactController@show');
$router->post('/contact', 'ContactController@send');

$router->get('/terms-of-service', 'StaticController@termsOfService');
$router->get('/privacy-policy', 'StaticController@privacyPolicy');

$router->get('/sitemap.xml', 'SitemapController@xml');
$router->get('/robots.txt', 'RobotsController@txt');
