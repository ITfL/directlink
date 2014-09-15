<?php

/**
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('directlink_ULTIMATE_ANSWER', 42);

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

require_once("$CFG->libdir/filelib.php");

function debug($object)
{
    echo "<pre>";
    print_r($object);
    echo "</pre>";
    echo "<br/>";
}

function get_filetype_from_file_path($filepath)
{
    $file_type_part = strrchr($filepath, '.');
    $extension = substr($file_type_part, 1);
    $extension = strtolower($extension);

    return $extension;
}

function  directlink_get_coursemodule_info($coursemodule)
{
    global $DB;
    /**
     *    http://docs.moodle.org/dev/Module_visibility_and_display#get_fast_modinfo_data
     */


    # http://phpdocs.moodle.org/HEAD/core/lib/cached_cm_info.html

    $info = new cached_cm_info();
    $empty_folder = get_string('empty_folder', 'directlink');
    $javaScriptForDirectlinkTypeContent = <<<JS
		if((typeof jQuery) == "undefined") {
			var s=document.createElement('script');
			s.setAttribute('src', '../mod/directlink/js/jquery-1.7.2.min.js');
			document.getElementsByTagName('body')[0].appendChild(s);
			void(s);
		}
		
		function create_content(json) {
            console.log(json)
			instance_array = json.instance.split('_');
			if(json.type == 'content'){
				if(!json.error){
					var content_count = 0;	

					$.each(json.files, function(index, value) {
						var fileextension = value.name.split('.').reverse()[0].toLocaleLowerCase();
						 if (value.embeddable){
                            $('#'+json.instance).prepend('<div><a class="d_link" href="../mod/directlink/view.php?id=' + json.cm_id + '&folder_embed=1&token='+value.token+'"><img src="../mod/directlink/get_ressource_icon.php?extension='+fileextension+'" class="activityicon dl_ressource_image" alt="File"><span class="instancename"> '+value.name+'<span class="accesshide">File</span></span></a></div>');
						 } else {
                            $('#'+json.instance).prepend('<div><a class="d_link" href="../mod/directlink/file.php?id='+instance_array[3]+'&instance='+instance_array[1]+'&token='+value.token+'"><img src="../mod/directlink/get_ressource_icon.php?extension='+fileextension+'" class="activityicon dl_ressource_image" alt="File"><span class="instancename"> '+value.name+'<span class="accesshide">File</span></span></a></div>');
						 }
						 content_count++;
					});
					
					$.each(json.folders, function(index, value) {
						$('#'+json.instance).prepend('<div><a class="d_link" href="../mod/directlink/view.php?id='+json.cm_id+'&token='+value.token+'"><img src="../mod/directlink/get_ressource_icon.php?extension=folder" class="activityicon dl_ressource_image" alt="File"><span class="instancename"> '+value.name+'<span class="accesshide">File</span></span></a></div>');
						content_count++;
					});
					
					if(content_count == 0) {
						$('#'+json.instance).prepend('<div><span class="instancename" style="color: gray;">{$empty_folder}<span class="accesshide">File</span></span></div>');
					}
				}
			}
			$('#loading_'+json.instance).hide();
		}
		
		window.onload = function(){
			$('.directlink_show').each(function(id, instance){
				if($(instance).children().length == 0){
					var id_array = $(instance).attr('id').split(/[a-zA-Z]*_/);
					var instance = id_array[1];
					var course = id_array[3];
					
					$.getJSON('../mod/directlink/show.php',
						{
							id: course,
							instance: instance
						},
						create_content
					);	
				}
			});
			
			
		}
