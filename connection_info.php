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


$directlink_info = $DB->get_records_sql('select dl.name, cs.id, cs.shortname, usr.firstname, usr.lastname, usr.email from {directlink} as dl, {course} as cs, {user} as usr, {directlink_connections} as dlc where dl.connection_id = ? and dl.course = ? and cs.id = dl.course and usr.id = dl.directlink_user_id and dlc.id = dl.connection_id and dlc.connection_owner = ?', array($cid, $id, $USER->id));
sort($directlink_info); // sort to clean array id's which are otherwise the id's of the db fields

$table_contents = "";
foreach ($directlink_info as $info) {
	$table_contents .= "<tr><td>{$info->name}</td><td><u><a href='view.php?id={$info->id}' target='_blank'>{$info->shortname}</a></u></td><td><u><a href='mailto:{$info->email}' target='_blank'>{$info->firstname} {$info->lastname}</a></u></td></tr>";
}
if($table_contents == "") {
	$no_references = get_string('manage_no_reference','directlink');
	$table_contents .= "<tr><td colspan='3'><i>{$no_references}</i></td></tr>";
}

$connection_info = get_string('manage_connection_info', 'directlink');
$user = get_string('share_user', 'directlink');
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