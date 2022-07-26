<?php

/*
 -------------------------------------------------------------------------
 Autoexportsearches plugin for GLPI
 Copyright (C) 2003-2016 by the Autoexportsearches Development Team.

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

include('../../../inc/includes.php');

if (Plugin::isPluginActive("autoexportsearches")) {

   Session::checkRight("config", UPDATE);
   $config = new PluginAutoexportsearchesConfig();
   if (isset($_POST["update"])) {
      if ($config->getFromDB(1)) {
         $config->update(['id' => 1, 'folder' => $_POST['folder']]);
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
