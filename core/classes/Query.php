<?php


class Query {
	private $socket;
	private $address;
	private $port;
	private $engine;
	private $timeout;
	public  $status;
	private $protocol;


	public function __construct($address, $port, $engine = 'minecraft', $timeout = 2) {

		/* Instantiating the variables */
		$this->address 	= $address;
		$this->port 	= $port;
		$this->engine 	= $engine;
		$this->timeout 	= $timeout;

		/* Load the engine if existing and initiate the class throught the protocol variable */
		if($this->engine !== false) {
			$this->loadEngine($this->engine);

			$this->protocol = new $this->engine();
		} 
		/* Else create an empty class and give the connectionType a default value */
		else {
			$this->protocol = new stdClass();
			$this->protocol->connectionType = "udp://";
		}

		/* Try and connect to the server */
		$this->connect();

	}


	public function loadEngine($engine) {
		$path = "core/classes/engines/" . $engine . ".php";

		if(file_exists($path)) {
			require_once $path;
		} else {
			throw new Exception("The selected engine ( " . $engine . " ) doesn't exist !");
		}

	}

	public function connect() {

		/* Try and connect to the server */
		$this->socket = @fsockopen($this->protocol->connectionType . $this->address, $this->port, $errno, $errstr, $this->timeout);

		/* Set the timeout */
		@stream_set_timeout($this->socket, $this->timeout);

		/* Establish the server status */
		$this->status = (!$this->socket) ? false : true;
	
	}

	public function disconnect() {

		/* Disconnect from the server */
		fclose($this->socket);
		
	}


	public function query() {

		/* If server is offline, return false */
		if(!$this->status) return false;

		/* Initiate the protocol */
		$this->protocol->initiate($this->socket, $this->address, $this->port);

		/* Store the received data into a variable */
		$info = $this->protocol->get_data();

		/* If we got no response then return_false() */
		return $info;

	}


	public function return_false() {

		return array(
				'general' => array(
					'online_players' => array(
						'name' => 'false',
						'icon' => 'false',
						'value' => 'false'
					),
					'maximum_online_players' => array(
						'name' => 'false',
						'icon' => 'false',
						'value' => 'false'
					),
					'motd' => array(
						'name' => 'false',
						'icon' => 'false',
						'value' => 'false'
					),
					'server_version' => array(
						'name' => 'false',
						'icon' => 'false',
						'value' => 'false'
					),
				),

				'players' => 'false',
			);

	}






}


?>