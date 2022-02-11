<div class="modal fade" id="purchase_highlight" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title"><?php echo $language['forms']['payment_checkout']; ?></h4>
			</div>

			<div class="modal-body">

				<form name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post">

					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="business" value="<?php echo $settings->paypal_email; ?>">
					<input type="hidden" name="currency_code" value="<?php echo $settings->payment_currency; ?>">
					<input type="hidden" name="item_name" value="Highlighted Server Spot">
					<input type="hidden" name="amount" value="">
					<input type="hidden" name="return" value="">
					<input type="hidden" name="notify_url" value="<?php echo $settings->url . 'processing/ipn.php'; ?>">
					<input type="hidden" name="custom" value="">
					<input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_checkout_pp_142x27.png" border="0" name="submit" alt="Make payments with PayPal - it's fast,free and secure!">
			
				</form>

			</div>
			
		</div>
	</div>
</div>

