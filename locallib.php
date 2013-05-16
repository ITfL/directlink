<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * get number of directlinks referencing a connection with id 
 */
function get_reference_count($connection_id) {
	global $DB;
	$result = $DB->get_records_sql('select count(*) as reference_count from {directlink_connections} as c, {directlink} as d where c.id = ? and d.connection_id = c.id', array($connection_id));
	sort($result);
	return $result[0]->reference_count;
}

/**
 * delete a connection from the database
 */
function delete_connection($connection_id) {
	global $USER, $DB;
	if(get_reference_count($connection_id) == 0){
		$DB->delete_records('directlink_connections',array("id"=>$connection_id, "connection_owner"=>$USER->id));
		return true;
	}
	else {
		return false;
	}
}

/**
 * Get ignorelist
 */
function get_ignore_list(){
	global $DB;
	
	$directlink_config = $DB->get_record('config', array('name' => 'directlink_filechoose_ignore'));
	$directlink_filechoose_ignore = $directlink_config->value;
	
	$ignore = preg_split("/\n*\s*,\n*\s*/", $directlink_filechoose_ignore);
	
	return  $ignore;
}

/**
 * Checks if a file exists and if its share is mounted
 * 
 * @param unknown_type $filename
 * @param unknown_type $connection_id
 */
function shared_file_exists($filename, $connection_id) {
	global $DB;
	
	
	
	/*
	 * if file does not exist check if corresponding share is mounted. If not
	 * remount the share
	 */ 
	if(!file_exists($filename)) {
		//$connection_id = $instance_dl_data->connection_id;
		$connection = $DB->get_record('directlink_connections',array('id'=> $connection_id ));
	
		$server = $connection->server;
		$share = $connection->user_share;
		$domain = $connection->domain;
		$share_user = $connection->share_user;
		$share_user_pwd = decrypt($connection->share_user_pwd);
	
		mount_share_to_fs($server, $share, $domain, $share_user, $share_user_pwd);
	}
	
	// now the share should be mounted, so just return normal
	return file_exists($filename);
}

/**
 * directlink support method for adding/replacing slashes to server path
 * rebuilding windows-path to shell compatible path
 * 
 * @copyright  2012 Michael Hamatschek, Hans-Christian Sperker Uni-Bamberg
 *
 */
function add_slashes($server) {
	$rebuild_server_path = '';
	
	$rebuild_server_path = str_replace("\\", "/", $server);
	
	if(preg_match('/^[a-zA-Z0-9]/', $rebuild_server_path)) {
		$rebuild_server_path = "//" . $rebuild_server_path;
	}
	
	if(preg_match('/[a-zA-Z0-9]$/', $rebuild_server_path)) {
		$rebuild_server_path = $rebuild_server_path . "/";
	}
	
	// what is if path is containing -> "/"
	
	return $rebuild_server_path;
}

/**
 * directlink support method for checking smb authorization
 * @copyright  2012 Michael Hamatschek, Hans-Christian Sperker Uni-Bamberg
 * 
 */
