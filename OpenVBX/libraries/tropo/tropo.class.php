<?php
/**
 * Updated by Disruptive Technologies, for Tropo VBX conversion.
 * Duplicate class names, several classes have been renamed.
 */
/**
 * This file contains PHP classes that can be used to interact with the Tropo WebAPI/
 * @see https://www.tropo.com/docs/webapi/
 * 
 * @copyright 2010 Mark J. Headd (http://www.voiceingov.org)
 * @package TropoPHP
 * @author Mark Headd
 * @author Adam Kalsey
 */

/**
 * The main Tropo WebAPI class.
 * The methods on this class can be used to invoke specifc Tropo actions.
 * @package TropoPHP
 * @see https://www.tropo.com/docs/webapi/tropo.htm
 *
 */
include 'tropo-rest.class.php';

class Tropo extends BaseClass {
	
	/**
	 * The container for JSON actions.
	 *
	 * @var array
	 * @access private
	 */
	public $tropo;
	
	/**
	 * The TTS voice to use when rendering content.
	 *
	 * @var string
	 * @access private
	 */
	private $_voice;
	
	/**
	 * The language to use when rendering content.
	 *
	 * @var string
	 * @access private
	 */
	private $_language;
	
	/**
	 * Class constructor for the Tropo class.
	 * @access private
	 */
	public function __construct() {
		$this->tropo = array();
	}
	
	/**
	 * Set a default voice for use with all Text To Speech.
	 *
	 * Tropo's text to speech engine can pronounce your text with
	 * a variety of voices in different languages. All elements where
	 * you can create text to speech (TTS) accept a voice parameter.
	 * Tropo's default is "Allison" but you can set a default for this
	 * script here.
	 *
	 * @param string $voice
	 */
	public function setVoice($voice) {
		$this->_voice = $voice;
	}
	
	/**
	 * Set a default language to use in speech recognition.
	 *
	 * When recognizing spoken input, Tropo allows you to set a language
	 * to let the platform know which language is being spoken and which
	 * recognizer to use. The default is en-us (US English), but you can
	 * set a different default to be used in your application here.
	 *
	 * @param string $language
	 */
	public function setLanguage($language) {
		$this->_language = $language;
	}
		
	/**
	 * Sends a prompt to the user and optionally waits for a response.
	 *
	 * The ask method allows for collecting input using either speech 
	 * recognition or DTMF (also known as Touch Tone). You can either 
	 * pass in a fully-formed Ask object or a string to use as the 
	 * prompt and an array of parameters.
	 *
	 * @param string|Ask $ask
	 * @param array $params
	 * @see https://www.tropo.com/docs/webapi/ask.htm
	 */
	public function ask($ask, Array $params=NULL) {
		if(!is_object($ask)) {
		  $p = array('as','event','voice','attempts', 'bargein', 'minConfidence', 'name', 'required', 'timeout', 'allowSignals', 'recognizer');
			foreach ($p as $option) {
	      $$option = null;
  	    if (is_array($params) && array_key_exists($option, $params)) {
  	    	$$option = $params[$option];
  	    }
  		}
	  	$say[] = new Say_Tropo($ask, $as, null, $voice);
	  	if (is_array($event)) {
	  	    // If an event was passed in, add the events to the Ask
    		  foreach ($event as $e => $val){
    		    $say[] = new Say_Tropo($val, $as, $e, $voice); 
    		  }
	  	}
	  	$params["mode"] = isset($params["mode"]) ? $params["mode"] : null;
	  	$params["dtmf"] = isset($params["dtmf"]) ? $params["dtmf"] : null;
	  	$params["terminator"] = isset($params["terminator"]) ? $params["terminator"] : null;
	  	if (!isset($voice) && isset($this->_voice)) {
	  	   $voice = $this->_voice;
	  	}
		  $choices = isset($params["choices"]) ? new Choices_Tropo($params["choices"], $params["mode"], $params["terminator"]) : null;
	  	$ask = new Ask_Tropo($attempts, $bargein, $choices, $minConfidence, $name, $required, $say, $timeout, $voice, $allowSignals, $recognizer);
 		}
		$this->ask = sprintf($ask);
	}

	/**
	 * Places a call or sends an an IM, Twitter, or SMS message. To start a call, use the Session API to tell Tropo to launch your code. 
	 *
	 * @param string|Call $call
	 * @param array $params
	 * @see https://www.tropo.com/docs/webapi/call.htm
	 */
	public function call($call, Array $params=NULL) {
	if(!is_object($call)) {
  	  $p = array('to', 'from', 'network', 'channel', 'answerOnMedia', 'timeout', 'headers', 'recording', 'allowSignals');
  	  foreach ($p as $option) {
	      $$option = null;
  	    if (is_array($params) && array_key_exists($option, $params)) {
  	      $$option = $params[$option];
  	    }
  	  }
		$call = new Call_Tropo($call, $from, $network, $channel, $answerOnMedia, $timeout, $headers, $recording, $allowSignals);
	}
		$this->call = sprintf($call);
	}
	
	/**
	 * This object allows multiple lines in separate sessions to be conferenced together so that the parties on each line can talk to each other simultaneously. 
	 * This is a voice channel only feature. 
	 *
	 * @param string|Conference $conference
	 * @param array $params
	 * @see https://www.tropo.com/docs/webapi/conference.htm
	 */
	public function conference($conference, Array $params=NULL) {
		if(!is_object($conference)) {
			$p = array('name', 'id', 'mute', 'on', 'playTones', 'required', 'terminator', 'allowSignals');
	  	foreach ($p as $option) {
	      $$option = null;
  	    if (is_array($params) && array_key_exists($option, $params)) {
  	      $$option = $params[$option];
  	    }
	  	}
	  	$conference = new Conference_Tropo($name, $id, $mute, $on, $playTones, $required, $terminator, $allowSignals);
		}
		$this->conference = sprintf($conference);
	}
	
	/**
	 * This function instructs Tropo to "hang-up" or disconnect the session associated with the current session.
	 * @see https://www.tropo.com/docs/webapi/hangup.htm
	 */
	public function hangup() {
		$hangup = new Hangup_Tropo();
		$this->hangup = sprintf($hangup);
	}
	
	/**
	 * A shortcut method to create a session, say something, and hang up, all in one step. This is particularly useful for sending out a quick SMS or IM. 
	 *
	 * @param string|Message $message
	 * @param array $params
	 * @see https://www.tropo.com/docs/webapi/message.htm
	 */
	public function message($message, Array $params=null) {
	  if(!is_object($message)) {
	  	$say = new Say_Tropo($message);
  		$to = $params["to"];
  		$p = array('channel', 'network', 'from', 'voice', 'timeout', 'answerOnMedia','headers');
  		foreach ($p as $option) {
	      $$option = null;
		  	if (is_array($params) && array_key_exists($option, $params)) {
  	      $$option = $params[$option];
		  	}
	  	}
	  	$message = new Message_Tropo($say, $to, $channel, $network, $from, $voice, $timeout, $answerOnMedia, $headers);
	  }
	  $this->message = sprintf($message);
	}
	
