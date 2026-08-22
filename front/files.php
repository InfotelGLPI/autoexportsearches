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

use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Autoexportsearches\Files;
use GlpiPlugin\Autoexportsearches\Menu;

// This page lists and permanently deletes exported files: gate on the feature's
// own right (Files::$rightname = plugin_autoexportsearches_accessfiles), like
// document.send.php and Menu do. The previous gate checked the unrelated
// "exportconfigs" right, so an exportconfigs-only profile could delete files while
// an accessfiles-only profile (shown the menu link) was denied.
if (Session::haveRight(Files::$rightname, READ)) {
    if (isset($_SESSION['glpiactiveprofile']['interface']) &&
        $_SESSION['glpiactiveprofile']['interface'] == 'central' &&
        !isset($_POST['export'])) {
        Html::header(
            Files::getTypeName(2),
            '',
            "tools",
            Menu::class,
            Files::getType(),
        );
    } elseif (!isset($_POST['export'])) {
        Html::helpHeader(Files::getTypeName(2));
    }

    $files = new Files();
    // Files are isolated per owner (see Files::getUserDir). resolveOwnerId() forces a
    // non-elevated caller back to their own sub-directory regardless of the requested
    // users_id, so a forged value can never reach another user's files; only a config
    // UPDATE holder may target another owner (or the "0" legacy bucket).
    // Note: "accessfiles" is an all-or-nothing boolean right (there is no separate
    // UPDATE/DELETE/PURGE level for it), so being able to read the exported files also
    // grants the ability to delete them permanently below. This is an accepted design
    // choice: the export files are transient artifacts and the feature is only granted to
    // profiles that already manage the plugin.
    if (isset($_POST["filedelete"])) {
        $delete_owner = Files::resolveOwnerId($_POST['filedelete_users_id'] ?? null);
        $noFile = true;
        foreach ($_POST["filedelete"] as $fileName => $file) {
            if ($file == 1) {
                $noFile = false;
                $files->processFiles("delete", basename($fileName), "", $delete_owner);
            }
        }
        if (!$noFile) {
            Session::addMessageAfterRedirect(__('File successfully deleted', 'autoexportsearches'), true, INFO);
        } else {
            Session::addMessageAfterRedirect(__('No file selected', 'autoexportsearches'), true, ERROR);
        }
        Html::back();
    }

    $owner_id = Files::resolveOwnerId($_GET['users_id'] ?? null);
    if (!isset($_GET['type'])) {
        $files->showMenu($owner_id);
    } else {
        $type = $_GET['type'];
        $files->showListFiles($type, $owner_id);
    }

    if (isset($_SESSION['glpiactiveprofile']['interface']) &&
        $_SESSION['glpiactiveprofile']['interface'] == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }
} else {
    throw new AccessDeniedHttpException();
}
