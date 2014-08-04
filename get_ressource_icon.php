<?php
/**
 * File is loading ressource icons for included files or folders
 *
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once('locallib.php');
require_once("$CFG->libdir/filelib.php");

global $CFG;

$extension = required_param('extension', PARAM_TEXT);

$theme = $CFG->theme;
$theme_image_path = "../../theme/{$theme}/pix_core/";

if ($extension == 'folder') {
    $image = $theme_image_path . "f/folder.png";
} else if ($extension == 'lpd') {
    $image = $theme_image_path . "f/video.png";
} else {
    $image = $theme_image_path . file_extension_icon("fooooo." . $extension) . ".png";
}

header('content-type: image/png');

@$im = file_get_contents($image);
if ($im) {
    echo $im;
    exit;
}
