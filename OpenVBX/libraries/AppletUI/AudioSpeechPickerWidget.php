<?php
/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.

 *  The Original Code is OpenVBX, released June 15, 2010.

 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.

 * Contributor(s):
 **/

/**
 * Class AudioSpeechPickerWidget
 * @property CI_Loader $load
 */
class AudioSpeechPickerWidget extends AppletUIWidget
{
	protected $template = 'AudioSpeechPicker';
	
	protected $name;
	protected $mode;
	protected $say_value;
	protected $play_value;
	protected $tag;
	
	public function __construct($name, $mode = null, $say_value = null, $play_value = null, $tag = 'global')
	{
		$this->name = $name;
		$this->mode = $mode;
		$this->say_value = $say_value;
		$this->play_value = $play_value;
		$this->tag = $tag;
		
		parent::__construct($this->template);
	}

	public function render($data = array())
	{
		$hasValue = empty($this->mode) ? false : true;
		
		$this->load =& load_class('Loader');
		$this->load->model('vbx_audio_file');
		$this->load->model('vbx_device');
		
		// Get a list of all previously recorded items so we can populate the library
		$ci = &get_instance();

		$ci->db->where('url IS NOT NULL');
		$ci->db->where('tag', $this->tag);
		$ci->db->where('tenant_id', $ci->tenant->id);
		$ci->db->from('audio_files');
		$ci->db->order_by('created DESC');

		$results = $ci->db->get()->result();

		foreach($results as $i => $result)
		{
			$results[$i] = new VBX_Audio_File($result);
		}
		
		// Pre-fill the record text field with the the first device phone number we
		// find for the current user that is active.
		$ci = &get_instance();

		$user = VBX_User::get($ci->session->userdata('user_id'));
		$user_phone = '';
		if (count($user->devices)) 
		{
			foreach ($user->devices as $device)
			{
				if ($device->is_active)
				{
					$user_phone = format_phone($device->value);
					break;
				}
			}
		}

		// set the caller id for recording via the phone
		$caller_id = '';
		$ci->load->model('vbx_incoming_numbers');
		try
		{
			$numbers = $ci->vbx_incoming_numbers->get_numbers();
			foreach ($numbers as $number)
			{
				// find the first number that has voice enabled
				// yes, this is a rather paranoid check
				if (isset($number->capabilities->voice) && $number->capabilities->voice > 0)
				{
					$caller_id = normalize_phone_to_E164($number->phone);
					break;
				}
			}
		}
		catch(VBX_IncomingNumberException $e)
		{
			// fail silently, for better or worse
			error_log($e->getMessage());
		}

		$data = array_merge(array(
			'name' => $this->name,
			'hasValue' => $hasValue,
			'mode' => $this->mode,
			'say' => $this->say_value,
			'play' => $this->play_value,
			'tag' => $this->tag,
			'library' => $results,
			'first_device_phone_number' => $user_phone,
			'caller_id' => $caller_id
		), $data);
		return parent::render($data);
	}
	
	/**
	 * Set the proper verb for the pickers value
	 * 
	 * @example 
	 * 		$response = new Services_Twilio_Twiml;
	 * 		AudioSpeechPickerWidget::setVerbForValue($value, $response);
	 *
	 * @param string $value
	 * @param object $response Services_Twilio_Twiml
	 * @return mixed Services_Twilio_Twiml on success, boolean false on fail
	 */
	public static function setVerbForValue($value, $response) {
		$matches = array();
		if (empty($value) || !($response instanceof Services_Twilio_Twiml))
		{
			return false;
		}
		else if (preg_match('/^vbx-audio-upload:\/\/(.*)/i', $value, $matches))
		{
			// This is a locally hosted file, and we need to return the correct absolute URL for the file.
			return $response->play(asset_url('audio-uploads/'.$matches[1]));
		}
		else if (preg_match('/^http(s)?:\/\/(.*)/i', $value))
		{
			// it's already an absolute URL
			return $response->play($value);
		}
		else
		{
			$ci =& get_instance();
			return $response->say($value, array(
					'voice' => $ci->vbx_settings->get('voice', $ci->tenant->id),
					'language' => $ci->vbx_settings->get('voice_language', $ci->tenant->id)
				));
		}		
	}
	
	/**
	 * Create the proper verb for the Picker's value
	 *
	 * @deprecated use AudioSpeechPickerWidget::setVerbForValue instead
	 * @param mixed $value 
	 * @param object $defaultVerb 
	 * @return object subclass of Verb
	 */
	public static function getVerbForValue($value, $defaultVerb)
	{
		_deprecated_notice(__METHOD__, '1.0.4', 'AudioSpeechPickerWidget::setVerbForValue');
		$matches = array();

		if (empty($value))
		{
			return $defaultVerb;
		}
		else if (preg_match('/^vbx-audio-upload:\/\/(.*)/i', $value, $matches))
		{
			// This is a locally hosted file, and we need to return the correct
			// absolute URL for the file.
			return new Play(asset_url("audio-uploads/" . $matches[1]));
		}
		else if (preg_match('/^http(s)?:\/\/(.*)/i', $value))
		{
			// it's already an absolute URL
			return new Play($value);
		}
		else
		{
			return new Say($value);
		}
	}
}