JS;

    $instance_data = $DB->get_record('directlink', array('id' => $coursemodule->instance, 'course' => $coursemodule->course));

    if ($instance_data) {

        $share_type = $instance_data->ffc;
        $path_to_file = decrypt($instance_data->path_to_file);


        if ($share_type == 'file') {

            $path_parts = pathinfo($path_to_file);
            //debug($instance_data->path_to_file);
            //debug($path_to_file);
            //debug(encrypt($path_to_file));


            $extension = '';

            if (isset($path_parts['extension'])) {
                $extension = $path_parts['extension'];
            }


            $extension = strtolower($extension);


            if (in_array($extension, array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'svg', 'tiff', 'tif', 'ico'))) {
                $extension = "image";
            }

            $filename = $path_parts['basename'];

            if (preg_match('/\.lpd$/', $filename)) {
                $filename = preg_replace('/(lpd)$/', 'avi', $filename);
            }

            $info->icon = file_extension_icon($filename);

            $info->content = '';
        } else if ($share_type == 'folder') {
            $path_parts = array_reverse(explode("/", $path_to_file));

            $info->icon = 'f/folder';
            $info->content = '';
        } else if ($share_type == 'content') {

            $info->icon = 'i/files';
            $info->content = "<script type=\"text/javascript\">{$javaScriptForDirectlinkTypeContent}</script><div id=\"loading_directlink_{$coursemodule->instance}_course_{$coursemodule->course}\" style=\"display: block;\"><img src=\"../mod/directlink/pix/loader.gif\"></div><div class=\"directlink_show\" id=\"directlink_{$coursemodule->instance}_course_{$coursemodule->course}\" style=\"margin-left: 20px;\"></div>";
        }

    } else {
        $info->icon = 'i/cross_red_big';
        $info->name = get_string('link_not_found', 'directlink');
    }


    unset($_SESSION['directlink_data']['initial_mountpoint']);

    return $info;
}


/**
 * Saves a new instance of the directlink into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $directlink An object from the form in mod_form.php
 * @param mod_directlink_mod_form $mform
 * @return int The id of the newly inserted directlink record
 */
function directlink_add_instance(stdClass $directlink, mod_directlink_mod_form $mform = null)
{
    global $DB;

    $directlink->timecreated = time();
    # You may have to add extra stuff in here #

    /*
     * Check for reference etc. 
     * build directlink object to split it afterwards
    */
    if ($directlink->reference != 0) {
        $fileds_to_compare = array();
        $fileds_to_compare['server'] = $directlink->server;
        $fileds_to_compare['user_share'] = $directlink->user_share;
        $fileds_to_compare['domain'] = $directlink->domain;
        $fileds_to_compare['share_user'] = $directlink->share_user;

        if ($reference_password = complete_reference_entry($directlink->reference, $fileds_to_compare)) {
            $directlink->share_user_pwd = encrypt($reference_password);
        } else {
            throw new moodle_exception(get_string('reference_error', 'directlink'), 'directlink', 'view.php?id=' . $directlink->course);
        }
    } else {
        $directlink->share_user_pwd = encrypt($directlink->share_user_pwd);
    }


    /*
     * path_to_file only has the last segment of share so we need to search and replace only that! 
    */

    $share_path_sections = explode("/", $directlink->user_share);
    $share_path_sections_reversed = array_reverse($share_path_sections);

    $directlink->path_to_file = preg_replace('/^' . $share_path_sections_reversed[0] . '/', $_SESSION['directlink_data']['mountpoint'], $directlink->path_to_file);
    $directlink->path_to_file = encrypt($directlink->path_to_file);
    unset($_SESSION['directlink_data']);

    /**
     * Split the directlink object to insert into the different tables
     */


    // the supplied entry uses a new connection thus this must be stored in the db
    if ($directlink->reference == 0) {
        $directlink_connection_entry = new stdClass();

        $directlink_connection_entry->initial_course = $directlink->course;
        $directlink_connection_entry->connection_name = $directlink->connection_name;
        $directlink_connection_entry->connection_owner = $directlink->directlink_user_id;
        $directlink_connection_entry->server = $directlink->server;
        $directlink_connection_entry->domain = $directlink->domain;
        $directlink_connection_entry->user_share = $directlink->user_share;
        $directlink_connection_entry->share_user = $directlink->share_user;
        $directlink_connection_entry->share_user_pwd = $directlink->share_user_pwd;
        $directlink_connection_entry->share_access_type = $directlink->share_access_type;

        $connection_id = $DB->insert_record('directlink_connections', $directlink_connection_entry);
        // set the reference in directlink to use the newly created connection
        $directlink->reference = $connection_id;
    }

    $directlink_entry = new stdClass();

    $directlink_entry->course = $directlink->course;
    $directlink_entry->connection_id = $directlink->reference;
    $directlink_entry->directlink_user_id = $directlink->directlink_user_id;
    $directlink_entry->name = $directlink->name;
    $directlink_entry->intro = $directlink->introeditor['text'];

    $directlink_entry->introformat = 1;
    $directlink_entry->embedding = $directlink->embedding;
    $directlink_entry->offer_download_link = $directlink->offer_download_link;
    // $directlink_entry->introformat = $directlink->introeditor['format'];
    $directlink_entry->ffc = $directlink->ffc;
    $directlink_entry->path_to_file = $directlink->path_to_file;
    $directlink_entry->timemodified = $directlink->timecreated;

    return $DB->insert_record('directlink', $directlink_entry);
}