function directlink_check_credentials($smbclient_server, $share, $smbclient_domain, $smbclient_user, $smbclient_pass){
	global $DB;
	
	$directlink_config = $DB->get_record('config', array('name' => 'directlink_smbclient_path'));
	$smbclient_path = $directlink_config->value;
	
	$smbclient_server = add_slashes($smbclient_server);
	
	//gets parameters for smbclients
	$path = preg_split('/\\//', $share, -1, PREG_SPLIT_NO_EMPTY);
	$user_dir = $share;
	$user_dir = str_replace($path[0], '', $user_dir);
	
	$smbclient_domain = str_replace("\\", "/", $smbclient_domain);
	if(preg_match('/[a-zA-Z0-9]$/', $smbclient_domain)) {
		$smbclient_domain = $smbclient_domain . "/";
	}
	
	#uni-bamberg_debug
	#$smbclient_pass = 1 ? $smbclient_pass : 'wrong_password'; // to check for wrong passwords
	
	
	# check that domain ends with a /

	$smbclient_pass = mask_password($smbclient_pass);
	//old functionality, can be removed while refactoring
	//$smbclient_connect_string = "{$smbclient_path} {$smbclient_server}{$share} {$smbclient_pass} -U {$smbclient_domain}{$smbclient_user} -c 'ls'";
	///$smbclient_connect_string_print = "{$smbclient_path} {$smbclient_server}{$share} <password> -U {$smbclient_domain}<user> -c 'ls'";
	
	//test for subdir connection
	$smbclient_connect_string = "{$smbclient_path} {$smbclient_server}{$path[0]} -D '{$user_dir}' -U {$smbclient_domain}{$smbclient_user}%{$smbclient_pass} -c 'ls'";
	$smbclient_connect_string_print = "{$smbclient_path} {$smbclient_server}{$path[0]} -D '{$user_dir}' -U {$smbclient_domain}{$smbclient_user}%<PASSWORD> -c 'ls'";
	
	$smbclient_connect_result = shell_exec($smbclient_connect_string);
	$debug_msg = $smbclient_connect_result;
	
	$smbclient_connect_result = str_replace("\n", '', $smbclient_connect_result);
	$smbclient_connect_result = preg_replace('/\s{2,}/', ' ', $smbclient_connect_result);
	
	$smbclient_regex_pattern = '/.*(fail|FAIL|DENIED).*/';

	$smbclient_check_credentials = preg_match($smbclient_regex_pattern, $smbclient_connect_result);
	if($smbclient_check_credentials == 0){
		return array("valid" => true, "msg" => $smbclient_connect_result, "connect_string" => $smbclient_connect_string_print);
	}else{
		// return array("valid" => false, "msg" => $smbclient_connect_result, "connect_string" => $smbclient_connect_string_print, "debug" => $debug_msg);
		
		if(preg_match('/session setup failed: NT_STATUS_LOGON_FAILURE.*/', $debug_msg)){
			return array("valid" => false, "msg" => $smbclient_connect_result, "connect_string" => $smbclient_connect_string_print, "debug" => get_string('connection_error_user', 'directlink'));
		}else if(preg_match('/tree connect failed: NT_STATUS_BAD_NETWORK_NAME.*/', $debug_msg)){
			return array("valid" => false, "msg" => $smbclient_connect_result, "connect_string" => $smbclient_connect_string_print, "debug" => get_string('connection_bad_mountpoint', 'directlink'));
		}else if(preg_match('/NT_STATUS_ACCESS_DENIED.*/', $debug_msg)){
			return array("valid" => false, "msg" => $smbclient_connect_result, "connect_string" => $smbclient_connect_string_print, "debug" => get_string('connection_bad_mountpoint', 'directlink'));
		}else{
			return array("valid" => false, "msg" => $smbclient_connect_result, "connect_string" => $smbclient_connect_string_print, "debug" => $debug_msg);
		}
	}

}
/**
 * creates new dir for mountpoint
 */
function directlink_mkdir($destination){
	
	$shell_return = shell_exec("mkdir -p {$destination}");
	
	if($shell_return != "") {
		return false;
	}
	
	return true;
}

function has_more_general_share($share, $domain) {
	global $DB;
	
	$more_general_share = '';
	$more_general_share_id = 0;
	
	$result = $DB->get_records_sql('SELECT DISTINCT id, user_share FROM {directlink_connections} WHERE domain = ?', array($domain));
	
	foreach ($result as $key => $tmp_share) {
		$tmp_share_id = $tmp_share->id;
		$tmp_share = $tmp_share->user_share;
		$tmp_share = str_replace("/", "\/", $tmp_share);
		if(preg_match('/^'.$tmp_share.'.+$/', $share)) {
			if(strlen($more_general_share) == 0 || strlen($tmp_share) < strlen($share)) {
				$more_general_share = $tmp_share;
				$more_general_share_id = $tmp_share_id;
			}
		}
	}
	return strlen($more_general_share) > 0 ? $more_general_share_id : false;
}

function get_less_general_shares($share, $domain) {
	global $DB;
	
	$result = $DB->get_records_sql('SELECT MIN(id) AS id, user_share FROM {directlink_connections} WHERE domain = ? GROUP BY user_share', array($domain));
	$share = str_replace("/", "\/", $share);
	$less_general_shares = array();
	
	foreach ($result as $key => $tmp_share) {
		$tmp_share_id = $tmp_share->id;
		$tmp_share = $tmp_share->user_share;
		if(preg_match('/^'.$share.'.+$/', $tmp_share)) {
			$less_general_shares[] = $tmp_share_id;
		}
	}
	
	return $less_general_shares;
}

