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
 * Define the complete directlink structure for backup, with file and id annotations
 */
class backup_directlink_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
		global $DB;
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $directlink_course_connection = new backup_nested_element('directlink_connections', array('id'), array('initial_course', 'connection_name', 'connection_owner', 'server', 'domain', 'user_share', 'share_user', 'share_user_pwd', 'share_access_type' ));
               
        $directlink_course = new backup_nested_element('directlink', array('id'), array('course', 'connection_id', 'directlink_user_id', 'name', 'intro', 'ffc', 'path_to_file', 'introformat', 'timemodified' ));

        $directlink_course_connection->add_child($directlink_course);

        // Define source of directlink
        $directlink_course_connection->set_source_sql('
            SELECT dlc.id AS id, initial_course, connection_name, connection_owner, server, domain, user_share, share_user, share_user_pwd, share_access_type
            FROM    {directlink_connections} AS dlc, {directlink} AS dl
            WHERE   dlc.id = dl.connection_id AND
                    dl.id = ?',
                    array( backup::VAR_ACTIVITYID));     

        
        // Define source of directlink connection
        $directlink_course->set_source_table('directlink', array('id' => backup::VAR_ACTIVITYID));

        $directlink_course_connection->annotate_files('mod_directlink', 'intro', null);

  //       // Define each element separated
  //       $directlink = new backup_nested_element('directlink', array('id'), array('course', 'connection_id', 'directlink_user_id', 'name', 'intro', 'ffc', 'path_to_file', 'introformat', 'timemodified' ));
       
		// $directlink_connection = new backup_nested_element('directlink_connection', array('id'), array('initial_course', 'connection_name', 'connection_owner', 'server', 'domain', 'user_share', 'share_user', 'share_user_pwd', 'share_access_type' ));
		
  //      	$directlink->add_child($directlink_connection);

  //       // Define source of directlink
  //       $directlink->set_source_table('directlink', array('id' => backup::VAR_ACTIVITYID));
        
  //       // Define source of directlink connection
  //       $directlink_connection->set_source_sql('
  //           SELECT dlc.id AS id, initial_course, connection_name, connection_owner, server, domain, user_share, share_user, share_user_pwd, share_access_type
  //           FROM 	{directlink_connections} AS dlc, {directlink} AS dl
  //        	WHERE	dlc.id = dl.connection_id AND
  //           		dl.id = ?',
  //           		array( backup::VAR_ACTIVITYID));     
        
  //       $directlink->annotate_files('mod_directlink', 'intro', null);

        // Return the root element (directlink), wrapped into standard activity structure
        return $this->prepare_activity_structure($directlink_course_connection);
    }
}
