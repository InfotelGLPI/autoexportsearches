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

namespace GlpiPlugin\Autoexportsearches;

use CommonDBTM;
use DBConnection;
use Migration;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class Customsearchcriteria extends CommonDBTM
{
    const CRITERIA_FIRST_DAY_OF_MONTH = 'first day of ';
    const CRITERIA_FIRST_DAY_OF_WEEK = 'last monday';

    public static function install(Migration $migration)
    {
        global $DB;

        $default_charset   = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign  = DBConnection::getDefaultPrimaryKeySignOption();
        $table  = self::getTable();

        if (!$DB->tableExists($table)) {
            $query = "CREATE TABLE `$table` (
                        `id` int {$default_key_sign} NOT NULL auto_increment,
                        `exportconfigs_id`    int {$default_key_sign} NOT NULL COMMENT 'RELATION to glpi_plugin_autoexportsearches_exportconfigs (id)',
                        `savedsearches_id`    int {$default_key_sign} NOT NULL COMMENT 'RELATION to glpi_savedsearches (id)',
                        `criteria_field`      int {$default_key_sign} NOT NULL,
                        `criteria_value`      VARCHAR(255) NOT NULL,
                        `criteria_searchtype` VARCHAR(255) NOT NULL,
                        PRIMARY KEY (`id`),
                        KEY                   `exportconfigs_id` (`exportconfigs_id`),
                        KEY                   `savedsearches_id` (`savedsearches_id`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

            $DB->doQuery($query);
        }
    }

    public static function uninstall()
    {
        global $DB;

        $DB->dropTable(self::getTable(), true);
    }

    public static function createCriterias(ExportConfig $exportConfig)
    {
        global $DB;
        // clear old relations (in case of update with the saved search criterias changed)
        $DB->delete(
            'glpi_plugin_autoexportsearches_customsearchcriterias',
            [
                'exportconfigs_id' => $exportConfig->fields['id'],
                'savedsearches_id' => $exportConfig->fields['savedsearches_id']
            ]
        );

        if (isset($exportConfig->input['custom_criterias'])) {
            $customCriterias = $exportConfig->input['custom_criterias'];
            if (is_array($customCriterias)) {
                foreach ($customCriterias as $criteria) {
                    $criteria['exportconfigs_id'] = $exportConfig->fields['id'];
                    $self = new self();
                    $self->add($criteria);
                }
            }
        }
    }
}
