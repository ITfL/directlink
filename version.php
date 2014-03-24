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
 * Defines the version of newmodule
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

<<<<<<< HEAD
$module->version  = 2014031701;  // The current module version (Date: YYYYMMDDXX)
=======
$module->version  = 2013101101;  // The current module version (Date: YYYYMMDDXX)
>>>>>>> e45a0631c9878fabefb76a6638e14383ed03eb80

// moodle version can be found at "Settings > Site administration > Notifications"
$module->requires = 2011070100;  // Requires this Moodle version
$module->cron     = 0;           // Period for cron to check this module (secs)

$module->maturity = MATURITY_BETA;
$module->release  = 'squabbling squid';

