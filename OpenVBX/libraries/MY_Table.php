<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

// ------------------------------------------------------------------------

/**
 * HTML Table Generating Class: extended to include row/column ids
 *
 */

class MY_Table extends CI_Table
{
	var $row_ids = array();		// holds the ID values to be appended to each row
	var $id;

	function MY_Table($id = '') {
		parent::CI_Table();
		$this->id = empty($id) ? 'table_' . uniqid() : $id;
	}


	// --------------------------------------------------------------------

	/**
	 * Add a table row
	 *
	 * The first parameter will be the {ID} value that is replaced on each row template.
	 * Data can be passed as an array or discreet params
	 *
	 * @access	public
	 * @param   int $id
	 * @param   mixed $data
	 * @return	void
	 */
	function add_id_row($id, $data)
	{
		$args = func_get_args();
		array_shift($args);
		$this->rows[] = (is_array($data)) ? $data : $args;

		$index = count($this->rows) - 1;
		$this->row_ids[$index] = $id;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the table
	 *
	 * @access	public
	 * @param	mixed
	 * @return	string
	 */
	function generate($table_data = NULL)
	{
		// The table data can optionally be passed to this function
		// either as a database result object or an array
		if ( ! is_null($table_data))
		{
			if (is_object($table_data))
			{
				$this->_set_from_object($table_data);
			}
			elseif (is_array($table_data))
			{
				$set_heading = (count($this->heading) == 0 AND $this->auto_heading == FALSE) ? FALSE : TRUE;
				$this->_set_from_array($table_data, $set_heading);
			}
		}

		// Is there anything to display?  No?  Smite them!
		if (count($this->heading) == 0 AND count($this->rows) == 0)
		{
			return 'Undefined table data';
		}

		// Compile and validate the template date
		$this->_compile_template();


		// Build the table!

		$out = str_replace('{ID}', $this->id, $this->template['table_open']);
		$out .= $this->newline;

		// Add any caption here
		if ($this->caption)
		{
			$out .= $this->newline;
			$out .= '<caption>' . $this->caption . '</caption>';
			$out .= $this->newline;
		}

		// Is there a table heading to display?
		if (count($this->heading) > 0)
		{
			$out .= $this->template['heading_row_start'];
			$out .= $this->newline;

			for($col = 0; $col < count($this->heading); $col++)
			{
				$out .= str_replace('{COL}', $col, $this->template['heading_cell_start']);
				$out .= $this->heading[$col];
				$out .= $this->template['heading_cell_end'];
			}

			$out .= $this->template['heading_row_end'];
			$out .= $this->newline;
		}

		// Build the table rows
		if (count($this->rows) > 0)
		{
			$i = 1;
			$row_find = array('{ROW}', '{ID}');
			for($r = 0; $r < count($this->rows); $r++)
			{
				$row = $this->rows[$r];
				if ( ! is_array($row)) break;
				$row_id = isset($this->row_ids[$r]) ? $this->row_ids[$r] : '';

				// We use modulus to alternate the row colors
				$name = (fmod($i++, 2)) ? '' : 'alt_';

				$out .= str_replace($row_find, array($r, $row_id), $this->template['row_'.$name.'start']);
				$out .= $this->newline;

				for($col = 0; $col < count($row); $col++)
				{
					$out .= str_replace('{COL}', $col, $this->template['cell_'.$name.'start']);

					$cell = $row[$col];
					if ($cell === "")
					{
						$out .= $this->empty_cells;
					}
					else
					{
						$out .= $cell;
					}

					$out .= $this->template['cell_'.$name.'end'];
				}

				$out .= $this->template['row_'.$name.'end'];
				$out .= $this->newline;
			}
		}

		$out .= $this->template['table_close'];

		return $out;
	}

	function clear()
	{
		parent::clear();
		$this->row_ids = array();
	}

	// --------------------------------------------------------------------

	/**
	 * Default Template
	 *
	 * @access	private
	 * @return	array
	 */
	function _default_template()
	{
		return  array (
						'table_open' 			=> '<table border="0" id="{ID}" class="grid">',

						'heading_row_start' 	=> '<thead><tr>',
						'heading_row_end' 		=> '</tr></thead><tbody>',
						'heading_cell_start'	=> '<th class="col_{COL}">',
						'heading_cell_end'		=> '</th>',

						'row_start' 			=> '<tr rel="{ID}" class="even_row row_{ROW}">',
						'row_end' 				=> '</tr>',
						'cell_start'			=> '<td class="col_{COL}">',
						'cell_end'				=> '</td>',

						'row_alt_start' 		=> '<tr rel="{ID}" class="odd_row row_{ROW}">',
						'row_alt_end' 			=> '</tr>',
						'cell_alt_start'		=> '<td class="col_{COL}">',
						'cell_alt_end'			=> '</td>',

						'table_close' 			=> '</tbody></table>'
					);
	}

}