	/**
	 * Adds an event callback so that your application may be notified when a particular event occurs. 
	 * Possible events are: "continue", "error", "incomplete" and "hangup". 
	 *
	 * @param array $params
	 * @see https://www.tropo.com/docs/webapi/on.htm
	 */
	public function on($on) {	
	  if (!is_object($on) && is_array($on))	{
	    $params = $on;
  	  $say = (array_key_exists('say', $params)) ? new Say_Tropo($params["say"]) : null;
  	  $next = (array_key_exists('next', $params)) ? $params["next"] : null;
  		$on = new On_Tropo($params["event"], $next, $say);	    
	  }
		$this->on = sprintf($on);
	}
	
	/**
	 * Plays a prompt (audio file or text to speech) and optionally waits for a response from the caller that is recorded. 
	 * If collected, responses may be in the form of DTMF or speech recognition using a simple grammar format defined below. 
	 * The record funtion is really an alias of the prompt function, but one which forces the record option to true regardless of how it is (or is not) initially set. 
	 * At the conclusion of the recording, the audio file may be automatically sent to an external server via FTP or an HTTP POST/Multipart Form. 
	 * If specified, the audio file may also be transcribed and the text returned to you via an email address or HTTP POST/Multipart Form.
	 *
	 * @param array|Record $record
	 * @see https://www.tropo.com/docs/webapi/record.htm
	 */
	public function record($record) {
		if(!is_object($record) && is_array($record)) {
		  $params = $record;
			$choices = isset($params["choices"]) ? new Choices_Tropo($params["choices"]) : null;
			$say = new Say_Tropo($params["say"], $params["as"], null, $params["voice"]);
			if (is_array($params['transcription'])) {
			  $p = array('url', 'id', 'emailFormat');
  			foreach ($p as $option) {
  	      $$option = null;
    	    if (!is_array($params["transcription"]) || !array_key_exists($option, $params["transcription"])) {
    	      $params["transcription"][$option] = null;
    	    }
  	  	}	
  			$transcription = new Transcription_Tropo($params["transcription"]["url"],$params["transcription"]["id"],$params["transcription"]["emailFormat"]);    		
			} else {
			  $transcription = $params["transcription"];
			}
			$p = array('attempts', 'allowSignals', 'bargein', 'beep', 'format', 'maxTime', 'maxSilence', 'method', 'password', 'required', 'timeout', 'username', 'url', 'voice');
			foreach ($p as $option) {
			  $$option = null;
  	    if (is_array($params) && array_key_exists($option, $params)) {
  	      $$option = $params[$option];
  	    }
	  	}
	  	$record = new Record_Tropo($attempts, $allowSignals, $bargein, $beep, $choices, $format, $maxSilence, $maxTime, $method, $password, $required, $say, $timeout, $transcription, $username, $url, $voice);
		}
		$this->record = sprintf($record);		
	}
	
	/**
	 * The redirect function forwards an incoming call to another destination / phone number before answering it. 
	 * The redirect function must be called before answer is called; redirect expects that a call be in the ringing or answering state. 
	 * Use transfer when working with active answered calls. 
	 *
	 * @param string|Redirect $redirect
	 * @param array $params
	 * @see https://www.tropo.com/docs/webapi/redirect.htm
	 */
	public function redirect($redirect, Array $params=NULL) {
		if(!is_object($redirect)) {
			$to = isset($params["to"]) ? $params["to"]: null;
			$from = isset($params["from"]) ? $params["from"] : null;
			$redirect = new Redirect_Tropo($to, $from);
		}
		$this->redirect = sprintf($redirect);
	}
	
	/**
	 * Allows Tropo applications to reject incoming sessions before they are answered. 
	 * For example, an application could inspect the callerID variable to determine if the user is known, and then use the reject call accordingly. 
	 * 
	 * @see https://www.tropo.com/docs/webapi/reject.htm
	 *
	 */
	public function reject() {
		$reject = new Reject_Tropo();
		$this->reject = sprintf($reject);
	}
	
	/**
	 * When the current session is a voice channel this key will either play a message or an audio file from a URL. 
	 * In the case of an text channel it will send the text back to the user via i nstant messaging or SMS. 
	 *
	 * @param string|Say $say
	 * @param array $params
	 * @see https://www.tropo.com/docs/webapi/say.htm
	 */
	public function say($say, Array $params=NULL) {
		if(!is_object($say)) {
			$p = array('as', 'format', 'event','voice', 'allowSignals');
			$value = $say;
			foreach ($p as $option) {
			  $$option = null;
  	    if (is_array($params) && array_key_exists($option, $params)) {
  	      $$option = $params[$option];
  	    }
	  	}
	  	$voice = isset($voice) ? $voice : $this->_voice;
	  	$say = new Say_Tropo($value, $as, $event, $voice, $allowSignals);
		}
		$this->say = array(sprintf($say));	
	}
	
	/**
	 * Allows Tropo applications to begin recording the current session. 
	 * The resulting recording may then be sent via FTP or an HTTP POST/Multipart Form. 
	 *
	 * @param array|StartRecording $startRecording
	 * @see https://www.tropo.com/docs/webapi/startrecording.htm
	 */
	public function startRecording($startRecording) {
		if(!is_object($startRecording) && is_array($startRecording)) {
		  $params = $startRecording;
			$p = array('format', 'method', 'password', 'url', 'username');
			foreach ($p as $option) {
	      $$option = null;
  	    if (is_array($params) && array_key_exists($option, $params)) {
  	      $$option = $params[$option];
  	    }
	  	}
	  	$startRecording = new StartRecording_Tropo($format, $method, $password, $url, $username);
		}		
		$this->startRecording = sprintf($startRecording);
	}
	
	/**
	 * Stops a previously started recording.
	 * 
	 * @see https://www.tropo.com/docs/webapi/stoprecording.htm
	 */
	public function stopRecording() {
		$stopRecording = new stopRecording_Tropo();
		$this->stopRecording = sprintf($stopRecording);
	}
	
	/**
	 * Transfers an already answered call to another destination / phone number. 
	 * Call may be transferred to another phone number or SIP address, which is set through the "to" parameter and is in URL format.
	 *
	 * @param string|Transfer $transfer
	 * @param array $params
	 * @see https://www.tropo.com/docs/webapi/transfer.htm
	 */
	public function transfer($transfer, Array $params=NULL) {
		if(!is_object($transfer)) {
			$choices = isset($params["choices"]) ? $params["choices"] : null;
			$to = isset($params["to"]) ? $params["to"] : $transfer;
			$p = array('answerOnMedia', 'ringRepeat', 'timeout', 'from', 'on', 'allowSignals');
			foreach ($p as $option) {
	      $$option = null;
  	    if (is_array($params) && array_key_exists($option, $params)) {
  	      $$option = $params[$option];
  	    }
	  	}
	  	$transfer = new Transfer_Tropo($to, $answerOnMedia, $choices, $from, $ringRepeat, $timeout, $on, $allowSignals);
		}
		$this->transfer = sprintf($transfer);
	}

