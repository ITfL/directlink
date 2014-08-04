<?php
/**
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once 'locallib.php';

// Required parameters
$id = required_param('course', PARAM_INT); // course
$cid = required_param('cid', PARAM_INT); // connection id

// Optinal parameters
$name = optional_param('name', '', PARAM_TEXT);
$server = optional_param('server', '', PARAM_TEXT);
$share = optional_param('share', '', PARAM_TEXT);
$domain = optional_param('domain', '', PARAM_TEXT);
$user = optional_param('user', '', PARAM_TEXT);
$pwd = optional_param('pwd', '', PARAM_TEXT);
$type = optional_param('type', '', PARAM_TEXT);


$user_id = $USER->id;

if (!$course = $DB->get_record('course', array('id' => $id))) {
    error('Course ID is incorrect');
}

require_course_login($course);

$result = directlink_check_credentials($server, $share, $domain, $user, $pwd);

if ($result['valid']) {
    $password = encrypt($pwd);

    $entry = new stdClass();
    $entry->id = $cid;
    $entry->connection_name = $name;
    $entry->share_user_pwd = $password;

    $DB->update_record('directlink_connections', $entry);
}


echo json_encode($result);