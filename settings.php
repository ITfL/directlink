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
 * Folder module admin settings and defaults
 *
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    /*
     * -- general admin settings (plugin administration fields)--
     * smbclient_path
     * mountpoint
     * fileserver
     * domain
     * default_user_name
     * default_user_pass
     * deny_external_hosts
     * desc_required
     * file_choose_ignore
     * admin_mail
     */
    $settings->add(new admin_setting_configtext('directlink_smbclient_path', get_string('smbclient_path', 'directlink'),
        get_string('smbclient_path_desc', 'directlink'), '/usr/bin/smbclient', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('directlink_mount_point', get_string('mount_point', 'directlink'),
        get_string('moint_point_desc', 'directlink'), null, PARAM_TEXT));

    $settings->add(new admin_setting_configtext('directlink_fileserver', get_string('fileserver', 'directlink'),
        get_string('fileserver_desc', 'directlink'), null, PARAM_TEXT));

    $settings->add(new admin_setting_configtext('directlink_domain', get_string('domain', 'directlink'),
        get_string('domain_desc', 'directlink'), null, PARAM_TEXT));

    $settings->add(new admin_setting_configtext('directlink_default_user_name', get_string('default_user_name', 'directlink'),
        get_string('default_user_name_desc', 'directlink'), null, PARAM_TEXT));

    $settings->add(new admin_setting_configpasswordunmask('directlink_default_user_pass', get_string('default_user_pass', 'directlink'),
        get_string('default_user_pass_desc', 'directlink'), null));

    $settings->add(new admin_setting_configcheckbox('directlink_deny_external_hosts', get_string('deny_external_hosts', 'directlink'),
        get_string('deny_external_hosts_desc', 'directlink'), 1));

    $settings->add(new admin_setting_configcheckbox('directlink_desc_required', get_string('desc_required', 'directlink'),
        get_string('desc_required_desc', 'directlink'), 1));

    $settings->add(new admin_setting_configtextarea('directlink_filechoose_ignore', get_string('filechoose_ignore', 'directlink'), get_string('filechoose_ignore_desc', 'directlink'), '.htaccess, error_log, cgi-bin, php.ini, .ftpquota, .git'));


    $settings->add(new admin_setting_configtext('directlink_admin_mail', get_string('admin_mail', 'directlink'),
        get_string('admin_mail_desc', 'directlink'), null, PARAM_TEXT));
}
