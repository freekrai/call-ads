<?php
/*

Twimlets are tiny web applications that implement basic voice functionality. Think of them as "Widgets" in the web world.

You don't need to sign up to use them, they're just available for your use. And they're stateless, so you can just pass the URL parameters of execution in when you call them.

Twimlets are also open source, so if you'd prefer to download the code to your server, you can modify to suit your exact needs!

User would build a call flow, which would appear as:

	http://mysite.com/flow/123
	
and 123 would then get translated into:

	http://twimlets.com/voicemail?Email=somebody@somedomain.com&Message=Please+Leave+A+Message

As an example...


	This would let users be able to set up their own call flows...
	
As a plus side, this would also work well for troubleshooting TWiML as we can let them output as XML files..


Voicemail:

	http://twimlets.com/voicemail?Email=somebody@somedomain.com

	http://twimlets.com/voicemail?Email=somebody@somedomain.com&Message=Please+Leave+A+Message

	http://twimlets.com/voicemail?Email=somebody@somedomain.com&Message=http://myserver.com/please-leave-message.mp3
	
	http://twimlets.com/voicemail?Email=somebody@somedomain.com&Transcribe=false

Forward:

	http://twimlets.com/forward?PhoneNumber=415-555-1212
	
	http://twimlets.com/forward?PhoneNumber=415-555-1212&FailUrl=http://myapp.com/please-try-later.mp3
	
	http://twimlets.com/forward?PhoneNumber=415-555-1212&AllowedCallers[0]=650-555-1212&AllowedCallers[1]=510-555-1212

Conference:

	http://twimlets.com/conference?Name=foo
	
	http://twimlets.com/conference?Moderators[0]=415-555-1212&Moderators[1]=555-867-5309
	
	http://twimlets.com/conference?Music=rock
	
	http://twimlets.com/conference?Password=12345

Menu:

	http://twimlets.com/menu?Message=Hi+There&Options[1]=http://foo.com
	
	http://twimlets.com/menu?Message=Hi+There&Options[1]=http://foo.com&Options[2]=http://bar.com
	
	http://twimlets.com/menu?Message=Hi+There&Options[101]=http://bob.com&Options[102]=http://ann.com&Options[0]=http://operator.com

Find Me:

	http://twimlets.com/findme?PhoneNumbers[0]=415-555-1212&PhoneNumbers[1]=415-555-1313&PhoneNumbers[2]=415-555-1414

	http://twimlets.com/findme?PhoneNumbers[0]=415-555-1212&PhoneNumbers[1]=415-555-1313&PhoneNumbers[2]=415-555-1414&FailUrl=http://myapp.com/please-try-later.mp3

Simulring:

	http://twimlets.com/simulring?PhoneNumbers[0]=415-555-1212&PhoneNumbers[1]=415-555-1313&PhoneNumbers[2]=415-555-1414

	http://twimlets.com/simulring?PhoneNumbers[0]=415-555-1212&PhoneNumbers[1]=415-555-1313&PhoneNumbers[2]=415-555-1414&FailUrl=http://myapp.com/please-try-later.mp3

Call Me:

	http://twimlets.com/callme?PhoneNumber=415-555-1212
	
	http://twimlets.com/callme?PhoneNumber=415-555-1212&FailUrl=http://myapp.com/please-try-later.mp3

Message:

	http://twimlets.com/message?Message[0]=http://myserver.com/hello.mp3&Message[1]=Thank+You+For+Calling
	
	http://twimlets.com/message?Message[0]=http://myserver.com/1.mp3&Message[1]=http://myserver.com/2.mp3&Message[2]=I+Just+Played+A+File&Message[3]=I+Just+Said+Some+Text

Echo:

	http://twimlets.com/echo?Twiml=%3CResponse%3E%3CSay%3EHi+there.%3C%2FSay%3E%3C%2FResponse%3E

Hold Music:

	http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical&Message=thankyou%20for%20waiting.%20please%20stay%20on%20the%20line


com.twilio.music.ambient
com.twilio.music.classical
com.twilio.music.electronica
com.twilio.music.guitars
com.twilio.music.newage
com.twilio.music.rock
com.twilio.music.soft-rock

*/
class twimletsController extends \Jolt\Controller{
    public function my_name($name = 'default'){
		$this->app->render( 'page', array(
			"pageTitle"=>"Greetings ".$this->sanitize($name)."!",
			'title'=>'123',
			"body"=>"Greetings ".$this->sanitize($name)."!"
		));
    }

