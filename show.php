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

$id = required_param('id', PARAM_INT); // course
$instance = required_param('instance', PARAM_INT); // course

if (!$course = $DB->get_record('course', array('id' => $id))) {
    error('Course ID is incorrect');
}

require_course_login($course);


$instance_data = $DB->get_records_sql("
		SELECT
			*
		FROM
			{directlink} as dl,
			{directlink_connections} as dlc
		WHERE
			dl.connection_id = dlc.id AND
			dl.course = ? AND
			dl.id = ?", array($id, $instance));


// sort is called here to set  the key to zero
sort($instance_data);

$instance_data = $instance_data[0];
$instance_data->id = $instance;
$instance_data->share_user_pwd = decrypt($instance_data->share_user_pwd);

$embedding = $instance_data->embedding;

$server = $instance_data->server;
$share = $instance_data->user_share;
$domain = $instance_data->domain;
$share_user = $instance_data->share_user;
$share_user_pwd = $instance_data->share_user_pwd;
$check_results = mount_share_to_fs($server, $share, $domain, $share_user, $share_user_pwd);

$share_type = $instance_data->ffc;

/**
 * Set it to zero to use the connection cretentials of
 * the entry. Prevents errors if the referenced entry
 * is deleted
 */
$reference = $instance_data->connection_id;

$path_to_file = decrypt($instance_data->path_to_file);

$check_results = (object)$check_results;


header('Content-Type: application/json');

if ($check_results->valid) {
    if ($share_type == 'file') {
        $filename = pathinfo($path_to_file);
        $file_error = file_exists($path_to_file) ? false : true;
        echo '{"type": "file", "name": "' . $filename['basename'] . '", "token": "' . urlencode(encrypt($path_to_file, true)) . '", "instance": "directlink_' . $instance . '_course_' . $id . '", "error": "' . $file_error . '"}';
    } else if ($share_type == 'folder') {
        $foldername = array_reverse(explode("/", $path_to_file));
        $foldername = $foldername[1];
        $folder_error = file_exists($path_to_file) ? false : true;
        echo '{"type": "folder", "name": "' . $foldername . '", "instance": "directlink_' . $instance . '_course_' . $id . '", "error": "' . $folder_error . '"}';
    } else if ($share_type == 'content') {
        $ignore = get_ignore_list();

        // Muss aus der default db kommen
        $path = substr($path_to_file, 0, -1);

        $foldername = array_reverse(explode("/", $path));
        $foldername = $foldername[0];

        $dir_tree = array();
        get_directory($path, $dir_tree, $ignore);

        $files = array();
        foreach ($dir_tree[$foldername]['file'] as $index => $value) {
            $abs_path = $path_to_file . $index;
            $file_extension = array_reverse(explode(".", $index));
            $file_extension = $file_extension[0];
            if (in_array($file_extension, $DIRECTLINK_SUPPORTED_FORMATS)) {
                $files[] = array('embeddable' => 1, 'name' => $index, 'token' => urlencode(encrypt($abs_path, true)));
            } else {
                $files[] = array('embeddable' => 0, 'name' => $index, 'token' => urlencode(encrypt($abs_path, true)));
            }

        }

        $folders = array();
        foreach ($dir_tree[$foldername]['folder'] as $index => $value) {
            $abs_path = $path_to_file . $index . "/";
            $folders[] = array('name' => $index, 'token' => urlencode(encrypt($abs_path, true)), 'original_name' => $abs_path);
        }

        $folders = array_reverse($folders);
        $files = array_reverse($files);

        $foldername = array_reverse(explode("/", $path_to_file));
        $foldername = $foldername[1];
        $folder_error = file_exists($path_to_file) ? false : true;

        $module_id_query = $DB->get_record_sql('SELECT id FROM {modules} WHERE name LIKE "directlink"');
        $directlink_module_id = $module_id_query->id;
        $cm_query = $DB->get_records_sql('SELECT id FROM {course_modules} WHERE module = ? AND course = ? AND instance = ?', array($directlink_module_id, $id, $instance));

        // sort result such that the id is at key 0 not key = id ...
        sort($cm_query);

        $result = array();
        $result['type'] = 'content';
        $result['name'] = $foldername;
        $result['embedding'] = $embedding;
        $result['instance'] = 'directlink_' . $instance . '_course_' . $id;
        $result['cm_id'] = $cm_query[0]->id;
        $result['error'] = $folder_error;
        $result['files'] = $files;
        $result['folders'] = $folders;

        echo json_encode($result);
    }
} else {
    echo json_encode($check_results);
}