<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 autoexportsearches plugin for GLPI
 Copyright (C) 2009-2016 by the autoexportsearches Development Team.

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
Session::checkLoginUser();
//central or helpdesk access
if (Session::getCurrentInterface() == 'central') {
   Html::header(PluginAutoexportsearchesMenu::getTypeName(2), '', 'tools',"PluginAutoexportsearchesMenu");
} else {
   Html::helpHeader(PluginAutoexportsearchesMenu::getTypeName(2));
}

$autoexportsearchesMenu = new PluginAutoexportsearchesMenu();
if (Session::haveRight("plugin_autoexportsearches_exportconfigs", READ)) {
   PluginAutoexportsearchesMenu::showMenu($autoexportsearchesMenu);
} else {
   Html::displayRightError();
}

if (Session::getCurrentInterface() == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
