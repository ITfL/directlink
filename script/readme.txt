/**
 * @package    mod
 * @subpackage directlink
 * @copyright  2012 Michael Hamatschek, Hans-Christian Sperker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


Attention: We are not liable for any loss or damage of software and/or data saved on storage systems of appliances delivered by us or other appliances.

Run script before installing plugin in moodle.
This script will be ensure your host system will supply all necessary requirements for running directlink properly.
Some important packages (libmcrypt, smbfs, cifs-utils) will be installed while running this script.
www-data will be added to sudoers list and a folder for mounting shares will be created.

