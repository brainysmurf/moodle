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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Grid Format - A topics based format that uses a grid of user selectable images to popup a light box of the section.
 *
 * @package	   course/format
 * @subpackage grid
 * @copyright  &copy; 2012 G J Barnard in respect to modifications of standard topics format.
 * @author	   G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @author	   Based on code originally written by Paul Krix and Julian Ridden.
 * @license	   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/renderer.php');
require_once($CFG->dirroot . '/course/format/grid/lib.php');

class format_grid_renderer extends format_section_renderer_base {

	private $topic0_at_top; // Boolean to state if section zero is at the top (true) or in the grid (false).
	private $courseformat; // Our course format object as defined in lib.php.
	private $shadeboxshownarray = array(); // Value of 1 = not shown, value of 2 = shown - to reduce ambiguity in JS.

	/**
	 * Generate the starting container html for a list of sections
	 * @return string HTML to output.
	 */
	protected function start_section_list() {
		return html_writer::start_tag('ul', array('class' => 'gtopics', 'id' => 'gtopics'));
	}

	/**
	 * Generate the closing container html for a list of sections
	 * @return string HTML to output.
	 */
	protected function end_section_list() {
		return html_writer::end_tag('ul');
	}

	/**
	 * Generate the title for this section page
	 * @return string the page title
	 */
	protected function page_title() {
		return get_string('sectionname', 'format_grid');
	}

	/**
	 * Output the html for a multiple section page
	 *
	 * @param stdClass $course The course entry from DB
	 * @param array $sections The course_sections entries from the DB
	 * @param array $mods
	 * @param array $modnames
	 * @param array $modnamesused
	 */
	public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
		global $PAGE;

		$this->courseformat = course_get_format($course);

		$summary_status = $this->courseformat->get_summary_visibility($course->id);
		$context = context_course::instance($course->id);
		$editing = $PAGE->user_is_editing();
		$has_cap_update = has_capability('moodle/course:update', $context);
		$has_cap_vishidsect = has_capability('moodle/course:viewhiddensections', $context);

		if ($editing) {
			$str_edit_summary = get_string('editsummary');
			$url_pic_edit = $this->output->pix_url('t/edit');
		} else {
			$url_pic_edit = false;
			$str_edit_summary = '';
		}

		echo html_writer::start_tag('div', array('id' => 'gridmiddle-column'));
		echo $this->output->skip_link_target();

		$modinfo = get_fast_modinfo($course);
		$sections = $modinfo->get_section_info_all();

		// Start at 1 to skip the summary block or include the summary block if it's in the grid display.
		$this->topic0_at_top = $summary_status->showsummary == 1;
		if ($this->topic0_at_top) {
			$this->topic0_at_top = $this->make_block_topic0($course, $sections, $modinfo, $editing, $has_cap_update,
					$url_pic_edit, $str_edit_summary, false);
			// For the purpose of the grid shade box shown array topic 0 is not shown.
			$this->shadeboxshownarray[0] = 1;
		}

		echo html_writer::start_tag('div', array('id' => 'gridiconcontainer'));
		echo html_writer::start_tag('ul', array('class' => 'gridicons gridformatbuttons buttons'));
		// Print all of the icons.
		$this->make_block_icon_topics($context, $modinfo, $course, $editing, $has_cap_update, $has_cap_vishidsect,
				$url_pic_edit);
		echo html_writer::end_tag('ul');
		echo html_writer::end_tag('div');


		echo html_writer::start_tag('div', array('id' => 'gridshadebox'));
		echo html_writer::tag('div', '', array('id' => 'gridshadebox_overlay', 'style' => 'display:none;'));
		echo html_writer::start_tag('div', array('id' => 'gridshadebox_content', 'class' => 'hide_content'));
		echo html_writer::start_tag('div', array('id' => 'gridshadebox_content_inner'));

		echo html_writer::tag('img', '', array('id' => 'gridshadebox_close', 'style' => 'display:none;',
			'src' => $this->output->pix_url('close', 'format_grid')));
		echo html_writer::tag('img', '', array('id' => 'gridshadebox_left', 'class' => 'gridshadebox_arrow', 'style' => 'display:none;',
			'src' => $this->output->pix_url('arrow_l', 'format_grid')));
		echo html_writer::tag('img', '', array('id' => 'gridshadebox_right', 'class' => 'gridshadebox_arrow', 'style' => 'display:none;',
			'src' => $this->output->pix_url('arrow_r', 'format_grid')));
		echo $this->start_section_list();
		// If currently moving a file then show the current clipboard.
		$this->make_block_show_clipboard_if_file_moving($course);

