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
use Session;

/**
 * Class Menu
 */
class Menu extends CommonDBTM
{
    static $rightname = '';

   /**
    * @param int $nb
    *
    * @return string
    */
    static function getMenuName($nb = 1)
    {
        return _n('Auto export', 'Auto exports', $nb, 'autoexportsearches');
    }

    static function getIcon()
    {
        return "ti ti-file-export"; //todo find a other
    }

    static function getMenuContent()
    {

        $menu          = [];
        $menu['icon']  = self::getIcon();
        $menu['title'] = self::getMenuName(2);

        $menu['page']                                                     = self::getSearchURL(false);
        $menu['options'][Exportconfig::getType()] = [
         'title' => Exportconfig::getTypeName(2),
         'page'  => Exportconfig::getSearchURL(false),
         'links' => [
            'search' => Exportconfig::getSearchURL(false),
            'add'    => Exportconfig::getFormURL(false)
         ]
        ];

        $menu['options'][Files::getType()] = [
         'title' => Files::getTypeName(2),
         'page'  => Files::getSearchURL(false),

        ];

        $menu['links']['config']                      = Config::getFormURL(false);
       //Link to config page in admin plugins list
        $menu['config_page']                          = Config::getFormURL(false);

        $menu['options']['config']['title']           = __('Setup');
        $menu['options']['config']['page']            = Config::getFormURL(false);
        $menu['options']['config']['links']['search'] = Config::getFormURL(false);
        $menu['options']['config']['links']['add']    = Config::getFormURL(false);


        return $menu;
    }

    static function removeRightsFromSession()
    {
        if (isset($_SESSION['glpimenu'][Menu::class])) {
            unset($_SESSION['glpimenu'][Menu::class]);
        }
    }

    static function showMenu()
    {

        echo "<div class='center'>
        <table class='tab_cadre' width='30%' cellpadding='5'>";
        echo "<tr><th colspan='6'>" . __('Menu', 'autoexportsearches') . "</th></tr>";

        echo "<tr>";
        if (Session::haveRight("plugin_autoexportsearches_exportconfigs", READ)) {
            echo "<td class='center' colspan='3'>";
            echo "<a href=\"../front/exportconfig.php\">";
            echo "<i class=\"fas fa-list fa-4x\"></i>";
            echo "<br>" . __('Export config list to export', 'autoexportsearches') . "</a>";
            echo "</td>";
        }
        if (Session::haveRight("plugin_autoexportsearches_accessfiles", READ)) {
            echo "<td class='center' colspan='3'>";
            echo "<a href=\"../front/files.php\">";
            echo "<i class=\"fas fa-folder-open fa-4x\"></i>";
            echo "<br>" . __('List of export files', 'autoexportsearches') . "</a>";
            echo "</td>";
        }
        echo "</tr>";

        echo "</table>";
        echo "</div>";
    }
}
