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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginAutoexportsearchesConfig extends CommonDBTM
{

    static $rightname = 'plugin_autoexportsearches_configs';

   /**
    * Show form
    *
    * @global type $CFG_GLPI
    * @return boolean
    */
    function showConfigForm()
    {

        if (!$this->canView() && !$this->canUpdate()) {
            return false;
        }

        if (! $this->getFromDB(1)) {
            $this->getEmpty();
        }

        echo "<form name='form' method='post' action='" . Toolbox::getItemTypeFormURL('PluginAutoexportsearchesConfig') . "'>";
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
    }
}
