<?php
/*
 * Event occuring when a course module element viewed (view.php)
 *
 * see:
 * https://docs.moodle.org/dev/Migrating_logging_calls_in_plugins
 * https://docs.moodle.org/dev/Logging_2
 * https://docs.moodle.org/dev/Event_2
 *
 */
namespace mod_directlink\event;

defined('MOODLE_INTERNAL') || die();

class course_module_viewed extends \core\event\course_module_viewed
{
    protected function init()
    {
        $this->data['objecttable'] = 'directlink';
        parent::init();
    }
    // You might need to override get_url() and get_legacy_log_data() if view mode needs to be stored as well.
}