	public function voicemail(){
		// initiate response library
		$response = new Services_Twilio_Twiml();

		// setup from email headers
		$headers = 'From: voicemail@twimlets.com' . "\r\n" .
		'Reply-To: voicemail@twimlets.com' . "\r\n" .
		'X-Mailer: Twilio Twimlets';
		
		// grab the to and from phone numbers
		$from = strlen($_REQUEST['From']) ? $_REQUEST['From'] : $_REQUEST['Caller'];
		$to = strlen($_REQUEST['To']) ? $_REQUEST['To'] : $_REQUEST['Called'];
		
		if(in_array($_GET['Email'], $emailBlacklist)) {
			$response->hangup();
			echo $response;
			die;
		}
		
		// check transcription response
		if(strtolower($_REQUEST['TranscriptionStatus']) == "completed") {
			
			// email message with the text of the transcription and a link to the audio recording
			$body = "You have a new voicemail from " . format_telephone($from) . "\n\n";
			$body .= "Text of the transcribed voicemail:\n{$_REQUEST['TranscriptionText']}.\n\n";
			$body .= "Click this link to listen to the message:\n{$_REQUEST['RecordingUrl']}.mp3";
			
			mail($_GET['Email'], "New Voicemail Message from " . format_telephone($from), $body, $headers);
			die;
		
		} else if(strtolower($_REQUEST['TranscriptionStatus']) == "failed") {
		
			// transcription failed so just email message with just a link to the audio recording
			$body = "You have a new voicemail from ".format_telephone($from)."\n\n";
			$body .= "Click this link to listen to the message:\n{$_REQUEST['RecordingUrl']}.mp3";
			
			mail($_GET['Email'], "New Voicemail Message from " . format_telephone($from), $body, $headers);
			die;
			
		} else if(strlen($_REQUEST['RecordingUrl'])) {
			
			// returning from the Record so hangup
			$response->say("Thanks.  Good bye.");
			$response->hangup();
			
			// not transcribing, email message with a link to the audio recording
			if(strlen($_GET['Transcribe']) && strtolower($_GET['Transcribe']) != 'true') {
				$body = "You have a new voicemail from ".format_telephone($from)."\n\n";
				$body .= "Click this link to listen to the message:\n{$_REQUEST['RecordingUrl']}.mp3";
				
				mail($_GET['Email'], "New Voicemail Message from " . format_telephone($from), $body, $headers);
			}
		} else {
			// no message has been received, so play a VM greeting
			// figure out the message to say or play before the recording
			// first, check to see if we have an http URL (simple check)
			if(strtolower(substr(trim($_GET['Message']), 0, 4)) == "http")
				$response->play($_GET['Message']);
				// check if we have any message, if so, read it back 
			elseif(strlen(trim($_GET['Message'])))
				$response->say(stripslashes($_GET['Message']));
				// no message, just use a default
			else
				$response->say("Please leave a message after the beep.");

			// record with / without transcription
			if((!strlen($_GET['Transcribe'])) || strtolower($_GET['Transcribe']) == 'true')
				$params = array("transcribe"=>"true", "transcribeCallback"=>"{$_SERVER['SCRIPT_URI']}?Email={$_GET['Email']}");
			else
				$params = array();
			
			// add record with the specified params
			$response->record($params);
		}
		echo $response; 
	}
	
