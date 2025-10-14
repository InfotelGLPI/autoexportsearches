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
use Html;
use Migration;
use Toolbox;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class Config extends CommonDBTM
{
    public static $rightname = 'plugin_autoexportsearches_configs';

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
                        `folder` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
                        `monthBeforePurge` int {$default_key_sign} NOT NULL DEFAULT '0',
                        PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

            $DB->doQuery($query);

            $DB->insert(
                $table,
                ['id' => 1,
                    'folder' => 'autoexportsearches',
                    'monthBeforePurge' => 3]
            );
        }
    }

    public static function uninstall()
    {
        global $DB;

        $DB->dropTable(self::getTable(), true);
    }


    /**
     * Show form
     *
     * @return boolean
     */
    public function showConfigForm()
    {

        if (!$this->canView() && !$this->canUpdate()) {
            return false;
        }

        if (! $this->getFromDB(1)) {
            $this->getEmpty();
        }

        echo "<form name='form' method='post' action='" . Toolbox::getItemTypeFormURL(Config::class) . "'>";
        echo "<div class='center'><table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='2'>" . __('Setup') . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Number of months before purge files', 'autoexportsearches') . "</td>";
        echo "<td>";
        echo Html::input('monthBeforePurge', ['value' => $this->fields['monthBeforePurge'], 'size' => 6]);
        echo "</td>";
        echo "</tr>";

        echo "<tr><td class='tab_bg_2 center' colspan='2'>";
        echo Html::submit(_sx('button', 'Save'), ['name' => 'update', 'class' => 'btn btn-primary']);
        echo "</td></tr>";

        echo "</table></div>";
        Html::closeForm();

        return true;
    }
}
