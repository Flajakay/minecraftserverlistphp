<?php
User::logged_in_redirect();
if(empty($_GET['email']) || empty($_GET['lost_password_code'])) redirect();

/* Check if the lost password code is correct */
$stmt = $database->prepare("SELECT `user_id` FROM `users` WHERE `email` = ? AND `lost_password_code` = ?");
$stmt->bind_param('ss', $_GET['email'], $_GET['lost_password_code']);
$stmt->execute();
$stmt->store_result();
$num_rows = $stmt->num_rows;
$stmt->fetch();
$stmt->close();

if($num_rows < 1 || strlen($_GET['lost_password_code']) < 32) redirect();

if(!empty($_POST)) {
    /* Check for any errors */
    if(strlen(trim($_POST['new_password'])) < 6) {
        $_SESSION['error'][] = $language['errors']['password_too_short'];
    }
    if($_POST['new_password'] !== $_POST['repeat_password']) {
        $_SESSION['error'][] = $language['errors']['passwords_doesnt_match'];
    }

    if(empty($_SESSION['error'])) {
        /* Encrypt the new password */
        $new_password = User::encrypt_password(User::x_to_y('email', 'username', $_GET['email']), $_POST['new_password']);

        /* Update the password & empty the reset code from the database */
        $stmt = $database->prepare("UPDATE `users` SET `password` = ?, `lost_password_code` = 0  WHERE `email` = ?");
        $stmt->bind_param('ss', $new_password, $_GET['email']);
        $stmt->execute(); 
        $stmt->close();

        /* Store success message */
        $_SESSION['success'][] = $language['messages']['resetpassword'];

    } 

    display_notifications();

}

initiate_html_columns();

?>

<h3><?php echo $language['headers']['resetpassword']; ?></h3>

<form action="" method="post" role="form">

    <div class="form-group">
    	<label><?php echo $language['forms']['new_password']; ?></label>
    	<input type="password" name="new_password" class="form-control" />
    </div>

    <div class="form-group">
    	<label><?php echo $language['forms']['repeat_password']; ?></label>
    	<input type="password" name="repeat_password" class="form-control" />
    </div>

    <div class="form-group">
        <button type="submit" name="submit" class="btn btn-default col-lg-4"><?php echo $language['forms']['submit']; ?></button><br /><br />
    </div>

</form>