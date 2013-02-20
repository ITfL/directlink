Welcome to the DirectLink!

The content of this plugin is the integration of samba-fileshares into moodle. Reasons for this development are supplying a huge amount of files in courses, easier management of files and folders within the operating system, separation of web- and file-server, smaller backups and actuality (updated instantly after refreshin browser) of coursefiles.

# German / Deutsch

# Arbeitsmaterial Netzlaufwerk

Mit dem Arbeitsmaterial Netzlaufwerk können Windows-Freigaben in einen Kurs integriert werden. Dateien und Verzeichnisse aus diesen Windows-Freigaben werden hierbei nicht in der Moodle-Datenbank gespeichert, um Datenhaltung und -sicherung zu entlasten. Damit werden Veränderungen an Dateien und Verzeichnissen, die über das Arbeitsmaterial Netzlaufwerk in Moodle-Kurse eingebunden sind live sichtbar.

###Systemvoraussetzungen:
* **Betriebssystem:**			Linux
* **Moodle-Version:**			> 2.0
* **Zusätzliche Software:**	PHP-Modul: MCrypt (Datenverschlüsselungsmodul) 
						Samba mit smbclient zum Arbeiten mit Windows- Freigaben
###Weitere Voraussetzungen:

* **Rechte:**	www-data muss Besitzer des Verzeichnisses auf dem Moodle-Server sein, in den die Freigaben verfügbar gemacht (gemountet) werden.
* **Default-User:**	Es muss einen Benutzer im Netzwerk geben, der lesend auf alle Windows-Freigaben zugreifen kann, um die Verbindung  zwischen dem jeweiligen Kurs und der Freigabe aufzubauen, aufrechtzuerhalten und wiederherzustellen. Benutzername und Passwort dieses Nutzers werden in den Einstellungen für das Netzlaufwerk in der Website-Administration in Moodle hinterlegt.
Installation

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