	/**
	 * Launches a new session_Tropo with the Tropo Session API.
	 * (Pass through to SessionAPI class.)
	 * 
	 * @param string $token Your outbound session token from Tropo
	 * @param array $params An array of key value pairs that will be added as query string parameters
	 * @return bool True if the session was launched successfully 
	 */
	public function createSession($token, Array $params=NULL) {
		try {
			$session = new SessionAPI_Tropo();
			$result = $session->createSession($token, $params);
			return $result;
		}
		// If an exception occurs, wrap it in a TropoException and rethrow.
		catch (Exception $ex) {
			throw new TropoException($ex->getMessage(), $ex->getCode());
		}		
	}
	
	public function sendEvent($session_id, $value) {
		try {
			$event = new EventAPI_Tropo();
			$result = $event->sendEvent($session_id, $value);
			return $result;
		}
		catch (Exception $ex) {
			throw new TropoException($ex->getMessage(), $ex->getCode());
		}
	}
	
	/**
	 * Creates a new Tropo Application
	 * (Pass through to ProvisioningAPI class).
	 *
	 * @param string $userid
	 * @param string $password
	 * @param array $params
	 * @return string JSON
	 */
	public function createApplication($userid, $password, Array $params) {
		$p = array('href', 'name', 'voiceUrl', 'messagingUrl', 'platform', 'partition');
		foreach ($p as $property) {
			$$property = null;
			if (is_array($params) && array_key_exists($property, $params)) {
	  	      $$property = $params[$property];
	  	    }
		}
		try {
			$provision = new ProvisioningAPI_Tropo($userid, $password);
			$result = $provision->createApplication($href, $name, $voiceUrl, $messagingUrl, $platform, $partition);
			return $result;
		}
		// If an exception occurs, wrap it in a TropoException and rethrow.
		catch (Exception $ex) {
			throw new TropoException($ex->getMessage(), $ex->getCode());
		}
	}
	
	/**
	 * Add/Update an address (phone number, IM address or token) for an existing Tropo application.
	 * (Pass through to ProvisioningAPI class).
	 *
	 * @param string $userid
	 * @param string $password
	 * @param string $applicationID
	 * @param array $params
	 * @return string JSON
	 */
	public function updateApplicationAddress($userid, $passwd, $applicationID, Array $params) {
		$p = array('type', 'prefix', 'number', 'city', 'state', 'channel', 'username', 'password', 'token');
		foreach ($p as $property) {
			$$property = null;
			if (is_array($params) && array_key_exists($property, $params)) {
	  	      $$property = $params[$property];
	  	    }
		}
		try {
			$provision = new ProvisioningAPI_Tropo($userid, $passwd);
			$result = $provision->updateApplicationAddress($applicationID, $type, $prefix, $number, $city, $state, $channel, $username, $password, $token);
			return $result;
		}
		// If an exception occurs, wrap it in a TropoException and rethrow.
		catch (Exception $ex) {
			throw new TropoException($ex->getMessage(), $ex->getCode());
		}
	}
	
	/**
	 * Update a property (name, URL, platform, etc.) for an existing Tropo application.
	 * (Pass through to ProvisioningAPI class).
	 *
	 * @param string $userid
	 * @param string $password
	 * @param string $applicationID
	 * @param array $params
	 * @return string JSON
	 */
	public function updateApplicationProperty($userid, $password, $applicationID, Array $params) {
		$p = array('href', 'name', 'voiceUrl', 'messagingUrl', 'platform', 'partition');
		foreach ($p as $property) {
			$$property = null;
			if (is_array($params) && array_key_exists($property, $params)) {
	  	      $$property = $params[$property];
	  	    }
		}
		try {
			$provision = new ProvisioningAPI_Tropo($userid, $password);
			$result = $provision->updateApplicationProperty($applicationID, $href, $name, $voiceUrl, $messagingUrl, $platform, $partition);
			return $result;
		}
		// If an exception occurs, wrap it in a TropoException and rethrow.
		catch (Exception $ex) {
			throw new TropoException($ex->getMessage(), $ex->getCode());
		}
	}
	
	/**
	 * Delete an existing Tropo application.
	 * (Pass through to ProvisioningAPI class).
	 *
	 * @param string $userid
	 * @param string $password
	 * @param string $applicationID
	 * @return string JSON
	 */
	public function deleteApplication($userid, $password, $applicationID) {
		$provision = new ProvisioningAPI_Tropo($userid, $password);
		return $provision->deleteApplication($applicationID);
	}
	
	/**
	 * Delete an address for an existing Tropo application.
	 * (Pass through to ProvisioningAPI class).
	 *
	 * @param string $userid
	 * @param string $password
	 * @param string $applicationID
	 * @param string $number
	 * @return string JSON
	 */
	public function deleteApplicationAddress($userid, $password, $applicationID, $addresstype, $address) {
		$provision = new ProvisioningAPI_Tropo($userid, $password);
		return $provision->deleteApplicationAddress($applicationID, $addresstype, $address);
	}
	
	/**
	 * View a list of Tropo applications.
	 * (Pass through to ProvisioningAPI class).
	 *
	 * @param string $userid
	 * @param string $password
	 * @return string JSON
	 */
	public function viewApplications($userid, $password) {
		$provision = new ProvisioningAPI_Tropo($userid, $password);
		return $provision->viewApplications();
	}
	
	/**
	 * View the details of a specific Tropo application.
	 * (Pass through to ProvisioningAPI class).
	 *
	 * @param string $userid
	 * @param string $password
	 * @param string $applicationID
	 * @return string JSON
	 */
	public function viewSpecificApplication($userid, $password, $applicationID) {
		$provision = new ProvisioningAPI_Tropo($userid, $password);
		return $provision->viewSpecificApplication($applicationID);
	}
	
	/**
	 * View the addresses for a specific Tropo application.
	 * (Pass through to ProvisioningAPI class).
	 *
	 * @param string $userid
	 * @param string $password
	 * @param string $applicationID
	 * @return string JSON
	 */
	public function viewAddresses($userid, $password, $applicationID) {
		$provision = new ProvisioningAPI_Tropo($userid, $password);
		return $provision->viewAddresses($applicationID);
	}
	
