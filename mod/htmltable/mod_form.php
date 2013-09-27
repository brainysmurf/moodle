<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * htmltable configuration form
 *
 * @package    mod
 * @subpackage htmltable
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/htmltable/locallib.php');
require_once($CFG->libdir.'/filelib.php');

class mod_htmltable_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $config = get_config('htmltable');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->add_intro_editor($config->requiremodintro);

        //-------------------------------------------------------
        $mform->addElement('header', 'contentsection', get_string('contentheader', 'htmltable'));
        $mform->setExpanded('contentsection');
        
/*$tableEditButtons = '<div id="htmltable_edittable_buttons">
		<a href="#" class="htmltable_addrow_button"><button><i class="icon-th-list"></i> Add another row</button></a>
		<a href="#" class="htmltable_addcol_button"><button><i class="icon-th"></i> Add another column</button></a>
	</div>';        */
	
$tableHTML = '<script>
	var currentTable;
</script>';
	
	
//Editing an existing table?
if ( $this->current && !is_null($this->current->content) )
{
	//It's already in JSON format so set it as a JS variable
	echo '<script>
		var currentTable = '.$this->current->content.';
	</script>';
}       	
    
$tableHTML .= <<<EOT

	<table class="userinfotable" id="htmltable_edittable">
		<tr class="addrow"><td style="text-align:center;"><a href="#" class="htmltable_addrow_button"><button><i class="icon-plus"></i> Add A Row</button></a></td></tr>
	</table>
	
	<script>

		var htmltable_titleinput = '<input type="text" placeholder="Title" tabindex="1" />';
		var htmltable_datainput =  '<input type="text" placeholder="Data"  tabindex="1" />';	
		var htmltable_removecol_button = '<a href="#" class="htmltable_removecol_button" title="Remove Column"><button><i class="icon-minus"></i></button></a><br/>';
		
		var htmltable_cols = currentTable ? currentTable[0].length : 2;
		var htmltable_initial_rows = currentTable ? currentTable.length-1 : 1;
		
		function htmltable_addcol()
		{
			++htmltable_cols;
			
			$('#htmltable_edittable tr:not(".addrow")').each(function()
			{
				if ( $(this).hasClass('header') )
				{
					$(this).children('th').last().before('<th>'+htmltable_removecol_button+htmltable_titleinput+'</th>');
				}
				else
				{
					$(this).children('td').last().before('<td>'+htmltable_datainput+'</td>');
				}
			});
			
			return false;
		}
	
	
		function htmltable_addheader()
		{
			var tr = '<tr class="header">';
				for ( var i = 0 ; i < htmltable_cols ; ++i )
				{
					tr += '<th style="height:70px;">';
						if ( i >= 2 )
						{
							tr += htmltable_removecol_button;
						}
						tr += htmltable_titleinput;				
					tr += '</th>';
				}
				tr += '<th class="addcol" style="width:32px;"><a href="#" class="htmltable_addcol_button" title="Add A Column"><button><i class="icon-plus"></i></button></a></th>';			
			tr += '</tr>';
			
			$('#htmltable_edittable tr.addrow').before(tr);
			
			return false;	
		}
	
		function htmltable_addrow()
		{
			var tr = '<tr class="data">';
			for ( var i = 0 ; i < htmltable_cols ; ++i )
			{
				if ( i == 0 )
				{
					tr += '<td><a href="#" class="htmltable_removerow_button" title="Remove Row"><button><i class="icon-minus"></i></button></a>'+htmltable_datainput+'</td>';
				}
				else
				{
					tr += '<td>'+htmltable_datainput+'</td>';
				}
			}
			
			tr += '<td style="width:32px;">&nbsp;</td>';	
			
			tr += '</tr>';
			
			$('#htmltable_edittable tr.addrow').before(tr);
			
			return false;
		}
		
		$(document).on('click','.htmltable_addcol_button',htmltable_addcol);
		$(document).on('click','.htmltable_addrow_button',htmltable_addrow);
		
		//Remove row
		$(document).on('click','.htmltable_removerow_button',function()
		{
			$(this).closest('tr').remove();
			return false;
		});
		
		//Remove column
		$(document).on('click','.htmltable_removecol_button',function()
		{
			var index = $(this).closest('th').index() +1;
			
			$('#htmltable_edittable tr').each(function()
			{
				$(this).children('th:nth-child('+index+'), td:nth-child('+index+')').remove();
			});
			
			return false;
		});
		
		//Add htmltable_initial_rows rows by default
		htmltable_addheader();
		for ( var i = 0 ; i < htmltable_initial_rows ; ++i )
		{
			htmltable_addrow();
		}
	
		function htmltable_export()
		{
			var table = [];
			$('#htmltable_edittable tr:not(.addrow)').each(function()
			{
				var row = [];
				$(this).find('input').each(function()
				{
					row.push( $(this).val() );
				});
				table.push(row);
			});
			return table;
		}
	
		//When submitting the form, put the table data into a field
		$(document).on('submit','#mform1',function()
		{
			var table = htmltable_export();
			table = JSON.stringify(table);
			$('input[name=content]').val(table);
		});
		
		//Populate table with existing content
		if ( currentTable )
		{
			$('#htmltable_edittable tr:not(.addrow)').each(function(rowNum)
			{
				var row = currentTable[rowNum];
				
				$(this).find('th input, td input').each(function(colNum)
				{
					$(this).val( row[colNum] );
				});
			});
		}
	
	</script>

