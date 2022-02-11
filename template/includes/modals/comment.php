<div class="modal fade" id="comment" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<?php if(!User::logged_in()) { ?>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php echo $language['messages']['logged_in_action']; ?></h4>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $language['misc']['close_modal']; ?></button>
				</div>
			<?php } else { ?>
			<form method="post" role="form" class="comments">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php echo $language['server']['sidebar_add_comment']; ?></h4>
				</div>

				<div class="modal-body">

					<div class="form-group">
						<input type="hidden" name="token" value="<?php echo $token->hash; ?>" />
					</div>

					<div class="form-group">
						<textarea name="comment" class="form-control" rows="4" style="resize:none;"></textarea>
					</div>

					<div class="form-group" id="comment_recaptcha">
						
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
	$('#comment').on('show.bs.modal',  function () {
		$('#recaptcha').appendTo('#comment_recaptcha').show();
	});
	/* Transfer the recaptcha code */
	$('#comment').on('hide.bs.modal', function () {
		$('#recaptcha').appendTo('#recaptcha_base').hide();
	});

	/* Initialize the success message variable */
	var SuccessMessage = $('#response').html();
	
	$('form.comments').submit(function(event) {
		var $button = $(this).find(':submit');

		/* Close the modal */
		$('#comment').modal('hide')
		
		/* Get the form element the submit button belongs to */
		var $form = $(this).closest('form');

		/* Get the values from elements on the specific form */
		var Data = $form.serializeArray();
		
		/* Insert the captcha code into the posting data */
		var recaptcha_response_field = $('[name="recaptcha_response_field"]').val();
		var recaptcha_challenge_field = $('[name="recaptcha_challenge_field"]').val();
		Data.push({name: 'recaptcha_response_field', value: recaptcha_response_field}, {name: 'recaptcha_challenge_field', value: recaptcha_challenge_field});
		
		/* Post and get response */
		$.post('processing/process_comments.php', Data, function(data) {
			$('html, body').animate({scrollTop:0},'slow');

			if(data == "success") {
				/* Display success message */
				$('#response').html(SuccessMessage).fadeIn('slow');

				/* Remove all the comments */
				$('#comments').empty();

				/* Initiate the commets again */
				showMore(0, 'processing/comments_show_more.php', '#comments');

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