	/**
	 * View a list of available exchanges for assigning a number to a Tropo application.
	 * (Pass through to ProvisioningAPI class).
	 *
	 * @param string $userid
	 * @param string $password
	 * @return string JSON
	 */
	public function viewExchanges($userid, $password) {
		$provision = new ProvisioningAPI_Tropo($userid, $password);
		return $provision->viewExchanges();
	}
	
	/**
	 * Renders the Tropo object as JSON.
	 *
	 */
	public function renderJSON() {
		header('Content-type: application/json');
		echo $this;
	}
	
	/**
	 * Allows undefined methods to be called.
	 * This method is invloked by Tropo class methods to add action items to the Tropo array.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @access private
	 */
	public function __set($name, $value) {
		array_push($this->tropo, array($name => $value));
	}	
	
	/**
	 * Controls how JSON structure for the Tropo object is rendered.
	 *
	 * @return string
	 * @access private
	 */
	public function __toString() {
		// Remove voice and language so they do not appear in the rednered JSON.
	  	unset($this->_voice);
	  	unset($this->_language);
	  	
	  	// Call the unescapeJSON() method in the parent class.
	  	return parent::unescapeJSON(json_encode($this));	
	}	
}

/**
 * Base class for Tropo class and indvidual Tropo action classes.
 * Derived classes must implement both a constructor and __toString() function.
 * @package TropoPHP_Support
 * @abstract BaseClass
 */

abstract class BaseClass {
	
	/**
	 * Class constructor
	 * @abstract __construct()
	 */
	abstract public function __construct();
	
	/**
	 * toString Function
	 * @abstract __toString()
	 */
	abstract public function __toString();
	
	/**
	 * Allows derived classes to set Undeclared properties.
	 *
	 * @param mixed $attribute
	 * @param mixed $value
	 */
	public function __set($attribute, $value) {
		$this->$attribute= $value;
	}
	
	/**
	 * Removes escape characters from a JSON string.
	 *
	 * @param string $json
	 * @return string
	 */
	public function unescapeJSON($json) {
	  return str_replace(array("\\", "\"{", "}\""), array("", "{", "}"), $json);
	}
}

/**
 * Base class for empty actions. 
 * @package TropoPHP_Support
 *
 */
class EmptyBaseClass_Tropo {	
	
	final public function __toString() { 
		return json_encode(null);
	}	
}



/**
 * Action classes. Each specific object represents a specific Tropo action.
 *
 */

/**
 * Sends a prompt to the user and optionally waits for a response.
 * @package TropoPHP_Support
 *
 */
class Ask_Tropo extends BaseClass {
	
	private $_attempts;
	private $_bargein;
	private $_choices;
	private $_minConfidence;
	private $_name;
	private $_required;
	private $_say;
	private $_timeout;
	private $_voice;
	private $_allowSignals;
	private $_recognizer;
	
	/**
	 * Class constructor
	 *
	 * @param int $attempts
	 * @param boolean $bargein
	 * @param Choices $choices
	 * @param float $minConfidence
	 * @param string $name
	 * @param boolean $required
	 * @param Say $say
	 * @param int $timeout
	 * @param string $voice
	 * @param string|array $allowSignals
	 */
	public function __construct($attempts=NULL, $bargein=NULL, Choices_Tropo $choices=NULL, $minConfidence=NULL, $name=NULL, $required=NULL, $say=NULL, $timeout=NULL, $voice=NULL, $allowSignals=NULL, $recognizer=NULL) {
		$this->_attempts = $attempts;
		$this->_bargein = $bargein;
		$this->_choices = isset($choices) ? sprintf($choices) : null ;
		$this->_minConfidence = $minConfidence;
		$this->_name = $name;
		$this->_required = $required;
		$this->_say = isset($say) ? $say : null;
		$this->_timeout = $timeout;	
		$this->_voice = $voice;
		$this->_allowSignals = $allowSignals;
		$this->_recognizer = $recognizer;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {
		if(isset($this->_attempts)) { $this->attempts = $this->_attempts; }
		if(isset($this->_bargein)) { $this->bargein = $this->_bargein; }
		if(isset($this->_choices)) { $this->choices = $this->_choices; }
		if(isset($this->_minConfidence)) { $this->minConfidence = $this->_minConfidence; }
		if(isset($this->_name)) { $this->name = $this->_name; }
		if(isset($this->_required)) { $this->required = $this->_required; }
		if(isset($this->_say)) { $this->say = $this->_say; }
		if (is_array($this->_say)) {
		  foreach ($this->_say as $k => $v) {
		    $this->_say[$k] = sprintf($v);
		  }
		}
		if(isset($this->_timeout)) { $this->timeout = $this->_timeout; }		
		if(isset($this->_voice)) { $this->voice = $this->_voice; }
		if(isset($this->_allowSignals)) { $this->allowSignals = $this->_allowSignals; } 
		if(isset($this->_recognizer)) { $this->recognizer = $this->_recognizer; } 
		return $this->unescapeJSON(json_encode($this));
	}
	
	/**
	 * Adds an additional Say to the Ask
	 *
	 * Used to add events such as a prompt to say on timeout or nomatch
	 * 
	 * @param Say $say A say object
	 */
	public function addEvent(Say_Tropo $say) {
	  $this->_say[] = $say;
	}
}

/**
 * This object allows Tropo to make an outbound call. The call can be over voice or one
 * of the text channels.
 * @package TropoPHP_Support
 *
 */
class Call_Tropo extends BaseClass {
	
	private $_to;
	private $_from;
	private $_network;
	private $_channel;
	private $_answerOnMedia;
	private $_timeout;
	private $_headers; 
	private $_recording;
	private $_allowSignals;
	
	/**
	 * Class constructor
	 *
	 * @param string $to
	 * @param string $from
	 * @param string $network
	 * @param string $channel
	 * @param boolean $answerOnMedia
	 * @param int $timeout
	 * @param array $headers
	 * @param StartRecording $recording
	 * @param string|array $allowSignals
	 */
	public function __construct($to, $from=NULL, $network=NULL, $channel=NULL, $answerOnMedia=NULL, $timeout=NULL, Array $headers=NULL, StartRecording_Tropo $recording=NULL, $allowSignals=NULL) {
		$this->_to = $to;
		$this->_from = $from;		
		$this->_network = $network;		
		$this->_channel = $channel;		
		$this->_answerOnMedia = $answerOnMedia;		
		$this->_timeout = $timeout;
		$this->_headers = $headers;
		$this->_recording = isset($recording) ? sprintf($recording) : null ;
		$this->_allowSignals = $allowSignals;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {
		$this->to = $this->_to;
		if(isset($this->_from)) { $this->from = $this->_from; }
		if(isset($this->_network)) { $this->network = $this->_network; }
		if(isset($this->_channel)) { $this->channel = $this->_channel; }
		if(isset($this->_timeout)) { $this->timeout = $this->_timeout; }		
		if(isset($this->_answerOnMedia)) { $this->answerOnMedia = $this->_answerOnMedia; }
		if(count($this->_headers)) { $this->headers = $this->_headers; }		
		if(isset($this->_recording)) { $this->recording = $this->_recording; }
		if(isset($this->_allowSignals)) { $this->allowSignals = $this->_allowSignals; }
		return $this->unescapeJSON(json_encode($this));	
	}	
}

/**
 * Defines the input to be collected from the user.
 * @package TropoPHP_Support
 */
class Choices_Tropo extends BaseClass {
	
