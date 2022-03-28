<div class="modal fade" id="blog" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<?php if(!User::logged_in() && @$account_user_id != User::x_to_y('server_id', 'user_id', $_SESSION['server_id'], 'servers')) { ?>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php echo $language['errors']['command_denied']; ?></h4>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $language['misc']['close_modal']; ?></button>
				</div>
			<?php } else { ?>
			<form method="post" role="form" class="blog">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php echo $language['server']['sidebar_add_blog_post']; ?></h4>
				</div>

				<div class="modal-body">

					<div class="form-group">
						<input type="hidden" name="token" value="<?php echo $token->hash; ?>" />
						<input type="hidden" name="type" value="1" />
					</div>

					<div class="form-group">
						<textarea id="editorincluded" name="comment" class="form-control" rows="4" style="resize:none;"></textarea>
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
	/* Initialize the success message variable */
	var SuccessMessage = $('#response').html();
	
	$('form.blog').submit(function(event) {
		var $button = $(this).find(':submit');

		/* Close the modal */
		$('#blog').modal('hide')
		
		/* Get the form element the submit button belongs to */
		var $form = $(this).closest('form');

		/* Get the values from elements on the specific form */
		var Data = $form.serializeArray();
		

		/* Post and get response */
		$.post('processing/process_comments.php', Data, function(data) {
			$('html, body').animate({scrollTop:0},'slow');

			if(data == "success") {
				/* Display success message */
				$('#response').html(SuccessMessage).fadeIn('slow');

				/* Remove all the blog posts */
				$('#blog_posts').empty();

				/* Initiate the commets again */
				showMore(0, 'processing/blog_show_more.php', '#blog_posts');

			} else {
				$('#response').hide().html(data).fadeIn('slow');
			}
			setTimeout(function() {
				$('#response').fadeOut('slow');
			}, 5000);

			/* Clear the textarea */
			$('textarea').val('');

		});

		event.preventDefault();
	});

});
</script>
<script type="text/javascript">
	$('#editorincluded').each(function () {
		var editor = new Jodit(this, {"buttons": "bold,italic,underline,strikethrough,eraser,ul,ol,indent,outdent,left,font,fontsize,paragraph,brush,superscript,subscript,image,video"});
		editor.value = '<?php echo $server->data->description; ?>';
	});
</script>