<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 autoexportsearches plugin for GLPI
 Copyright (C) 2018-2019 by the autoexportsearches Development Team.

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

define('PLUGIN_AUTOEXPORTSEARCH_VERSION', '2.0.0');

if (!defined("PLUGIN_AUTOEXPORTSEARCH_DIR")) {
   define("PLUGIN_AUTOEXPORTSEARCH_DIR", Plugin::getPhpDir("AUTOEXPORTSEARCH"));
   define("PLUGIN_AUTOEXPORTSEARCH_DIR_NOFULL", Plugin::getPhpDir("AUTOEXPORTSEARCH",false));
   define("PLUGINAUTOEXPORTSEARCH_WEBDIR", Plugin::getWebDir("AUTOEXPORTSEARCH"));
}

// Init the hooks of the plugins -Needed
function plugin_init_autoexportsearches() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['autoexportsearches'] = true;
   $PLUGIN_HOOKS['change_profile']['autoexportsearches']   = ['PluginAutoexportsearchesProfile', 'initProfile'];


   if (Session::getLoginUserID()) {
      $PLUGIN_HOOKS['menu_toadd']['autoexportsearches']          = ['tools' => 'PluginAutoexportsearchesMenu'];

      Plugin::registerClass('PluginAutoexportsearchesProfile', ['addtabon' => 'Profile']);
      $PLUGIN_HOOKS['use_massive_action']['autoexportsearches'] = 1;

      if (Session::haveRight("config", UPDATE)) {
         $PLUGIN_HOOKS['config_page']['autoexportsearches'] = 'front/config.form.php';
      }
   }

}

/**
 * Get the name and the version of the plugin - Needed
 *e
 * @return array
 */
function plugin_version_autoexportsearches() {

   return [
      'name'           => _n('Auto export searches', 'Auto exports searches', 2, 'autoexportsearches'),
      'version'        => PLUGIN_AUTOEXPORTSEARCH_VERSION,
      'author'         => "<a href='http://blogglpi.infotel.com'>Infotel</a>, Alban Lesellier",
      'license'        => 'GPLv2+',
      'homepage'       => '',
      'requirements'   => [
         'glpi' => [
            'min' => '10.0',
            'max' => '11.0',
            'dev' => false
         ]
      ]
   ];
}