	private $_value;
	private $_mode;
	private $_terminator;
	
	/**
	 * Class constructor
	 *
	 * @param string $value
	 * @param string $mode
	 * @param string $terminator
	 */
	public function __construct($value=NULL, $mode=NULL, $terminator=NULL) {
		$this->_value = $value;
		$this->_mode = $mode;
		$this->_terminator = $terminator;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {
		$this->value = $this->_value;
		if(isset($this->_mode)) { $this->mode = $this->_mode; }
		if(isset($this->_terminator)) { $this->terminator = $this->_terminator; }	
		return $this->unescapeJSON(json_encode($this));	
	}
}

/**
 * This object allows multiple lines in separate sessions to be conferenced together so that 
 *   the parties on each line can talk to each other simultaneously. 
 *   This is a voice channel only feature. 
 * 
 * TODO: Conference object should support multiple event handlers (e.g. join and leave).
 * @package TropoPHP_Support
 *
 */
class Conference_Tropo extends BaseClass {
	
	private $_id;
	private $_mute;
	private $_name;
	private $_on;
	private $_playTones;
	private $_required;
	private $_terminator;
	private $_allowSignals;
	
	
	/**
	 * Class constructor
	 *
	 * @param int $id
	 * @param boolean $mute
	 * @param string $name
	 * @param On $on
	 * @param boolean $playTones
	 * @param boolean $required
	 * @param string $terminator
	 * @param string|array $allowSignals
	 */
	public function __construct($name, $id=NULL, $mute=NULL, On_Tropo $on=NULL, $playTones=NULL, $required=NULL, $terminator=NULL, $allowSignals=NULL) {
		$this->_name = $name;
		$this->_id = $id;
		$this->_mute = $mute;		
		$this->_on = isset($on) ? sprintf($on) : null;
		$this->_playTones = $playTones;
		$this->_required = $required;
		$this->_terminator = $terminator;
		$this->_allowSignals = $allowSignals;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {
		$this->name = $this->_name;
		if(isset($this->_id)) { $this->id = $this->_id; }
		if(isset($this->_mute)) { $this->mute = $this->_mute; }
		if(isset($this->_on)) { $this->on = $this->_on; }
		if(isset($this->_playTones)) { $this->playTones = $this->_playTones; }
		if(isset($this->_required)) { $this->required = $this->_required; }
		if(isset($this->_terminator)) { $this->terminator = $this->_terminator; }
		if(isset($this->_allowSignals)) { $this->allowSignals = $this->_allowSignals; }	
		return $this->unescapeJSON(json_encode($this));	
	}	
}

/**
 * This function instructs Tropo to "hang-up" or disconnect the session associated with the current session.
 * @package TropoPHP_Support
 *
 */
class Hangup_Tropo extends EmptyBaseClass_Tropo { }

/**
 * This function instructs Tropo to send a message. 
 * @package TropoPHP_Support
 *
 */
class Message_Tropo extends BaseClass {
	
	private $_say;
	private $_to;
	private $_channel;
	private $_network;
	private $_from;
	private $_voice;
	private $_timeout;
	private $_answerOnMedia;
	private $_headers;
	
	/**
	 * Class constructor
	 *
	 * @param Say $say
	 * @param string $to
	 * @param string $channel
	 * @param string $network
	 * @param string $from
	 * @param string $voice
	 * @param integer $timeout
	 * @param boolean $answerOnMedia
	 * @param array $headers
	 */
	public function __construct(Say_Tropo $say, $to, $channel=null, $network=null, $from=null, $voice=null, $timeout=null, $answerOnMedia=null, Array $headers=null) {
		$this->_say = isset($say) ? sprintf($say) : null ;
		$this->_to = $to;
		$this->_channel = $channel;
		$this->_network = $network;
		$this->_from = $from;
		$this->_voice = $voice;
		$this->_timeout = $timeout;
		$this->_answerOnMedia = $answerOnMedia;
		$this->_headers = $headers;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {
		$this->say = $this->_say;
		$this->to = $this->_to;
		if(isset($this->_channel)) { $this->channel = $this->_channel; }
		if(isset($this->_network)) { $this->network = $this->_network; }
		if(isset($this->_from)) { $this->from = $this->_from; }
		if(isset($this->_voice)) { $this->voice = $this->_voice; }
		if(isset($this->_timeout)) { $this->timeout = $this->_timeout; }
		if(isset($this->_answerOnMedia)) { $this->answerOnMedia = $this->_answerOnMedia; }	
		if(count($this->_headers)) { $this->headers = $this->_headers; }			
		return $this->unescapeJSON(json_encode($this));	
	}
}

/**
 * Adds an event callback so that your application may be notified when a particular event occurs.
 * @package TropoPHP_Support
 *
 */
class On_Tropo extends BaseClass {
	
	private $_event;
	private $_next;
	private $_say;
	
	/**
	 * Class constructor
	 *
	 * @param string $event
	 * @param string $next
	 * @param Say $say
	 */
	public function __construct($event=NULL, $next=NULL, Say_Tropo $say=NULL) {
		$this->_event = $event;
		$this->_next = $next;
		$this->_say = isset($say) ? sprintf($say) : null ;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {
		if(isset($this->_event)) { $this->event = $this->_event; }
		if(isset($this->_next)) { $this->next = $this->_next; }
		if(isset($this->_say)) { $this->say = $this->_say; }		
		return $this->unescapeJSON(json_encode($this));	
	}
}

/**
 * Plays a prompt (audio file or text to speech) and optionally waits for a response from the caller that is recorded.
 * @package TropoPHP_Support
 *
 */
class Record_Tropo extends BaseClass {
	
