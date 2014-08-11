<?php
/**
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

global $USER;

$id = required_param('id', PARAM_INT); // course_module ID, or
$token = optional_param('token', '', PARAM_RAW);

$cm = get_coursemodule_from_id('directlink', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$directlink = $DB->get_record('directlink', array('id' => $cm->instance), '*', MUST_EXIST);

$DIRECTLINK_SUPPORTED_FORMATS = array("mp3", "mp4");

require_login($course, true, $cm);

$context = context_module::instance($cm->id);


$PAGE->requires->js('/mod/directlink/js/jquery-1.7.2.min.js', true);
$PAGE->requires->js('/mod/directlink/js/frontend.php', true);

$PAGE->requires->css('/mod/directlink/css/mod_form_styles.css');


function get_html_folder_statement($foldername, $folder, $path)
{

    global $cm;
    $file_section = '';
    $folder_section = '';

    $path = $path . $foldername . "/";


    foreach ($folder['file'] as $index => $value) {
        $name = $index;
        //for mouseover of complete filename
        $titlename = $index;

        $size = $value['size'];
        $changed = $value['changed'];

        $fileextension = array_reverse(explode(".", $name));

        $token = urlencode(encrypt($path . $name, true));

        // shorten long filenames and extend with "..."
        if (strlen($name) >= 64) {
            $longfilename = substr($name, 0, 64);
            $longfilename = $longfilename . "...";
            $name = $longfilename;
        }
        $file_section_tmp = <<<HTML
			<div class='file_name'>
			
			
				<div style="float: left;">
					<img src="get_ressource_icon.php?extension={$fileextension[0]}" class="activityicon dl_ressource_image" alt="File">
					<span class="file_name_text">
						<a title="Super{$titlename}" href="file.php?id={$cm->course}&instance={$cm->instance}&token={$token}">{$name}</a>
					</span>
				</div>
				<div align="right" style="float: right; width: 250px;">{$changed}</div>
				<div align="right" style="float: right; width: 75px;">{$size}</div>
				<div style="clear: both;"></div>
			
			
				
			</div>
HTML;
        $file_section = $file_section . $file_section_tmp;
    }

    foreach ($folder['folder'] as $index => $value) {
        $folder_section = $folder_section . get_html_folder_statement($index, $value, $path);
    }

    $html_code = <<<HTML
		<div class='folder_pane'>
			<div class='folder_name'>
				<div class="activityicon dl_ressource_image dl_folder dl_folder_opened" alt="Folder"></div>
				<span class="folder_name_text">{$foldername}</span>
			</div>
			<div class='content_pane' style='display: block;'>
				<div class='folders_pane'>
					{$folder_section}
				</div>
				<div class='files_pane'>
					{$file_section}
				</div>
			</div>
		</div>
HTML;

    return $html_code;
}

$ffc = $directlink->ffc;

function local_embed($url, $directlinkname, $filetype)
{
    require_once("mediaplayers.php");
    global $PAGE;

    $width = 800;
    $height = 600;
    $options = array(
        core_media::OPTION_TRUSTED => true,
        core_media::OPTION_BLOCK => true,
    );
    $name = $directlinkname;


    $placeholder = "<!--FALLBACK-->";
    $out = $placeholder;

    $moodle_url = new moodle_url($url);

    $supported = array($moodle_url);
    if ($filetype == 'mp3'){
        $player = new directlink_core_media_player_html5audio();
        $text = $player->embed($supported, $name, $width, $height, $options, 'audio/mp3');
    } elseif ($filetype == 'mp4'){
        $player = new directlink_core_media_player_html5video();
        $text = $player->embed($supported, $name, $width, $height, $options, 'video/mp4');
    }

    // always add fallback Download Link:
    $text .= '<br/>';
    $fallback_player = new directlink_core_media_player_link();
    $text .= $fallback_player->embed($supported, $name, $width, $height, $options, '');


    $out = str_replace($placeholder, $text, $out);
    // remove Fallback
    $out = str_replace($placeholder, '', $out);

    $out = html_writer::tag('div', $out, array('class' => 'resourcecontent'));

    debug($out);
}

if ($ffc == 'file') {

    //echo $directlink->embedding;

    $path = decrypt($directlink->path_to_file);
    $file_type = get_filetype_from_file_path($path);

    if ($directlink->embedding && in_array($file_type, $DIRECTLINK_SUPPORTED_FORMATS)) {

        $token = urlencode(encrypt($path, true));

        // Output starts here
        $PAGE->set_url('/mod/directlink/view.php', array('id' => $cm->id));
        $PAGE->set_title(format_string($directlink->name));
        $PAGE->set_heading(format_string($course->fullname));
        $PAGE->set_context($context);

        // Output starts here
        echo $OUTPUT->header();

        // Replace the following lines with you own code
        echo $OUTPUT->heading($directlink->name);

        $mediarenderer = $PAGE->get_renderer('core', 'media');

        $embed_url = $CFG->wwwroot . '/mod/directlink/file.php?id=' . $directlink->course . '&instance=' . $directlink->id . '&token=' . $token;

        $embedoptions = array(
            core_media::OPTION_TRUSTED => true,
            core_media::OPTION_BLOCK => true,
        );

        //try to embed files
        local_embed($embed_url, $directlink->name, $file_type);

        // Finish the page
        echo $OUTPUT->footer();
    } else {
        $path = decrypt($directlink->path_to_file);
        $token = urlencode(encrypt($path, true));
        header('Location: ' . $CFG->wwwroot . '/mod/directlink/file.php?id=' . $directlink->course . '&instance=' . $directlink->id . '&token=' . $token);
        exit;
    }

    //echo $mediarenderer->embed_alternatives(array($moodle_url));
    //$text = $player->embed(array($moodle_url), "Test", 200, 100, array());
    //echo "<br/>Player:" . $text . "<br/>";


    //header('Location: '.$CFG->wwwroot.'/mod/directlink/file.php?id='.$directlink->course.'&instance='.$directlink->id.'&token='. $token);


    // Replace the following lines with you own code
    //echo $OUTPUT->heading($directlink->name);


} else if ($ffc == 'folder' || $ffc == 'content') {
    $directlink->path_to_file = decrypt($directlink->path_to_file);

    add_to_log($course->id, 'directlink', 'view', "view.php?id={$cm->id}", "{$directlink->ffc}: {$directlink->path_to_file}", $id, $USER->id);

    if ($token != '') {
        $path_to_file = decrypt($token, true);

        $directlink->path_to_file = $path_to_file;
    }
    $PAGE->set_url('/mod/directlink/view.php', array('id' => $cm->id));
    $PAGE->set_title(format_string($directlink->name));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->set_context($context);

    $ignore = get_ignore_list();

    $path_parts = array_reverse(explode("/", $directlink->path_to_file));

    $start_path = str_replace_once($path_parts[1] . '/', '', $directlink->path_to_file);
    // Output starts here
    echo $OUTPUT->header();

    // Replace the following lines with you own code
    echo $OUTPUT->heading($directlink->name);

    $modintro = format_module_intro('directlink', $directlink, $cm->id);

    if ($modintro != '') {
        echo $OUTPUT->box_start('mod_introbox', 'pageintro');
        echo $modintro;
        echo $OUTPUT->box_end();
    }

    // comes from default db
    $path = substr($directlink->path_to_file, 0, -1);

    $dir_tree = array();

    //--- fix
    $connection_id = $directlink->connection_id;
    $connection = $DB->get_record('directlink_connections', array('id' => $connection_id));

    $share = $connection->user_share;
    $domain = $connection->domain;

    $more_general_path = $path;
    $general_connection_id = has_more_general_share($share, $domain);
    if ($general_connection_id != false) {

        $update_connection = $DB->get_record('directlink', array('connection_id' => $general_connection_id));
        $general_directlink_connection = $DB->get_record('directlink_connections', array('id' => $general_connection_id));
        $general_path = decrypt($update_connection->path_to_file);

        $more_general_path = construct_mountpoint($general_directlink_connection->server, $general_directlink_connection->domain, $general_directlink_connection->user_share);
    }

    //--- fix end

    $get_directory_ok = get_directory($more_general_path, $dir_tree, $ignore);
    // $get_directory_ok = true;

    if (!$get_directory_ok && !is_dir_empty($dir_tree)) {

        if (!share_already_mounted($directlink->path_to_file)) {
            /**
             * Remount here
             */
            $connection_id = $directlink->connection_id;
            $connection = $DB->get_record('directlink_connections', array('id' => $connection_id));

            $server = $connection->server;
            $share = $connection->user_share;
            $domain = $connection->domain;
            $share_user = $connection->share_user;
            $share_user_pwd = decrypt($connection->share_user_pwd);

            mount_share_to_fs($server, $share, $domain, $share_user, $share_user_pwd);
        }
    }
    $get_directory_ok = get_directory($path, $dir_tree, $ignore);

    if ($get_directory_ok) {
        echo $OUTPUT->box_start('generalbox');
        // Folder Structure is generated here

        $file_name = get_string('file_name', 'directlink');
        $file_size = get_string('file_size', 'directlink');
        $file_changed = get_string('file_changed', 'directlink');
        echo <<<HTML
		<div style="margin:0px; background-color: #FFFAE5; border: 1px solid #FFD300;">
			<div style="float: left; font-weight: bold;">&nbsp;&nbsp;&nbsp;{$file_name}</div>
			<div style="float: right; width: 250px; font-weight: bold;">&nbsp;&nbsp;&nbsp;{$file_changed}</div>
			<div style="float: right; width: 75px; font-weight: bold;">&nbsp;&nbsp;&nbsp;{$file_size}</div>
			<div style="clear: both;"></div>
		</div>
HTML;

        $html_code = get_html_folder_statement($path_parts[1], $dir_tree[$path_parts[1]], $start_path);
        $pattern = '/(\<span class="folder_name_text"\>)(.+)(\<\/span\>)/';
        if (!(isset($token) && $token != '')) {
            $replacement = "$1 {$directlink->name} $3";
            $html_code = preg_replace($pattern, $replacement, $html_code, 1);
        }
        echo $html_code;
        echo $OUTPUT->box_end();
    } else {
        echo $OUTPUT->box_start('notifyproblem');
        echo get_string('folder_doesnt_exist', 'directlink');
        echo $OUTPUT->box_end();
    }

    // Finish the page
    echo $OUTPUT->footer();
} else {

    $PAGE->set_url('/mod/directlink/view.php', array('id' => $cm->id));
    $PAGE->set_title(format_string($directlink->name));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->set_context($context);


    // Output starts here
    echo $OUTPUT->header();


    // Replace the following lines with you own code
    echo $OUTPUT->heading('Yay! It works!');

    // Finish the page
    echo $OUTPUT->footer();
}

