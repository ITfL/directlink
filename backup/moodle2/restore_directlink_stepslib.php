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
		
    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('directlink', '/activity/directlink');
        $paths[] = new restore_path_element('directlink_connection', '/activity/directlink/directlink_connection');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }


    protected function process_directlink($data) {
        global $DB;
		
        $data = (object)$data;

        $this->old_course_id = $data->course;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
		
		
        // insert the directlink record
        $newitemid = $DB->insert_record('directlink', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid); 
    }
    
    //insert connections into db while restoring course 
    protected function process_directlink_connection($data) {
        global $DB;

        $data = (object)$data;
        
        $is_in = $this->connection_already_in($data);
        
		/*
		 * if copied directlink connection was created in source course and is a course share
		 * we need to change initial_course of the connection to be restored (set to newly created course) 
		 */
		 if(($data->initial_course == $this->old_course_id && $data->share_access_type == "course")){

			$data->initial_course = $this->get_courseid();
			
			// check if new connection already created while restoring course connections
			$temp_is_in = $this->connection_already_in($data);
			
			if(!$temp_is_in){
				// insert the directlink record
				$newitemid = $DB->insert_record('directlink_connections', $data);
			}
		}else if(!$is_in){
			// insert the directlink record
			$newitemid = $DB->insert_record('directlink_connections', $data);
		}
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

    protected function after_execute() {
    	// initially left blank
    }
}
