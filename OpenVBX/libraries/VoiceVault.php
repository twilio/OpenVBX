<?php

/**
 * VoiceVault library.
 **/
class VoiceVault
{
	/**
	 * @var boolean $debug Is the voice vault account in debug mode?
	 */
	public $debug = false;

	/**
	 * @var array $_auth Authentication values for the voice vault API.
	 */
	private $_auth;
	/**
	 * @var string $_api_url Base API URL.
	 */
	private $_api_url;
	
	/**
	 * Initialization. Sets the voice vault API URLs and parameters.
	 */
	function __construct($username, 
			$password,
			$organisation_id,
			$config_id,
			$debug=true,
			$api_url=null)
	{
		// Initialization
		$this->debug = $debug;
		$this->_auth = array(
			'username' => $username,
			'password' => $password,
			'organisation_id' => $organisation_id,
			'config_id' => $config_id,
		);

		// Set the API url
		if (!$api_url) {
			if ($this->debug)
				$this->_api_url = 'https://development.voicevault.net/RestApi700';
			else
				$this->_api_url = null;
		} else {
			$this->_api_url = $api_url;
		}
	}

	/**
	 * VoiceVault RegisterClaimant method.
	 *
	 * @return SimpleXMLElement result of the request
	 */
	public function RegisterClaimant()
	{
		$result = $this->doRequest($this->_api_url.'/RegisterClaimant.ashx',
			'POST',
			array(
				'username' => $this->_auth['username'],
				'password' => $this->_auth['password'],
				'organisation_unit' => $this->_auth['organisation_id'],
			)
		);

		return new SimpleXMLElement($result);
	}

	/**
	 * VoiceVault StartDialogue method.
	 *
	 * @param string $claimant_id
	 * @param string $externalRef External reference that can be used to 
	 *	perform a lookup in the system.
	 * @return SimpleXMLElement result of the request
	 */
	public function StartDialogue($claimant_id, $externalRef=null)
	{
		$result = $this->doRequest($this->_api_url.'/StartDialogue.ashx',
			'POST',
			array(
				'username' => $this->_auth['username'],
				'password' => $this->_auth['password'],
				'configuration_id' => $this->_auth['config_id'],
				'claimant_id' => $claimant_id,
				'external_ref' => $externalRef,
			)
		);

		return new SimpleXMLElement($result);
	}

	/**
	 * VoiceVault SubmitPhrase method.
	 *
	 * @param string $file File path to the file.
	 * @param string $phrase Phrase for the recording.
	 * @param integer $dialogue_id Dialogue ID from StartDialogue.
	 * @return SimpleXMLElement result
	 */
	public function SubmitPhrase($file, $phrase, $dialogue_id)
	{
		$result = $this->doRequest($this->_api_url.'/SubmitPhrase.ashx',
			// 'http://localhost/sendshorty/index.php/VoiceVault/default/test',
			'POST',
			array(
				'username' => $this->_auth['username'],
				'password' => $this->_auth['password'],
				'dialogue_id' => $dialogue_id,
				'prompt' => $phrase,
				'format' => 'Unknown',
				'utterance' => "@$file"
			)
		);

		return new SimpleXMLElement($result);
	}

	public function GetDialogueSummary($dialogue_id)
	{
		$result = $this->doRequest($this->_api_url.'/GetDialogueSummary.ashx',
			'POST',
			array(
				'username' => $this->_auth['username'],
				'password' => $this->_auth['password'],
				'dialogue_id' => $dialogue_id,
			)
		);

		return new SimpleXMLElement($result);
	}

	
	/**
	 * Initates a curl request.
	 */
	private function doRequest($url, $method='GET', $parameters=array())
	{
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
		}

		if (!empty($parameters)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
		}

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		return curl_exec($ch);
	}
}
