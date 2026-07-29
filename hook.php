<?php

/*
 -------------------------------------------------------------------------
 autoexportsearches plugin for GLPI
 Copyright (C) 2025-2026 by the autoexportsearches Development Team.

 https://github.com/InfotelGLPI/autoexportsearches
 -------------------------------------------------------------------------

 LICENSE

 This file is part of autoexportsearches.

 autoexportsearches is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
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

    // Remove the profile rights inserted at install (Profile::initProfile /
    // addRight); GLPI does not purge plugin rights automatically, so they would
    // otherwise linger in glpi_profilerights for every profile after uninstall.
    ProfileRight::deleteProfileRights([
        'plugin_autoexportsearches_exportconfigs',
        'plugin_autoexportsearches_accessfiles',
        'plugin_autoexportsearches_configs',
    ]);

    $rep_files_autoexportsearches = GLPI_PLUGIN_DOC_DIR . "/autoexportsearches";

    if (is_dir($rep_files_autoexportsearches)) {
        array_map('unlink', glob($rep_files_autoexportsearches . '/*'));
        rmdir($rep_files_autoexportsearches);
    }

    return true;
}

/**
 * Restrict the Exportconfig search list to the current user's own rows.
 *
 * The plugin is self-service: a non-elevated user (no core `config` UPDATE right)
 * must only see the export configs they own. The Search engine does not scope this
 * class (it has no entities_id), so without this every user holding the read right
 * would list everyone's exports (sendto, targeted saved search, impersonated user).
 * Mirrors Exportconfig::validateExportInput() and the canXItem() ownership guards.
 *
 * @param string $itemtype
 * @return string SQL WHERE fragment (empty string = no restriction)
 */
function plugin_autoexportsearches_addDefaultWhere($itemtype)
{
    if ($itemtype === Exportconfig::class && !Session::haveRight('config', UPDATE)) {
        $table = Exportconfig::getTable();
        return "`$table`.`users_id` = " . (int) Session::getLoginUserID();
    }
    return '';
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

function plugin_autoexportsearches_pre_item_purge(CommonDBTM $item)
{
    global $DB;
    // Runs BEFORE the core zeroes the savedsearches_id foreign key
    // (cleanRelationData, triggered by getDatabaseRelations): at this point
    // $item->fields['id'] still identifies the SavedSearch being purged and the
    // related export configs still point to it, so we can delete only its own
    // rows. Doing this in item_purge (where the FK is already 0) would instead
    // match every row with savedsearches_id = 0 and wipe unrelated users' export
    // configs created without a saved search.
    if ($item::getType() === SavedSearch::getType()) {
        $DB->delete('glpi_plugin_autoexportsearches_exportconfigs', [
            'savedsearches_id' => $item->fields['id'],
        ]);
        $DB->delete('glpi_plugin_autoexportsearches_customsearchcriterias', [
            'savedsearches_id' => $item->fields['id'],
        ]);
    }
}

function plugin_autoexportsearches_item_purge(CommonDBTM $item)
{
    global $DB;
    if ($item::getType() === Exportconfig::getType()) {
        $DB->delete('glpi_plugin_autoexportsearches_customsearchcriterias', [
            'exportconfigs_id' => $item->fields['id'],
        ]);
    }
}