/**
 * Updates an instance of the directlink in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $directlink An object from the form in mod_form.php
 * @param mod_directlink_mod_form $mform
 * @return boolean Success/Fail
 */
function directlink_update_instance(stdClass $directlink, mod_directlink_mod_form $mform = null)
{
    global $DB;

    $directlink->timemodified = time();
    $directlink->id = $directlink->instance;

    # You may have to add extra stuff in here #

    /*
     * Check for reference etc.
    */
    if ($directlink->reference != 0) {
        $fileds_to_compare = array();
        $fileds_to_compare['server'] = $directlink->server;
        $fileds_to_compare['user_share'] = $directlink->user_share;
        $fileds_to_compare['domain'] = $directlink->domain;
        $fileds_to_compare['share_user'] = $directlink->share_user;

        if ($reference_password = complete_reference_entry($directlink->reference, $fileds_to_compare)) {
            $directlink->share_user_pwd = encrypt($reference_password);
        } else {
            throw new moodle_exception(get_string('reference_error', 'directlink'), 'directlink', 'view.php?id=' . $directlink->course);
        }
    } else {
        $directlink->share_user_pwd = encrypt($directlink->share_user_pwd);
    }

    $share_path_sections = explode("/", $directlink->user_share);
    $share_path_sections_reversed = array_reverse($share_path_sections);

    $directlink->path_to_file = preg_replace('/^' . $share_path_sections_reversed[0] . '/', $_SESSION['directlink_data']['mountpoint'], $directlink->path_to_file);
    $directlink->path_to_file = encrypt($directlink->path_to_file);
    unset($_SESSION['directlink_data']);


    /**
     * Split the directlink object to insert into the different tables
     */


    //
    // the supplied entry uses a new connection thus this must be stored in the db

    /**
     * wie verfahren wir mit Anpassungen an der connection?
     */
    if ($directlink->reference == 0 && false) {
        $directlink_connection_entry = new stdClass();
        $directlink_connection_entry->id = $directlink->reference;
        $directlink_connection_entry->initial_course = $directlink->course;
        $directlink_connection_entry->connection_name = $directlink->connection_name;
        $directlink_connection_entry->connection_owner = $directlink->directlink_user_id;
        $directlink_connection_entry->server = $directlink->server;
        $directlink_connection_entry->domain = $directlink->domain;
        $directlink_connection_entry->user_share = $directlink->user_share;
        $directlink_connection_entry->share_user = $directlink->share_user;
        $directlink_connection_entry->share_user_pwd = $directlink->share_user_pwd;
        $directlink_connection_entry->share_access_type = $directlink->share_access_type;

        $connection_id = $DB->update_record('directlink_connections', $directlink_connection_entry);
        // set the reference in directlink to use the newly created connection
        $directlink->reference = $connection_id;
    }

    $directlink_entry = new stdClass();
    $directlink_entry->id = $directlink->id;
    $directlink_entry->course = $directlink->course;
    $directlink_entry->connection_id = $directlink->reference;
    $directlink_entry->directlink_user_id = $directlink->directlink_user_id;
    $directlink_entry->name = $directlink->name;
    $directlink_entry->intro = $directlink->introeditor['text'];
    // $directlink_entry->introformat = 1;
    $directlink_entry->introformat = $directlink->introeditor['format'];
    $directlink_entry->embedding = $directlink->embedding;
    $directlink_entry->offer_download_link = $directlink->offer_download_link;
    $directlink_entry->ffc = $directlink->ffc;
    $directlink_entry->path_to_file = $directlink->path_to_file;
    $directlink_entry->timemodified = $directlink->timemodified;

    return $DB->update_record('directlink', $directlink_entry);
}