	public function forward(){
		$response = new Services_Twilio_Twiml();

		if(isset($_REQUEST['Dial']) && (strlen($_REQUEST['DialStatus']) || strlen($_REQUEST['DialCallStatus']))) { 
			if($_REQUEST['DialCallStatus'] == "completed" || $_REQUEST['DialStatus'] == "answered" || !strlen($_REQUEST['FailUrl'])) {
				// answered, or no failure url given, so just hangup
				$response->hangup();
			} else {
				// DialStatus was not answered, so redirect to FailUrl
				header("Location: {$_REQUEST['FailUrl']}");
				die;
			}
		} else {
			// No dial flag, means it's our first run through the script
			
			// if an array of Allowed Callers is provided and populated, determine if we're allowed to be forwarded
			if(is_array($_GET['AllowedCallers']) && count(array_filter($_GET['AllowedCallers']))) {
				
				// normalize all numbers, removing any non-digits
				foreach($_GET['AllowedCallers'] AS &$phone) {
					$phone = preg_replace('/[^0-9]/', '', $phone);
					
					if($_REQUEST['ApiVersion'] == '2008-08-01' && strlen($phone) == 11 && substr($phone, 0, 1) == "1") {
						$phone = substr($phone, 1);
					}
				}
			
				// grab the to and from phone numbers
				$from = strlen($_REQUEST['From']) ? $_REQUEST['From'] : $_REQUEST['Caller'];
				$to = strlen($_REQUEST['To']) ? $_REQUEST['To'] : $_REQUEST['Called'];
				
				// figure out if we're allowed to call or not
				$isAllowed = (in_array(preg_replace('[^0-9]', '', $from), $_GET['AllowedCallers']) || in_array(preg_replace('[^0-9]', '', $to), $_GET['AllowedCallers']));
			
			} else {
				// no allowed callers given, so just forward the call
				$isAllowed = true; 
			}
			
			if(!$isAllowed) {
				// forwarding is being restricted and we are not allowed to abort with a message
				$response->say("Sorry, you are calling from a restricted number. Good bye.");
			}else {
				// we made it to here, so just dial the number, with the optional Timeout given
				$actionUrl = $_SERVER['SCRIPT_URL'] . "?Dial=true" .
				(	$_REQUEST['FailUrl'] ? "&FailUrl=".urlencode($_REQUEST['FailUrl']) : "");
				$attributes =  array(
					'action' => $actionUrl,
					'timeout' => $_REQUEST['Timeout'] ? $_REQUEST['Timeout'] : 20,
				);
				if (isset($_GET['CallerId'])) {
					$attributes['callerId'] = $_GET['CallerId'];
				}
				$response->dial($_GET['PhoneNumber'], $attributes);
			}
		}
		echo $response;
	}
	
	public function conference(){
		$response = new Services_Twilio_Twiml();

		// grab the to and from phone numbers
		$from = strlen($_REQUEST['From']) ? $_REQUEST['From'] : $_REQUEST['Caller'];
		$to = strlen($_REQUEST['To']) ? $_REQUEST['To'] : $_REQUEST['Called'];
		
		// if password is set, then ask for it
		if(strlen($_GET['Password']) && $_REQUEST['Digits'] != $_GET['Password']) {
			// gather just enough digits, but a min of 3 for security
			$gather = $response->gather( array("numDigits" => max(3, strlen($_GET['Password']))) );
			$gather->say("Please enter your conference pass code");
			$response-redirect();
			echo $response;
			die;
		}
		
		// if an array of Moderator phone numbers is provided, determine if we're the moderator
		if(is_array($_GET['Moderators'])) {
			// normalize all numbers, removing any non-digits
			foreach($_GET['Moderators'] AS &$phone) {
				$phone = preg_replace('/[^0-9]/', '', $phone);
				// remove leading 1 if US
				if(strlen($phone) == 11 && substr($phone, 0, 1) == "1")
					$phone = substr($phone, 1);
			}
		
			//normalize the from number       
			$from = preg_replace('/[^0-9]/', '', $from);
			if(strlen($from) == 11 && substr($from, 0, 1) == "1") {            
				$from = substr($from, 1);
			}
		
			//normalize the to number          
			$to = preg_replace('/[^0-9]/', '', $to);
			if(strlen($to) == 11 && substr($to, 0, 1) == "1") {
				$to = substr($to, 1);
			}
		
			// figure out if we're a moderator or not
			$isModerator = (in_array($from, $_GET['Moderators']) || in_array($to, $_GET['Moderators']));
		} else{
			// no moderators given, so just do a normal conference w/o a moderator
			$isModerator = null; 
		}
		
		// if Caller is not a moderator, and SMS notifications are turned on, send SMS to the moderator numbers
		if((!$isModerator) && is_array($_GET['Moderators']) && $_GET['EnableSmsNotifications']) {
			foreach($_GET['Moderators'] AS $moderator){
				$response->sms("{$_REQUEST['Caller']} has entered your conference.  Call the number this text came from to join.", 
					array("to"=>$moderator)
				);
			}
		}
		
		// if a message has been given, then play it
		// first, check to see if we have an http URL (simple check)
		if(strtolower(substr(trim($_GET['Message']), 0, 4)) == "http")
			$response->play($_GET['Message']);
		elseif(strlen($_GET['Message']))
			$response->say(stripslashes($_GET['Message']));
		else
			$response->say("You are now entering the conference line.");
		
		if(!strlen($_GET['Name'])) {
			// create a hash of Message + Moderators
			$hashme = $_GET['Message'];
			if(is_array($_GET['Moderators']))
				foreach($_GET['Moderators'] AS $m)
					$hashme .= "$m";
			$_GET['Name'] = md5($hashme);
		}
		
		// init params for Conference
		$params = array();
		
		// validate genre, and construct a twimlet url for the music from the given genre
		switch($_GET['Music']) {
			case "classical":
			case "ambient":
			case "electronica":
			case "guitars":
			case "rock":
			case "soft-rock":
				$params["waitUrl"] = "http://twimlets.com/holdmusic?Bucket=com.twilio.music.{$_GET['Music']}";
				$params["waitMethod"] = "GET";
				break;
			default:
				if(strtolower(substr($_GET['Music'], 0, 4)) == "http") {
					$params["waitUrl"] = $_GET['Music'];
					$params["waitMethod"] = "GET";
				}
				break;
		}
		
		// add moderator if given
		if(!is_null($isModerator))
			$params["startConferenceOnEnter"] = $isModerator?"true":"false";
		
		// add a Dial which will encapsulate the conference we're dialing
		$dial = $response->dial();
		
		// add the conference noun to the dial
		$dial->conference($_GET['Name'], $params);

		echo $response;
		die();
	}
	