function construct_mountpoint($server, $domain, $share) {
	global $DB;
	
	$directlink_config = $DB->get_record('config', array('name' => 'directlink_mount_point'));
	$directlink_mount_point = $directlink_config->value;
	
	$directlink_config = $DB->get_record('config', array('name' => 'directlink_domain'));
	$directlink_domain = $directlink_config->value;
	
	if(preg_match('/[a-zA-Z0-9]$/', $directlink_mount_point)) {
		$directlink_mount_point = $directlink_mount_point . "/";
	}
	
	$server = $server;
	$server = str_replace("\\", "", $server);
	$server = str_replace("/", "", $server);
	
	$mountpoint = $directlink_mount_point;
	
	if($directlink_domain != $domain){
		$mountpoint = $mountpoint.$server. "/";
	}
	
	$mountpoint = $mountpoint.$share;
	return $mountpoint;
}

function mount($smbclient_server, $share, $domain, $user, $pwd) {
	global $DB;
	$mountpoint = construct_mountpoint($smbclient_server, $domain, $share);
	
	$directlink_config = $DB->get_record('config', array('name' => 'directlink_domain'));
	$directlink_domain = $directlink_config->value;
	
	
	
	if(!share_already_mounted($mountpoint)) {
		if($domain == $directlink_domain) {
			$directlink_config = $DB->get_record('config', array('name' => 'directlink_default_user_name'));
			$user = $directlink_config->value;
	
			$directlink_config = $DB->get_record('config', array('name' => 'directlink_default_user_pass'));
			$pwd = $directlink_config->value;
		}
		
		$mkdir_return = directlink_mkdir($mountpoint);
	
		if(!$mkdir_return){
			return array("valid" => false, "msg" => get_string('mkdir_error', 'directlink'));
		}else{
			$smbclient_server = add_slashes($smbclient_server);
			$server_path = $smbclient_server.$share;
			$pwd = mask_password($pwd);
			$mount_result = shell_exec("sudo mount -t cifs -o uid=www-data,ro,iocharset=utf8,username={$user},password={$pwd} {$server_path} {$mountpoint} 2>&1");

			if(preg_match('/^session \s+ setup \s+ failed:/', $mount_result)) {
				return array("valid" => false, "msg" => "{$notification}");
			}

			if(preg_match('/error/', $mount_result)) {
				return array("valid" => false, "msg" => "{$mount_result}");
			}
			$notification = get_string('mount_succesful_01', 'directlink').$mountpoint.get_string('mount_succesful_02', 'directlink');
			return array("valid" => true, "msg" => "{$notification}");
		}
	
	}
	else {
		$notification = get_string('mount_succesful_01', 'directlink').$mountpoint.get_string('mount_succesful_02', 'directlink');
		return array("valid" => true, "msg" => "{$notification}");
	}
}

	function mount_share_to_fs($smbclient_server, $share, $domain, $user, $pwd){
	global $DB;
	
	$mount_msg = '';
	$more_general_share = has_more_general_share($share, $domain);
	
	$_SESSION['directlink_data']['mountpoint'] = construct_mountpoint($smbclient_server, $domain, $share);
	
	if(!$more_general_share){
		$less_general_shares = get_less_general_shares($share, $domain);
		foreach ($less_general_shares as $tmp_share_id) {
			/*
			 * Umount that!
			 */
			$result = $DB->get_records_sql('SELECT id, server, domain, user_share FROM {directlink_connections} WHERE id = ?', array($tmp_share_id));
			$result = $result[$tmp_share_id];
			$mountpoint = construct_mountpoint($result->server, $result->domain, $result->user_share);

			umount($mountpoint);
		}
		$mount_msg = mount($smbclient_server, $share, $domain, $user, $pwd);

		return $mount_msg;
	}
	
	$result = $DB->get_records_sql('SELECT id, server, domain, user_share, share_user, share_user_pwd  FROM {directlink_connections} WHERE id = ?', array($more_general_share));
	$result = $result[$more_general_share];
	
	$server = $result->server;
	$share = $result->user_share;
	$domain = $result->domain;
	$user = $result->share_user;
	$pwd = decrypt($result->share_user_pwd);
	


	$mount_msg = mount($server, $share, $domain, $user, $pwd);
	
	return $mount_msg;
}

