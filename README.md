## AutoExportSearches plugin for GLPI

[![License](https://img.shields.io/badge/License-GNU%20v2-blue.svg?style=flat-square)](https://github.com/InfotelGLPI/autoExportSearches/blob/master/LICENSE)
[![Web](https://img.shields.io/badge/Web-Infotel-blue.svg?style=flat-square)](https://blogglpi.infotel.com)
[![Translate](https://img.shields.io/badge/Translate-Transifex-cyan)](https://explore.transifex.com/infotelGLPI/GLPI_autoexportsearches/)

---

### English

This plugin allows for the automatic export of saved search results to CSV files via a cron task.

* For each configured export, select a **saved search** as the data source and set a **periodicity** (every X minutes, hours, days, weekly, or monthly).
* Optionally send the generated CSV by **e-mail** to a recipient address after each export.
* Apply **dynamic date criteria adjustments** (first day of the month, first day of the week) on top of the saved search's relative-date criteria.
* **Download** generated files directly from the GLPI interface (**Tools › Auto export searches › Files**).
* An automatic **purge task** deletes files older than the configured retention period.

The export configuration interface is accessible from the **Tools** menu.  
The sender e-mail address is taken from the *Sender email* field in **Setup › Notifications › Email followups configuration**.

**[Full English documentation →](docs/en/index.md)**

---

### Français

Ce plugin permet d'exporter automatiquement le résultat de recherches sauvegardées vers des fichiers CSV avec une action automatique (cron).

* Pour chaque export configuré, sélectionnez une **recherche sauvegardée** et définissez une **périodicité** (toutes les X minutes, heures, jours, hebdomadaire, mensuel).
* Envoyez optionnellement le CSV par **e-mail** à une adresse destinataire après chaque export.
* Appliquez des **ajustements de critères de date dynamiques** (premier jour du mois, premier jour de la semaine).
* **Téléchargez** les fichiers générés depuis l'interface GLPI (**Outils › Auto export searches › Fichiers**).
* Une **tâche de purge** automatique supprime les fichiers plus anciens que la durée de conservation configurée.

L'interface de configuration des exports est accessible dans le menu **Outils**.  
L'adresse utilisée pour l'expéditeur est celle définie dans le champ *Courriel de l'expéditeur* de la configuration des notifications (**Configuration › Notifications › Configuration des suivis par courriel**).

**[Documentation complète en français →](docs/fr/index.md)**