	public function menu(){
		$response = new Services_Twilio_Twiml();

		if(!is_array($_REQUEST['Options']))
			$_REQUEST['Options'] = array($_REQUEST['Options']);
		
		// remove empty entries from PhoneNumbers
		$_REQUEST['Options'] = array_filter($_REQUEST['Options']);
		
		// if DialStatus was sent, it means we got here after a Dial attempt
		if(strlen($_REQUEST['Digits'])) {
			// if valid option given, the redirect
			if(strlen($location = $_REQUEST['Options'][$_REQUEST['Digits']])) {
				header("Location: $location");
				die;
			} else{
				// answered call, so just hangup
				$response->say("I'm sorry, that wasn't a valid option.");
			}
		} 
		
		// calculate the max number of digits we need to wait for
		$maxDigits = 1;
		foreach($_REQUEST['Options'] AS $key=>$value){
			$maxDigits = max($maxDigits, strlen($key));
		}

		// add a gather with numDigits
		$gather = $response->gather(array("numDigits"=>$maxDigits));
		
		// play the greeting while accepting digits
		// figure out the message
		// first, check to see if we have an http URL (simple check)
		if(strtolower(substr(trim($_GET['Message']), 0, 4)) == "http"){
			$gather->play($_GET['Message']);
		}elseif(strlen($_GET['Message'])){
			// read back the message given
			$gather->aay(stripslashes($_GET['Message']));
		}
		// add a redirect if nothing was pressed
		$response->redirect();

		echo $response;	
	}
	
	public function findme(){
		$response = new Services_Twilio_Twiml();

		if(!is_array($_REQUEST['PhoneNumbers']))
			$_REQUEST['PhoneNumbers'] = array($_REQUEST['PhoneNumbers']);
		
		// remove empty entries from PhoneNumbers
		$_REQUEST['PhoneNumbers'] = @array_filter($_REQUEST['PhoneNumbers']);
		
		// verify no more than 10 numbers given
		if(count($_REQUEST['PhoneNumbers']) > 10)
			$_REQUEST['PhoneNumbers'] = array_splice($_REQUEST['PhoneNumbers'], 10);
		
		// if The Dial flag is present, it means we're returning from an attempted Dial
		if(isset($_REQUEST['Dial']) && ($_REQUEST['DialStatus'] == "answered" || $_REQUEST['DialCallStatus'] == "completed")) {
			
			// answered call, so just hangup
			$response->hangup();
		
		} else {
		
			// No dial flag, or anything other than "answered", roll on to the next (or first, as it may be) number
			
			// get the next number of the array
			if(!$nextNumber = @array_shift($_REQUEST['PhoneNumbers'])) {
				// if no phone numbers left, redirect to the FailUrl
				
				// FailUrl found, so redirect and kill the cookie
				if(strlen($_REQUEST["FailUrl"])) {
					header("Location: {$_REQUEST["FailUrl"]}");
					die;
				} else {
				
					// no FailUrl found, so just end the call
					$response->hangup();
				
				}
			
			} else {
			
				// re-assemble remaining numbers into a QueryString, shifting the 0th off the array
				$qs = "FailUrl=".urlencode($_REQUEST['FailUrl'])."&Timeout=".urlencode($_REQUEST['Timeout'])."&Message=".urlencode($_REQUEST['Message']);
				foreach($_REQUEST['PhoneNumbers'] AS $number)
				$qs .= "&PhoneNumbers%5B%5D=" . urlencode($number);
				
				// add a dial to the response
				$dial = $response->dial(
					array(
						"action"=>"{$_SERVER['SCRIPT_URL']}?Dial=true&$qs", 
						"timeout"=>$_REQUEST['Timeout'] ? $_REQUEST['Timeout'] : 60
					)
				);
				
				// add the number to dial
				$dial->number($nextNumber, array(
					"url"=>"/whisper?Message=".urlencode($_REQUEST['Message']) . "&HumanCheck=1"
				));
			
			}
		} 
		// send the response
		echo $response;
	}

