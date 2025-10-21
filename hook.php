<?php

/*
 -------------------------------------------------------------------------
 autoexportsearches plugin for GLPI
 Copyright (C) 2020-2025 by the autoexportsearches Development Team.

 https://github.com/InfotelGLPI/autoexportsearches
 -------------------------------------------------------------------------

 LICENSE

 This file is part of autoexportsearches.

 autoexportsearches is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 autoexportsearches is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with autoexportsearches. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

use GlpiPlugin\Autoexportsearches\Config;
use GlpiPlugin\Autoexportsearches\Customsearchcriteria;
use GlpiPlugin\Autoexportsearches\Exportconfig;
use GlpiPlugin\Autoexportsearches\Files;
use GlpiPlugin\Autoexportsearches\Profile;
use function Safe\mkdir;

function plugin_autoexportsearches_install()
{

    $migration = new Migration(PLUGIN_AUTOEXPORTSEARCH_VERSION);

    // Adds the right(s) to all pre-existing profiles with no access by default
    Profile::initProfile();

    // Grants full access to profiles that can update the Config (super-admins)
    $migration->addRight(Exportconfig::$rightname, ALLSTANDARDRIGHT, [Config::$rightname => UPDATE]);

    Exportconfig::install($migration);

    Config::install($migration);

    Customsearchcriteria::install($migration);

    Files::install($migration);

    $migration->executeMigration();

    $rep_files_autoexportsearches = GLPI_PLUGIN_DOC_DIR . "/autoexportsearches";
    if (!is_dir($rep_files_autoexportsearches)) {
        mkdir($rep_files_autoexportsearches);
    }

    Profile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

    return true;
}

// Uninstall process for plugin : need to return true if succeeded
/**
 * @return bool
 * @throws GlpitestSQLError
 */
function plugin_autoexportsearches_uninstall()
{

    Exportconfig::uninstall();

    Config::uninstall();

    Customsearchcriteria::uninstall();

    CronTask::unregister("autoexportsearches");

    $rep_files_autoexportsearches = GLPI_PLUGIN_DOC_DIR . "/autoexportsearches";

    if (is_dir($rep_files_autoexportsearches)) {
        array_map('unlink', glob($rep_files_autoexportsearches . '/*'));
        rmdir($rep_files_autoexportsearches);
    }

    return true;
}

// Define dropdown relations
/**
 * @return array|string[][]
 */
function plugin_autoexportsearches_getDatabaseRelations()
{
    $plugin = new Plugin();
    if ($plugin->isActivated("autoexportsearches")) {
        return [
            "glpi_savedsearches" => ["glpi_plugin_autoexportsearches_exportconfigs" => "savedsearches_id"],
            "glpi_users" => ["glpi_plugin_autoexportsearches_exportconfigs" => "users_id"],
        ];
    } else {
        return [];
    }
}

function plugin_autoexportsearches_item_purge(CommonDBTM $item)
{
    global $DB;
    if ($item::getType() === SavedSearch::getType()) {
        // relation field set to 0 by the core when deleted (because of getDatabaseRelations?)
        $DB->delete('glpi_plugin_autoexportsearches_exportconfigs', [
            'savedsearches_id' => 0,
        ]);
        $DB->delete('glpi_plugin_autoexportsearches_customsearchcriterias', [
            'savedsearches_id' => 0,
        ]);
    } elseif ($item::getType() === Exportconfig::getType()) {
        $DB->delete('glpi_plugin_autoexportsearches_customsearchcriterias', [
            'exportconfigs_id' => $item->fields['id'],
        ]);
    }
}
