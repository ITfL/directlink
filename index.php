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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Creates a view for all directlink activities in a course when called with id GET parameter


require('../../config.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

add_to_log($course->id, 'directlink', 'view all', "index.php?id=$course->id", '');

$strdirectlink = get_string('modulename', 'directlink');
$strdirectlinks = get_string('modulenameplural', 'directlink');
$strsectionname = get_string('sectionname', 'format_' . $course->format);
$strname = get_string('name');
$strintro = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/directlink/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname . ': ' . $strdirectlinks);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strdirectlinks);


echo $OUTPUT->header();

if (!$directlinks = get_all_instances_in_course('directlink', $course)) {
    notice(get_string('thereareno', 'moodle', $strdirectlinks), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_all_sections($course->id);
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $table->head = array($strsectionname, $strname, $strintro);
    $table->align = array('center', 'left', 'left');
} else {
    $table->head = array($strlastmodified, $strname, $strintro);
    $table->align = array('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($directlinks as $directlink) {
    $cm = $modinfo->cms[$directlink->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($directlink->section !== $currentsection) {
            if ($directlink->section) {
                $printsection = get_section_name($course, $sections[$directlink->section]);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $directlink->section;
        }
    } else {
        $printsection = '<span class="smallinfo">' . userdate($directlink->timemodified) . "</span>";
    }

    $extra = empty($cm->extra) ? '' : $cm->extra;
    $icon = '';
    if (!empty($cm->icon)) {
        // each directlink file has an icon in 2.0
        $icon = '<img src="' . $OUTPUT->pix_url($cm->icon) . '" class="activityicon" alt="' . get_string('modulename', $cm->modname) . '" /> ';
    }

    $class = $directlink->visible ? '' : 'class="dimmed"'; // hidden modules are dimmed
    $table->data[] = array(
        $printsection,
        "<a $class $extra href=\"view.php?id=$cm->id\">" . $icon . format_string($directlink->name) . "</a>",
        format_module_intro('directlink', $directlink, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();