	public function simulring(){
		$response = new Services_Twilio_Twiml();

		// if PhoneNumbers isn't an array, make it one
		if(!is_array($_REQUEST['PhoneNumbers']))
			$_REQUEST['PhoneNumbers'] = array($_REQUEST['PhoneNumbers']);
		
		// remove empty entries from PhoneNumbers
		$_REQUEST['PhoneNumbers'] = array_filter($_REQUEST['PhoneNumbers']);
		
		// if The Dial flag is present, it means we're returning from an attempted Dial
		if(isset($_REQUEST['Dial']) && (strlen($_REQUEST['DialCallStatus']) || strlen($_REQUEST['DialStatus']))) { 
			
			if($_REQUEST['DialCallStatus'] == "completed" || $_REQUEST['DialStatus'] == "answered" || !strlen($_REQUEST['FailUrl'])) {
			
				// answered, or no failure url given, so just hangup
				$response->hangup();
			
			} else {
			
				// DialStatus was not answered, so redirect to FailUrl
				header("Location: {$_REQUEST['FailUrl']}");
				die;
			
			}
			
		} else {
			
			// No dial flag, means it's our first run through the script
			
			// dial everybody with default timeout 20, submitting back to this URL and set a dial flag
			$dial = $response->dial(array("action" => "{$_SERVER['SCRIPT_URL']}?Dial=true&FailUrl=".urlencode($_REQUEST['FailUrl']), "timeout"=>$_REQUEST['Timeout'] ? $_REQUEST['Timeout'] : 20));
			
			// resort the PhoneNumbers array, in case anything untoward happened to it        
			sort($_REQUEST['PhoneNumbers']);
			
			// add each number to the Dial
			foreach($_REQUEST['PhoneNumbers'] AS $number)
				$dial->number($number, array("url"=>"/whisper?Message=".urlencode($_REQUEST['Message'])));
		
		}
		
		// send the response
		echo $response;
	}

	public function callme(){
		$response = new Services_Twilio_Twiml();

		// if The Dial flag is present, it means we're returning from an attempted Dial
		if(isset($_REQUEST['Dial'])    && (strlen($_REQUEST['DialStatus']) || strlen($_REQUEST['DialCallStatus']))) {
			if($_REQUEST['DialCallStatus'] == "completed" || $_REQUEST['DialStatus'] == "answered" || !strlen($_REQUEST['FailUrl'])) {
			
				// answered, or no failure url given, so just hangup
				$response->hangup();
			
			} else {
			
				// DialStatus was not answered, so redirect to FailUrl
				header("Location: {$_REQUEST['FailUrl']}");
				die;
			
			}
			
		} else {
		
			// No dial flag, means it's our first run through the script
			
			// Add the FailUrl to the action param, if specified
			$failParam="";
			if (isset($_REQUEST['FailUrl']))
				$failParam="&FailUrl=" . urlencode($_REQUEST['FailUrl']);
			
			// we made it to here, so just dial the number, with the optional Timeout given
			$dial = $response->dial(array("action"=>"{$_SERVER['SCRIPT_URL']}?Dial=true$failParam", "timeout"=>$_REQUEST['Timeout'] ? $_REQUEST['Timeout'] : 20));

			// add number attribute
			$dial->number($_GET['PhoneNumber'], array("url"=>"/whisper?Message=" . urlencode($_GET['Message'])));
			
		}
		
		// send response
		echo $response;
	}
	
