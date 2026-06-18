# Documentation — Plugin AutoExportSearches pour GLPI

**Licence :** GNU GPL v2+  
**Auteur :** Infotel (Alban LESELLIER)  
**Dépôt :** https://github.com/InfotelGLPI/autoexportsearches

---

## Table des matières

1. [Présentation](#présentation)
2. [Installation](#installation)
3. [Configuration globale](#configuration-globale)
4. [Fonctionnalités](#fonctionnalités)
   - [Configurations d'export](#configurations-dexport)
   - [Périodicité](#périodicité)
   - [Critères personnalisés](#critères-personnalisés)
   - [Envoi par e-mail](#envoi-par-e-mail)
   - [Téléchargement des fichiers générés](#téléchargement-des-fichiers-générés)
   - [Action automatique (cron)](#action-automatique-cron)
5. [Gestion des droits](#gestion-des-droits)
6. [Désinstallation](#désinstallation)

---

## Présentation

Le plugin **AutoExportSearches** permet d'exporter automatiquement les résultats de **recherches sauvegardées** GLPI vers des fichiers **CSV**. Pour chaque export configuré, il est possible de :

- Choisir une **recherche sauvegardée** existante comme source de données
- Définir une **périodicité** de déclenchement (minutes, heures, jours, semaine, mois)
- Envoyer le fichier CSV par **e-mail** à une adresse destinataire
- Télécharger les fichiers générés directement depuis l'interface GLPI
- Appliquer des **ajustements de critères** de date dynamiques (ex. : premier jour du mois, premier jour de la semaine)

L'export est exécuté par une **tâche automatique** GLPI (`AutoexportsearchesExportconfigExport`).

---

## Installation

1. Télécharger le plugin depuis [GitHub](https://github.com/InfotelGLPI/autoexportsearches) ou la marketplace GLPI.
2. Décompresser l'archive dans le dossier `plugins/` (ou `marketplace/`) de votre installation GLPI.
3. Se connecter à GLPI en tant qu'administrateur.
4. Aller dans **Configuration › Plugins**, cliquer sur **Installer** puis **Activer** pour *AutoExportSearches*.

---

## Configuration globale

Accès : **Configuration › Plugins › AutoExportSearches › Configurer**  
(Droit requis : `config`)

| Paramètre | Description |
|-----------|-------------|
| **Dossier de stockage** | Sous-dossier dans `GLPI_PLUGIN_DOC_DIR` où les fichiers CSV sont générés (par défaut : `autoexportsearches`) |
| **Durée de conservation** | Nombre de mois avant purge automatique des fichiers anciens (par défaut : 3 mois) |

---

## Fonctionnalités

### Configurations d'export

Accès : **Outils › Auto export searches**  
(Droit requis : `plugin_autoexportsearches_exportconfigs`)

Chaque **configuration d'export** associe une recherche sauvegardée à une règle d'exécution :

| Champ | Description |
|-------|-------------|
| **Utilisateur** | Utilisateur GLPI dont la recherche sauvegardée sera utilisée (le cron s'exécute dans le contexte de cet utilisateur) |
| **Profil** | Profil à utiliser lors de l'exécution du cron pour cet utilisateur |
| **Recherche sauvegardée** | Recherche sauvegardée GLPI à exporter |
| **Périodicité** | Type et valeur de la fréquence d'export |
| **Jours ouvrés uniquement** | Si coché, l'export est ignoré les week-ends (selon le calendrier de travail GLPI) |
| **Adresse e-mail destinataire** | Adresse à laquelle le fichier CSV est envoyé (optionnel) |
| **Actif** | Active ou désactive cette configuration d'export |
| **Dernier export** | Date et heure du dernier export exécuté (lecture seule) |

---

### Périodicité

Cinq modes de périodicité sont disponibles :

| Mode | Description |
|------|-------------|
| **Toutes les X minutes** | Export déclenché lorsque le délai en minutes depuis le dernier export est écoulé |
| **Toutes les X heures** | Export déclenché lorsque le délai en heures est écoulé |
| **Tous les X jours** | Export déclenché lorsque le délai en jours est écoulé (option : jours ouvrés uniquement) |
| **Hebdomadaire** | Export déclenché le jour de la semaine sélectionné (0 = dimanche … 6 = samedi) |
| **Mensuel** | Export déclenché le jour du mois sélectionné ; si le mois est plus court, s'exécute le dernier jour du mois |

> Pour les modes *Tous les X jours* et *Mensuel*, l'option **Jours ouvrés uniquement** permet d'ignorer les exports les jours non travaillés (sauf dernier jour du mois pour le mode mensuel, afin de garantir au moins un export par mois).

---

### Critères personnalisés

Lors de la création ou de la modification d'une configuration d'export, le plugin analyse les critères de la recherche sauvegardée et propose des **ajustements dynamiques** pour les critères de type date relatif :

| Valeur de critère | Ajustement disponible |
|-------------------|-----------------------|
| Période relative (ex. : `-1MONTH`) | Remplacer la date par le **premier jour du mois** correspondant |
| Période relative (ex. : `-1WEEK`) | Remplacer la date par le **premier jour (lundi) de la semaine** correspondante |

Ces ajustements sont stockés dans la table `glpi_plugin_autoexportsearches_customsearchcriterias` et appliqués automatiquement à chaque exécution du cron.

---

### Envoi par e-mail

Si une **adresse e-mail destinataire** est renseignée dans la configuration d'export, le fichier CSV est envoyé automatiquement après chaque génération.

- **Expéditeur** : adresse définie dans le champ *Courriel de l'expéditeur* de la configuration des notifications GLPI (**Configuration › Notifications › Configuration des suivis par courriel**)
- **Sujet** : `[GLPI] <nom_recherche>_<date_heure>.csv`
- **Pièce jointe** : le fichier CSV généré

> Si l'envoi échoue, un message d'erreur est affiché dans l'interface.

---

### Téléchargement des fichiers générés

Accès : **Outils › Auto export searches › Fichiers**  
(Droit requis : `plugin_autoexportsearches_accessfiles`)

Cette section liste les fichiers CSV générés et stockés sur le serveur. Chaque fichier peut être **téléchargé** directement depuis l'interface.

Une **tâche automatique de purge** (`AutoexportsearchesFilesDeleteFile`) supprime automatiquement les fichiers plus anciens que la durée de conservation configurée.

---

### Action automatique (cron)

| Nom | Description |
|-----|-------------|
| `AutoexportsearchesExportconfigExport` | Parcourt toutes les configurations d'export actives, vérifie si l'export est dû selon la périodicité, génère le CSV et envoie l'e-mail si configuré |
| `AutoexportsearchesFilesDeleteFile` | Purge les fichiers CSV plus anciens que la durée de conservation définie en configuration |

Les tâches sont visibles et configurables dans **Configuration › Actions automatiques**.

> La tâche `AutoexportsearchesExportconfigExport` est enregistrée en **mode externe** — elle doit être déclenchée par un `cron` système (ex. : `php bin/console glpi:task:run`).

---

## Gestion des droits

Accès : **Administration › Profils › [profil] › onglet Auto export searches**

| Droit | Description |
|-------|-------------|
| `plugin_autoexportsearches_exportconfigs` | Gestion des configurations d'export (lecture, création, modification, suppression) |
| `plugin_autoexportsearches_accessfiles` | Accès à la liste et au téléchargement des fichiers générés |
| `plugin_autoexportsearches_configs` | Accès à la configuration globale du plugin |

À l'installation, le profil Super-Admin reçoit tous les droits.

---

## Désinstallation

1. Aller dans **Configuration › Plugins**.
2. Cliquer sur **Désactiver** puis **Désinstaller** pour *AutoExportSearches*.

> **Attention :** La désinstallation supprime toutes les tables du plugin. Les fichiers CSV déjà générés dans `GLPI_PLUGIN_DOC_DIR/autoexportsearches/` ne sont pas supprimés automatiquement.
