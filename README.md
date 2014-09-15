**Welcome to DirectLink! Willkommen bei DirectLink!**

(DirectLink is a Moodle activity module called Network-Drive / DirectLink ist eine Moodle Aktivität die Netzlaufwerk genannt wird)
We renamed the activity module from DirectLink to Network-Drive in Moodle, for a better understanding of the supplied functionality.
Um ein besseres Verständnis der angebotenen Funktionalität zu ermöglichen, wurde das Plugin in Moodle von DirectLink in Netzlaufwerk umbenannt.

#General

We will provide further information via the integrated wiki functionality of github e.g. preparation to install the module.
Wir werden weitere Information über das in github-integrierte Wiki zur Verfügung stellen z.B. welche Vorbereitungen für die Installtion des Moduls getroffen werden müssen.

The goal of this plugin is the integration of samba-fileshares into moodle. Reasons for this development are supplying a huge amount of files in courses, easier management of files and folders within the operating system, separation of web- and file-server, smaller backups and actuality (updated instantly after refreshing browser) of coursefiles.

# English

## Activity module Network-Drive

To integrate the content of Windows shares into Moodle courses you can use the activity module **Network-Drive**. Files and folders of Windows shares are not stored in the database to reduce load off the data-storage and backups. Changes via the filesystem to the content of the share are visible in the Moodle course after refreshing the browser.

###System requirements:
* **Operating system:**			Linux
* **Webserver:**			Apache
* **Moodle-Version:**			> 2.0
* **Additional software:**	
	* PHP module: MCrypt (data encryption) 
	* Samba with smbclient for working with Windows shares

###Further requirements:

* **Permissions:**	www-data has to be the owner of the directory, where the shares are mounted into at the Moodle server.
* **Default-User:**	There must be a user with read permissions to all mounted shares. This user will establish, keep alive and restore the connection between the course and the certain share. Username and password of this (default) user are deposed in the activity module administration.
* **Salt and Password:** The file config.php.changeme has to be edited: Replace the salt and password with custom hexadecimal values. Rename the file to config.php

###Administration settings

Via Moodle Site-Administration you have to set (or change) some default values in Plugins – Activity Modules – Network-Drive.

* **Path to smbclient:** Local path to smbclient. Smbclient is used to access SMB/CIFS resources on servers (Windows shares). Default: /usr/bin/smbclient
* **Mountpoint:** Path where shares are mounted into (www-data must be owner of this directory).
* **Default fileserver:** (of your institution) Please enter the fully qualified domain name of your server here.
* **Default domain:** (of your institution) Please enter your domain name here.
* **Default user:** User name of your default user. Must have read permission to your mounted network shares to establish an keep alive the smb connections.
* **Password for default user:** Password for default user.
* **Deny external hosts:** 	It's also possible to connect shares of other hosts than the default domain. By default it's forbidden (= Default: Yes), to avoid a negative impact to the performance of Moodle server.
* **Description required:** Similiar to other activities and resources. Default: Yes.
* **Filechoose:** 	Enter filetypes into the textbox, which not be displayed in the filechooser of the Network-Drive or inside the course. 
There are two possibilities to specifiy files: Explicit filenames e.g. .htaccess, Thumbs.db, .git, ...
 or you can use regular expressions: /.*\.php/, /^~.*$/
* **Contact mail:** Mail-adress of your Moodle support, this adress will be displayed in module-specific error messages.

# German / Deutsch

## Arbeitsmaterial Netzlaufwerk

Mit dem Arbeitsmaterial Netzlaufwerk können Windows-Freigaben in einen Kurs integriert werden. Dateien und Verzeichnisse aus diesen Windows-Freigaben werden hierbei nicht in der Moodle-Datenbank gespeichert, um Datenhaltung und -sicherung zu entlasten. Damit werden Veränderungen an Dateien und Verzeichnissen, die über das Arbeitsmaterial Netzlaufwerk in Moodle-Kurse eingebunden sind live sichtbar.

###Systemvoraussetzungen:
* **Betriebssystem:**			Linux
* **Webserver:**			Apache
* **Moodle-Version:**			> 2.0
* **Zusätzliche Software:**	
	* PHP-Modul: MCrypt (Datenverschlüsselungsmodul) 
	* Samba mit smbclient zum Arbeiten mit Windows- Freigaben

###Weitere Voraussetzungen:

* **Rechte:**	www-data muss Besitzer des Verzeichnisses auf dem Moodle-Server sein, in den die Freigaben verfügbar gemacht (gemountet) werden.
* **Default-User:**	Es muss einen Benutzer im Netzwerk geben, der lesend auf alle Windows-Freigaben zugreifen kann, um die Verbindung  zwischen dem jeweiligen Kurs und der Freigabe aufzubauen, aufrechtzuerhalten und wiederherzustellen. Benutzername und Passwort dieses Nutzers werden in den Einstellungen für das Netzlaufwerk in der Website-Administration in Moodle hinterlegt.
* **Salt und Passwort:** Die Datei config.php.changeme muss angepasst werden: Salt und Password sollten aus Sicherheitsgründen mit eigenen hexadezimalen Werten überschrieben werden. Außerdem muss die Datei in config.php umbenannt werden

###Einstellungen in der Administration

In der Website-Administration unter Plugins – Aktivitäten – Netzlaufwerk müssen Sie folgende Werte einstellen bzw. Standardwerte verändern:

* **Pfad zum smbclient:** Lokaler Serverpfad zum smbclient. Der smbclient dient zum Zugriff auf Laufwerks-Freigaben unter Windows oder Samba. Standard: /usr/bin/smbclient
* **Mountpoint:** Pfad, in dem die Freigaben verfügbar gemacht (gemountet) werden. Achtung: Der Ordner muss www-data gehören.
* **Standard-Dateiserver:** (in Ihrer Institution) Tragen Sie hier die Bezeichnung des Standard-Dateiservers für die Freigaben ein.
An der Universität Bamberg ist das zum Beispiel daten.uni-bamberg.de
* **Standard-Domäne:** an Ihrer Institution 	Geben Sie die Standard-Domäne Ihrer Institution an. An der Universität Bamberg ist das zum Beispiel uni-bamberg.de.
* **Standard-Nutzer:** 	Benutzername des Standard-Nutzers für den lesenden Zugriff auf die Freigaben (smb-Verbindung), damit die Verbindungen zu den Freigaben wiederhergestellt werden können, nachdem das System neu gestartet wurde.
* **Standard-Passwort:** 	Passwort für den Standard-Nutzer.
* **Externe Hosts verbieten:** 	Es können auch noch Freigaben von weiteren Hosts (Computer/Server) eingebunden werden. Standardmäßig wird dies verboten (= Standard: Ja), um eine Leistungsbeeinträchtigung des Moodle-Servers durch eine Verbindung zu langsamen Computern/Servern zu vermeiden.
* **Beschreibung notwendig:** 	Auswahl wie bei allen Aktivitäten und Materialien. Standard: Ja.
* **Dateiauswahl:** 	Geben Sie Dateitypen in das Textfeld ein, die in der Dateiauswahl des Netzlaufwerks nicht angezeigt werden sollen.
Es gibt zwei Möglichkeiten Dateien anzugeben: Explizite Dateinamen sind zum Beispiel .htaccess, Thumbs.db, .git, etc. Wohingegen Beispiele für reguläre Ausdrücke nachfolgend aufgelistet werden: /.*\.php/, /^~.*$/
* **Kontakt-Mail:** 	Mail-Adresse der Supportstelle an der jeweiligen Institution, z.B. Rechenzentrum. Die E-Mailadresse wird in Fehlermeldungen angegeben.