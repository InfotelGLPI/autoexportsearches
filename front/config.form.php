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

include('../../../inc/includes.php');

$plugin = new Plugin();

if ($plugin->isActivated("autoexportsearches")) {

   Session::checkRight("config", UPDATE);
   $config = new PluginAutoexportsearchesConfig();
   if (isset($_POST["update"])) {
      if ($config->getFromDB(1)) {
          if(isset($_POST['monthBeforePurge']) && is_numeric($_POST['monthBeforePurge'])){
              $config->update(['id' => 1, 'monthBeforePurge' => $_POST['monthBeforePurge']]);
          }
      } else {
         $config->add($_POST);
      }
      Html::back();
   } else {
      Html::header(PluginAutoexportsearchesFiles::getTypeName(2), '', "tools",'PluginAutoexportsearchesMenu');
      $config->showConfigForm();
      Html::footer();
   }

} else {
   Html::header(__('Setup'), '', "config", "plugins");
   echo "<div align='center'><br><br>";
   echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/warning.png\" alt=\"warning\"><br><br>";
   echo "<b>" . __('Please activate the plugin', 'autoexportsearches') . "</b></div>";
}
