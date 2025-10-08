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

Session::checkLoginUser();

use GlpiPlugin\Autoexportsearches\Exportconfig;
use GlpiPlugin\Autoexportsearches\Menu;

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}

$export = new Exportconfig();

if (!isset($_POST['periodicity_open_days'])) {
    $_POST['periodicity_open_days'] = 0;
}

if (isset($_POST["add"])) {
    $export->check(-1, CREATE, $_POST);

    $newID = $export->add($_POST);
    if ($_SESSION['glpibackcreated']) {
        Html::redirect($export->getFormURL() . "?id=" . $newID);
    }
    Html::back();
} elseif (isset($_POST["delete"])) {
    $export->check($_POST['id'], DELETE);
    $export->delete($_POST);
    $export->redirectToList();
} elseif (isset($_POST["restore"])) {
    $export->check($_POST['id'], PURGE);
    $export->restore($_POST);
    $export->redirectToList();
} elseif (isset($_POST["purge"])) {
    $export->check($_POST['id'], PURGE);
    $export->delete($_POST, 1);
    $export->redirectToList();
} elseif (isset($_POST["update"])) {
    $export->check($_POST['id'], UPDATE);
    $export->update($_POST);
    Html::back();
} else {
    $export->checkGlobal(READ);

    Html::header(
        Menu::getTypeName(2),
        '',
        'tools',
        Menu::class,
        Exportconfig::getType()
    );

    $export->display($_GET);

    Html::footer();
}
