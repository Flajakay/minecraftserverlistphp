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
	<link
		rel="stylesheet"
		href="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.11.2/jodit.es2018.min.css"
	/>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.11.2/jodit.es2018.min.js"></script>
	<script src="template/js/jquery.js"></script>
	<script src="template/js/bootstrap.min.js"></script>
	<script src="template/js/timeago.js"></script>
	<script src="template/js/functions.js"></script>
	<link href="template/images/favicon.ico" rel="shortcut icon" />
	<?php if(!empty($settings->analytics_code)) { ?>
	<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', '<?php echo $settings->analytics_code; ?>', 'auto');
	ga('send', 'pageview');

	</script>
	<?php }	?>
</head>