		// Print Section 0 with general activities.
		if (!$this->topic0_at_top) {
			$this->make_block_topic0($course, $sections, $modinfo, $editing, $has_cap_update, $url_pic_edit, $str_edit_summary,
					false);
		}

		// Now all the normal modules by topic.
		// Everything below uses "section" terminology - each "section" is a topic/module.
		$this->make_block_topics($course, $sections, $modinfo, $editing, $has_cap_update, $has_cap_vishidsect,
				$str_edit_summary, $url_pic_edit, false);

		echo html_writer::end_tag('div'); //gridshadebox_content_inner
		echo html_writer::end_tag('div'); //gridshadebox_content
		echo html_writer::end_tag('div'); //gridshadeboxs
		echo html_writer::tag('div', '&nbsp;', array('class' => 'clearer'));
		echo html_writer::end_tag('div');

		// Initialise the shade box functionality:...
		$PAGE->requires->js_init_call('M.format_grid.init', array(
			$PAGE->user_is_editing(),
			has_capability('moodle/course:update', $context),
			$course->numsections,
			json_encode($this->shadeboxshownarray)));
		// Initialise the key control functionality...
		//$PAGE->requires->js('/course/format/grid/javascript/gridkeys.js');
		$PAGE->requires->yui_module('moodle-format_grid-gridkeys', 'M.format_grid.gridkeys.init', null, null, true);
	}

	/**
	 * Generate the edit controls of a section
	 *
	 * @param stdClass $course The course entry from DB
	 * @param stdClass $section The course_section entry from DB
	 * @param bool $onsectionpage true if being printed on a section page
	 * @return array of links with edit controls
	 */
	protected function section_edit_controls($course, $section, $onsectionpage = false) {
		global $PAGE;

		if (!$PAGE->user_is_editing()) {
			return array();
		}

		$coursecontext = context_course::instance($course->id);

		if ($onsectionpage) {
			$url = course_get_url($course, $section->section);
		} else {
			$url = course_get_url($course);
		}
		$url->param('sesskey', sesskey());

		$controls = array();
		if (has_capability('moodle/course:setcurrentsection', $coursecontext)) {
			if ($course->marker == $section->section) {	 // Show the "light globe" on/off.
				$url->param('marker', 0);
				$controls[] = html_writer::link($url,
									html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marked'),
										'class' => 'icon ', 'alt' => get_string('markedthistopic'))),
									array('title' => get_string('markedthistopic'), 'class' => 'editing_highlight'));
			} else {
				$url->param('marker', $section->section);
				$controls[] = html_writer::link($url,
								html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marker'),
									'class' => 'icon', 'alt' => get_string('markthistopic'))),
								array('title' => get_string('markthistopic'), 'class' => 'editing_highlight'));
			}
		}

		return array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));
	}

	// Grid format specific code.
	/**
	 * Makes section zero.
	 */
	private function make_block_topic0($course, $sections, $modinfo, $editing, $has_cap_update, $url_pic_edit,
			$str_edit_summary, $onsectionpage) {
		$section = 0;
		if (!array_key_exists($section, $sections)) {
			return false;
		}

		$thissection = $modinfo->get_section_info($section);
		if (!is_object($thissection)) {
			return false;
		}

		if ($this->topic0_at_top) {
			echo html_writer::start_tag('ul', array('class' => 'gtopics-0'));
		}
		echo html_writer::start_tag('li', array(
			'id' => 'section-0',
			'class' => 'section main' . ($this->topic0_at_top ? '' : ' grid_section')));

		echo html_writer::tag('div', '&nbsp;', array('class' => 'right side'));

		echo html_writer::start_tag('div', array('class' => 'content'));

		if (!$onsectionpage) {
			echo $this->output->heading(get_section_name($course, $thissection), 3, 'sectionname');
		}

		echo html_writer::start_tag('div', array('class' => 'summary'));

		echo $this->format_summary_text($thissection);

		if ($editing && $has_cap_update) {
			$link = html_writer::link(
							new moodle_url('editsection.php', array('id' => $thissection->id)),
								html_writer::empty_tag('img', array(
								'src' => $url_pic_edit,
								'alt' => $str_edit_summary,
								'class' => 'iconsmall edit')), array('title' => $str_edit_summary));
			echo $this->topic0_at_top ? html_writer::tag('p', $link) : $link;
		}
		echo html_writer::end_tag('div');

		echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);

		if ($editing) {
			echo $this->courserenderer->course_section_add_cm_control($course, $thissection->section);

			if ($this->topic0_at_top) {
				$str_hide_summary = get_string('hide_summary', 'format_grid');
				$str_hide_summary_alt = get_string('hide_summary_alt', 'format_grid');

				echo html_writer::link(
						$this->courseformat->grid_moodle_url('mod_summary.php', array(
							'sesskey' => sesskey(),
							'course' => $course->id,
							'showsummary' => 0)), html_writer::empty_tag('img', array(
							'src' => $this->output->pix_url('into_grid', 'format_grid'),
							'alt' => $str_hide_summary_alt)) . '&nbsp;' . $str_hide_summary,
								array('title' => $str_hide_summary_alt));
			}
		}
		echo html_writer::end_tag('div');
		echo html_writer::end_tag('li');

		if ($this->topic0_at_top) {
			echo html_writer::end_tag('ul');
		}
		return true;
	}

	static function make_button_gradient_style($color)
	{
		$hex = ltrim($color, '#');

		//Convert hex to rgb
		list($r, $g, $b) = sscanf($hex, "%02x%02x%02x");

		$r2 = $r  - 80;
		$g2 = $g  - 80;
		$b2 = $b  - 80;

		return "
		background: rgb($r,$g,$b);
		background: -moz-linear-gradient(top,  rgba($r,$g,$b,1) 0%, rgba($r2,$g2,$b2,1) 100%);
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba($r,$g,$b,1)), color-stop(100%,rgba($r2,$g2,$b2,1)));
		background: -webkit-linear-gradient(top,  rgba($r,$g,$b,1) 0%,rgba($r2,$g2,$b2,1) 100%);
		background: -o-linear-gradient(top,  rgba($r,$g,$b,1) 0%,rgba($r2,$g2,$b2,1) 100%);
		background: -ms-linear-gradient(top,  rgba($r,$g,$b,1) 0%,rgba($r2,$g2,$b2,1) 100%);
		background: linear-gradient(to bottom,  rgba($r,$g,$b,1) 0%,rgba($r2,$g2,$b2,1) 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#$hex', endColorstr='#$hex',GradientType=0 );";
	}

	/**
	 * Makes the grid icons.
	 */
	private function make_block_icon_topics($context, $modinfo, $course, $editing, $has_cap_update, $has_cap_vishidsect,
			$url_pic_edit) {
		global $USER, $CFG;

		$currentlanguage = current_language();
		if ( !file_exists("$CFG->dirroot/course/format/grid/pix/new_activity_".$currentlanguage.".png") ) {
		  $currentlanguage = 'en';
		}
		$url_pic_new_activity = $this->output->pix_url('new_activity_'.$currentlanguage, 'format_grid');

		if ($editing) {
			$str_edit_image = get_string('editimage', 'format_grid');
			$str_edit_image_alt = get_string('editimage_alt', 'format_grid');

			global $OUTPUT;
			$OUTPUT->enable_color_picker();
		}

		// Get all the section information about which items should be marked with the NEW picture.
		$section_updated = $this->new_activity($course);

		if ($editing) {
			//Javascript for color editing
			?>
			<script>
				$(function(){
					$('ul.gridformatbuttons .colorpicker').each(function(){
						var color = $(this).attr('data-color');
						$(this).css('color','#'+color);

						$(this).bind('changecolor',function(e, color)
						{
							$(this).attr('data-color', color);
							$(this).css('color','#'+color);

							var li = $(this).closest('li');
							var sectionID = $(li).attr('data-sectionid');

							//Save in the DB...
							$.post('/course/format/grid/ajax/setcolor.php', {courseid:courseID, sectionid:sectionID, color:color.toHex()}, function(res){

								var id = $(li).find('a.btn').attr('id');

								//Update the style
								var style = '#' + id + ' { ' +res.style + ' }';
								$(li).find('style').html(style);

							});

						});
					});
				});
			</script>
			<?
		}

		// Start at 1 to skip the summary block or include the summary block if it's in the grid display.
		for ($section = $this->topic0_at_top ? 1 : 0; $section <= $course->numsections; $section++) {
			$thissection = $modinfo->get_section_info($section);

			// Check if section is visible to user.
			$showsection = $has_cap_vishidsect || ($thissection->visible && ($thissection->available ||
						   $thissection->showavailability || !$course->hiddensections));

			if ($showsection) {
				// We now know the value for the grid shade box shown array.
				$this->shadeboxshownarray[$section] = 2;

				$section_name = $this->courseformat->get_section_name($thissection);

				$sectionicon = $this->courseformat->grid_get_icon($course->id, $thissection->id);

				$btnColor = $sectionicon->color;
				if (!$btnColor) {
					//Default button color
					$btnColor = '888888';
				}

				echo html_writer::start_tag('li', array('data-sectionid' => $thissection->id));

					if (is_object($sectionicon) && !empty($sectionicon->imagepath)) {
						$hasIcon = true;
						$imageURL = moodle_url::make_pluginfile_url($context->id, 'course', 'section', $thissection->id, '/', $sectionicon->imagepath);
						 $imageURL = $imageURL->out();
					} else {
						$hasIcon = false;
					}

					$btnClasses = 'btn';
					if ($course->coursedisplay != COURSE_DISPLAY_MULTIPAGE) {
						$btnClasses .= ' gridicon_link';
					}
					if ($this->courseformat->is_section_current($section)) {
						$btnClasses .= ' selected';
					}
					$btnClasses .= $hasIcon ? ' hasIcon' : ' noIcon';

					$btnID = 'gridsection-' . $thissection->section;

					 echo html_writer::start_tag('a', array(
						 'href' => '/course/view.php?id=' . $course->id . '&sectionid=' . $thissection->id,
						 'id' => $btnID,
						 'class' => $btnClasses,
						 'style' => $hasIcon ? "background-image:url({$imageURL});" : '',
						 'data-sectionid' => $thissection->id
					));

						//If section has been updated since the last visit, show the red star
						 /*if ( isset($section_updated[$thissection->id])) {
						 	$section_name = '<i class="icon-star icon-red"></i> '.$section_name;
						 }*/

						echo html_writer::tag('span',$section_name);

					 echo html_writer::end_tag('a');

					 if ($editing && $has_cap_update) {

					 	echo html_writer::start_tag('div', array('class' => 'editbuttons'));

					 	//Change image link
						$edit_image_url = $this->courseformat->grid_moodle_url('editimage.php', array( 'sectionid' => $thissection->id, 'contextid' => $context->id, 'userid' => $USER->id));
						echo html_writer::link($edit_image_url, '<i class="icon-picture"></i> Change image');

						echo ' &nbsp;<b>or</b>&nbsp; ';

						$edit_color_url = $this->courseformat->grid_moodle_url('editcolor.php', array( 'sectionid' => $thissection->id, 'contextid' => $context->id, 'userid' => $USER->id));

						echo html_writer::link($edit_color_url, '<i class="icon-pencil"></i> Change color', array('class' => 'colorpicker', 'data-color' => $btnColor));

						 if ($section == 0) {
							 $str_display_summary = get_string('display_summary', 'format_grid');
							 $str_display_summary_alt = get_string('display_summary_alt', 'format_grid');

							 echo html_writer::empty_tag('br') . html_writer::link(
									 $this->courseformat->grid_moodle_url('mod_summary.php', array(
										 'sesskey' => sesskey(),
										 'course' => $course->id,
										 'showsummary' => 1)), html_writer::empty_tag('img', array(
										 'src' => $this->output->pix_url('out_of_grid', 'format_grid'),
										 'alt' => $str_display_summary_alt)) . '&nbsp;' . $str_display_summary,
											 array('title' => $str_display_summary_alt));

						 }

						 echo html_writer::end_tag('div');
					 }

					 echo '<style type="text/css"> #'.$btnID.' { '.$this->make_button_gradient_style($btnColor).' } </style>';

				echo html_writer::end_tag('li');

			} else {
				// We now know the value for the grid shade box shown array.
				$this->shadeboxshownarray[$section] = 1;
			}
		}
	}

	/**
	 * If currently moving a file then show the current clipboard.
	 */
	private function make_block_show_clipboard_if_file_moving($course) {
		global $USER;

		if (is_object($course) && ismoving($course->id)) {
			$str_cancel = get_string('cancel');

			$stractivityclipboard = clean_param(format_string(
							get_string('activityclipboard', '', $USER->activitycopyname)), PARAM_NOTAGS);
			$stractivityclipboard .= '&nbsp;&nbsp;('
					. html_writer::link(new moodle_url('/mod.php', array(
								'cancelcopy' => 'true',
								'sesskey' => sesskey())), $str_cancel);

			echo html_writer::tag('li', $stractivityclipboard, array('class' => 'clipboard'));
		}
	}

	/**
	 * Makes the list of sections to show.
	 */
	private function make_block_topics($course, $sections, $modinfo, $editing, $has_cap_update, $has_cap_vishidsect,
			$str_edit_summary, $url_pic_edit, $onsectionpage) {
		$context = context_course::instance($course->id);
		unset($sections[0]);
		for ($section = 1; $section <= $course->numsections; $section++) {
			$thissection = $modinfo->get_section_info($section);

			if (!$has_cap_vishidsect && !$thissection->visible && $course->hiddensections) {
				unset($sections[$section]);
				continue;
			}

			$sectionstyle = 'section main';
			if (!$thissection->visible) {
				$sectionstyle .= ' hidden';
			}
			if ($this->courseformat->is_section_current($section)) {
				$sectionstyle .= ' current';
			}
			$sectionstyle .= ' grid_section hide_section';

			echo html_writer::start_tag('li', array(
				'id' => 'section-' . $section,
				'class' => $sectionstyle));

			if ($editing && $has_cap_update) {
				// Note, 'left side' is BEFORE content.
				$leftcontent = $this->section_left_content($thissection, $course, $onsectionpage);
				echo html_writer::tag('div', $leftcontent, array('class' => 'left side'));
				// Note, 'right side' is BEFORE content.
				$rightcontent = $this->section_right_content($thissection, $course, $onsectionpage);
				echo html_writer::tag('div', $rightcontent, array('class' => 'right side'));
			}

			echo html_writer::start_tag('div', array('class' => 'content'));
			if ($has_cap_vishidsect || ($thissection->visible && $thissection->available)) {
				// If visible.
				echo $this->output->heading(get_section_name($course, $thissection), 3, 'sectionname');

				echo html_writer::start_tag('div', array('class' => 'summary'));

				echo $this->format_summary_text($thissection);

				if ($editing && $has_cap_update) {
					echo html_writer::link(
							new moodle_url('editsection.php', array('id' => $thissection->id)),
								html_writer::empty_tag('img', array('src' => $url_pic_edit, 'alt' => $str_edit_summary,
								'class' => 'iconsmall edit')), array('title' => $str_edit_summary));
				}
				echo html_writer::end_tag('div');

				echo $this->section_availability_message($thissection, has_capability('moodle/course:viewhiddensections',
						$context));

				echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
				echo $this->courserenderer->course_section_add_cm_control($course, $thissection->section, 0);

			} else {
				echo html_writer::tag('h2', $this->get_title($thissection));
				echo html_writer::tag('p', get_string('hidden_topic', 'format_grid'));

				echo $this->section_availability_message($thissection, has_capability('moodle/course:viewhiddensections',
						$context));
			}

			echo html_writer::end_tag('div');
			echo html_writer::end_tag('li');

			unset($sections[$section]);
		}

		if ($editing and $has_cap_update) {
			// Print stealth sections if present.
			foreach ($modinfo->get_section_info_all() as $section => $thissection) {
				if ($section <= $course->numsections or empty($modinfo->sections[$section])) {
					// This is not stealth section or it is empty.
					continue;
				}
				echo $this->stealth_section_header($section);
				echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
				echo $this->stealth_section_footer();
			}

			echo $this->end_section_list();

			echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));

			// Increase number of sections.
			$straddsection = get_string('increasesections', 'moodle');
			$url = new moodle_url('/course/changenumsections.php',
							array('courseid' => $course->id,
								'increase' => true,
								'sesskey' => sesskey()));
			$icon = $this->output->pix_icon('t/switch_plus', $straddsection);
			echo html_writer::link($url, $icon . get_accesshide($straddsection), array('class' => 'increase-sections'));

			if ($course->numsections > 0) {
				// Reduce number of sections sections.
				$strremovesection = get_string('reducesections', 'moodle');
				$url = new moodle_url('/course/changenumsections.php',
								array('courseid' => $course->id,
									'increase' => false,
									'sesskey' => sesskey()));
				$icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
				echo html_writer::link($url, $icon . get_accesshide($strremovesection), array('class' => 'reduce-sections'));
			}

			echo html_writer::end_tag('div');
		} else {
			echo $this->end_section_list();
		}
	}

	/**
	 * Attempts to return a 40 character title for the section icon.
	 * If section names are set, they are used. Otherwise it scans
	 * the summary for what looks like the first line.
	 */
	private function get_title($section) {
		$title = is_object($section) && isset($section->name) &&
				is_string($section->name) ? trim($section->name) : '';

		if (!empty($title)) {
			// Apply filters and clean tags.
			$title = trim(format_string($section->name, true));
		}

		if (empty($title)) {
			$title = trim(format_text($section->summary));

			// Finds first header content. If it is not found, then try to find the first paragraph.
			foreach (array('h[1-6]', 'p') as $tag) {
				if (preg_match('#<(' . $tag . ')\b[^>]*>(?P<text>.*?)</\1>#si', $title, $m)) {
					if (!_is_empty_text($m['text'])) {
						$title = $m['text'];
						break;
					}
				}
			}
			$title = trim(clean_param($title, PARAM_NOTAGS));
		}

		if (strlen($title) > 40) {
			$title = $this->text_limit($title, 40);
		}

		return $title;
	}

	/**
	 * Cuts long texts up to certain length without breaking words.
	 */
	private function text_limit($text, $length, $replacer = '...') {
		if (strlen($text) > $length) {
			$text = wordwrap($text, $length, "\n", true);
			$pos = strpos($text, "\n");
			if ($pos === false) {
				$pos = $length;
			}
			$text = trim(substr($text, 0, $pos)) . $replacer;
		}
		return $text;
	}

	/**
	 * Checks whether there has been new activity.
	 */
	private function new_activity($course) {
		global $CFG, $USER, $DB;

		$sections_edited = array();
		if (isset($USER->lastcourseaccess[$course->id])) {
			$course->lastaccess = $USER->lastcourseaccess[$course->id];
		} else {
			$course->lastaccess = 0;
		}

		$sql = "SELECT id, section FROM {$CFG->prefix}course_modules " .
				"WHERE course = :courseid AND added > :lastaccess";

		$params = array(
			'courseid' => $course->id,
			'lastaccess' => $course->lastaccess);

		$activity = $DB->get_records_sql($sql, $params);
		foreach ($activity as $record) {
			$sections_edited[$record->section] = true;
		}

		return $sections_edited;
	}

	public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        global $PAGE;
        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();

        // Can we view the section in question?
        if (!($sectioninfo = $modinfo->get_section_info($displaysection))) {
            // This section doesn't exist
            print_error('unknowncoursesection', 'error', null, $course->fullname);
            return;
        }

        if (!$sectioninfo->uservisible) {
            if (!$course->hiddensections) {
                echo $this->start_section_list();
                echo $this->section_hidden($displaysection);
                echo $this->end_section_list();
            }
            // Can't view this section.
            return;
        }

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, $displaysection);

        //Shows section 0 at the top of every page
        /*$thissection = $modinfo->get_section_info(0);
        if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
            echo $this->start_section_list();
            echo $this->section_header($thissection, $course, true, $displaysection);
            echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
            echo $this->courserenderer->course_section_add_cm_control($course, 0, $displaysection);
            echo $this->section_footer();
            echo $this->end_section_list();
        }*/

        // Start single-section div
        echo html_writer::start_tag('div', array('class' => 'single-section'));

        // The requested section page.
        $thissection = $modinfo->get_section_info($displaysection);

        // Title with section navigation links.
        $sectionnavlinks = $this->get_nav_links($course, $modinfo->get_section_info_all(), $displaysection);
        $sectiontitle = '';
        $sectiontitle .= html_writer::start_tag('div', array('class' => 'section-navigation header headingblock'));
        //$sectiontitle .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
        //$sectiontitle .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));
        // Title attributes
        $titleattr = 'mdl-align title';
        if (!$thissection->visible) {
            $titleattr .= ' dimmed_text';
        }
        $sectiontitle .= html_writer::tag('div', get_section_name($course, $displaysection), array('class' => $titleattr));
        $sectiontitle .= html_writer::end_tag('div');
        echo $sectiontitle;

        // Now the list of sections..
        echo $this->start_section_list();

        echo $this->section_header($thissection, $course, true, $displaysection);
        // Show completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
        echo $this->courserenderer->course_section_add_cm_control($course, $displaysection, $displaysection);
        echo $this->section_footer();
        echo $this->end_section_list();

        // Display section bottom navigation.
        //$sectionbottomnav = '';
        //$sectionbottomnav .= html_writer::start_tag('div', array('class' => 'section-navigation mdl-bottom'));
        //$sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
        //$sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));
        //$sectionbottomnav .= html_writer::tag('div', $this->section_nav_selection($course, $sections, $displaysection),
        //    array('class' => 'mdl-align'));
        //$sectionbottomnav .= html_writer::end_tag('div');
        //echo $sectionbottomnav;

        // Close single-section div.
        echo html_writer::end_tag('div');
    }

}
