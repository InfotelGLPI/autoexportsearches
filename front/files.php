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
   define('GLPI_ROOT', realpath('../../..'));
}
include(GLPI_ROOT . '/inc/includes.php');

if (isset($_SESSION['glpiactiveprofile']['interface']) &&
    $_SESSION['glpiactiveprofile']['interface'] == 'central' &&
    !isset($_POST['export'])) {
   Html::header(PluginAutoexportsearchesFiles::getTypeName(2), '', "tools", "PluginAutoexportsearchesMenu",PluginAutoexportsearchesFiles::getType());

} else if (!isset($_POST['export'])) {
   Html::helpHeader(PluginAutoexportsearchesFiles::getTypeName(2));
}


$files  = new PluginAutoexportsearchesFiles();
$config = new PluginAutoexportsearchesConfig();
$config->getFromDB(1);
$dir = GLPI_PLUGIN_DOC_DIR . $config->getField('folder');
if(isset($_POST["filedelete"])){
   $noFile = true;
   foreach ($_POST["filedelete"] as $fileName => $file){
      if($file == 1){
         $noFile = false;
         $files->processFiles("delete",$fileName);
      }
   }
   if(!$noFile){
      Session::addMessageAfterRedirect(__('File successfully deleted', 'autoexportsearches'), true, INFO);
   } else {
      Session::addMessageAfterRedirect(__('No file selected', 'autoexportsearches'), true, ERROR);
   }
   Html::back();

}

if(!isset($_GET['type'])){
   $files->showMenu();
} else{
   $type = $_GET['type'];
   $files->showListFiles($dir,$type);
}
if (isset($_SESSION['glpiactiveprofile']['interface']) &&
    $_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
