<?php
/**
 * @package    mod
* @subpackage directlink
* @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once 'locallib.php';

$id = required_param('course', PARAM_INT);   // course
$cid = required_param('cid', PARAM_INT);   // connection id
$json = optional_param('json', 0, PARAM_BOOL);


if (! $course = $DB->get_record('course', array('id' => $id))) {
	error('Course ID is incorrect');
}

require_course_login($course);

if($json) {
	$directlink_info = $DB->get_records_sql('select dlc.id, dlc.connection_name as name, dlc.server, dlc.domain, dlc.user_share as share, dlc.share_user as user, dlc.share_access_type as type from {directlink_connections} as dlc where dlc.id = ? and dlc.connection_owner = ?', array($cid, $USER->id));
	sort($directlink_info); // sort to fix arrays indices
	$directlink_info = $directlink_info[0];
	echo json_encode($directlink_info);
	exit;
}

/*
* sql statement for connection management
* ATTENTION get_records_sql
* all the get_records_XX() family of functions inside Moodle always returns one associative array, i.e. one array of records where the key is the first field in the SELECT clause.
* So, if you get two records with the same value in the 1st field the functions will "group" them, returning the last one in the real recordset.
* --> https://moodle.org/mod/forum/discuss.php?d=57544
*/
$directlink_info = $DB->get_records_sql('select dl.id, dl.name, cs.shortname, usr.firstname, usr.lastname, usr.email, dl.course from {directlink} as dl, {course} as cs, {user} as usr, {directlink_connections} as dlc where dl.connection_id = ? and cs.id = dl.course and usr.id = dl.directlink_user_id and dlc.id = dl.connection_id and dlc.connection_owner = ?', array($cid, $USER->id));

// echo "<pre>";
// foreach ($directlink_info as $record) {
// 	print_r($record);
// }
// echo "<pre>";
// print_r($directlink_info);
// echo "</pre> <br/> HULULU <br/>";
sort($directlink_info); // sort to clean array id's which are otherwise the id's of the db fields
// echo "<pre>";
// print_r($directlink_info);

$table_contents = "";
foreach ($directlink_info as $info) {
	$table_contents .= "<tr><td>{$info->name}</td><td><u><a href='view.php?id={$info->course}' target='_blank'>{$info->shortname}</a></u></td><td><u><a href='mailto:{$info->email}' target='_blank'>{$info->firstname} {$info->lastname}</a></u></td></tr>";
}
if($table_contents == "") {
	$no_references = get_string('manage_no_reference','directlink');
	$table_contents .= "<tr><td colspan='3'><i>{$no_references}</i></td></tr>";
}

$connection_info = get_string('manage_connection_info', 'directlink');
$user = get_string('user_name', 'directlink');
$course_name = get_string('manage_connection_course', 'directlink');
$directlink_name = get_string('connection_name', 'directlink');

$html_statement = <<<html
	<div>
		<b>{$connection_info}</b>
	</div>
	<div>
		<table>
			<tr>
				<th style="text-align: left;">{$directlink_name}</th>
				<th style="text-align: left;">{$course_name}</th>
				<th style="text-align: left;">{$user}</th>
			</tr>
			{$table_contents}
		</table>
	</div>
html;

echo $html_statement;