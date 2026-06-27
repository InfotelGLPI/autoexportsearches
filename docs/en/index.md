# Documentation — AutoExportSearches Plugin for GLPI
 
**License:** GNU GPL v3+  
**Author:** Infotel (Alban LESELLIER)  
**Repository:** https://github.com/InfotelGLPI/autoexportsearches

---

## Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
3. [Global Configuration](#global-configuration)
4. [Features](#features)
   - [Export Configurations](#export-configurations)
   - [Periodicity](#periodicity)
   - [Custom Criteria](#custom-criteria)
   - [E-mail Delivery](#e-mail-delivery)
   - [Downloading Generated Files](#downloading-generated-files)
   - [Automatic Task (Cron)](#automatic-task-cron)
5. [Rights Management](#rights-management)
6. [Uninstallation](#uninstallation)

---

## Overview

The **AutoExportSearches** plugin automatically exports the results of GLPI **saved searches** to **CSV** files. For each configured export, you can:

- Choose an existing **saved search** as the data source
- Define a **periodicity** for triggering the export (minutes, hours, days, weekly, monthly)
- Send the CSV file by **e-mail** to a recipient address
- Download the generated files directly from the GLPI interface
- Apply **dynamic date criteria adjustments** (e.g. first day of the month, first day of the week)

Exports are executed by a GLPI **automatic task** (`AutoexportsearchesExportconfigExport`).

---

## Installation

1. Download the plugin from [GitHub](https://github.com/InfotelGLPI/autoexportsearches) or the GLPI marketplace.
2. Extract the archive into the `plugins/` (or `marketplace/`) directory of your GLPI installation.
3. Log in to GLPI as an administrator.
4. Go to **Setup › Plugins**, then click **Install** and **Enable** for *AutoExportSearches*.

---

## Global Configuration

Access: **Setup › Plugins › AutoExportSearches › Configure**  
(Required right: `config`)

| Parameter | Description |
|-----------|-------------|
| **Storage folder** | Sub-folder within `GLPI_PLUGIN_DOC_DIR` where CSV files are generated (default: `autoexportsearches`) |
| **Retention period** | Number of months before automatic purge of old files (default: 3 months) |

---

## Features

### Export Configurations

Access: **Tools › Auto export searches**  
(Required right: `plugin_autoexportsearches_exportconfigs`)

Each **export configuration** links a saved search to an execution schedule:

| Field | Description |
|-------|-------------|
| **User** | GLPI user whose saved search will be used (the cron runs in this user's context) |
| **Profile** | Profile to use when the cron runs for this user |
| **Saved search** | GLPI saved search to export |
| **Periodicity** | Type and value of the export frequency |
| **Open days only** | If checked, the export is skipped on weekends (according to the GLPI work calendar) |
| **Recipient e-mail** | Address to which the CSV file is sent (optional) |
| **Active** | Enables or disables this export configuration |
| **Last export** | Date and time of the last executed export (read-only) |

---

### Periodicity

Five periodicity modes are available:

| Mode | Description |
|------|-------------|
| **Every X minutes** | Export triggered when the delay in minutes since the last export has elapsed |
| **Every X hours** | Export triggered when the delay in hours has elapsed |
| **Every X days** | Export triggered when the delay in days has elapsed (option: open days only) |
| **Weekly** | Export triggered on the selected day of the week (0 = Sunday … 6 = Saturday) |
| **Monthly** | Export triggered on the selected day of the month; if the month is shorter, runs on the last day |

> For the *Every X days* and *Monthly* modes, the **Open days only** option skips non-working days (except the last day of the month in monthly mode, to ensure at least one export per month).

---

### Custom Criteria

When creating or editing an export configuration, the plugin analyses the criteria of the saved search and offers **dynamic adjustments** for relative-date criteria:

| Criterion value | Available adjustment |
|-----------------|----------------------|
| Relative period (e.g. `-1MONTH`) | Replace the date with the **first day of the corresponding month** |
| Relative period (e.g. `-1WEEK`) | Replace the date with the **first day (Monday) of the corresponding week** |

These adjustments are stored in the `glpi_plugin_autoexportsearches_customsearchcriterias` table and applied automatically on each cron execution.

---

### E-mail Delivery

If a **recipient e-mail address** is set in the export configuration, the CSV file is automatically sent after each generation.

- **Sender**: address defined in the *Sender email* field of the GLPI mail notification settings (**Setup › Notifications › Email followups configuration**)
- **Subject**: `[GLPI] <search_name>_<date_time>.csv`
- **Attachment**: the generated CSV file

> If sending fails, an error message is displayed in the interface.

---

### Downloading Generated Files

Access: **Tools › Auto export searches › Files**  
(Required right: `plugin_autoexportsearches_accessfiles`)

This section lists the CSV files generated and stored on the server. Each file can be **downloaded** directly from the interface.

An automatic **purge task** (`AutoexportsearchesFilesDeleteFile`) automatically deletes files older than the configured retention period.

---

### Automatic Task (Cron)

| Name | Description |
|------|-------------|
| `AutoexportsearchesExportconfigExport` | Iterates over all active export configurations, checks whether the export is due according to the periodicity, generates the CSV, and sends the e-mail if configured |
| `AutoexportsearchesFilesDeleteFile` | Purges CSV files older than the retention period set in the configuration |

Tasks are visible and configurable under **Setup › Automatic actions**.

> The `AutoexportsearchesExportconfigExport` task is registered in **external mode** — it must be triggered by a system cron job (e.g. `php bin/console glpi:task:run`).

---

## Rights Management

Access: **Administration › Profiles › [profile] › Auto export searches tab**

| Right | Description |
|-------|-------------|
| `plugin_autoexportsearches_exportconfigs` | Manage export configurations (read, create, update, delete) |
| `plugin_autoexportsearches_accessfiles` | Access to the list and download of generated files |
| `plugin_autoexportsearches_configs` | Access to the global plugin configuration |

At installation, the Super-Admin profile receives all rights.

---

## Uninstallation

1. Go to **Setup › Plugins**.
2. Click **Disable** then **Uninstall** for *AutoExportSearches*.

> **Warning:** Uninstalling removes all plugin tables. CSV files already generated in `GLPI_PLUGIN_DOC_DIR/autoexportsearches/` are not automatically deleted.
