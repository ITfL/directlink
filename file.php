<?php
/**
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/lib/filelib.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once 'locallib.php';



$id = required_param('id', PARAM_INT); // course
$instance = required_param('instance', PARAM_INT); // course
$token = required_param('token', PARAM_RAW);
$forcedownload = optional_param('forcedownload', 0, PARAM_INT);



if (!$course = $DB->get_record('course', array('id' => $id))) {
    error('Course ID is incorrect');
}

require_course_login($course);

$directlink_config = $DB->get_record('config', array('name' => 'directlink_mount_point'));
$directlink_mount_point = $directlink_config->value;

$instance_dl_data = $DB->get_record('directlink', array('id' => $instance, 'course' => $id));
$path_to_file = decrypt($instance_dl_data->path_to_file);
$cm = get_coursemodule_from_instance('directlink', $instance);

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$directlink = $DB->get_record('directlink', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);


global $USER;

/*
 * DO SOME CHECKS THAT FILE IS IN COURSES DIRECTLINKS!
 */

$filename = decrypt($token, true);
$file_type = filetype($filename);


add_to_log($course->id, 'directlink', 'file', "file.php?id={$course->id}&instance={$instance}&token=", "{$filename}", $cm->id, $USER->id);

if (shared_file_exists($filename, $instance_dl_data->connection_id)) {
    $file_info = posix_getpwuid(fileowner($filename));

    $file_owner = $file_info['name'];

    /**
     * Check if
     *    - path to file is located in default mount point
     *    - file_name is located in the path of the directlink instance
     *    - www-data is owner of that file
     */
    if (strstr($filename, $directlink_mount_point) && strstr($filename, $path_to_file) && $file_owner == "www-data") {
        /*
         * FROM: http://stackoverflow.com/questions/1968106/generate-download-file-link-in-php
        */

        session_write_close();

        $stream = false;
        if ($directlink->embedding && !$forcedownload) {
            send_file($filename, $filename, 0, 0, false, true, file_type_to_mime_type($file_type));
        } else {

            header('Pragma: public'); // required
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false); // required for certain browsers
            // header( 'Content-Type: '.mime_content_type( $filename ) );
            // header( 'Content-Type: '. mimeinfo('type', $filename) );
            header('Content-Type: ' . mimeinfo('type', $filename));
            header('Content-Disposition: attachment; filename="' . basename($filename) . '";');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($filename));

            readfile($filename);
        }

        exit;
    } else {
        echo get_string('no_permission', 'directlink');

    }
} else {
    $PAGE->set_url('/mod/directlink/view.php', array('id' => $cm->id));
    $PAGE->set_title(format_string($directlink->name));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->set_context($context);

    // Output starts here
    echo $OUTPUT->header();

    // Replace the following lines with you own code
    echo $OUTPUT->heading($directlink->name);
    echo $OUTPUT->box_start('notifyproblem');

    echo get_string('file_doesnt_exist', 'directlink');
    echo $OUTPUT->box_end();

    // Finish the page
    echo $OUTPUT->footer();

    exit;
}


