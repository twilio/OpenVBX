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
 * @param string $name
 * @param array $options
 * @param array $selected
 * @param string $extra
 * @return string
 */
function applet_dropdown($name = '', $options = array(), $selected = array(), $extra = '')
{
	if ( ! is_array($selected))
	{
		$selected = array($selected);
	}

	// If no selected state was submitted we will attempt to set it automatically
	if (count($selected) === 0)
	{
		// If the form name appears in the $_POST array we have a winner!
		if (isset($_POST[$name]))
		{
			$selected = array($_POST[$name]);
		}
	}

	if ($extra != '') $extra = ' '.$extra;

	$multiple = (count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

	$form = '<select class="medium" name="'.$name.'"'.$extra.$multiple.">\n";

	foreach ($options as $key => $val)
	{
		$key = (string) $key;

		$key = htmlspecialchars($key, ENT_COMPAT, 'UTF-8', false);
		$val = htmlspecialchars($val, ENT_COMPAT, 'UTF-8', false);
			
		if (is_array($val))
		{
			$form .= '<optgroup label="'.$key.'">'."\n";

			foreach ($val as $optgroup_key => $optgroup_val)
			{
				$optgroup_key = htmlspecialchars($optgroup_key, ENT_COMPAT, 'UTF-8', false);
				$optgroup_val = htmlspecialchars($optgroup_val, ENT_COMPAT, 'UTF-8', false);
					
				$sel = (in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';

				$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string) $optgroup_val."</option>\n";
			}

			$form .= '</optgroup>'."\n";
		}
		else
		{
			$sel = (in_array($key, $selected)) ? ' selected="selected"' : '';

			$form .= '<option value="'.$key.'"'.$sel.'>'.(string) $val."</option>\n";
		}
	}

	$form .= '</select>';

	return $form;
}