/**
 * Removes an instance of the directlink from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function directlink_delete_instance($id)
{
    global $DB;

    if (!$directlink = $DB->get_record('directlink', array('id' => $id))) {
        return false;
    }


    $directlink_connection = $DB->get_record('directlink_connections', array('id' => $directlink->connection_id));

    $server = $directlink_connection->server;
    $user_share = $directlink_connection->user_share;
    $domain = $directlink_connection->domain;

    $DB->delete_records('directlink', array('id' => $directlink->id));


    $ret = umount_share($server, $user_share, $domain);


    return true;
}

function umount_share($server, $share, $domain)
{
    global $DB;

    $directlink_mounts = $DB->get_records_sql("
			select
				*
			from
				mdl_directlink as dl,
				mdl_directlink_connections as dlc
			where
				dl.connection_id = dlc.id AND
				dlc.server = ? AND
				dlc.user_share = ? AND
				dlc.domain = ?", array($server, $share, $domain));

    $num_of_entries = count($directlink_mounts);

    $mountpoint = construct_mountpoint($server, $domain, $share);

    /**
     * If there is no such share in the db we can safely umount it
     * else we just leave it
     */
    if (!umount($mountpoint)) {
        return array("valid" => false, "msg" => 'Problem');
    }
    return array("valid" => true, "msg" => "umount from {$mountpoint} successful");
}

function construct_mountpoint($server, $domain, $share)
{
    global $DB;

    $directlink_config = $DB->get_record('config', array('name' => 'directlink_mount_point'));
    $directlink_mount_point = $directlink_config->value;

    $directlink_config = $DB->get_record('config', array('name' => 'directlink_domain'));
    $directlink_domain = $directlink_config->value;

    if (preg_match('/[a-zA-Z0-9]$/', $directlink_mount_point)) {
        $directlink_mount_point = $directlink_mount_point . "/";
    }

    $server = str_replace("\\", "", $server);
    $server = str_replace("/", "", $server);

    $mountpoint = $directlink_mount_point;

    if ($directlink_domain != $domain) {
        $mountpoint = $mountpoint . $server . "/";
    }

    $mountpoint = $mountpoint . $share;
    return $mountpoint;
}

function umount($mountpoint)
{
    $umount_string = "sudo umount -l {$mountpoint} 2>&1";
    $umounts = shell_exec($umount_string);

    if (preg_match('/(error|not)/', $umounts)) {
        return false;
    }
    return true;
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function directlink_cron()
{
    return true;
}

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function directlink_supports($feature)
{
    switch ($feature) {
        case FEATURE_IDNUMBER:
            return false;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_GROUPMEMBERSONLY:
            return true;
        case FEATURE_MOD_INTRO:
            return false;
        #case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:
            return true;
//  		case FEATURE_NO_VIEW_LINK:            return true;

        default:
            return null;
    }
}

function encrypt($text, $weak = false)
{
    include('config.php');
    $key = $directlink_config['password'];
    $cipher = MCRYPT_RIJNDAEL_128;
    if (!$weak) {
        $salt = $directlink_config['salt'];
        $text = $text . $salt;
        $cipher = MCRYPT_RIJNDAEL_256;
    }


    $iv_size = mcrypt_get_iv_size($cipher, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    @$crypttext = base64_encode(mcrypt_encrypt($cipher, $key, $text, MCRYPT_MODE_ECB, $iv));
    return $crypttext;
}

function decrypt($crypt, $weak = false)
{
    include('config.php');

    $key = $directlink_config['password'];
    $cipher = MCRYPT_RIJNDAEL_128;
    if (!$weak) {
        $salt = $directlink_config['salt'];
        $cipher = MCRYPT_RIJNDAEL_256;
    }

    $iv_size = mcrypt_get_iv_size($cipher, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    @$planetext = mcrypt_decrypt($cipher, $key, base64_decode($crypt), MCRYPT_MODE_ECB);

    return !$weak ? trim(strstr($planetext, $salt, true)) : trim($planetext);
}
