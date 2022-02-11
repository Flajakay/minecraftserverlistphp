<!DOCTYPE html>
<html>
	
	<?php include 'includes/head.php'; ?>
	<body>

		<?php include 'includes/menu.php'; ?>
		<?php if(!isset($_GET['page'])) include 'includes/home.php'; ?>
		<div class="container"><!-- Start Container -->

			<?php display_notifications(); ?>

			<?php include 'includes/widgets/top_ads.php'; ?>
