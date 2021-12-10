<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 autoexportsearches plugin for GLPI
 Copyright (C) 2018 by the autoexportsearches Development Team.

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

/**
 * Class PluginautoexportsearchesMenu
 */
class PluginAutoexportsearchesMenu extends CommonDBTM {
   static $rightname = '';

   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getMenuName($nb = 1) {
      return _n('Auto export', 'Auto exports', $nb, 'autoexportsearches');
   }

   static function getIcon() {
      return "fas fa-file-export"; //todo find a other
   }

   static function getMenuContent() {
      $plugin_page   = "/plugins/autoexportsearches/front/menu.php";
      $menu          = [];
      $menu['icon']  = self::getIcon();
      $menu['title'] = self::getMenuName(2);

      $menu['page']                                                     = $plugin_page;
      $menu['options'][PluginAutoexportsearchesExportconfig::getType()] = [
         'title' => PluginAutoexportsearchesExportconfig::getTypeName(2),
         'page'  => PluginAutoexportsearchesExportconfig::getSearchURL(false),
         'links' => [
            'search' => PluginAutoexportsearchesExportconfig::getSearchURL(false),
            'add'    => PluginAutoexportsearchesExportconfig::getFormURL(false)
         ]
      ];

      $menu['options'][PluginAutoexportsearchesFiles::getType()] = [
         'title' => PluginAutoexportsearchesFiles::getTypeName(2),
         'page'  => PluginAutoexportsearchesFiles::getSearchURL(false),

      ];


      return $menu;
   }

   static function getMainMenuContent() {
      $plugin_page = "/plugins/autoexportsearches/front/menu.php";
      $menu        = [];

      $menu['pluginautoexportsearches']['title'] = self::getMenuName();

      $menu['pluginautoexportsearches']['page'] = $plugin_page;

      return $menu;
   }


   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['pluginautoexportsearches'])) {
         unset($_SESSION['glpimenu']['pluginautoexportsearches']);
      }

   }

   static function getUrl() {
      global $CFG_GLPI;
      return $CFG_GLPI ['root_doc'] . "/plugins/autoexportsearches/front/menu.php";
   }

   static function showMenu() {

      echo "<div align='center'>
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