function umount($mountpoint) {
	$umount_string = "sudo umount -l {$mountpoint} 2>&1";
	$umounts = shell_exec($umount_string);
	
	if(preg_match('/(error|not)/', $umounts)) {
		return false;
	}
	return true;
}

function umount_share($server, $share, $domain){
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
				dlc.domain = ?",  array($server, $share, $domain));
	
	$num_of_entries = count($directlink_mounts);	
	
	$mountpoint = construct_mountpoint($server, $domain, $share);
	
	/**
	 * If there is no such share in the db we can safely umount it 
	 * else we just leave it
	 */
	if(!umount($mountpoint)) {
		return array("valid" => false, "msg" => 'Problem');
	}
	return array("valid" => true, "msg" => "umount from {$mountpoint} successful");
}

function share_already_mounted($mountpoint){
	//blank after mountpoint is needed, do not remove
	$check_mount = shell_exec("mount | grep '{$mountpoint} '");

	//additional fix || englischer Begriff
	$ls = shell_exec("ls $mountpoint");
	if(preg_match('/Keine Berechtigung/', $ls) || preg_match('/Permission Denied/', $ls)){
		$umount_string = "sudo umount -l {$mountpoint} 2>&1";
		$umount_fail = shell_exec($umount_string);
		$check_mount = '';
	}
	//additional fix end

	if($check_mount == '') {
		return false;
	}
	else {
		return true;
	}
}

/**
 * function used to check if a string is in the ignore list
 * entries in ignore list may be regular expressions
 * 
 * @param string $needle - the string we want to check
 * @param string $haystack - the ignore list
 */
function in_ext_array($needle, $haystack) {
	foreach($haystack as $pattern) {
		// is it a regex?
		if(strstr($pattern, "/")) {
			/*
			 * returns int
			 * 0 - no findings
			 * else finding
			 */
			if(preg_match($pattern, $needle) != 0) {
				return true;
			}
		}
		else if ($pattern == $needle) {
			return true;
		}
	}
	return false;
}

function is_dir_empty($array) {
	foreach ($array as $key => $value) {
		$folders = count($value['folder']);
		$files = count($value['file']);
		if(($folders == 0) && ($files == 0)) {
			return true;
		}
	}
	return false;
}

/**
 * Convert bytes to human readable format
 *
 * @param integer bytes Size in bytes to convert
 * @return string
 */
function bytesToSize($bytes, $precision = 2)
{  
    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;
    $terabyte = $gigabyte * 1024;
   
    if (($bytes >= 0) && ($bytes < $kilobyte)) {
        return $bytes . ' B';
 
    } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
        return round($bytes / $kilobyte, $precision) . ' KB';
 
    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
        return round($bytes / $megabyte, $precision) . ' MB';
 
    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
        return round($bytes / $gigabyte, $precision) . ' GB';
 
    } elseif ($bytes >= $terabyte) {
        return round($bytes / $terabyte, $precision) . ' TB';
    } else {
        return $bytes . ' B';
    }
}

/**
 * 
 * @param unknown_type $path
 * @param unknown_type $dir_tree
 * @param unknown_type $ignore
 */
function get_directory($path = '.', &$dir_tree, $ignore = '') {

	$ignore[] = '.';
	$ignore[] = '..';

	if(!file_exists($path)) {
		return false;
	}
	
	$path_array = explode("/", $path);
	$root_dir = $path_array[ count($path_array) - 1 ];
	
	$dh = @opendir($path);

	$dir_tree["$root_dir"]["folder"] = array ();
	$dir_tree["$root_dir"]["file"] = array ();

	while (false !== ($file = readdir($dh))) {
		if (!in_ext_array($file, $ignore)) {
			if (is_dir("$path/$file")) {
				$dir_tree["$root_dir"]["folder"]["$file"] = array();
				get_directory("$path/$file", $dir_tree["$root_dir"]["folder"], $ignore);

			}
			else {
				$file_size = bytesToSize(filesize("$path/$file"));
				$file_modified = userdate(filemtime("$path/$file"));
				
				$tmp_file  = array("size" => $file_size, "changed" => $file_modified);
				$dir_tree["$root_dir"]["file"]["$file"] = $tmp_file;
			}
		}
	}
	uksort($dir_tree["$root_dir"]["folder"], 'strnatcasecmp');
	uksort($dir_tree["$root_dir"]["file"], 'strnatcasecmp');
	//natcasesort($dir_tree["$root_dir"]["file"]);
	closedir($dh);
	
	return true;
}

