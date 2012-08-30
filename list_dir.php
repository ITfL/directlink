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

$id = required_param('id', PARAM_INT);   // course

if (! $course = $DB->get_record('course', array('id' => $id))) {
	error('Course ID is incorrect');
}

require_course_login($course);

$mountpoint = $_SESSION['directlink_data']['mountpoint'];

/*
 * Is a comma seperated string of files to ignore -> convert to array of files!
 */
$directlink_config = $DB->get_record('config', array('name' => 'directlink_filechoose_ignore'));
$directlink_filechoose_ignore = $directlink_config->value;

/*
 * preparation of parameters for get_directory call
 */

$path = $mountpoint;

$dir_tree = array ();

$ignore = preg_split("/\n*\s*,\n*\s*/", $directlink_filechoose_ignore);

/*
 * builds a tree of file structure 
 */
get_directory($path, $dir_tree, $ignore); 

$json_dir_tree = json_encode($dir_tree);

echo $json_dir_tree;