	public function message(){
		$response = new Services_Twilio_Twiml();

		if(!is_array($_GET['Message']))
			$_GET['Message'] = array($_GET['Message']);
		
		// foreach message, output it
		foreach($_GET['Message'] AS $msg) {
			// figure out the message
			// first, check to see if we have an http URL (simple check)
			if(strtolower(substr(trim($msg), 0, 4)) == "http"){
				$response->play($msg);
			}elseif(strlen(trim($msg))){
				// check if we have any message, if so, read it back 
				$response->say(stripslashes($msg));
			}
		}
		// send response
		echo $response;
	}
	
	public function echoml(){
		// send XML header
		header("Content-type: text/xml");
		
		// echo the TwiML passed into the URL
		echo $_GET['Twiml'];
	}

	public function holdmusic(){
		$response = new Services_Twilio_Twiml();

		// require an S3 bucket
		if(!strlen($_GET['Bucket'] = trim($_GET['Bucket']))) {
			$response->say("An S 3 bucket is required.");
			echo $response;
			die;
		}
		
		// use Curl to get the contents of the bucket
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT,5);
		curl_setopt($ch, CURLOPT_URL, "http://{$_GET['Bucket']}.s3.amazonaws.com");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		
		// do the fetch
		if(!$output = curl_exec($ch)) {
			$response->say("Failed to fetch the hold music.");
			echo $response;
			die;
		}
		
		// parse as XML
		$xml = new SimpleXMLElement($output);
		
		// construct an array of URLs
		$urls = array();
		foreach($xml->Contents as $c){
			// add any mp3, wav or ul to the urls array
			if((endsWith($c->Key, ".mp3")) ||(endsWith($c->Key, ".wav")) ||(endsWith($c->Key, ".ul"))) {
				$urls[]=$c->Key;
			}
		}
			
		// if no songs where found, then bail
		if(!count($urls)) {
			$response->say("Failed to fetch the hold music.");
			echo $response;
			die;
		}
		
		// and let's shuffle
		shuffle($urls);
		
		// Play each URL        
		foreach($urls as $url){
			
			// Play each url
			$response->play("http://{$_GET['Bucket']}.s3.amazonaws.com/".urlencode($url));
			
			// if a message was given, then output it between music
			// first, check to see if we have an http URL (simple check)
			if(strtolower(substr(trim($_GET['Message']), 0, 4)) == "http"){
				$response->play($_GET['Message']);
			}elseif(strlen($_GET['Message'])){
				// read back the message given
				$response->say(stripslashes($_GET['Message']));
			}
		}
		
		// and loop
		
		// send response
		echo $response;	
	}

	public function whisper(){
		$response = new Services_Twilio_Twiml();

		// if we have a Digits= parameter, then this is the 2nd loop of this script
		if(isset($_REQUEST['Digits'])) {
		
			// if a digit was pressed, then let us drop through
			if(!strlen($_REQUEST['Digits'])) {
				// no digit was pressed, so just hangup
				$response->hangup();
			}
			// otherwise, we'll just return an empty document, which will bridge the calls
		} else {
		
			// no digits submitted, so this is the first run of the whisper file
			
			// grab the caller's phone number
			$from = strlen($_REQUEST['From']) ? $_REQUEST['From'] : $_REQUEST['Caller'];
			
			// add a Gather to get the digits when pressed
			$gather = $response->gather(array('numDigits' => 1));
			
			// figure out the message
			// first, check to see if we have an http URL (simple check)
			if(strtolower(substr(trim($_GET['Message']), 0, 4)) == "http"){
				$gather->play($_GET['Message']);
			}elseif(strlen(trim($_GET['Message']))){
				// check if we have any message, if so, read it back 
				$gather->say(stripslashes($_GET['Message']));
			}else{
				// no message, just use a default
				$gather->say("You are receiving a call from ".preg_replace('/([^\s])/', '$1. ', $from).".  Press any key to accept.");
			}
			
			// if we're screening to check for a person answering, hangup the call if gather falls through
			if (isset($_REQUEST['HumanCheck']))
				$response->hangup();
		}


		// send response
		echo $response;	
	}

}