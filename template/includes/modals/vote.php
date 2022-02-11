<div class="modal fade" id="vote" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<?php if(!User::logged_in() && @$account_user_id == $server->data->user_id) { ?>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php echo $language['errors']['command_denied']; ?></h4>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $language['misc']['close_modal']; ?></button>
				</div>
			<?php } else { ?>
			<form method="post" role="form" class="vote">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php echo $language['server']['sidebar_vote']; ?></h4>
				</div>

				<div class="modal-body">

					<div class="form-group">
						<input type="hidden" name="token" value="<?php echo $token->hash; ?>" />
						<input type="hidden" name="type" value="1" />
					</div>

					<div class="form-group">
						<label><?php echo $language['forms']['username']; ?></label>
						<input type="text" name="username" class="form-control" placeholder="<?php echo $language['forms']['username']; ?>" />
					</div>

					<div class="form-group" id="vote_recaptcha">

					</div>

				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $language['misc']['close_modal']; ?></button>
					<button type="submit" class="btn btn-default"><?php echo $language['forms']['submit']; ?></button>
				</div>
			</form>
			<?php } ?>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	/*Get the recaptcha code */
	$('#vote').on('show.bs.modal', function () {
		$('#recaptcha').appendTo('#vote_recaptcha').show();
	});
	/* Transfer the recaptcha code */
	$('#vote').on('hide.bs.modal', function () {
		$('#recaptcha').appendTo('#recaptcha_base').hide();
	});

	/* Initialize the success message variable */
	var SuccessMessage = $('#response').html();
	
	$('form.vote').submit(function(event) {
		var $button = $(this).find(':submit');

		/* Close the modal */
		$('#vote').modal('hide')
		
		/* Get the form element the submit button belongs to */
		var $form = $(this).closest('form');

		/* Get the values from elements on the specific form */
		var Data = $form.serializeArray();
		
		/* Insert the captcha code into the posting data */
		var recaptcha_response_field = $('[name="recaptcha_response_field"]').val();
		var recaptcha_challenge_field = $('[name="recaptcha_challenge_field"]').val();
		Data.push({name: 'recaptcha_response_field', value: recaptcha_response_field}, {name: 'recaptcha_challenge_field', value: recaptcha_challenge_field});
		
		/* Post and get response */
		$.post('processing/process_votes.php', Data, function(data) {
			$('html, body').animate({scrollTop:0},'slow');

			if(data == "success") {

				/* Display success message */
				$('#response').html(SuccessMessage).fadeIn('slow');

				/* Increment the vote number */
				$('#votes_value').text(parseInt($('#votes_value').text()) + 1);


			} else {

				$('#response').hide().html(data).fadeIn('slow');
				
			}
			setTimeout(function() {
				$('#response').fadeOut('slow');
			}, 5000);

			/* Clear the textarea */
			$('textarea').val('');

			/* Reload recaptcha */
			Recaptcha.reload();
		});

		event.preventDefault();
	});

});
</script>