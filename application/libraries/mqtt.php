<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mqtt {

	private $socket; 			/* holds the socket	*/
	private $msgid = 1;			/* counter for message id */
	public $keepalive = 300;		/* default keepalive timmer */
	public $timesinceping;		/* host unix time, used to detect disconects */
	public $topics = array(); 	/* used to store currently subscribed topics */
	public $debug = false;		/* should output debug messages */
	public $address;			/* broker address */
	public $port;				/* broker port */
	public $clientid;			/* client id sent to brocker */
	public $will;				/* stores the will of the client */
	private $username;			/* stores username */
	private $password;			/* stores password */

	function __construct($params) {
		$address = $params['address'];
		$port = $params['port'];
		$clientid = $params['clientid'];
		
		$this->broker($address, $port, $clientid);
	}

	/* sets the broker details */
	function broker($address, $port, $clientid){
		$this->address = $address;
		$this->port = $port;
		$this->clientid = $clientid;		
	}

	/* connects to the broker 
		inputs: $clean: should the client send a clean session flag */
	function connect($clean = true, $will = NULL, $username = NULL, $password = NULL){

		if($will) $this->will = $will;
		if($username) $this->username = $username;
		if($password) $this->password = $password;

		$address = gethostbyname($this->address);	
		
		$this->socket = fsockopen($address, $this->port, $errno, $errstr, 60);

		if (!$this->socket ) {
		    error_log("fsockopen() $errno, $errstr \n");
			return false;
		}

		stream_set_timeout($this->socket, 5);
		stream_set_blocking($this->socket,true);

		$i = 0;
		$buffer = "";

		$buffer .= chr(0x00); $i++;
		$buffer .= chr(0x06); $i++;
		$buffer .= chr(0x4d); $i++;
		$buffer .= chr(0x51); $i++;
		$buffer .= chr(0x49); $i++;
		$buffer .= chr(0x73); $i++;
		$buffer .= chr(0x64); $i++;
		$buffer .= chr(0x70); $i++;
		$buffer .= chr(0x03); $i++;

		//No Will
		$var = 0;
		if($clean) $var+=2;

		//Add will info to header
		if($this->will != NULL){
			$var += 4; // Set will flag
			$var += ($this->will['qos'] << 3); //Set will qos
			if($this->will['retain'])	$var += 32; //Set will retain
		}

		if($this->username != NULL) $var += 128;	//Add username to header
		if($this->password != NULL) $var += 64;	//Add password to header

		$buffer .= chr($var); $i++;

		//Keep alive
		$buffer .= chr($this->keepalive >> 8); $i++;
		$buffer .= chr($this->keepalive & 0xff); $i++;

		$buffer .= $this->strwritestring($this->clientid,$i);

		//Adding will to payload
		if($this->will != NULL){
			$buffer .= $this->strwritestring($this->will['topic'],$i);  
			$buffer .= $this->strwritestring($this->will['content'],$i);
		}

		if($this->username) $buffer .= $this->strwritestring($this->username,$i);
		if($this->password) $buffer .= $this->strwritestring($this->password,$i);

		$head = "  ";
		$head{0} = chr(0x10);
		$head{1} = chr($i);

		fwrite($this->socket, $head, 2);
		fwrite($this->socket,  $buffer);

	 	$string = $this->read(4);

		if(ord($string{0})>>4 == 2 && $string{3} == chr(0)){
			if($this->debug) echo "Connected to Broker\n"; 
		}else{	
			error_log( sprintf("Connection failed! (Error: 0x%02x 0x%02x)\n", ord($string{0}), ord($string{3})));
			return false;
		}

		$this->timesinceping = time();

		return true;
	}

	/* read: reads in so many bytes */
	function read($int = 8192 ){
		$string="";
		$togo = $int;
		while (!feof($this->socket) && $togo>0) {
			$togo = $int - strlen($string);
			if($togo) $string .= fread($this->socket, $togo);
		}
		return $string;
	}

	/* subscribe: subscribes to topics */
	function subscribe($topics, $qos = 0){
		$i = 0;
		$buffer = "";
		$id = $this->msgid;
		$buffer .= chr($id >> 8);  $i++;
		$buffer .= chr($id % 256);  $i++;

		foreach($topics as $key => $topic){
			$buffer .= $this->strwritestring($key,$i);
			$buffer .= chr($topic["qos"]);  $i++;
			$this->topics[$key] = $topic; 
		}

		$cmd = 0x80;
		$cmd +=	($qos << 1);

		$head = chr($cmd);
		$head .= chr($i);
		
		fwrite($this->socket, $head, 2);
		fwrite($this->socket, $buffer, $i);
		$string = $this->read(2);
		
		$bytes = ord(substr($string,1,1));
		$string = $this->read($bytes);
	}

	/* ping: sends a keep alive ping */
	function ping(){
		$head = " ";
		$head = chr(0xc0);		
		$head .= chr(0x00);
		fwrite($this->socket, $head, 2);
		if($this->debug) echo "ping sent\n";
	}

	/* disconnect: sends a proper disconect cmd */
	function disconnect(){
		$head = " ";
		$head{0} = chr(0xe0);		
		$head{1} = chr(0x00);
		fwrite($this->socket, $head, 2);
	}

	/* close: sends a proper disconect, then closes the socket */
	function close(){
	 	$this->disconnect();
		fclose($this->socket);	
	}

	/* publish: publishes $content on a $topic */
	function publish($topic, $content, $qos = 0, $retain = 0){

		$i = 0;
		$buffer = "";

		$buffer .= $this->strwritestring($topic,$i);

		if($qos){
			$id = $this->msgid++;
			$buffer .= chr($id >> 8);  $i++;
		 	$buffer .= chr($id % 256);  $i++;
		}

		$buffer .= $content;
		$i+=strlen($content);

		$head = " ";
		$cmd = 0x30;
		if($qos) $cmd += $qos << 1;
		if($retain) $cmd += 1;

		$head{0} = chr($cmd);		
		$head .= $this->setmsglength($i);

		fwrite($this->socket, $head, strlen($head));
		fwrite($this->socket, $buffer, $i);
	}

	/* message: processes a received topic */
	function message($msg){
	 	
	 	$tlen = (ord($msg{0})<<8) + ord($msg{1});
		$topic = substr($msg,2,$tlen);
		$msg = substr($msg,($tlen+2));
		$found = 0;
		
		foreach($this->topics as $k=>$top) {
			if(strcmp($topic,$k) == 0) {
				$found = 1;
				break;
			}
		}
		if( $found ) {
			echo $msg;
		}
		if($this->debug && !$found) echo "Message received but no match in subscriptions\n";
	}
	
	/* proc: the processing loop for an "allways on" client */	
	function proc(){
		$sockets = array($this->socket);
		$w = $e = NULL;
		$cmd = 0;
		if($this->debug) echo "start wait\n";

		if(stream_select($sockets, $w, $e, ($this->keepalive/2))){
			if($this->debug) echo "found something\n";

			//$byte = fgetc($this->socket);
			if(feof($this->socket)){
				if($this->debug) echo "eof receive going to reconnect for good measure\n";
				fclose($this->socket);
				$this->connect(false);
				if(count($this->topics)) $this->subscribe($this->topics);
			}
			
			$byte = $this->read(1);
			$cmd = (int)(ord($byte)/16);
			if($this->debug) echo "Received: $cmd\n";

			$multiplier = 1; 
			$value = 0;
			do{
			  $digit = ord($this->read(1));
			  $value += ($digit & 127) * $multiplier; 
			  $multiplier *= 128;
			}while (($digit & 128) != 0);

			
			if($this->debug) echo "Fetching: $value\n";
			if($value) $string = $this->read($value,"fetch");
			if($cmd){
				switch($cmd){
					case 3:
						$this->message($string);
					break;
				}

				$this->timesinceping = time();
			}
			
		}else{
			if($this->debug) echo "not found something\n";
			$this->ping();	
		}

		if($this->timesinceping<(time()-($this->keepalive*2))){
			if($this->debug) echo "not seen a package in a while, disconnecting\n";
			fclose($this->socket);
			$this->connect(false);
			if(count($this->topics)) $this->subscribe($this->topics);
		}
		return true;
	}

	/* getmsglength: */
	function getmsglength(&$msg, &$i){

		$multiplier = 1; 
		$value = 0 ;
		do{
		  $digit = ord($msg{$i});
		  $value += ($digit & 127) * $multiplier; 
		  $multiplier *= 128;
		  $i++;
		}while (($digit & 128) != 0);

		return $value;
	}


	/* setmsglength: */
	function setmsglength($len){
		$string = "";
		do{
		  $digit = $len % 128;
		  $len = $len >> 7;
		  // if there are more digits to encode, set the top bit of this digit
		  if ( $len > 0 )
		    $digit = ($digit | 0x80);
		  $string .= chr($digit);
		}while ( $len > 0 );
		return $string;
	}

	/* strwritestring: writes a string to a buffer */
	function strwritestring($str, &$i){
		$ret = " ";
		$len = strlen($str);
		$msb = $len >> 8;
		$lsb = $len % 256;
		$ret = chr($msb);
		$ret .= chr($lsb);
		$ret .= $str;
		$i += ($len+2);
		return $ret;
	}

	function printstr($string){
		$strlen = strlen($string);
			for($j=0;$j<$strlen;$j++){
				$num = ord($string{$j});
				if($num > 31) 
					$chr = $string{$j}; else $chr = " ";
				printf("%4d: %08b : 0x%02x : %s \n",$j,$num,$num,$chr);
			}
	}
}

?>