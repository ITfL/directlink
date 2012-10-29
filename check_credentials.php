<?php
/**
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');


require_once('locallib.php');

$server = required_param('server', PARAM_TEXT);   
$share = required_param('user_share', PARAM_TEXT);
$domain = required_param('domain', PARAM_TEXT);
$share_user = required_param('share_user', PARAM_TEXT);
$share_user_pwd = required_param('share_user_pwd', PARAM_TEXT);   
$id = required_param('id', PARAM_INT);   // course
$reference = required_param('reference', PARAM_INT); 

if (! $course = $DB->get_record('course', array('id' => $id))) {
	error('Course ID is incorrect');
}

require_course_login($course);

header('Content-Type: application/json');

echo do_checks($server, $share, $domain, $share_user, $share_user_pwd, $reference);

