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

/**
 * Define all the restore steps that will be used by the restore_directlink_activity_task
 */

/**
 * Structure step to restore one directlink activity
 */
class restore_directlink_activity_structure_step extends restore_activity_structure_step {
		
	private $old_course_id = 0;
	var $newitemid ="-1";
    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('directlink_connections', '/activity/directlink_connections');
        $paths[] = new restore_path_element('directlink', '/activity/directlink_connections/directlink');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }
  
    //insert connections into db while restoring course 
    protected function process_directlink_connections($data) {
        global $DB;

        $data = (object)$data;
    	$oldid = $data->id;
        // $is_in = $this->connection_already_in($data);
        $temp_is_in_old = $this->connection_already_in($data);
        $data->initial_course = $this->get_courseid();
		$temp_is_in_new = $this->connection_already_in($data);
        
		/*
		 * if copied directlink connection was created in source course and is a course share
		 * we need to change initial_course of the connection to be restored (set to newly created course) 
		 */
		if(($data->share_access_type == "course")){
			$data->initial_course = $this->get_courseid();
			if(!$temp_is_in_new){
				// course connection does not exist yet
				$data->initial_course = $this->get_courseid();
				$newitemid = $DB->insert_record('directlink_connections', $data);
	        } else {
	        	// course connection already exists
	        	$newitemid = $this->get_id_of_existing_connection($data);
	        }
        }else if(($data->share_access_type == "private")){
			if($temp_is_in_old || $temp_is_in_new){
				// private connection already exists
        		$newitemid = $this->get_id_of_existing_private_connection($data);

			} else {
				// private connection does not exist yet
				$data->initial_course = $this->get_courseid();
				$newitemid = $DB->insert_record('directlink_connections', $data);
        	} 
		}
		$this->newitemid = $newitemid;
		$this->set_mapping('directlink_connections', $oldid, $newitemid);
	}

    protected function process_directlink($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;

        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
		
        $data->connection_id = $this->newitemid;
        // $data->connection_id = $this->get_mappingid('directlink_connections', $data->connection_id);
        $newitemid2 = $DB->insert_record('directlink', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid2);
    }

	/**
	 * additional function for checking if connection is already in {directlink_connections}
	 * @param $data data object of connection
	 */
	protected function connection_already_in($data) {
		global $DB;
		
		$data = (object)$data;
		
		$is_in = $DB->record_exists_sql("SELECT * FROM {directlink_connections} WHERE
				initial_course = ? AND
				connection_name = ? AND
				connection_owner = ? AND
				server = ? AND
				domain = ? AND
				user_share = ? AND
				share_user = ? AND	
				share_access_type = ?", array($data->initial_course, $data->connection_name, $data->connection_owner, $data->server, $data->domain, $data->user_share, $data->share_user, $data->share_access_type));
		
		return $is_in;
	
	}

	/**
	 * additional function for checking if connection is already in {directlink_connections}
	 * @param $data data object of connection
	 */
	protected function get_id_of_existing_connection($data) {
		global $DB;
		
		$data = (object)$data;
		
		$is_in_id = $DB->get_record_sql("SELECT id FROM {directlink_connections} WHERE
				initial_course = ? AND
				connection_name = ? AND
				connection_owner = ? AND
				server = ? AND
				domain = ? AND
				user_share = ? AND
				share_user = ? AND	
				share_access_type = ?", array($data->initial_course, $data->connection_name, $data->connection_owner, $data->server, $data->domain, $data->user_share, $data->share_user, $data->share_access_type));
		
		return $is_in_id->id;
	}

	/**
	 * additional function for checking if connection is already in {directlink_connections}
	 * @param $data data object of connection
	 */
	protected function get_id_of_existing_private_connection($data) {
		global $DB;
		
		$data = (object)$data;
		
		$is_in_id = $DB->get_record_sql("SELECT id FROM {directlink_connections} WHERE
				connection_name = ? AND
				connection_owner = ? AND
				server = ? AND
				domain = ? AND
				user_share = ? AND
				share_user = ? AND	
				share_access_type = ?", array($data->connection_name, $data->connection_owner, $data->server, $data->domain, $data->user_share, $data->share_user, $data->share_access_type));
		
		return $is_in_id->id;
	}

    protected function after_execute() {
    	// initially left blank
    }
}
