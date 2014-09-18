<?php
/*
 * Event occuring when a file element is downloaded or embedded (file.php)
 *
 * see:
 * https://docs.moodle.org/dev/Migrating_logging_calls_in_plugins
 * https://docs.moodle.org/dev/Logging_2
 * https://docs.moodle.org/dev/Event_2
 *
 */
namespace mod_directlink\event;

defined('MOODLE_INTERNAL') || die();

class file_accessed extends \core\event\course_module_viewed
{
    protected function init()
    {
        $this->data['objecttable'] = 'directlink';
        parent::init();
    }

    public static function get_name()
    {
        return get_string('eventfile_accessed', 'directlink');
    }


}