	private $_attempts;
	private $_allowSignals;
	private $_bargein;
	private $_beep;
	private $_choices;
	private $_format;
	private $_maxSilence;
	private $_maxTime;
	private $_method;
	private $_password;
	private $_required;
	private $_say;
	private $_timeout;
	private $_transcription;
	private $_username;	
	private $_url;
	private $_voice;
	
	
	/**
	 * Class constructor
	 *
	 * @param int $attempts
	 * @param string|array $allowSignals
	 * @param boolean $bargein
	 * @param boolean $beep
	 * @param Choices $choices
	 * @param string $format
	 * @param int $maxSilence
	 * @param string $method
	 * @param string $password
	 * @param boolean $required
	 * @param Say $say
	 * @param int $timeout
	 * @param string $username
	 * @param string $url
	 * @param string $voice 
	 */
	public function __construct($attempts=NULL, $allowSignals=NULL, $bargein=NULL, $beep=NULL, Choices_Tropo $choices=NULL, $format=NULL, $maxSilence=NULL, $maxTime=NULL, $method=NULL, $password=NULL, $required=NULL, $say=NULL, $timeout=NULL, Transcription_Tropo $transcription=NULL, $username=NULL, $url=NULL, $voice=NULL) {
		$this->_attempts = $attempts;
		$this->_allowSignals = $allowSignals;
		$this->_bargein = $bargein;
		$this->_beep = $beep;
		$this->_choices = isset($choices) ? sprintf($choices) : null;
		$this->_format = $format;
		$this->_maxSilence = $maxSilence;
		$this->_maxTime = $maxTime;
		$this->_method = $method;
		$this->_password = $password;
		if (!is_object($say)) {
		  $say = new Say_Tropo($say);
		}
		$this->_say = isset($say) ? sprintf($say) : null;
		$this->_timeout = $timeout;
		$this->_transcription = isset($transcription) ? sprintf($transcription) : null;
		$this->_username = $username;		
		$this->_url = $url;	
		$this->_voice = $voice;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {
		if(isset($this->_attempts)) { $this->attempts = $this->_attempts; }
		if(isset($this->_allowSignals)) { $this->allowSignals = $this->_allowSignals; }	
		if(isset($this->_bargein)) { $this->bargein = $this->_bargein; }
		if(isset($this->_beep)) { $this->beep = $this->_beep; }
		if(isset($this->_choices)) { $this->choices = $this->_choices; }
		if(isset($this->_format)) { $this->format = $this->_format; }
		if(isset($this->_maxSilence)) { $this->maxSilence = $this->_maxSilence; }
		if(isset($this->_maxTime)) { $this->maxTime = $this->_maxTime; }
		if(isset($this->_method)) { $this->method = $this->_method; }
		if(isset($this->_password)) { $this->password = $this->_password; }
		if(isset($this->_say)) { $this->say = $this->_say; }
		if(isset($this->_timeout)) { $this->timeout = $this->_timeout; }
		if(isset($this->_transcription)) { $this->transcription = $this->_transcription; }	
		if(isset($this->_username)) { $this->username = $this->_username; }		
		if(isset($this->_url)) { $this->url = $this->_url; }
		if(isset($this->_voice)) { $this->voice = $this->_voice; }	
		return $this->unescapeJSON(json_encode($this));	
	}
}

/**
 * The redirect function forwards an incoming call to another destination / phone number before answering it.
 * @package TropoPHP_Support
 *
 */
class Redirect_Tropo extends BaseClass {
	
	private $_to;
	private $_from;
	
	/**
	 * Class constructor
	 *
	 * @param Endpoint $to
	 * @param Endpoint $from
	 */
	public function __construct($to=NULL, $from=NULL) {
		$this->_to = sprintf($to);
		$this->_from = isset($from) ? sprintf($from) : null;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {
		$this->to = $this->_to;
		if(isset($this->_from)) { $this->from = $this->_from; }		
		return $this->unescapeJSON(json_encode($this));	
	}
}

/**
 * Allows Tropo applications to reject incoming sessions before they are answered.
 * @package TropoPHP_Support
 *
 */
class Reject_Tropo extends EmptyBaseClass_Tropo { }

/**
 * Returned anytime a request is made to the Tropo Web API.
 * @package TropoPHP
 *
 */
class Result_Tropo {
	
	private $_sessionId;
	private $_callId;
	private $_state;
	private $_sessionDuration;
	private $_sequence;
	private $_complete;
	private $_error;
	private $_actions;
	private $_name;
  	private $_attempts;
  	private $_disposition;
  	private $_confidence;
 	private $_interpretation;
  	private $_concept;
  	private $_utterance;
  	private $_value;
	
	/**
	 * Class constructor
	 *
	 * @param string $json
	 */
	public function __construct($json=NULL) {
		if(empty($json)) {
	 		$json = file_get_contents("php://input");
	 		// if $json is still empty, there was nothing in 
	 		// the POST so throw an exception
  		if(empty($json)) {
	 		  throw new TropoException('No JSON available.');
 		  }
	 	}
		$result = json_decode($json);
		if (!is_object($result) || !property_exists($result, "result")) {
 		  throw new TropoException('Not a result object.');
		}
		$this->_sessionId = $result->result->sessionId;
		$this->_callId = $result->result->callId;
		$this->_state = $result->result->state;
		$this->_sessionDuration = $result->result->sessionDuration;
		$this->_sequence = $result->result->sequence;
		$this->_complete = $result->result->complete;
		$this->_error = $result->result->error;
		$this->_actions = $result->result->actions;		
		$this->_name = $result->result->actions->name;
		$this->_attempts = $result->result->actions->attempts;
		$this->_disposition = $result->result->actions->disposition;
		$this->_confidence = $result->result->actions->confidence;
		$this->_interpretation = $result->result->actions->interpretation;
		$this->_utterance = $result->result->actions->utterance;
		$this->_value = $result->result->actions->value;
		$this->_concept = $result->result->actions->concept;		
	}
	
	function getSessionId() {
		return $this->_sessionId;
	}
	
	function getCallId() {
		return $this->_callId;
	}
	
	function getState() {
		return $this->_state;
	}
	
	function getSessionDuration() {
		return $this->_sessionDuration;
	}
	
	function getSequence() {
		return $this->_sequence;
	}
	
	function isComplete() {
		return (bool) $this->_complete;
	}
	
	function getError() {
		return $this->_error;
	}
	
	function getActions() {
		return $this->_actions;
	}
	
	function getName() {
		return $this->_name;
	}
	
	function getAttempts() {
		return $this->_attempts;
	}
	
	function getDisposition() {
		return $this->_disposition;
	}
	
	function getConfidence() {
		return $this->_confidence;
	}
	
	function getInterpretation() {
		return $this->_interpretation;
	}
	
	function getConcept() {
		return $this->_concept;
	}
	
	function getUtterance() {
		return $this->_utterance;
	}
	
	function getValue() {
		return $this->_value;
	}
}

/**
 * When the current session is a voice channel this key will either play a message or an audio file from a URL. 
 * In the case of an text channel it will send the text back to the user via instant messaging or SMS.
 * @package TropoPHP_Support
 *
 */
class Say_Tropo extends BaseClass {
	
	private $_value;
	private $_as;
	private $_event;
	private $_format;
	private $_voice;
	private $_allowSignals;
	