function remove_dublicated_objects(&$array_to_clean, $array_supply, $fields_to_check) {
	$clean_array = array();
	
	foreach ($array_to_clean as $entry_to_decide_on) {
		
		$is_unique = true;
		
		foreach ($array_supply as $master) {
			
			$all_fileds_equal = true;
			foreach ($fields_to_check as $field) {
				
				if($master->$field != $entry_to_decide_on->$field) {
					$all_fileds_equal = false;
					break;
				}
			}
			if($all_fileds_equal) {
				$is_unique = false;
			}
		}
		if($is_unique) {
			$clean_array[] = $entry_to_decide_on;
		}
	}
	
	$array_to_clean = $clean_array;
}

function get_connections($course, $user) {
	global $DB;
	
	
	
	//Get private shares and own public shares from different courses
		
	$connections_own_private_raw = $DB->get_records_sql("
		SELECT DISTINCT
			dlc.id,
			dlc.connection_name,
			dlc.server,
			dlc.domain,
			dlc.share_access_type,
			dlc.share_user,
			dlc.user_share
		FROM
			{directlink_connections} as dlc
		WHERE
			dlc.share_access_type = 'private' AND
			dlc.connection_owner = ?",  array($user));
	
	
	$connections_own_dif_course_raw = $DB->get_records_sql("
			SELECT DISTINCT
			dlc.id,
			dlc.connection_name,
			dlc.server,
			dlc.domain,
			dlc.share_access_type,
			dlc.share_user,
			dlc.user_share
		FROM
			{directlink_connections} as dlc
		WHERE
			dlc.share_access_type = 'course' AND
			dlc.initial_course != ? AND
			dlc.connection_owner = ?",  array($course, $user));
	
	$connections_own_this_course_raw = $DB->get_records_sql("
		SELECT DISTINCT
			dlc.id,
			dlc.connection_name,
			dlc.server,
			dlc.domain,
			dlc.share_access_type,
			dlc.share_user,
			dlc.user_share
		FROM
			{directlink_connections} as dlc
		WHERE
			dlc.share_access_type = 'course' AND
			dlc.initial_course = ? AND
			dlc.connection_owner = ?",  array($course, $user));
	
	remove_dublicated_objects($connections_own_dif_course_raw, $connections_own_this_course_raw, array('connection_name', 'server', 'domain', 'share_user', 'user_share'));
	
	
	$connections_someones_this_course_raw = $DB->get_records_sql("
		SELECT DISTINCT
			dlc.id,
			dlc.connection_name,
			dlc.server,
			dlc.domain,
			dlc.share_access_type,
			dlc.share_user,
			dlc.user_share
		FROM
			{directlink_connections} as dlc
		WHERE
			dlc.share_access_type = 'course' AND
			dlc.initial_course = ? AND
			dlc.connection_owner != ?",  array($course, $user));
	
	$connections = array();
	

	/*
	 * we need some more checks... is s.th. from the user_id or from the course?
	 * What's with course shares from other courses created by the user_id?
	 * 
	 */
	
	foreach ($connections_own_private_raw as $connection) {
		$entry = new stdClass;
		$entry->id = $connection->id;
		$entry->connection_name = $connection->connection_name;
		$entry->server = $connection->server;
		$entry->domain = $connection->domain;
		$entry->user_share = $connection->user_share;
		$entry->share_user = $connection->share_user;
		$entry->share_access_type = $connection->share_access_type;
		$entry->is_own = true;
		$entry->icon = 'user.png';
		
		$connections[$connection->id] = $entry;
	}
	
	foreach ($connections_own_dif_course_raw as $connection) {
		$entry = new stdClass;
		$entry->id = $connection->id;
		$entry->connection_name = $connection->connection_name;
		$entry->server = $connection->server;
		$entry->domain = $connection->domain;
		$entry->user_share = $connection->user_share;
		$entry->share_user = $connection->share_user;
		$entry->share_access_type = $connection->share_access_type;
		$entry->is_own = true;
		$entry->icon = 'course_other.png';
	
		$connections[$connection->id] = $entry;
	}

	foreach ($connections_own_this_course_raw as $connection) {
		$entry = new stdClass;
		$entry->id = $connection->id;
		$entry->connection_name = $connection->connection_name;
		$entry->server = $connection->server;
		$entry->domain = $connection->domain;
		$entry->user_share = $connection->user_share;
		$entry->share_user = $connection->share_user;
		$entry->share_access_type = $connection->share_access_type;
		$entry->is_own = true;
		$entry->icon = 'course_this.png';
	
		$connections[$connection->id] = $entry;
	}
	
	foreach ($connections_someones_this_course_raw as $connection) {
		$entry = new stdClass;
		$entry->id = $connection->id;
		$entry->connection_name = $connection->connection_name;
		$entry->server = $connection->server;
		$entry->domain = $connection->domain;
		$entry->user_share = $connection->user_share;
		$entry->share_user = $connection->share_user;
		$entry->share_access_type = $connection->share_access_type;
		$entry->is_own = false;
		$entry->icon = 'course_this.png';
	
		$connections[$connection->id] = $entry;
	}
	
	$entry = new stdClass;
	$entry->id = 0;
	$entry->connection_name = get_string('new_connection', 'directlink');
	$directlink_fileserver = $DB->get_record('config', array('name' => 'directlink_fileserver'));
	$entry->server = $directlink_fileserver->value;
	$directlink_domain = $DB->get_record('config', array('name' => 'directlink_domain'));
	$entry->domain = $directlink_domain->value;
	$entry->user_share = '';
	$entry->share_user = '';
	$entry->share_access_type = 'private';
	$entry->is_own = true;
	$entry->icon = 'user.png';
	
	$connections[0] = $entry;
	
	usort($connections, "connectoin_sort");
	
	return $connections;
}

function connectoin_sort($a, $b)
{
	if($a->id == 0) { return -1; }
	if($b->id == 0) { return 1; }
	if($a->is_own && !$b->is_own) { return -1; }
	if(!$a->is_own && $b->is_own){ return 1; }
	if($a->is_own && $b->is_own) {
		if($a->share_access_type == 'private' && $b->share_access_type != 'private') { return -1; }
		if($a->share_access_type != 'private' && $b->share_access_type == 'private') { return 1; }
		if($a->share_access_type == 'private' && $b->share_access_type == 'private') {
			strcasecmp($a->connection_name, $b->connection_name);
		}
		if($a->share_access_type != 'private' && $b->share_access_type != 'private') {
			strcasecmp($a->connection_name, $b->connection_name);
		}
	}
	if(!$a->is_own && !$b->is_own) {
		strcasecmp($a->connection_name, $b->connection_name);
	}
}

/**
 * Checks if the given connection (elements in fields to compare array) fits
 * to the referenced element.
 * If so, the password of the referenced element is returned
 * 
 * @param int $reference_entry_id
 * @param array $fields_to_compare
 */
function complete_reference_entry($reference_entry_id, $fields_to_compare) {
	global $DB;
	$equal = true;
	$reference_data = $DB->get_record('directlink_connections',array('id'=> $reference_entry_id));
	$reference_password = decrypt($reference_data->share_user_pwd);
	$reference_array = get_object_vars($reference_data); 
	
	foreach ($fields_to_compare as $key => $value) {
		if($value != $reference_array["$key"]) {
			$equal = false;
			break;
		}
	}
	
	return !$equal ? false : $reference_password;
}



function do_checks($server, $share, $domain, $share_user, $share_user_pwd, $reference) {
	
	global $DB;

	$directlink_config = $DB->get_record('config', array('name' => 'directlink_admin_mail'));
	$directlink_admin_mail = $directlink_config->value;
	
	$directlink_config = $DB->get_record('config', array('name' => 'directlink_domain'));
	$directlink_domain = $directlink_config->value;

	/*
	 * Speichern des Mountpoints für die Sitzung
	*/

	unset($_SESSION['directlink_data']);

	$message = null;
	
	
	
	/*
	 * Check for reference etc.
	 */
	if($reference != 0) {
		$fileds_to_compare = array();
		$fileds_to_compare['server'] = $server;
		$fileds_to_compare['user_share'] = $share;
		$fileds_to_compare['domain'] = $domain;
		$fileds_to_compare['share_user'] = $share_user;
		
		if($reference_password = complete_reference_entry($reference, $fileds_to_compare)) {
			$share_user_pwd = $reference_password;
		}
		else {
			return json_encode(array("valid" => false, "msg" => get_string('reference_error', 'directlink')));
		}
	}
	
	/*
	 * Check if default user can connect to share
	* if share is in default domain
	*/
	if($domain == $directlink_domain) {
		$directlink_config = $DB->get_record('config', array('name' => 'directlink_default_user_name'));
		$directlink_default_user = $directlink_config->value;
		
		$directlink_config = $DB->get_record('config', array('name' => 'directlink_default_user_pass'));
		$directlink_default_user_pwd = $directlink_config->value;
		
		$credentials_valid_default = directlink_check_credentials($server, $share, $domain, $directlink_default_user, $directlink_default_user_pwd);
		
		if(!$credentials_valid_default['valid']){
			$credentials_valid_default['msg'] = get_string('connection_error_default_user', 'directlink').$directlink_admin_mail;
			return json_encode($credentials_valid_default);
		}

	}

	$credentials_valid_user = directlink_check_credentials($server, $share, $domain, $share_user, $share_user_pwd);


	if(!$credentials_valid_user['valid']){
		$credentials_valid_user['msg'] = get_string('connection_error_user', 'directlink');
		return json_encode($credentials_valid_user);
	}

	/*
	 * Set data that is immutable for the user after this returns
	*/
	$_SESSION['directlink_data']['server'] = $server;
	$_SESSION['directlink_data']['user_share'] = $share;
	$_SESSION['directlink_data']['domain'] = $domain;
	$_SESSION['directlink_data']['share_user'] = $share_user;
	$_SESSION['directlink_data']['share_user_pwd'] = $share_user_pwd;
	$_SESSION['directlink_data']['reference'] = $reference;
	
	
	
	$_SESSION['directlink_data']['initial_mountpoint'] = construct_mountpoint($server, $domain, $share);

	$mount_temp = mount_share_to_fs($server, $share, $domain, $share_user, $share_user_pwd);
	
	return json_encode($mount_temp);
}

// /**
//  * generates a hash function of an directlink entry for an id
//  * 
//  * @param int $id - the id for which the hash is calculated
//  */
// function generate_connection_hash($id) {
// 	global $DB;
// 	include('config.php');
// 	$salt = $directlink_config['salt'];
// 	$hash = '';
// 	$to_be_hashed = '';
	
// 	$instance_data= $DB->get_record('directlink',array('id'=> $id));
// 	$instance_data->share_user_pwd = decrypt($instance_data->share_user_pwd);
// 	$to_be_hashed = $to_be_hashed . $instance_data->id;
// 	$to_be_hashed = $to_be_hashed . $instance_data->course;
// 	$to_be_hashed = $to_be_hashed . $instance_data->connection_name;
// 	$to_be_hashed = $to_be_hashed . $instance_data->directlink_user_id;
	
	
// 	$hash = hash("SHA256", $to_be_hashed, false);
	
// 	return $hash;
// }

function mask_password($pwd) {
	$pwd = str_replace('$', '\$', $pwd);
	return $pwd;
}

function encrypt($text, $weak=false) {
	include('config.php');
	$key = $directlink_config['password'];
	$cipher = MCRYPT_RIJNDAEL_128;
	if(!$weak){
		$salt = $directlink_config['salt'];
		$text = $text . $salt;
		$cipher = MCRYPT_RIJNDAEL_256;
	}
	
	
	$iv_size = mcrypt_get_iv_size($cipher, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	@$crypttext = base64_encode(mcrypt_encrypt($cipher, $key, $text, MCRYPT_MODE_ECB, $iv));
	return $crypttext;
}

function decrypt($crypt, $weak=false) {
	include('config.php');
	$key = $directlink_config['password'];
	$cipher = MCRYPT_RIJNDAEL_128;
	if(!$weak){
		$salt = $directlink_config['salt'];
		$cipher = MCRYPT_RIJNDAEL_256;
	}
	
	$iv_size = mcrypt_get_iv_size($cipher, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	@$planetext = mcrypt_decrypt($cipher, $key, base64_decode($crypt), MCRYPT_MODE_ECB);
	
	return !$weak ? trim(strstr($planetext, $salt, true)) : trim($planetext);
}

 function str_replace_once($str_pattern, $str_replacement, $string){ 
        
        if (strpos($string, $str_pattern) !== false){ 
            $occurrence = strpos($string, $str_pattern); 
            return substr_replace($string, $str_replacement, strpos($string, $str_pattern), strlen($str_pattern)); 
        } 
        
        return $string; 
    }