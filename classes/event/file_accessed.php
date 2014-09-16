<?php

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