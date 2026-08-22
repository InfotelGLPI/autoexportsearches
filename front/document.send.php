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

use Glpi\Exception\Http\BadRequestHttpException;
use GlpiPlugin\Autoexportsearches\Files;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

Session::checkRight('plugin_autoexportsearches_accessfiles', READ);

$files = new Files();

$check_download = $files::canDownload();

if (isset($_GET["file"]) && $check_download) { // for other file

    // Files are isolated per owner: resolveOwnerId() forces a non-elevated caller back
    // to their own sub-directory, so a forged users_id cannot reach another user's file.
    $owner_id = Files::resolveOwnerId($_GET['users_id'] ?? null);
    $dir      = Files::getUserDir($owner_id) . '/';
    $filename = basename($_GET["file"]);
    if (is_file("$dir$filename")) {
        // Confine the resolved path to the owner sub-directory before serving it.
        $safeDir  = realpath(Files::getUserDir($owner_id));
        $safePath = realpath($dir . $filename);
        if ($safeDir === false
            || $safePath === false
            || !str_starts_with($safePath, $safeDir . DIRECTORY_SEPARATOR)) {
            throw new BadRequestHttpException('Invalid filename');
        }
        $response = new BinaryFileResponse($safePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename,
        );
        $response->send();
        exit;
    } else {
        throw new BadRequestHttpException('Invalid filename');
    }
} else {
    throw new BadRequestHttpException('Unauthorized access to this file');
}