	/**
	 * Class constructor
	 *
	 * @param string $value
	 * @param SayAs $as
	 * @param string $event
	 * @param string $voice
	 * @param string|array $allowSignals
	 */
	public function __construct($value, $as=NULL, $event=NULL, $voice=NULL, $allowSignals=NULL) {
		$this->_value = $value;
		$this->_as = $as;
		$this->_event = $event;
		$this->_voice = $voice;
		$this->_allowSignals = $allowSignals;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {		
		if(isset($this->_event)) { $this->event = $this->_event; }
		$this->value = $this->_value;
		if(isset($this->_as)) { $this->as = $this->_as; }
		if(isset($this->_voice)) { $this->voice = $this->_voice; }
		if(isset($this->_allowSignals)) { $this->allowSignals = $this->_allowSignals; }	
		return $this->unescapeJSON(json_encode($this));	
	}
}

/**
 * The payload sent as an HTTP POST to the web application when a new session_Tropo arrives.
 *
 * TODO: Consider using associative array for To and From.
 * TODO: Need to break out headers into a more accessible data structure.
 * @package TropoPHP
 */
class Session_Tropo {
	
	private $_id;
	private $_accountID;
	private $_timestamp;
	private $_userType;
	private $_initialText;
	private $_to;
	private $_from;
	private $_headers;
	private $_parameters;
	
	/**
	 * Class constructor
	 *
	 * @param string $json
	 */
	public function __construct($json=NULL) {
		if(empty($json)) {
	 		$json = file_get_contents("php://input");
	 		// if $json is still empty, there was nothing in 
	 		// the POST so throw exception
  		if(empty($json)) {
	 		  throw new TropoException('No JSON available.', 1);
 		  }
	 	}
		$session = json_decode($json);
		if (!is_object($session) || !property_exists($session, "session")) {
		  throw new TropoException('Not a session object.', 2);
		}
		$this->_id = $session->session->id;
		$this->_accountId = $session->session->accountId;
		$this->_timestamp = $session->session->timestamp;
		$this->_userType = $session->session->userType;
		$this->_initialText = $session->session->initialText;
		$this->_to = array("id" => @$session->session->to->id, "channel" => @$session->session->to->channel, "name" => @$session->session->to->name, "network" => @$session->session->to->network);
		$this->_from = array("id" => @$session->session->from->id, "channel" => @$session->session->from->channel, "name" => @$session->session->from->name, "network" => @$session->session->from->network);
		$this->_headers = self::setHeaders(@$session->session->headers);
		$this->_parameters = property_exists($session->session, 'parameters') ? (Array) $session->session->parameters : null;			
	}
	
	public function getId() {
		return $this->_id;
	}
	
	public function getAccountID() {
		return $this->_accountId;
	}
	
	public function getTimeStamp() {
		return $this->_timestamp;
	}
	public function getUserType() {
		return $this->_userType;	
	}
	
	public function getInitialText() {
		return $this->_initialText;
	}
	
	public function getTo() {
		return $this->_to;
	}
	
	public function getFrom() {
		return $this->_from;
	}
	
	public function getHeaders() {
		return $this->_headers;
	}
		
	/**
	 * Returns the query string parameters for the session api
	 *
	 * If an argument is provided, a string containing the value of a
	 * query string variable matching that string is returned or null 
	 * if there is no match. If no argument is argument is provided, 
	 * an array is returned with all query string variables or an empty
	 * array if there are no query string variables.
	 * 
	 * @param string $name A specific parameter to return
	 * @return string|array $param 
	 */
	public function getParameters($name = null) {
	  if (isset($name)) {
  	  if (!is_array($this->_parameters)) {
  	    // We've asked for a specific param, not there's no params set
  	    // return a null.
  	    return null;
  	  }
	    if (isset($this->_parameters[$name])) {
	      return $this->_parameters[$name];
	    } else {
	      return null;
	    }
	  } else {
  	  // If the parameters field doesn't exist or isn't an array
  	  // then return an empty array()
  	  if (!is_array($this->_parameters)) {
  	    return array();
  	  }
  	  return $this->_parameters;	    
	  }
	}
	
	public function setHeaders($headers) {
		$formattedHeaders = new Headers_Tropo();
		// headers don't exist on outboud calls
		// so only do this if there are headers
		if (is_object($headers)) {
  		foreach($headers as $name => $value) {
  			$formattedHeaders->$name = $value;
  		}		  
		}
		return $formattedHeaders;
	}
}

/**
 * Allows Tropo applications to begin recording the current session. 
 * The resulting recording may then be sent via FTP or an HTTP POST/Multipart Form.
 * @package TropoPHP_Support
 * 
 */
class StartRecording_Tropo extends BaseClass {
	
	private $_name;
	private $_format;
	private $_method;
	private $_password;
	private $_url;
	private $_username;
	
	/**
	 * Class constructor
	 *
	 * @param string $name
	 * @param string $format
	 * @param string $method
	 * @param string $password
	 * @param string $url
	 * @param string $username
	 */
	public function __construct($format=NULL, $method=NULL, $password=NULL, $url=NULL, $username=NULL) {		
		$this->_format = $format;
		$this->_method = $method;
		$this->_password = $password;
		$this->_url = $url;
		$this->_username = $username;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {		
		if(isset($this->_format)) { $this->format = $this->_format; }
		if(isset($this->_method)) { $this->method = $this->_method; }
		if(isset($this->_password)) { $this->password = $this->_password; }
		if(isset($this->_url)) { $this->url = $this->_url; }
		if(isset($this->_username)) { $this->username = $this->_username; }		
		return $this->unescapeJSON(json_encode($this));	
	}	
}

/**
 * Stop an already started recording.
 * @package TropoPHP_Support
 *
 */
class StopRecording_Tropo extends EmptyBaseClass_Tropo { }

/**
 * Transcribes spoken text.
 * @package TropoPHP_Support
 *
 */
class Transcription_Tropo extends BaseClass {
	
	private $_url;
	private $_id;
	private $_emailFormat;
	
	/**
	 * Class constructor
	 *
	 * @param string $url
	 * @param string $id
	 * @param string $emailFormat
	 */
	public function __construct($url, $id=NULL, $emailFormat=NULL) {
		$this->_url = $url;
		$this->_id = $id;
		$this->_emailFormat = $emailFormat;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {
		if(isset($this->_id)) { $this->id = $this->_id; }
		if(isset($this->_url)) { $this->url = $this->_url; }
		if(isset($this->_emailFormat)) { $this->emailFormat = $this->_emailFormat; }
		return $this->unescapeJSON(json_encode($this));	
	}
}

/**
 * Transfers an already answered call to another destination / phone number. 
 * @package TropoPHP_Support
 *
 */
class Transfer_Tropo extends BaseClass {
	
	private $_answerOnMedia;
	private $_choices;
	private $_from;
	private $_on;
	private $_ringRepeat;
	private $_timeout;
	private $_to;
	private $_allowSignals;
	
