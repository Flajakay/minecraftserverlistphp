<?php
include 'core/init.php';
include 'template/overall_header.php';

if(isset($_GET['page'])) {
    $pages  = preg_replace('(pages/|.php)', "", glob('pages/*.php'));
    $page  = htmlspecialchars($_GET['page'], ENT_QUOTES);
    
	include 'pages/'. (in_array($page, $pages) ? $page : 'notfound') .'.php';
} else {
    include 'pages/home.php'; 
}

include 'template/overall_footer.php';
include 'core/deinit.php';
?>