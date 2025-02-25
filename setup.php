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

define('PLUGIN_AUTOEXPORTSEARCH_VERSION', '2.1.2');

if (!defined("PLUGIN_AUTOEXPORTSEARCH_DIR")) {
    define("PLUGIN_AUTOEXPORTSEARCH_DIR", Plugin::getPhpDir("autoexportsearches"));
    define("PLUGIN_AUTOEXPORTSEARCH_DIR_NOFULL", Plugin::getPhpDir("autoexportsearches", false));
    define("PLUGINAUTOEXPORTSEARCH_WEBDIR", Plugin::getWebDir("autoexportsearches"));
}

// Init the hooks of the plugins -Needed
function plugin_init_autoexportsearches()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['autoexportsearches'] = true;
    $PLUGIN_HOOKS['change_profile']['autoexportsearches'] = ['PluginAutoexportsearchesProfile', 'initProfile'];

    if (Session::getLoginUserID()) {
        if (Session::haveRightsOr('plugin_autoexportsearches_exportconfigs', [READ, CREATE, UPDATE]
            ) || Session::haveRightsOr('plugin_autoexportsearches_accessfiles', [READ, CREATE, UPDATE])) {
            $PLUGIN_HOOKS['menu_toadd']['autoexportsearches'] = ['tools' => 'PluginAutoexportsearchesMenu'];
        }
        Plugin::registerClass('PluginAutoexportsearchesProfile', ['addtabon' => 'Profile']);
        $PLUGIN_HOOKS['use_massive_action']['autoexportsearches'] = 1;

        $PLUGIN_HOOKS['pre_item_update']['autoexportsearches'] = [
            'PluginAutoexportsearchesExportconfig' =>
                ['PluginAutoexportsearchesCustomsearchcriteria', 'createCriterias']
        ];
        $PLUGIN_HOOKS['item_add']['autoexportsearches'] = [
            'PluginAutoexportsearchesExportconfig' =>
                ['PluginAutoexportsearchesCustomsearchcriteria', 'createCriterias']
        ];
        $PLUGIN_HOOKS['item_purge']['autoexportsearches'] = [
            'SavedSearch' => 'plugin_autoexportsearches_item_purge',
            'PluginAutoexportsearchesExportconfig' => 'plugin_autoexportsearches_item_purge'
        ];

        if (Session::haveRight("config", READ)) {
            $PLUGIN_HOOKS['config_page']['autoexportsearches'] = 'front/config.form.php';
        }
    }
}

/**
 * Get the name and the version of the plugin - Needed
 *e
 * @return array
 */
function plugin_version_autoexportsearches()
{
    return [
        'name' => _n('Auto export searches', 'Auto exports searches', 2, 'autoexportsearches'),
        'version' => PLUGIN_AUTOEXPORTSEARCH_VERSION,
        'author' => "<a href='http://blogglpi.infotel.com'>Infotel</a>, Alban Lesellier",
        'license' => 'GPLv2+',
        'homepage' => '',
        'requirements' => [
            'glpi' => [
                'min' => '10.0',
                'max' => '11.0',
                'dev' => false
            ]
        ]
    ];
}