EOT;
 
	 	$mform->addElement('hidden', 'content' , '');
	 	$mform->addElement('hidden', 'contentformat' , '1');
        $mform->addElement('html',$tableHTML);
        
        $mform->addElement('html','<br/><div class="generalbox inset"><h4 class="advice"><i class="icon-lightbulb"></i> <strong>Tip:</strong> you can style your text like this:</h4>
        <table class="styledtable">
        	<tr>
        		<th>Style</td>
        		<th>Type This</td>
        		<th>To Get This</td>
        	</tr>
        	<tr>
        		<td style="width:140px;">Bold</td>
        		<td>**bold**</td>
        		<td style="width:200px;"><strong>bold</strong></td>
        	</tr>
        	<tr>
        		<td>Italic</td>
        		<td>*italic*</td>
        		<td><em>italic</em></td>
        	</tr>
        	<tr>
        		<td>Links (With your own text)</td>
        		<td>[Text you want to appear](http://www.website-you-want-to-link-to.com)</td>
        		<td><a href="http://www.website-you-want-to-link-to.com">Text you want to appear</a></td>
        	</tr>
        	<tr>
        		<td>Links (Showing URL)</td>
        		<td>&lt;http://www.google.com&gt;</td>
        		<td><a href="http://www.google.com">http://www.google.com</a></td>
        	</tr>
        </table>      
        </div>');
        
        //-------------------------------------------------------
        $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
        
        //Change display options to user friendly labels
        foreach ( $options as &$option )
        {
        	switch ( $option )
        	{
        		case 'Embed':
        			$option = 'Show on course page';
        		break;
        		
        		case 'Open':
        			$option = 'Click to view';
        		break;
        	}
        }
        
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'htmltable'), $options);
            $mform->setDefault('display', $config->display);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'htmltable'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'htmltable'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
        }

        $mform->addElement('advcheckbox', 'printheading', get_string('printheading', 'htmltable'));
        $mform->setDefault('printheading', $config->printheading);
        $mform->addElement('advcheckbox', 'printintro', get_string('printintro', 'htmltable'));
        $mform->setDefault('printintro', $config->printintro);

        // add legacy files flag only if used
        if (isset($this->current->legacyfiles) and $this->current->legacyfiles != RESOURCELIB_LEGACYFILES_NO) {
            $options = array(RESOURCELIB_LEGACYFILES_DONE   => get_string('legacyfilesdone', 'htmltable'),
                             RESOURCELIB_LEGACYFILES_ACTIVE => get_string('legacyfilesactive', 'htmltable'));
            $mform->addElement('select', 'legacyfiles', get_string('legacyfiles', 'htmltable'), $options);
            $mform->setAdvanced('legacyfiles', 1);
        }

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('htmltable');
            $default_values['htmltable']['format'] = $default_values['contentformat'];
            $default_values['htmltable']['text']   = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_htmltable', 'content', 0, htmltable_get_editor_options($this->context), $default_values['content']);
            $default_values['htmltable']['itemid'] = $draftitemid;
        }
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $default_values['printheading'] = $displayoptions['printheading'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
        }
    }
}

