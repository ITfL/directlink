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
 * English strings for DirectLink
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * general strings
 */

$string['connection_properties'] = 'Connection Properties'; 
$string['modulename'] = 'Fileserver'; //frontend chooser name
$string['modulenameplural'] = 'Fileservers';
$string['modulename_help'] = 'Use the Fileserver module for integration of files and folders out of a share. Please contact your moodle support for requirements.';
$string['directlinkfieldset'] = 'Custom example fieldset';
$string['directlinkname'] = 'Fileserver name';
$string['directlinkname_help'] = 'This is the content of the help tooltip associated with the DirectLinkname field. Markdown syntax is supported.';
$string['directlink'] = 'Fileserver';
$string['pluginadministration'] = 'Fileserver Administration';
$string['pluginname'] = 'Fileserver';
/**
 * admin strings
 */

$string['smbclient_path'] = 'Path to smbclient';
$string['smbclient_path_desc'] = 'The servers local path to the smbclient (/usr/bin/smbclient).';

$string['mount_point'] = 'Mountpoint';
$string['moint_point_desc'] = 'Path where shares are mounted. www-data must be owner of this folder.';

$string['fileserver'] = 'Default fileserver';
$string['fileserver_desc'] = 'Fully qualified name of default server.';

$string['domain'] = 'Default domain';
$string['domain_desc'] = 'Default domain used to connect users.';

$string['default_user_name'] = 'Default user';
$string['default_user_name_desc'] = 'The default user for smb connection.';

$string['default_user_pass'] = 'Password for default user';
$string['default_user_pass_desc'] = 'The Password for default user.';

$string['deny_external_hosts'] = 'Deny external hosts';
$string['deny_external_hosts_desc'] = 'If this option is selected, only shares within the given server are allowed!';

$string['filechoose_ignore'] = 'Filechoose';
$string['filechoose_ignore_desc'] = 'This files are not shown within the Fileserver filechooser.';

$string['desc_required'] = 'Description required';
$string['desc_required_desc'] = 'Defines if description of Fileserver is required.';

$string['admin_mail'] = 'Contact mail';
$string['admin_mail_desc'] = 'Mail adress for Fileserver / moodle support.';

/**
 * plugin strings
 */

$string['edit_foreign_private_share'] = 'You can’t edit other ones shares.'; 
 
$string['existing_connections'] = 'Existing connections';
$string['existing_connections_desc'] = 'A list of already existing connections.';

$string['change_template']='Use as template';
$string['use_in_this_course']='Share in this Course';

$string['new_connection'] = 'Create new connection';

$string['private'] = 'My Shares';

$string['course'] = 'Someones public shares of the course';

$string['connection_name'] = 'Connection name';
$string['connection_name_help'] = 'Connection name displayed in connection manager.';

$string['name'] = 'Name';

$string['description'] = 'Description';
$string['description_help'] = 'Description for certain Fileserver. Description is only shown when file or folder is opened in a separate window';

$string['server'] = 'Server';

$string['choose_file'] = 'Content'; 


// server default from admin settings

$string['user_share'] = 'Share';

$string['share_user'] = 'User';

$string['share_user_pwd'] = 'Share user password';

$string['private_share'] = 'Private share';
$string['private_share_desc'] = 'Only you can see this share in connection manager.';

$string['course_share'] = 'Course share';
$string['course_share_desc'] = 'All moderators can see this share in connection manager.';

$string['test_credentials'] = 'Test credentials';

$string['discard_credentials'] = 'Discard credentials';

$string['warning_change_connection'] = 'Changing the connectin will result in loosing all informations supplied here! Proceed anyhow?';


$string['file'] = 'File';
$string['file_desc'] = 'A simple file should be integrated.';

$string['folder'] = 'Folder';
$string['folder_desc'] = 'Folder should open in separate window.';

$string['content'] = 'Content';
$string['content_desc'] = 'Content of folder should be integrated.';

$string['choose_ressource'] = 'Choose a ressource';

$string['empty_folder'] = 'Folder is empty';

/**
 * js content / manage
 */

$string['js_confirm'] = 'Are you sure, want to change chosen connection?';

$string['js_new_connection'] = 'New Connection';

$string['js_manage_connections'] = 'Manage Connections';
$string['js_manage_share_type'] = 'Connection Type';
$string['js_manage_actions'] = 'Actions';
$string['js_manage_save'] = 'Update Connection';
$string['js_manage_discard'] = 'Discard Changes';

$string['js_load_data'] = 'Loading data from server.';

$string['manage_connection_info'] = 'Connection Info:';
$string['manage_connection_course'] = 'Course Name';
$string['manage_no_reference'] = 'No reference for this connection.';
$string['manage_processing'] = 'Processing changes';

$string['manage_changes_success'] = 'Changes successful processed.';
$string['manage_changes_problem'] = 'Could not apply changes.';

//jquery adaption

$string['jq_manage_search'] = 'Search:';
$string['jq_manage_previous'] = 'Previous';
$string['jq_manage_next'] = 'Next';
$string['jq_show'] = 'Show _MENU_ entries';
$string['jq_show_entries'] = 'Show _START_ to _END_ of _TOTAL_ entries';


/**
 * errors
 */

$string['validation_error'] = 'Validation error occurred.'; 

$string['immutable_field_domain_changed'] = 'Immutable field "domain" was changed.'; 
$string['immutable_field_user_share_changed'] = 'Immutable field "Share" was changed.'; 
$string['immutable_field_share_user_changed'] = 'Immutable field "User" was changed.'; 

$string['connection_error_default_user'] = 'Default user can\'t connect to the certain share.</br> Please contact ';

$string['connection_error_user'] = 'Given user can\'t connect to the certain share. </br> Please check credentials.';

$string['file_doesnt_exist'] = 'File doesn\'t exist. </br> Please contact your course moderator.';

$string['folder_doesnt_exist'] = 'Folder doesn\'t exist. </br> Please contact your course moderator.';

$string['file_choose_error'] = 'Please choose file/folder.';

$string['link_not_found'] = 'Fileserver not found.';

$string['no_permission'] ='No permission to access file!';

$string['change_connection_error'] = 'Cannot edit/delete connection. It is in use!';

/**
 * view file info
 */

$string['file_name'] = 'Name';

$string['file_size'] = 'Size';

$string['file_changed'] = 'Last changes';