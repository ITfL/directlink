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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.


/**
 * German strings for directlink
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package  mod
 * @subpackage directlink
 * @copyright 2012 onwards Michael Hamatschek and Hans-Christian Sperker {@link http://www.uni-bamberg.de/itfl-service}
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * general strings
 */

$string['connection_properties'] = 'Verbindungseinstellungen'; 
$string['modulename'] = 'Netzlaufwerk'; // Anzeigename in Aktivitäten-Auswahl, Website Administration
$string['modulenameplural'] = 'Netzlaufwerke'; //Nutzung nicht gefunden
$string['modulename_help'] = 'Das Netzlaufwerk-Modul kann genutzt werden, um Dateien und Ordner von Netzlaufwerken direkt in Moodle einzubinden. Bitte kontaktieren Sie Ihren Moodle-Support.';
$string['Netzlaufwerkfieldset'] = 'Custom example fieldset';
$string['Netzlaufwerkname'] = 'Netzlaufwerk'; //Nutzung nicht gefunden
$string['Netzlaufwerkname_help'] = 'This is the content of the help tooltip associated with the Netzlaufwerkname field. Markdown syntax is supported.';
$string['Netzlaufwerk'] = 'Netzlaufwerk'; //Nutzung nicht gefunden
$string['pluginadministration'] = 'Netzlaufwerk Administration';
$string['pluginname'] = 'Netzlaufwerk'; //Nutzung nicht gefunden

/**
 * admin strings
 */

$string['smbclient_path'] = 'Pfad zum smbclient';
$string['smbclient_path_desc'] = 'Lokaler Serverpfad zum smbclient (/usr/bin/smbclient).';

$string['mount_point'] = 'Mountpoint';
$string['moint_point_desc'] = 'Pfad, in den die Freigaben gemountet werden. Der Ordner muss www-data gehören.';

$string['fileserver'] = 'Standard-Dateiserver';
$string['fileserver_desc'] = 'Standard-Server.';

$string['domain'] = 'Standard-Domäne';
$string['domain_desc'] = 'Standard-Domäne der Umgebung.';

$string['default_user_name'] = 'Standard-Nutzer';
$string['default_user_name_desc'] = 'Standard-Nutzer für die smb-Verbindung.';

$string['default_user_pass'] = 'Standard-Passwort';
$string['default_user_pass_desc'] = 'Passwort für den Standard-Nutzer.';

$string['filechoose_ignore'] = 'Dateiauswahl';
$string['filechoose_ignore_desc'] = 'Diese Dateitypen werden in der Dateiauswahl des Netzlaufwerks nicht angezeigt.';

$string['deny_external_hosts'] = 'Externe Hosts verbieten';
$string['deny_external_hosts_desc'] = 'Ist diese Option gewählt können nur Freigaben innerhalb des angegebenen Servers gewählt werden!';

$string['desc_required'] = 'Beschreibung notwendig';
$string['desc_required_desc'] = 'Legt fest, ob die Beschreibung ein Pflichtfeld ist.';

$string['admin_mail'] = 'Kontakt-Mail';
$string['admin_mail_desc'] = 'Mail-Adresse des Moodle-Supports.';

/**
 * plugin strings
 */

$string['edit_foreign_private_share'] = 'Sie können Freigaben anderer Nutzer/innen nicht editieren.'; 
 
$string['existing_connections'] = 'Vorhandene Verbindungen';
$string['existing_connections_desc'] = 'Liste bereits bestehender Verbindungen.';

$string['change_template']='Als Vorlage nutzen';
$string['use_in_this_course']='Hier freigeben';

$string['new_connection'] = 'Erstelle neue Verbindung';

$string['private'] = 'Eigene Freigaben';

$string['course'] = 'Fremde, öffentliche Freigaben dieses Kurses';

$string['connection_name'] = 'Verbindungsname';
$string['connection_name_help'] = 'Verbindungsname, der im Tab <b>Verbindungen verwalten</b> und im Menü <b>Vorhandene Verbindungen</b> angezeigt wird.';

$string['name'] = 'Name';

$string['description'] = 'Beschreibung';
$string['description_help'] = 'Beschreibung des eingefügten Netzlaufwerks.';

$string['server'] = 'Server';

/**
 * server Standard-from admin settings
 */

$string['user_share'] = 'Freigabe';

$string['share_user'] = 'Nutzerkennung';

$string['share_user_pwd'] = 'Passwort';

$string['user_name'] = 'Vor- und Nachname';

$string['private_share'] = 'Private Freigabe';
$string['private_share_desc'] = 'Nur Sie können die angelegte Freigabe im Tab <b>Verbindungen verwalten</b> und im Menü <b>Vorhandene Verbindungen</b> sehen.';

