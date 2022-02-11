<?php

class Server {
	public $exists;
	public $data;
	public $category;

	public function __construct($address, $port, $server_id = false) {
		global $database; 

		/* Get all the server information from the database */
		if($server_id) {
			$stmt = $database->prepare("SELECT * FROM `servers` WHERE `server_id` = ?");
			$stmt->bind_param('s', $server_id);
		} else {
			$stmt = $database->prepare("SELECT * FROM `servers` WHERE `address` = ? AND `connection_port` = ?");
			$stmt->bind_param('ss', $address, $port);
		}
		$stmt->execute();
		bind_object($stmt, $this->data);
		$stmt->fetch();
		$stmt->close();

		/* Determine if the server exists */
		$this->exists = ($this->data !== NULL) ? true : false;

		/* If server exists gather the category information */
		if($this->exists) {

			/* Get this month hits */
			$result = $database->query("
				SELECT 
					COUNT(`id`) FROM `points` as `count`
				WHERE 
					`type` = 0 AND
					`server_id` = {$this->data->server_id} AND
					MONTH(FROM_UNIXTIME(`timestamp`)) = MONTH(CURDATE()) AND
					YEAR(FROM_UNIXTIME(`timestamp`)) = YEAR(CURDATE())
			");

			$this->hits = $result->fetch_row();
			$this->hits = $this->hits[0];

			/* Get category data */
			$this->category = new StdClass;

			$stmt = $database->prepare("SELECT `name`, `url` FROM `categories` WHERE `category_id` = ?");
			$stmt->bind_param('s', $this->data->category_id);
			$stmt->execute();
			bind_object($stmt, $this->category);
			$stmt->fetch();
			$stmt->close(); 
			
			/* Determine if category exists */
			if($this->category !== NULL) {
				$this->category->exists = true;
			} else {
				$this->category = new StdClass;
				$this->category->exists = false;
			}

			/* Process the custom field */				
			if(!empty($this->data->custom)) {				
				$this->data->custom = json_decode($this->data->custom);
			} else {
				$this->data->custom = new StdClass;
				$this->data->custom->votifier_public_key = $this->data->custom->votifier_ip = $this->data->custom->votifier_port = null;
			}
			

		}
	}

	public static function delete_server($server_id) {
		global $database;

		/* We need to make sure to delete all the data of the specific server */
		$database->query("DELETE FROM `servers` WHERE `server_id` = {$server_id}");
		$database->query("DELETE FROM `points` WHERE `server_id` = {$server_id}");
		$database->query("DELETE FROM `reports` WHERE `type` = 2 AND `reported_id` = {$server_id}");
		$database->query("DELETE FROM `favorites` WHERE `server_id` = {$server_id}");
		$database->query("DELETE FROM `comments` WHERE `server_id` = {$server_id}");

	}

	public static function get_category($category_id) {
		global $database;

		$stmt = $database->prepare("SELECT `name`, `url` FROM `categories` WHERE `category_id` = ?");
		$stmt->bind_param('s', $category_id);
		$stmt->execute();
		bind_object($stmt, $category);
		$stmt->fetch();
		$stmt->close();

		echo '<a href="category/' . $category->url . '">' . $category->name . '</a>';
	}

}



?>