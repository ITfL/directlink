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
 * The main directlink configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

#uni-bamberg_debug
$PAGE->requires->js('/mod/directlink/js/jquery-1.7.2.min.js', true);
$PAGE->requires->js('/mod/directlink/js/jquery.dataTables.min.php', true);
$PAGE->requires->js('/mod/directlink/js/jquery-ui-1.8.20.custom.min.js', true);
$PAGE->requires->js('/mod/directlink/js/jquery.dd.js', true);
$PAGE->requires->js('/mod/directlink/js/configure_directlink.php', true);
$PAGE->requires->css('/mod/directlink/css/mod_form_styles.css');
$PAGE->requires->css('/mod/directlink/css/dd.css');
$PAGE->requires->css('/mod/directlink/css/jquery.dataTables.css');
$PAGE->requires->css('/mod/directlink/css/smoothness/jquery-ui-1.8.20.custom.css');

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once('locallib.php');


/**
 * Module instance settings form
 */
class mod_directlink_mod_form extends moodleform_mod {
	
	function data_preprocessing(&$default_values){
		global $DB, $USER, $COURSE;
		
		parent::data_preprocessing($default_values);
		
		// new 
		if(!isset($default_values['directlink_user_id'])) {
			return;
		}
		
		
		
		
		$directlink_config = $DB->get_record('config', array('name' => 'directlink_mount_point'));
		$directlink_mount_point = $directlink_config->value;
		
		$directlink_connection = $DB->get_record('directlink_connections', array('id' => $default_values['connection_id']));
		

		
		$default_values['share_access_type'] = $directlink_connection->share_access_type;
		$default_values['initial_course'] = $directlink_connection->initial_course;
		$default_values['connection_name'] = $directlink_connection->connection_name;
		$default_values['server'] = $directlink_connection->server;
		
		$default_values['domain'] = $directlink_connection->domain;
		$default_values['user_share'] = $directlink_connection->user_share;
		$default_values['share_user'] = $directlink_connection->share_user;
		$default_values['share_user_pwd'] = "**********";
		
		$share_parts = explode('/', $default_values['user_share']);
		
		$default_values['path_to_file'] = decrypt($default_values['path_to_file']);
		
		
		
		$default_values['path_to_file'] = str_replace($directlink_mount_point . '/', '', $default_values['path_to_file']);
		
		
		/*
		 * If share contains two directorynames we append the first one to the server because it needs to be removed
		 * file path starts at second.
		 */
		if(count($share_parts) > 1) {
			$different_domain_extension = $default_values['server'] . '/' . $share_parts[0] . '/';
		}
		else {
			$different_domain_extension = $default_values['server'] . '/';
		}
		
		
		$different_domain_extension = preg_replace('/\//', '\/', $different_domain_extension);
		
		if(preg_match('/^'.$different_domain_extension.'/', $default_values['path_to_file'])){
			$default_values['path_to_file'] = preg_replace('/^'.$different_domain_extension.'/', '', $default_values['path_to_file']);
		}
		
		$default_values['introeditor']['text'] = $default_values['intro'];
		$default_values['introeditor']['format'] = $default_values['introformat'];
		
		
		
		
		// edit own or public
		if(($USER->id == $default_values['directlink_user_id']) || ($default_values['share_access_type'] == 'course' && $default_values['initial_course'] == $COURSE->id)) {
			
			$mform = $this->_form;
			$mform->addElement('html', '<script type="text/javascript">'.
					'$.each(existing_connections, function(index, value) {'.
						'if(value.id == \''.$default_values['connection_id'].'\') {'.
							'$(\'#id_existing_connections\').val(index);'.
							'return false;'.
						'}'.
					'});'.
					'insert_form_data();'.
					'$(\'#change_template\').attr(\'disabled\', \'disabled\');'.
					'$(\'#use_in_this_course\').attr(\'disabled\', \'disabled\');'.
					'resource = $(\'input[name=path_to_file]\').val().replace(\''. $directlink_mount_point .'\'+\'/\',\'\');'.
					'send_credentials_clicked(resource);'.
					'</script>');
			
		}
		// edit foreign private
		else {
			throw new moodle_exception(get_string('edit_foreign_private_share', 'directlink'), 'directlink', 'view.php?id='.$COURSE->id);
		}
	}
	
