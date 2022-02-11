<?php

class minecraft {
	public $connectionType = "tcp://";
	private $socket;
	private $address;
	private $port;


	public function initiate($socket, $address, $port) {

		$this->socket 	= $socket;
		$this->address 	= $address;
		$this->port	 	= $port;

	}


	public function get_data() {
		global $language;

		/* Handshake with the server */
		$this->handshake();

		/* Request data */
		$this->request();

		/* Gather data */
		$data = $this->receive();

		/* Custom check for description */
		if(is_array($data['description'])) $data['description'] = (string) $data['description']['text'];

		return array(
			"general" => array(
				'online_players' => array(
					'name' => $language['server']['general_online_players'],
					'icon' => 'user',
					'value' => $data['players']['online']
				),

				'maximum_online_players' => array(
					'name' => $language['server']['general_maximum_online_players'],
					'icon' => 'user',
					'value' => $data['players']['max']
				),
				
				'motd' => array(
					'name' => $language['server']['motd'],
					'icon' => 'tasks',
					'value' => $data['description']
				),

				'server_version' => array(
					'name' =>  $language['server']['server_version'],
					'icon' => 'wrench',
					'value' => $data['version']['name']
				)
			),

			'players' => 'false'
			
		); 

		/* 	'Favicon'				 => '<img src="' . $data['favicon'] . '" alt="Favicon" />' */
	}


	private function handshake() {

		/* Handshake using xPaw's scriptt */
		$data = "\x00";  										// packet ID = 0 (varint)
		$data .= "\x04"; 										// Protocol version (varint)
		$data .= pack('c', strlen($this->address)) . $this->address;  // Server (varint len + UTF-8 addr)
		$data .= pack('n', $this->port);						// Server port (unsigned short)
		$data .= "\x01"; 										// Next state: status (varint)
		$data = pack('c', strlen($data)) . $data; 			// prepend length of packet ID + data

		/* Sending the data to the server */
		fwrite($this->socket, $data); 

	}


	private function request() {

		/* Request data */
		fwrite($this->socket, "\x01\x00");

	}

	private function receive() {

		/* Read the length with xpaws script */
		$length = $this->ReadVarInt();

		/* Initiate the $data variable ( where returned data from the server will be stored ) */
		$data = "";

		/* Read until there is no more data to read */
		while(strlen($data) < $length) {
			$r = $length - strlen($data);
			$data .= fread($this->socket, $r);
		}

		/* Decode JSON */
		//$data = json_decode(substr($data, 3), true);
		$data = json_decode(strstr($data, '{'), true);

		return $data;
	}

	private function ReadVarInt() {

		$i = 0;
		$j = 0;

		while( true )
		{
			$k = @fgetc( $this->socket );

			if( $k === FALSE )
			{
				return 0;
			}

			$k = Ord( $k );

			$i |= ( $k & 0x7F ) << $j++ * 7;

			if( $j > 5 )
			{
				throw new Exception( 'VarInt too big' );
			}

			if( ( $k & 0x80 ) != 128 )
			{
				break;
			}
		}

		return $i;
	}

	private function cleanMotd($motd) {

		if(is_array($motd)) {
			$data = preg_replace("/(§.)/", "",$motd['text']);
			$data = preg_replace("/[^[:alnum:][:punct:] ]/", "", $motd['text']);
		} else {
			$data = preg_replace("/(§.)/", "",$motd);
			$data = preg_replace("/[^[:alnum:][:punct:] ]/", "", $motd);
		}

		return $data;

	}

	public static function decodeMotd($minetext) {
		preg_match_all("/[^§&]*[^§&]|[§&][0-9a-z][^§&]*/", $minetext, $brokenupstrings); 
		$returnstring = "";
		foreach ($brokenupstrings as $results) {
			$ending = '';
			foreach ($results as $individual) {
				$code = preg_split("/[&§][0-9a-z]/", $individual);
				preg_match("/[&§][0-9a-z]/", $individual, $prefix);
				if (isset($prefix[0])) {
					$actualcode = substr($prefix[0], 1);
					switch ($actualcode) {
						case "1":
						$returnstring = $returnstring.'<FONT COLOR="0000AA">';
						$ending = $ending ."</FONT>";
						break;
						case "2":
						$returnstring = $returnstring.'<FONT COLOR="00AA00">';
						$ending =$ending ."</FONT>";
						break;
						case "3":
						$returnstring = $returnstring.'<FONT COLOR="00AAAA">';
						$ending = $ending ."</FONT>";
						break;
						case "4":
						$returnstring = $returnstring.'<FONT COLOR="AA0000">';
						$ending =$ending ."</FONT>";
						break;
						case "5":
						$returnstring = $returnstring.'<FONT COLOR="AA00AA">';
						$ending =$ending . "</FONT>";
						break;
						case "6":
						$returnstring = $returnstring.'<FONT COLOR="FFAA00">';
						$ending =$ending ."</FONT>";
						break;
						case "7":
						$returnstring = $returnstring.'<FONT COLOR="AAAAAA">';
						$ending = $ending ."</FONT>";
						break;
						case "8":
						$returnstring = $returnstring.'<FONT COLOR="555555">';
						$ending =$ending ."</FONT>";
						break;
						case "9":
						$returnstring = $returnstring.'<FONT COLOR="5555FF">';
						$ending =$ending . "</FONT>";
						break;
						case "a":
						$returnstring = $returnstring.'<FONT COLOR="55FF55">';
						$ending =$ending ."</FONT>";
						break;
						case "b":
						$returnstring = $returnstring.'<FONT COLOR="55FFFF">';
						$ending = $ending ."</FONT>";
						break;
						case "c":
						$returnstring = $returnstring.'<FONT COLOR="FF5555">';
						$ending =$ending ."</FONT>";
						break;
						case "d":
						$returnstring = $returnstring.'<FONT COLOR="FF55FF">';
						$ending =$ending ."</FONT>";
						break;
						case "e":
						$returnstring = $returnstring.'<FONT COLOR="FFFF55">';
						$ending = $ending ."</FONT>";
						break;
						case "f":
						$returnstring = $returnstring.'<FONT COLOR="FFFFFF">';
						$ending =$ending ."</FONT>";
						break;
						case "l":
						if (strlen($individual)>2) {
							$returnstring = $returnstring.'<span style="font-weight:bold;">';
							$ending =  "</span>".$ending;
							break;
						}
						case "m":
						if (strlen($individual)>2) {
							$returnstring = $returnstring.'<strike>';
							$ending = "</strike>".$ending;
							break;
						}
						case "n":
						if (strlen($individual)>2) {
							$returnstring = $returnstring.'<span style="text-decoration: underline;">';
							$ending = "</span>".$ending;
							break;
						}
						case "o":
						if (strlen($individual)>2) {
							$returnstring = $returnstring.'<i>';
							$ending ="</i>".$ending;
							break;
						}
						case "r":
						$returnstring = $returnstring.$ending;
						$ending = '';
						break;
					}
					if (isset($code[1])) {
						$returnstring = $returnstring.$code[1];
						if (isset($ending)&&strlen($individual)>2) {
							$returnstring = $returnstring.$ending;
							$ending = '';
						}
					}
				}
				else{
					$returnstring = $returnstring.$individual;
				}

			}
		}

		return $returnstring;
	}

}

?>