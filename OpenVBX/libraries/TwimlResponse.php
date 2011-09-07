<?php

if (!class_exists('Services_Twilio_Twiml')) {
	include_once(APPPATH.'libraries/Services/Twilio.php');
}

/**
 * Extends the Services_Twilio_Twiml class to provide a respond
 * method that allows us to control the exit path of the TwiML
 * to properly manage headers & session data
 *
 */
class TwimlResponse extends Services_Twilio_Twiml {		
	public function respond() {
		if (!headers_sent()) {
			header("Content-type: text/xml");
			
			// persist session data
			$ci = &get_instance();
			if(is_object($ci) && isset($ci->session) && is_object($ci->session)) {
				$ci->session->persist();
			}
		}

		echo $this;
	}	
}

?>