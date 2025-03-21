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

include ('../../../inc/includes.php');

$files = new PluginAutoexportsearchesFiles();
$check_download = $files::canDownload();

if (isset($_GET["file"]) && $check_download) { // for other file

    $config = new PluginAutoexportsearchesConfig();
    $config->getFromDB(1);
    $dir = GLPI_PLUGIN_DOC_DIR . $config->getField('folder');
    $filename = $_GET["file"];
    if(is_file("$dir$filename")){
        Toolbox::sendFile("$dir$filename", $filename);
    }else{
        Html::displayErrorAndDie(__('Invalid filename'), true);
    }
}else{
    Html::displayErrorAndDie(__('Unauthorized access to this file'), true);
}