$string['course_share'] = 'Kurs-Freigabe';
$string['course_share_desc'] = 'Alle Moderator/innen können die angelegte Freigabe im Tab <b>Verbindungen verwalten</b> und im Menü <b>Vorhandene Verbindungen</b> sehen.';

$string['test_credentials'] = 'Prüfe Verbindungsdaten';

$string['discard_credentials'] = 'Verwerfe Verbindungsdaten';

$string['warning_change_connection'] = 'Eine Änderung der Verbindung führt zum Verlust der unten angegebenen Daten! Dennoch weiter?';

$string['file'] = 'Datei';
$string['file_desc'] = 'Eine Datei wird eingebunden.';

$string['folder'] = 'Verzeichnis';
$string['folder_desc'] = 'Ein Verzeichnis wird eingebunden.';

$string['content'] = 'Inhalt';
$string['content_desc'] = 'Inhalte eines Verzeichnisses werden eingebunden.';

$string['choose_ressource'] = 'Ressource wählen';

$string['empty_folder'] = 'Verzeichnis ist leer';

$string['choose_file'] = 'Inhalt';


/**
 * js content / manage
 */

$string['js_confirm'] = 'Sind Sie sicher, dass Sie eine andere Verbindung wählen möchten?';

$string['js_new_connection'] = 'Neue Verbindung';

$string['js_manage_connections'] = 'Verbindungen verwalten';
$string['js_manage_share_type'] = 'Verbindungstyp';
$string['js_manage_share_type_public_this'] = '(dieser Kurs)';
$string['js_manage_share_type_public_other'] = '(anderer Kurs)';
$string['js_manage_share_type_private'] = 'private Freigabe';
$string['js_manage_actions'] = 'Optionen';
$string['js_manage_save'] = 'Änderungen speichern';
$string['js_manage_discard'] = 'Verwerfe Änderungen';

$string['js_load_data'] = 'Daten werden vom Server geladen.';

$string['manage_connection_info'] = 'Verbindungsinformation:';
$string['manage_connection_course'] = 'Kursname';
$string['manage_no_reference'] = 'Keine Verweise auf diese Verbindung.';
$string['manage_processing'] = 'Änderungen werden angewendet';

$string['manage_changes_success'] = 'Änderungen erfolgreich.';
$string['manage_changes_problem'] = 'Änderungen konnten nicht angewendet werden. </br> Entweder Kombination Nutzerkennung/Passwort falsch </br> oder Nutzer/in hat keine Zugriffsrechte auf die Freigabe.';

//jquery adaption

$string['jq_manage_search'] = 'Suche:';
$string['jq_manage_previous'] = 'Vorherige';
$string['jq_manage_next'] = 'Nächste';
$string['jq_show'] = 'Zeige _MENU_ Einträge';
$string['jq_show_entries'] = 'Zeige _START_ bis _END_ von _TOTAL_ Verbindungen';


/**
 * errors
 */

$string['validation_error'] = 'Fehler während Validierung aufgetreten.'; 

$string['immutable_field_domain_changed'] = 'Achtung: Nicht-veränderbares Feld <b>Domäne</b> wurde geändert.'; 
$string['immutable_field_user_share_changed'] = 'Achtung: Nicht-veränderbares Feld <b>Freigabe</b> wurde geändert.'; 
$string['immutable_field_share_user_changed'] = 'Achtung: Nicht-veränderbares Feld <b>Nutzerkennung</b> wurde geändert.'; 

$string['connection_error_default_user'] = 'Standard-Nutzer kann sich nicht auf die angegebene Freigabe verbinden.</br> Bitte kontaktieren Sie ';

$string['connection_error_user'] = 'Bitte überprüfen Sie die Nutzerdaten. </br> Entweder Kombination Nutzerkennung/Passwort falsch </br> oder Nutzer/in hat keine Zugriffsrechte auf die Freigabe. ';

$string['file_doesnt_exist'] = 'Datei existiert nicht. </br> Bitte kontaktieren Sie die Kursleitung.';

$string['folder_doesnt_exist'] = 'Verzeichnis existiert nicht. </br> Bitte kontaktieren Sie die Kursleitung.';

$string['file_choose_error'] = 'Bitte wählen Sie eine Datei/Verzeichnis.';

$string['link_not_found'] = 'Netzlaufwerk nicht gefunden.';

$string['no_permission'] ='Keine Zugriffsberechtigung für diese Datei/Verzeichnis!';

$string['change_connection_error'] = 'Änderungen nicht möglich, da Verbindung verwendet wird.';

/**
 * view file info
 */

$string['file_name'] = 'Name';

$string['file_size'] = 'Größe';

$string['file_changed'] = 'Letzte Änderungen';
