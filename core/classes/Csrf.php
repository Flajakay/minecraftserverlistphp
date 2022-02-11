<?php

class CsrfProtection {
	private $previous_hash;
	public  $hash;

	public function __construct() {
		/* Generate a new token */
		$token = md5(time() + time());

		/* Save the previous hash, if there is none then add the new token */
		$this->previous_hash = (isset($_SESSION['token'])) ? $_SESSION['token'] : $token;

		/* Save the new session / variable */
		if(basename($_SERVER['SCRIPT_NAME']) == 'index.php') $_SESSION['token'] = $token;
		@$this->hash = $_SESSION['token'];

	}

	public function is_valid($key = 'token') {

		return (isset($_POST[$key]) && ($_POST[$key] === $this->previous_hash)) ||
			   (isset($_GET[$key])  && ($_GET[$key]  === $this->previous_hash));
	}

}

?>