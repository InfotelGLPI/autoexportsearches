<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Autoexportsearches plugin for GLPI
 Copyright (C) 2020-2022 by the Autoexportsearches Development Team.

 https://github.com/InfotelGLPI/autoexportsearches
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Autoexportsearches.

 Autoexportsearches is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Autoexportsearches is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Autoexportsearches. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * @return bool
 * @throws \GlpitestSQLError
 */
function plugin_autoexportsearches_install()
{
    global $DB;
    if (!$DB->tableExists("glpi_plugin_autoexportsearches_configs")) {
        $DB->runFile(PLUGIN_AUTOEXPORTSEARCH_DIR . "/install/sql/empty-2.1.0.sql");
    } else {
        if (!$DB->fieldExists("glpi_plugin_autoexportsearches_exportconfigs", "sendto")) {
            $DB->runFile(PLUGIN_AUTOEXPORTSEARCH_DIR . "/install/sql/update-2.0.0.sql");
        }
        if ($DB->fieldExists("glpi_plugin_autoexportsearches_exportconfigs", "searches_id")) {
            $DB->runFile(PLUGIN_AUTOEXPORTSEARCH_DIR . "/install/sql/update-2.0.1.sql");
        }
        if (!$DB->tableExists('glpi_plugin_autoexportsearches_customsearchcriterias')) {
            $DB->runFile(PLUGIN_AUTOEXPORTSEARCH_DIR . "/install/sql/update-2.1.0.sql");
        }
    }

    $rep_files_autoexportsearches = GLPI_PLUGIN_DOC_DIR . "/autoexportsearches";
    if (!is_dir($rep_files_autoexportsearches)) {
        mkdir($rep_files_autoexportsearches);
    }

    CronTask::Register(
        'PluginAutoexportsearchesExportconfig',
        'AutoexportsearchesExportconfigExport',
        DAY_TIMESTAMP,
        ['mode' => CronTask::MODE_EXTERNAL]
    );
    PluginAutoexportsearchesProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
    PluginAutoexportsearchesProfile::initProfile();


    CronTask::Register(
        'PluginAutoexportsearchesFiles',
        'DeleteFile',
        MONTH_TIMESTAMP,
        ['state' => CronTask::STATE_DISABLE]
    );

    return true;
}

// Uninstall process for plugin : need to return true if succeeded
/**
 * @return bool
 * @throws \GlpitestSQLError
 */
function plugin_autoexportsearches_uninstall()
{
    global $DB;

    // Plugin tables deletion
    $tables = [
        "glpi_plugin_autoexportsearches_exportconfigs",
        "glpi_plugin_autoexportsearches_configs",
        "glpi_plugin_autoexportsearches_customsearchcriterias",
    ];
    foreach ($tables as $table) {
        $DB->query("DROP TABLE IF EXISTS `$table`;");
    }
    CronTask::unregister("autoexportsearches");
    $rep_files_autoexportsearches = GLPI_PLUGIN_DOC_DIR . "/autoexportsearches";


    if (is_dir($rep_files_autoexportsearches)) {
        array_map('unlink', glob($rep_files_autoexportsearches . '/*'));
        rmdir($rep_files_autoexportsearches);
    }

    return true;
}

// Define Dropdown tables to be manage in GLPI
/**
 * @return array
 */
function plugin_autoexportsearches_getDropdown()
{
    $plugin = new Plugin();

    if ($plugin->isActivated("autoexportsearches")) {
        return [
        ];
    } else {
        return [];
    }
}

// Hook done on purge item case
/**
 * @param $item
 */
function plugin_pre_item_purge_autoexportsearches($item)
{
    switch (get_class($item)) {
    }
}


// Define dropdown relations
/**
 * @return array|\string[][]
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

function plugin_autoexportsearches_item_purge(CommonDBTM $item) {
    global $DB;
    if ($item::getType() === SavedSearch::getType()) {
        // relation field set to 0 by the core when deleted (because of getDatabaseRelations?)
        $DB->delete('glpi_plugin_autoexportsearches_exportconfigs', [
            'savedsearches_id' => 0
        ]);
        $DB->delete('glpi_plugin_autoexportsearches_customsearchcriterias', [
            'savedsearches_id' => 0
        ]);
    } elseif ($item::getType() === PluginAutoexportsearchesExportconfig::getType()) {
        $DB->delete('glpi_plugin_autoexportsearches_customsearchcriterias', [
            'exportconfigs_id' => $item->fields['id']
        ]);
    }
}

