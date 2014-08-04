<?php

/**
 *
 * File for checking if share is mounted and can be umounted
 *
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

$id = required_param('id', PARAM_INT); // course_module ID, or


if (!$course = $DB->get_record('course', array('id' => $id))) {
    error('Course ID is incorrect');
}

require_course_login($course);

if (isset($_SESSION['directlink_data']['initial_mountpoint'])) {
    $initial_mountpoint = $_SESSION['directlink_data']['initial_mountpoint'];

    return umount($initial_mountpoint);
}