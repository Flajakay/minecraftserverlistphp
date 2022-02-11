<?php

class User {
	public $user_id;


	public function __construct($user_id) {
		global $database;
		$this->user_id = $user_id;

		$stmt = $database->prepare("SELECT * FROM `users` WHERE `user_id` = ?");
		$stmt->bind_param('s', $this->user_id);
		$stmt->execute();

		$parameters = array();
		$meta = $stmt->result_metadata();
		while($field = $meta->fetch_field()) {
			$parameters[] = &$row[$field->name]; 
		}

		call_user_func_array(array($stmt, 'bind_result'), $parameters);

		while($stmt->fetch()) {
			foreach($row as $key => $val) {
				$this->{$key} = $val;
			}
		}

		$stmt->close();
	}
	


	public static function encrypt_password($username, $password) {
		//using $username as salt
		$username = hash('sha512', $username);
		$hash	  = hash('sha512', $password . $username);
		
		//iterating the hash
		for($i = 1;$i <= 1000;$i++) {
			$hash = hash('sha512', $hash);
		}
		
		return $hash;
	}

	public static function login($username, $password) {
		global $database;

		$stmt = $database->prepare("SELECT `user_id` FROM `users` WHERE `username` = ? AND `password` = ?");
		$stmt->bind_param('ss', $username, $password);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();

		return (!is_null($result)) ? $result : false;
	}

	public static function logout() {
		session_destroy();
		setcookie('username', '', time()-30);
		setcookie('password', '', time()-30);
		setcookie('user_id', '', time()-30);
		redirect();
	}

	public static function user_active($username) {
		global $database;

		$stmt = $database->prepare("SELECT `active` FROM `users` WHERE `username` = ?");
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();

		if($result == true) return true;
		else return false;
	}

	public static function logged_in_redirect() { 
		global $language;

		if(self::logged_in()) {
			$_SESSION['error'][] = $language['errors']['cant_access'];
			redirect();
		}
	}

	public static function logged_in() { 
		if(isset($_COOKIE['username']) && isset($_COOKIE['password']) && User::login($_COOKIE['username'], $_COOKIE['password']) !== false && $_COOKIE['user_id'] == User::login($_COOKIE['username'], $_COOKIE['password'])) {
			return true;
		} elseif(isset($_SESSION['user_id'])) {
			return true;
		} else return false;
	}

	public static function get_back($new_page = 'index') {
		if(isset($_SERVER['HTTP_REFERER']))
			Header('Location: ' . $_SERVER['HTTP_REFERER']);
		else
			redirect($new_page);
		die();
	}

	public static function get_type($user_id) {
		global $database;

		$stmt = $database->prepare("SELECT `type` FROM `users` WHERE `user_id` = ?");
		$stmt->bind_param('s', $user_id);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();

		return $result;
	}

	public static function get_servers($user_id) {
		global $database;

		$stmt = $database->prepare("SELECT COUNT(`user_id`) FROM `servers` WHERE `user_id` = ?");
		$stmt->bind_param('s', $user_id);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();

		return $result;
	}

	public static function get_profile_link($user_id) {
		global $database;

		$stmt = $database->prepare("SELECT `username`, `name` FROM `users` WHERE `user_id` = ?");
		$stmt->bind_param('s', $user_id);
		$stmt->execute();
		$stmt->bind_result($username, $name);
		$stmt->fetch();
		$stmt->close();

		return ($username == false) ? 'Anonymous' : '<a href="profile/' . $username . '">' . $name . '</a>';
	}


	public static function x_to_y($x, $y, $x_value, $from = 'users') {
		global $database;

		$stmt = $database->prepare("SELECT `{$y}` FROM `{$from}` WHERE `{$x}` = ?");
		$stmt->bind_param('s', $x_value);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();

		return $result;
	}

	public static function x_exists($x, $x_value, $from = 'users') {
		global $database;

		$stmt = $database->prepare("SELECT `{$x}` FROM `{$from}` WHERE `{$x}` = ?");
		$stmt->bind_param('s', $x_value);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();

		if(!is_null($result)) return true;
		else return false;
	}


	public static function online_users($seconds) {
		global $database;

		$stmt = $database->prepare("SELECT COUNT(`user_id`) FROM `users` WHERE `last_activity` > UNIX_TIMESTAMP() - ?");
		$stmt->bind_param('i', $seconds);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();

		return $result;
	}	


	public static function check_permission($level = 1) {
		global $account_user_id;
		global $language;

		if(!self::logged_in() || self::get_type($account_user_id) < $level) {
			$_SESSION['error'][] = $language['errors']['cant_access'];

			if(isset($_SERVER['HTTP_REFERER'])) Header('Location: ' . $_SERVER['HTTP_REFERER']); else redirect();
			die();
		}
	}

	public static function is_admin($user_id) {
		return (self::get_type($user_id) > 0) ? true : false;
	}

}

?>