	/**
	 * get formula data and is validating content
	 * @see moodleform_mod::validation()
	 */
	function validation(&$data, $files) {
		
		$errors = parent::validation($data, $files);
		
		$js_show_all_fields = "send_credentials_clicked();";
		$found_problem = false;
		
		/**
		 * some fields must not change after ajax call was sent
		 * parameters for ajax call are stored in session and are used to check
		 * given parameters
		 */
		
		$immutable_fields = array('server', 'domain', 'user_share', 'share_user', 'share_user_pwd', 'reference');
		
		foreach($immutable_fields as $immutable_field_id) {
			
			/**
			 * No need to compare password if the connection references another connection
			 */
			if($immutable_field_id == 'share_user_pwd' && $_SESSION['directlink_data']['reference'] != 0) {
				continue;
			}
			
			if($data[$immutable_field_id] != $_SESSION['directlink_data'][$immutable_field_id]){
				$found_problem = true;
				$errors[$immutable_field_id] = get_string('immutable_field_'.$immutable_field_id.'_changed', 'directlink') . ' ' . $_SESSION['directlink_data'][$immutable_field_id];
				/*
				 * if immutable fields are changed umount the drive something strange is going on at the client side ;-)
				 */
				umount_share($_SESSION['directlink_data']['server'], $_SESSION['directlink_data']['user_share'], $_SESSION['directlink_data']['domain']);
				$js_show_all_fields = "";
			}
		}

		
		$path_to_file_array = explode("/", $data['path_to_file']);
		$directlink_mountpoint_array = explode("/", $_SESSION['directlink_data']['mountpoint']);
		
		
		if($data['path_to_file'] == "") {
			$found_problem = true;
			$js_show_all_fields = $js_show_all_fields . "$('#show_files').before('<span class=\"error\">".get_string('file_choose_error', 'directlink')."</span><br />');";
		}
		else if(preg_match('/(\.{2}|\?|%|\*|:|\||"|<|>)/', $data['path_to_file']) == 1) {
			$found_problem = true;
			$js_show_all_fields = $js_show_all_fields . "$('#show_files').before('<span class=\"error\">".get_string('file_choose_error', 'directlink')."</span><br />');";
		}
		else if($path_to_file_array[0] != $directlink_mountpoint_array[ count($directlink_mountpoint_array) - 1 ]) {
			$found_problem = true;
			$js_show_all_fields = $js_show_all_fields . "$('#show_files').before('<span class=\"error\">".get_string('file_choose_error', 'directlink')."</span><br />');";
		}
		
		
		if($found_problem) {
			// use the mform to get access to javascript or html
			$mform = $this->_form;
			$mform->addElement('html', '<script type="text/javascript">'.
					$js_show_all_fields.
					'message("problem", "'.get_string('validation_error', 'directlink').'");'.
					'</script>');
		}
		
		$errors = array();
		
		return $errors;
	}
	
