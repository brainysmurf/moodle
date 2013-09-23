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
 * htmltable module admin settings and defaults
 *
 * @package    mod
 * @subpackage htmltable
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_OPEN, RESOURCELIB_DISPLAY_EMBED));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_EMBED);

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configcheckbox('htmltable/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 1));
    $settings->add(new admin_setting_configmultiselect('htmltable/displayoptions',
        get_string('displayoptions', 'htmltable'), get_string('configdisplayoptions', 'htmltable'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('htmltablemodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('htmltable/printheading',
        get_string('printheading', 'htmltable'), get_string('printheadingexplain', 'htmltable'), 1));
    $settings->add(new admin_setting_configcheckbox('htmltable/printintro',
        get_string('printintro', 'htmltable'), get_string('printintroexplain', 'htmltable'), 0));
    $settings->add(new admin_setting_configselect('htmltable/display',
        get_string('displayselect', 'htmltable'), get_string('displayselectexplain', 'htmltable'), RESOURCELIB_DISPLAY_OPEN, $displayoptions));
    $settings->add(new admin_setting_configtext('htmltable/popupwidth',
        get_string('popupwidth', 'htmltable'), get_string('popupwidthexplain', 'htmltable'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('htmltable/popupheight',
        get_string('popupheight', 'htmltable'), get_string('popupheightexplain', 'htmltable'), 450, PARAM_INT, 7));
}
