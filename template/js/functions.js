
$(document).ready(function() {

	/* Submit disable after 1 click */
	$('[type=submit][name=submit]').on('click', function() {
		$(this).addClass('disabled');
	});

	/* Confirm delete handler */
	$('body').on('click', '[data-confirm]', function(){
		var message = $(this).attr('data-confirm');
		if(confirm(message) == false) return false;
	});

	/* Enable tooltips */
	$('.tooltipz').tooltip();

	/* Initialize the timeago jquery function */
	$('.timeago').timeago();
	
});

/* Report system */
function report(reported_id, type) {

	/* Change the type of the report and the reported_id */
	$('input[name="type"]').val(type);
	$('input[name="reported_id"]').val(reported_id);

	/* Display the modal */
	$('#report').modal('show');
}


/* Show More */ 
function showMore(start, page, selector, showmore) {
	/* Post and get response */
	$.post(page, "limit="+start, function(data) {
		
		if($.trim(data) == "") {
			/* If no response, fadeOut the button */
			$(showmore).fadeOut('slow');

		} else {
			/* Remove the current show more button */
			$(showmore).remove(); 

			/* Append the result to the div */
			$(data).hide().appendTo(selector).fadeIn('slow');

			/* Refresh the bootstrap tooltip */
			$('.tooltipz').tooltip();
		}
	});
}
