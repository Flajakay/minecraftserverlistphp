<?php
include 'core/init.php';
include 'template/overall_header.php';

if(isset($_GET['page'])) {
	$_GET['page'] = htmlspecialchars($_GET['page'], ENT_QUOTES);
	$pages = glob("pages/" . "*.php");
	$pages = preg_replace("(pages/|.php)", "", $pages);

	if(in_array($_GET['page'], $pages)) {
		include 'pages/'.$_GET['page'].'.php';
	} else {
		include 'pages/notfound.php';
	}
} else {
	include 'pages/home.php';
}

include 'template/overall_footer.php';
include 'core/deinit.php';
?>
