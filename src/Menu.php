<?php

/**
 * -------------------------------------------------------------------------
 * autoexportsearches plugin for GLPI
 * Copyright (C) 2025-2026 by the autoexportsearches Development Team.
 *
 * https://github.com/InfotelGLPI/autoexportsearches
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of autoexportsearches.
 *
 * autoexportsearches is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * autoexportsearches is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with autoexportsearches. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Autoexportsearches;

use CommonDBTM;
use Glpi\Application\View\TemplateRenderer;
use Session;

/**
 * Class Menu
 */
class Menu extends CommonDBTM
{
    public static $rightname = '';

    /**
     * @param int $nb
     *
     * @return string
     */
    public static function getMenuName($nb = 1)
    {
        return _n('Auto export', 'Auto exports', $nb, 'autoexportsearches');
    }

    public static function getIcon()
    {
        return "ti ti-file-export"; //todo find a other
    }

    public static function getMenuContent()
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
                'add'    => Exportconfig::getFormURL(false),
            ],
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

    public static function removeRightsFromSession()
    {
        if (isset($_SESSION['glpimenu'][Menu::class])) {
            unset($_SESSION['glpimenu'][Menu::class]);
        }
    }

    public static function showMenu()
    {
        $items = [];
        if (Session::haveRight("plugin_autoexportsearches_exportconfigs", READ)) {
            $items[] = [
                'url'   => '../front/exportconfig.php',
                'icon'  => 'fas fa-list fa-4x',
                'label' => __('Export config list to export', 'autoexportsearches'),
            ];
        }
        if (Session::haveRight("plugin_autoexportsearches_accessfiles", READ)) {
            $items[] = [
                'url'   => '../front/files.php',
                'icon'  => 'fas fa-folder-open fa-4x',
                'label' => __('List of export files', 'autoexportsearches'),
            ];
        }

        TemplateRenderer::getInstance()->display('@autoexportsearches/menu.html.twig', [
            'title' => __('Menu', 'autoexportsearches'),
            'items' => $items,
        ]);
    }
}