	/**
	 * Class constructor
	 *
	 * @param string $to
	 * @param boolean $answerOnMedia
	 * @param Choices $choices
	 * @param Endpoint $from
	 * @param On $on
	 * @param int $ringRepeat
	 * @param int $timeout
	 * @param string|array $allowSignals
	 */
	public function __construct($to, $answerOnMedia=NULL, Choices_Tropo $choices=NULL, $from=NULL, $ringRepeat=NULL, $timeout=NULL, $on=NULL, $allowSignals=NULL) {
		$this->_to = $to;
		$this->_answerOnMedia = $answerOnMedia;
		$this->_choices = isset($choices) ? sprintf($choices) : null; 
		$this->_from = $from;
		$this->_ringRepeat = $ringRepeat;
		$this->_timeout = $timeout;
		$this->_on = isset($on) ? sprintf($on) : null;
		$this->_allowSignals = $allowSignals;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {		
		$this->to = $this->_to;
		if(isset($this->_answerOnMedia)) { $this->answerOnMedia = $this->_answerOnMedia; }
		if(isset($this->_choices)) { $this->choices = $this->_choices; }
		if(isset($this->_from)) { $this->from = $this->_from; }
		if(isset($this->_ringRepeat)) { $this->ringRepeat = $this->_ringRepeat; }
		if(isset($this->_timeout)) { $this->timeout = $this->_timeout; }
		if(isset($this->_on)) { $this->on = $this->_on; }
		if(isset($this->_allowSignals)) { $this->allowSignals = $this->_allowSignals; }
		return $this->unescapeJSON(json_encode($this));			
	}	
}

/**
 * Defnies an endoint for transfer and redirects.
 * @package TropoPHP_Support
 *
 */
class Endpoint_Tropo extends BaseClass {
	
	private $_id;
	private $_channel;
	private $_name = 'unknown';
	private $_network;
	
	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $channel
	 * @param string $name
	 * @param string $network
	 */
	public function __construct($id, $channel=NULL, $name=NULL, $network=NULL) {
		
		$this->_id = $id;
		$this->_channel = $channel;
		$this->_name = $name;
		$this->_network = $network;
	}
	
	/**
	 * Renders object in JSON format.
	 *
	 */
	public function __toString() {
		
		if(isset($this->_id)) { $this->id = $this->_id; }
		if(isset($this->_channel)) { $this->channel = $this->_channel; }
		if(isset($this->_name)) { $this->name = $this->_name; }
		if(isset($this->_network)) { $this->network = $this->_network; }		
		return $this->unescapeJSON(json_encode($this));			
	}
}

/**
 * A helper class for wrapping exceptions. Can be modified for custom excpetion handling.
 *
 */
class TropoException extends Exception { }

/**
 * Date Helper class.
 * @package TropoPHP_Support
 */
class Date_Tropo {
	public static $monthDayYear = "mdy";
	public static $dayMonthYear = "dmy";
	public static $yearMonthDay = "ymd";
	public static $yearMonth = "ym";
	public static $monthYear = "my";
	public static $monthDay = "md";
	public static $year = "y";
	public static $month = "m";
	public static $day = "d";		
}

/**
 * Duration Helper class.
 * @package TropoPHP_Support
 */
class Duration_Tropo {
	public static $hoursMinutesSeconds = "hms";
	public static $hoursMinutes = "hm";	
	public static $hours = "h";
	public static $minutes = "m";
	public static $seconds = "s";
}

/**
 * Event Helper class.
 * @package TropoPHP_Support
 */
class Event_Tropo {
	
	public static $continue = 'continue';
	public static $incomplete = 'incomplete';
	public static $error = 'error';
	public static $hangup = 'hangup';
	public static $join = 'join';
	public static $leave = 'leave';
	public static $ring = 'ring';
}

/**
 * Format Helper class.
 * @package TropoPHP_Support
 */
class Format_Tropo {
	public $date;
	public $duration;
	public static $ordinal = "ordinal";
	public static $digits = "digits";
	
	public function __construct($date=NULL, $duration=NULL) {
		$this->date = $date;
		$this->duration = $duration;
	}
}

/**
 * SayAs Helper class.
 * @package TropoPHP_Support
 */
class SayAs_Tropo {
	public static $date = "DATE";
	public static $digits = "DIGITS";
	public static $number = "NUMBER";
}

/**
 * Network Helper class.
 * @package TropoPHP_Support
 */
class Network_Tropo {
	public static $pstn = "PSTN";
	public static $voip = "VOIP";
	public static $aim = "AIM";
	public static $gtalk = "GTALK";
	public static $jabber = "JABBER";
	public static $msn = "MSN";
	public static $sms = "SMS";
	public static $yahoo = "YAHOO";	
	public static $twitter = "TWITTER";
}

/**
 * Channel Helper class.
 * @package TropoPHP_Support
 */
class Channel_Tropo {
	public static $voice = "VOICE";
	public static $text = "TEXT";
}

/**
 * AudioFormat Helper class.
 * @package TropoPHP_Support
 */
class AudioFormat_Tropo {
	public static $wav = "audio/wav";
	public static $mp3 = "audio/mp3";
}

/**
 * Voice Helper class.
 * @package TropoPHP_Support
 */
class Voice_Tropo {
	public static $Castilian_Spanish_male = "jorge";
	public static $Castilian_Spanish_female = "carmen";
	public static $French_male = "bernard";
	public static $French_female = "florence";
	public static $US_English_male = "dave";
	public static $US_English_female = "jill";
	public static $British_English_male = "dave";
	public static $British_English_female = "kate";
	public static $German_male = "stefan";
	public static $German_female = "katrin";
	public static $Italian_male = "luca";
	public static $Italian_female = "paola";
	public static $Dutch_male = "willem";
	public static $Dutch_female = "saskia";
	public static $Mexican_Spanish_male = "carlos";
	public static $Mexican_Spanish_female = "soledad";
}

/**
 * Recognizer Helper class
 * @package TropoPHP_Support
 *
 */
class Recognizer_Tropo {
	public static $German = 'de-de';
	public static $British_English = 'en-gb';
	public static $US_English = 'en-us';
	public static $Castilian_Spanish = 'es-es';
	public static $Mexican_Spanish = 'es-mx'; 
	public static $French_Canadian = 'fr-ca';
	public static $French = 'fr-fr';
	public static $Italian = 'it-it';
	public static $Polish = 'pl-pl';
	public static $Dutch = 'nl-nl';	
}

/**
 * SIP Headers Helper class.
 * @package TropoPHP_Support
 */
class Headers_Tropo {
	
	public function __set($name, $value) {
		if(!strstr($name, "-")) {
			$this->$name = $value;	
		} else {
			$name = str_replace("-", "_", $name);
			$this->$name = $value;	
		}		
	}
	
}

?>
