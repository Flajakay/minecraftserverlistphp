				<?php include 'includes/close_main_column.php'; ?>

				<!-- START Sidebar -->
				<?php if(isset($_GET['page'])) { ?>
					<div class="col-md-2">
						<?php include 'includes/sidebar.php'; ?>
					</div>
				<?php } ?>
				<!-- END Sidebar -->

			</div><!-- END ROW -->

			<?php include 'includes/widgets/bottom_ads.php'; ?>

		</div><!-- END Container -->

		<div class="sticky-footer">
			<div class="container">
				<p><?php include 'includes/footer.php'; ?></p>
			</div>
		</div>
	</body>
</html>