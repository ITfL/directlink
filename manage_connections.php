<?php
/**
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
* visualisation of connection manager
*/

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once 'locallib.php';

$id = required_param('course', PARAM_INT);   // course

if (! $course = $DB->get_record('course', array('id' => $id))) {
	error('Course ID is incorrect');
}

require_course_login($course);

global $USER, $DB;


$user_id = $USER->id;

// $user_id = 19;

$directlink_connection = $DB->get_records_sql('SELECT id, connection_name AS name, share_user AS user, share_access_type AS type, server, initial_course AS course FROM {directlink_connections} WHERE connection_owner = ?', array($user_id));
sort($directlink_connection); // sort to clean array id's which are otherwise the id's of the db fields


$connections_response = array();
$connections_response['sEcho'] = "1";
$connections_response['iTotalRecords'] = "".count($directlink_connection);
$connections_response['iTotalDisplayRecords'] = "".count($directlink_connection);


$connections_response['aaData'] = array();

foreach ($directlink_connection as $key => $value) {
	$connection_is_used = false;
	$operation_style = "cursor:pointer;";
	$delete_action = "delete_connection({$value->id});";
	$edit_action = "edit_connection({$value->id})";
	// check if there exist references to this share
	if(get_reference_count($value->id) != 0) {
		$connection_is_used = true;	
		$operation_style_delete = "opacity : 0.4; filter: alpha(opacity=40);";
		$delete_action = "alert(\"". get_string('change_connection_error', 'directlink') ."\");";
		// $edit_action = "alert(\"". get_string('change_connection_error', 'directlink') ."\");";
	}else{
		// do not change css style of delete button
		$operation_style_delete = "cursor:pointer;";
	}
		
	$data = array();
	$data[] = $value->id;
	$course_reference = " ";
	$share_type = "";
	if($value->type == "private") {
		$share_type = get_string('private_share', 'directlink');
		$data[] = "<img src='../mod/directlink/pix/icons/user.png' style='padding-top: 2px;'>";
	}
	else if($value->course == $id) {
		$share_type = get_string('course_share', 'directlink');
		$data[] = "<img src='../mod/directlink/pix/icons/course_this.png' style='padding-top: 2px;'>";
		$course_reference = "<i> " . get_string('js_manage_share_type_public_this', 'directlink'). "</i>";;
	}
	else {
		$share_type = get_string('course_share', 'directlink');
		$share_type = "<a href='view.php?id={$value->course}' target='_blank'>{$share_type}</a>";
		$data[] = "<img src='../mod/directlink/pix/icons/course_other.png' style='padding-top: 2px;'>";
		$course_reference = "<i> " . get_string('js_manage_share_type_public_other', 'directlink'). "</i>";
	}
	$data[] = $value->name;
	$data[] = $value->user;
	$data[] = $share_type . $course_reference;
	$data[] = $value->server;
	$data[] = "<img src='../theme/image.php?theme={$CFG->theme}&image=t/edit&rev={$CFG->themerev}' onClick='{$edit_action}' style='{$operation_style}'><img src='../theme/image.php?theme={$CFG->theme}&image=t/delete&rev={$CFG->themerev}' onClick='{$delete_action}' style='{$operation_style_delete}'>";
	$connections_response['aaData'][] = $data;
}


header('Content-Type: application/json');
echo json_encode($connections_response);
