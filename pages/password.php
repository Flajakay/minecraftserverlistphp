<?php
User::check_permission(0);


if(!empty($_POST)) {
    if(User::encrypt_password($account->username, $_POST['old_password']) !== $account->password) {
        $_SESSION['error'][] = $language['errors']['incorrect_old_password'];
    }
    if(strlen(trim($_POST['new_password'])) < 6) {
       $_SESSION['error'][] = $language['errors']['password_too_short'];
    }
    if($_POST['new_password'] !== $_POST['repeat_password']) {
        $_SESSION['error'][] = $language['errors']['passwords_doesnt_match'];
    }
    

    if(empty($_SESSION['error'])) {
        $new_password = User::encrypt_password($account->username, $_POST['new_password']);
        $stmt = $database->prepare("UPDATE `users` SET `password` = ?  WHERE `user_id` = {$account_user_id}");
        $stmt->bind_param('s', $new_password);
        $stmt->execute(); 
        $stmt->close();

        User::logout();
    }

    display_notifications();
    
}

initiate_html_columns();


?>

<h3><?php echo $language['headers']['change_password']; ?></h3>

<form action="" method="post" role="form">

    <div class="form-group">
    	<label><?php echo $language['forms']['old_password']; ?></label>
    	<input type="password" name="old_password" class="form-control" />
    </div>

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
