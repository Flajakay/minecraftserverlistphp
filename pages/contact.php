<?php


if(!empty($_POST)) {
	$name = $_POST['name'];
	$subject = $_POST['subject'];
	$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
	$message = $_POST['message'];

	$required_fields = array('name', 'subject', 'email', 'message');
	
	foreach($_POST as $key=>$value) {
		if(empty($value) && in_array($key, $required_fields) == true) {
			$_SESSION['error'][] = $language['errors']['marked_fields_empty'];
			break 1;
		}
	}
	if(empty($_SESSION['error'])) {
		sendmail($settings->contact_email, $email, $subject, $message);
		
		$_SESSION['success'][] = $language['messages']['contact'];
	}
	
display_notifications();

}

initiate_html_columns();

?>



<h3><?php echo $language['headers']['contact']; ?></h3>
<form action="" method="post" role="form" enctype="multipart/form-data">

	<div class="form-group">
		<label><?php echo $language['forms']['name']; ?> *</label>
		<input type="text" name="name" class="form-control" value="" />
	</div>


	<div class="form-group">
		<label><?php echo $language['forms']['email']; ?> *</label>
		<input type="text" name="email" class="form-control" value="" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['subject']; ?> *</label>
		<input type="text" name="subject" class="form-control" value="" />
	</div>
	

	<div class="form-group">
		<label><?php echo $language['forms']['message']; ?> *</label>
		<textarea name="message" class="form-control" rows="6"></textarea>
	</div>
	
	<div class="form-group">
		<button type="submit" name="submit" class="btn btn-default col-lg-4"><?php echo $language['forms']['submit']; ?></button><br /><br />
	</div>

</form>