    /**
     * Defines forms elements
     */
    public function definition() {
    	global $USER, $DB, $COURSE;
    	
    	$usercontext = get_context_instance(CONTEXT_USER, $USER->id);
    	
        $mform = $this->_form;
        
        $mform->addElement('header', 'manage_connections',get_string('connection_properties', 'directlink'));
        
        $js_new_connection = get_string('js_new_connection', 'directlink');
        $js_manage_connections = get_string('js_manage_connections', 'directlink');
        $connection_name = get_string('connection_name', 'directlink');
        $user = get_string('share_user', 'directlink');
        $js_manage_share_type = get_string('js_manage_share_type', 'directlink');
        $js_manage_server = get_string('server', 'directlink');
        $js_manage_domain = get_string('domain', 'directlink');
        $js_manage_actions = get_string('js_manage_actions', 'directlink');
        $manage_share = get_string('user_share', 'directlink');
        $manage_password = get_string('share_user_pwd', 'directlink');
        $private_share = get_string('private_share', 'directlink');
        $private_share_desc = get_string('private_share_desc', 'directlink');
        $course_share = get_string('course_share', 'directlink');
        $course_share_desc = get_string('course_share_desc', 'directlink');
        $js_manage_save = get_string('js_manage_save', 'directlink');
        $js_manage_discard = get_string('js_manage_discard', 'directlink');
        $js_load_data = get_string('js_load_data', 'directlink');
        
        
        $html_table_dummy = <<<HTML
        	<div id="tabs">
        		<ul>
        			<li><a href="#new-connection" onClick="tab_toggler(true);(function(){if(changes_in_connection_manager){window.location.reload();}})();">{$js_new_connection}</a></li>
      				<li><a href="#manage-connections" onClick="tab_toggler(false);">{$js_manage_connections}</a></li>
        		</ul>
	        	<div id="new-connection">
				
	        	</div>
	        	<div id="manage-connections">
		        	<table id="myTable" class="tablesorter"> 
						<thead> 
							<tr>
								<th>Id</th>
								<th width="10px"></th>
							    <th>{$connection_name}</th> 
							    <th>{$user}</th> 
							    <th>{$js_manage_share_type}</th>
							    <th>{$js_manage_server}</th> 
							    <th>{$js_manage_actions}</th> 
							</tr> 
						</thead> 
						<tbody>
							<tr>
								<td colspan="6" class="dataTables_empty">{$js_load_data}</td>
							</tr>
						</tbody>
					</table>
					<div style="margin-top: 20px;">
						
					<div id="edit_connection" class="fcontainer clearfix" style="border: 1px solid #97BF0D; display: none;">			
						<div class="fitem" style="display: none;">
							<div class="fitemtitle">
								<label for="connection_id">
									Connection-Id
								</label>
							</div>
							<div class="felement ftext">
								<input size="32" name="edit_connection_id" type="text" id="edit_connection_id">
							</div>
						</div>
					
						<div class="fitem">
							<div class="fitemtitle">
								<label for="connection_name">
									{$connection_name}
								</label>
							</div>
							<div class="felement ftext">
								<input size="32" name="edit_connection_name" type="text" id="edit_connection_name">
							</div>
						</div>
						
						<div class="fitem">
							<div class="fitemtitle">
								<label for="connection_server">
									{$js_manage_server}
								</label>
							</div>
							<div class="felement ftext">
								<input size="32" name="edit_connection_server" type="text" id="edit_connection_server">
							</div>
						</div>
						
						<div class="fitem">
							<div class="fitemtitle">
								<label for="connection_domain">
									{$js_manage_domain}
								</label>
							</div>
							<div class="felement ftext">
								<input size="32" name="edit_connection_domain" type="text" id="edit_connection_domain">
							</div>
						</div>
						
						<div class="fitem">
							<div class="fitemtitle">
								<label for="connection_share">
									{$manage_share}
								</label>
							</div>
							<div class="felement ftext">
								<input size="32" name="edit_connection_share" type="text" id="edit_connection_share">
							</div>
						</div>
						
						<div class="fitem">
							<div class="fitemtitle">
								<label for="connection_user">
									{$user}
								</label>
							</div>
							<div class="felement ftext">
								<input size="32" name="edit_connection_user" type="text" id="edit_connection_user">
							</div>
						</div>
						
						<div class="fitem">
							<div class="fitemtitle">
								<label for="connection_password">
									{$manage_password}
								</label>
							</div>
							<div class="felement ftext">
								<input size="32" name="edit_connection_password" type="password" id="edit_connection_password">
							</div>
						</div>
						
						<div class="fitem">
							<div class="fitemtitle">
								<label for="connection_private">
									{$private_share}
								</label>
							</div>
							<div class="felement fradio">
								<span>
									<input name="edit_connection_share_access_type" value="private" type="radio" id="connection_private" checked="checked" disabled>
									<label for="connection_private">
										{$private_share_desc}
									</label>
								</span>
							</div>
						</div>
						
						<div class="fitem">
							<div class="fitemtitle">
								<label for="connection_course">
								{$course_share}
								</label>
							</div>
							<div class="felement fradio">
								<span>
									<input name="edit_connection_share_access_type" value="course" type="radio" id="connection_course" disabled>
									<label for="connection_course">
										{$course_share_desc}
									</label>
								</span>
							</div>
						</div>
						
						<div class="fitem">
		        			<div class="fitemtitle">
		        			</div>
		        			<div class="felement">
		        				<button id="update_connection" onclick="send_changes();return false;">
		        					{$js_manage_save}
		        				</button>
		        				<button id="discard_connection_changes" onclick="discard_edit();return false;">
		        					{$js_manage_discard}
		        				</button>
		        			</div>
		        		</div>
					</div>
					
					</div>
	        	</div>
        	</div>
HTML;

        $mform->addElement('html', $html_table_dummy);
        
        
        $mform->addElement('header', 'connection_properties', get_string('connection_properties', 'directlink'));
        
        $connections = array();
        $connections = get_connections($COURSE->id, $usercontext->instanceid);
        
        $options = '';
        
        $group = '';
        foreach ($connections as $index => $connection) {
        	$icon = $connection->icon;
        	
        	if($connection->id == 0) {
        	}
        	else if($connection->is_own && $connection->share_access_type == 'private') {
        		if($group == '') {
        			$group = 'private';
        			$options = $options . "<optgroup label='".get_string($group, 'directlink')."'>";
        		}
        	}
        	else {
        		if($group == '') {
        			$group = $connection->is_own ? 'private' : 'course';
        			$options = $options . "<optgroup label='".get_string($group, 'directlink')."'>";
        		}
        		else if($group == 'private' && !$connection->is_own) {
        			$options = $options . "</optgroup>";
	        		$group = 'course';
	        		$options = $options . "<optgroup label='".get_string($group, 'directlink')."'>";
        		}
        	}
        	
        	
        	$options = $options . "<option value='{$index}' title='../mod/directlink/pix/icons/{$icon}'>{$connection->connection_name}</option>";
        }
        
        if($group != '') {
        	$options = $options . "</optgroup>";
        }
        
        
        
        
        $mform->addElement('html', '
        		<script type="text/javascript">
        			course_id = '.$COURSE->id.';
        			existing_connections = '.json_encode($connections).';
        		
					function change_template_fn() {
        				$(\'input[name=reference]\').val(0);
        				$(\'#id_connection_name\').removeAttr(\'disabled\');
        		
        				if($(\'input[name=deny_external_hosts]\').val() == "0"){
							$(\'#id_server\').removeAttr(\'disabled\');
							$(\'#id_domain\').removeAttr(\'disabled\');
						}
        				
        				$(\'#id_user_share\').removeAttr(\'disabled\');
						$(\'#id_share_user\').removeAttr(\'disabled\');
						$(\'#id_share_user_pwd\').removeAttr(\'disabled\');
						$(\'#id_share_user_pwdunmask\').removeAttr(\'disabled\');
						$(\'input:[name=share_access_type]\').removeAttr(\'disabled\');
        				$(\'#change_template\').attr(\'disabled\', \'disabled\');	
        				$("#id_share_user_pwd").val(\'\');
    				}        		
        		
        			function use_in_this_course_fn() {
    					$(\'input[name=reference]\').val(0);
        				$(\'#id_share_user_pwd\').removeAttr(\'disabled\');
						$(\'#id_share_user_pwdunmask\').removeAttr(\'disabled\');
        				$("#id_share_user_pwd").val(\'\');
        				$(\'#use_in_this_course\').attr(\'disabled\', \'disabled\');
    				}
        			
        			function insert_form_data() {
        				$(\'#id_connection_name\').removeAttr(\'disabled\');
						$(\'#id_server\').removeAttr(\'disabled\');
						$(\'#id_domain\').removeAttr(\'disabled\');
						$(\'#id_user_share\').removeAttr(\'disabled\');
						$(\'#id_share_user\').removeAttr(\'disabled\');
						$(\'#id_share_user_pwd\').removeAttr(\'disabled\');
						$(\'#id_share_user_pwdunmask\').removeAttr(\'disabled\');
						$(\'input:[name=share_access_type]\').removeAttr(\'disabled\');	
        		
        				var id = $(\'#id_existing_connections\').val();
        				
        				
        		
    					var form_data = existing_connections[id];
        		
        				if(form_data.id != 0) {
        					$(\'#id_connection_name\').attr(\'disabled\', \'disabled\');
        					$(\'#id_server\').attr(\'disabled\', \'disabled\');
							$(\'#id_domain\').attr(\'disabled\', \'disabled\');
							$(\'#id_user_share\').attr(\'disabled\', \'disabled\');
							$(\'#id_share_user\').attr(\'disabled\', \'disabled\');
							$(\'#id_share_user_pwd\').attr(\'disabled\', \'disabled\');
							$(\'#id_share_user_pwdunmask\').attr(\'disabled\', \'disabled\');
        					$(\'input:[name=share_access_type]\').attr(\'disabled\', \'disabled\');
    					}
        		
        				if(form_data.is_own && form_data.id != 0) {
        					$(\'#change_template\').removeAttr(\'disabled\');
        					if(form_data.icon == \'course_other.png\') {
        						$(\'#use_in_this_course\').removeAttr(\'disabled\');	
    						}
        					else {
        						$(\'#use_in_this_course\').attr(\'disabled\', \'disabled\');
    						}
    					}
        				else {
    						$(\'#change_template\').attr(\'disabled\', \'disabled\');
        					$(\'#use_in_this_course\').attr(\'disabled\', \'disabled\');
    					}
        		
        				$(\'input[name=reference]\').val(form_data.id);
        		
        				$.each(form_data, function(key, value) {
        					if(key == "connection_name" && id == 0) {
        						$("#id_connection_name").val(\'\');
        					}
        					else if(key == "share_access_type") {
        						$("input[name=\'share_access_type\'][value=\'"+value+"\']").attr("checked","checked");
        					}
        					else {
        						$("#id_"+key).val(value);
        					}
						});
        		
        				if(id != 0) {
        					$("#id_share_user_pwd").val(\'**********\');
    					}
        				else {
        					$("#id_share_user_pwd").val(\'\');
    					}
    				}
        			
        		</script>
        		<div class="fitem">
	        		<div class="fitemtitle">
		        		<label for="id_existing_connections">
		        			'.get_string('existing_connections', 'directlink').'
		        		</label>
	        		</div>
	        		<div class="felement">
	        			<select id="id_existing_connections" style="width: 228px;" onchange="insert_form_data()">
							'.$options.'
						</select>
	        		</div>
        		</div>');
        
        $mform->addElement('html', '
        		<div class="fitem">
        		<div class="fitemtitle">
        		</div>
        		<div class="felement">
        		<button id="change_template" onclick="change_template_fn();return false;" disabled="disabled">'.get_string('change_template', 'directlink').'</button>
        		<button id="use_in_this_course" onClick="use_in_this_course_fn();return false;" disabled="disabled">'.get_string('use_in_this_course', 'directlink').'</button>
        		</div>
        		</div>');
        
        $mform->addElement('hidden', 'reference', '0');
        $directlink_deny_external_hosts = $DB->get_record('config', array('name' => 'directlink_deny_external_hosts'));
        $directlink_deny_external_hosts = $directlink_deny_external_hosts->value;
        $mform->addElement('hidden', 'deny_external_hosts', $directlink_deny_external_hosts);
        
        $mform->addElement('text', 'connection_name', get_string('connection_name', 'directlink'), array('size'=>'32'));
        $mform->addRule('connection_name', null, 'required', null, 'client');
        $mform->addHelpButton('connection_name', 'connection_name', 'directlink');
        
        
        
        $mform->addElement('hidden', 'directlink_user_id', $usercontext->instanceid);
                
        $directlink_fileserver = $DB->get_record('config', array('name' => 'directlink_fileserver'));
        $server_attr = array('size'=>'32', 'value'=>$directlink_fileserver->value);
        
		if($directlink_deny_external_hosts) {
        	$server_attr['disabled'] = 'disabled';
        }
        
        $mform->addElement('text', 'server', get_string('server', 'directlink'), $server_attr);
        $mform->addRule('server', null, 'required', null, 'client');
        
		
        
        
        $directlink_domain = $DB->get_record('config', array('name' => 'directlink_domain'));
        $domain_attr = array('size'=>'32', 'value'=>$directlink_domain->value);
        
        if($directlink_deny_external_hosts) {
        	$domain_attr['disabled'] = 'disabled';
        }
        
        $mform->addElement('text', 'domain', get_string('domain', 'directlink'), $domain_attr);
        $mform->addRule('domain', null, 'required', null, 'client');
        
        
        $mform->addElement('text', 'user_share', get_string('user_share', 'directlink'), array('size'=>'32'));
        $mform->addRule('user_share', null, 'required', null, 'client');
        
        $mform->addElement('text', 'share_user', get_string('share_user', 'directlink'), array('size'=>'32'));
        $mform->addRule('share_user', null, 'required', null, 'client');
        
        
        
        $mform->addElement('passwordunmask', 'share_user_pwd', get_string('share_user_pwd', 'directlink'), array('size'=>'32'));
        $mform->addRule('share_user_pwd', null, 'required', null, 'client');
        
        
        $mform->addElement('radio', 'share_access_type', get_string('private_share', 'directlink'), get_string('private_share_desc', 'directlink'), 'private' );
        $mform->addElement('radio', 'share_access_type', get_string('course_share', 'directlink'), get_string('course_share_desc', 'directlink'), 'course' );
        $mform->setDefault('share_access_type', 'private');
        
        $mform->addElement('html', '
        		<div class="fitem">
        			<div class="fitemtitle">
        			</div>
        			<div class="felement">
        				<button id="send_credentials" onClick="send_credentials_clicked();return false;">
        					'.get_string('test_credentials', 'directlink').'
        				</button>
        				<button id="discard_credentials" onClick="send_discard_credentials();return false;" disabled="disabled">
        					'.get_string('discard_credentials', 'directlink').'
        				</button>
        			</div>
        		</div>');
        
        
       
        $mform->addElement('header', 'general', get_string('general', 'form'));
        
        
        $mform->addElement('text', 'name', get_string('name', 'directlink'), array('size'=>'32'));
        $mform->addRule('name', null, 'required', null, 'client');
        
        
        $directlink_desc = $DB->get_record('config', array('name' => 'directlink_desc_required'));
        
//         $mform->addElement('text', 'intro', get_string('description', 'directlink'), array('size'=>'32'));        
//         if($directlink_desc->value){
//         	$mform->addRule('intro', null, 'required', null, 'client');
//         }
//         $mform->addRule('intro', null, 'maxlength', 255, 'client');
//         $mform->addHelpButton('intro', 'description', 'directlink');
        
        
        // $mform->addElement('editor', 'introeditor', get_string('description', 'directlink'), null, array('height'=>'640px', 'maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->context));
        // $mform->addElement('editor', 'introeditor', get_string('description', 'directlink'));
        $mform->addElement('editor', 'entry', get_string('description', 'directlink'), null, array('maxfiles' => EDITOR_UNLIMITED_FILES));
        $mform->setType('introeditor', PARAM_RAW); // no XSS prevention here, users must be trusted
        if ($directlink_desc->value) {
        	$mform->addRule('introeditor', null, 'required');
        }
        
        $mform->addElement('header', 'choosefile', get_string('choose_file', 'directlink'));
        
        $mform->addElement('radio', 'ffc', get_string('file', 'directlink'), get_string('file_desc', 'directlink'), 'file' );
        $mform->addElement('radio', 'ffc', get_string('folder', 'directlink'), get_string('folder_desc', 'directlink'), 'folder' );
        $mform->addElement('radio', 'ffc', get_string('content','directlink'), get_string('content_desc','directlink'), 'content' );
        $mform->setDefault('ffc', 'file');
        
        $mform->addElement('html', '
        		<div class="fitem">
        		<div class="fitemtitle">
        			<label for="show_files">
        		'.get_string('choose_ressource', 'directlink').'
        			</label>
        		</div>
        		<div class="felement">
        			<div id="show_files" style="background-color: white; border: 1px solid silver;">
	        			<div><img src="../mod/directlink/pix/loader.gif"></div>
        			</div>
        		</div>
        		</div>');
        
        
        $mform->addElement('text', 'path_to_file', 'path_to_file', array('size'=>'125'));
        
        
        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements(false);
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
}
