<head>
	<title><?php echo $page_title; ?></title>
	<base href="<?php echo $settings->url; ?>">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="interkassa-verification" content="a471eb52b168c5ef5e9ef19954d677e2" />
	<?php 
	if(!empty($settings->meta_description) && (!isset($_GET['page']) || (isset($_GET['page']) && $_GET['page'] != 'category')))
		echo '<meta name="description" content="' . $settings->meta_description . '" />'; 
	elseif(isset($_GET['page']) && $_GET['page'] == 'category' && !empty($category->description))
		echo '<meta name="description" content="' . $category->description . '" />';
	?>

	<link href="template/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link href="template/css/custom.css" rel="stylesheet" media="screen">
	<link href="template/css/font-awesome.min.css" rel="stylesheet" media="screen">
	<link href="template/css/jodit.css" rel="stylesheet" media="screen">
	<link href="template/css/tagmanager.css" rel="stylesheet" media="screen">

	<script src="template/js/typeahead.min.js"></script>
	<script src="template/js/tagmanager.js"></script>
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
