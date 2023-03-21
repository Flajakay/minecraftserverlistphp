<head>
	<title><?php echo $page_title; ?></title>
	<base href="<?php echo $settings->url; ?>">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta property="og:title" content="<?php echo $page_title; ?>">	
	<meta property="og:url" content="<?php echo $settings->url; ?>">
	<meta name="twitter:title" content="<?php echo $page_title; ?>">	
	<meta name="twitter:url" content="<?php echo $settings->url; ?>">
	<meta name="keywords" content="minecraft, minecraft servers, minecraft server list">

	<?php 
	if(!empty($settings->meta_description) && (!isset($_GET['page']) || (isset($_GET['page']) && $_GET['page'] != 'category' && $_GET['page'] != 'server'))) {
		echo '<meta name="description" content="' . $settings->meta_description . '" />'; 
		echo '<meta property="og:description" content="' . $settings->meta_description . '" />'; 
		echo '<meta name="twitter:description" content="' . $settings->meta_description . '" />'; 
	}
	elseif(isset($_GET['page']) && $_GET['page'] == 'category' && !empty($category->description)) {
		echo '<meta name="description" content="' . $category->description . '" />';
		echo '<meta property="og:description" content="' . $category->description . '" />'; 
		echo '<meta name="twitter:description" content="' . $category->description . '" />';
	}
	elseif(isset($_GET['page']) &&  $_GET['page'] == 'server' && isset($server->data)) {
		echo '<meta name="description" content="' . get_description($server->data->description) . '" />'; 
		echo '<meta property="og:description" content="' . get_description($server->data->description) . '" />'; 
		echo '<meta name="twitter:description" content="' . get_description($server->data->description) . '" />'; 
	}
	?>

	<link href="template/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link href="template/css/custom.css" rel="stylesheet" media="screen">
	<link href="template/css/font-awesome.min.css" rel="stylesheet" media="screen">
	<link href="template/css/jodit.css" rel="stylesheet" media="screen">

	<script src="template/js/jodit.js"></script>
	<script src="template/js/jquery.js"></script>
	<script src="template/js/bootstrap.min.js"></script>
	<script src="template/js/timeago.js"></script>
	<script src="template/js/functions.js"></script>
	<link href="template/images/favicon.ico" rel="shortcut icon" />
	<?php if(!empty($settings->analytics_code)) { ?>
	<script async src='<?php echo "https://www.googletagmanager.com/gtag/js?id=" . $settings->analytics_code; ?>' </script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', '<?php echo $settings->analytics_code; ?>');
	</script>
	<?php }	?>
</head>
