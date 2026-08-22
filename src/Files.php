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
use CronTask;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Migration;
use Session;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Files
 */
class Files extends CommonDBTM
{
    public static $rightname = 'plugin_autoexportsearches_accessfiles';

    public static function getTypeName($nb = 0)
    {
        return __('Download files', 'autoexportsearches');
    }

    public static function install(Migration $migration)
    {
        CronTask::Register(
            Files::class,
            'DeleteFile',
            MONTH_TIMESTAMP,
            ['state' => CronTask::STATE_DISABLE],
        );
    }

    public static function canDownload(): bool
    {
        // Test the effective right value, not the mere existence of the profileright row.
        // getProfileRights() returns an associative array that is always non-empty (every
        // profile carries the row after install, possibly with value 0), so casting it to
        // bool was always true regardless of the granted access. haveRight() checks the bit.
        return Session::haveRight('plugin_autoexportsearches_accessfiles', READ);
    }

    /**
     * Root export directory (shared parent of every per-user sub-directory).
     * Read from the plugin Config so writers (Exportconfig::executeExport) and
     * readers agree on the same location.
     *
     * @return string
     */
    public static function getBaseDir(): string
    {
        $config = new Config();
        $config->getFromDB(1);
        return GLPI_PLUGIN_DOC_DIR . '/' . $config->getField('folder');
    }

    /**
     * Per-user export sub-directory. Export files are isolated by owner so that
     * the "accessfiles" right does not expose one user's exports to another; the
     * owner id is the users_id that created the export config.
     *
     * @param int $users_id
     * @return string
     */
    public static function getUserDir(int $users_id): string
    {
        return self::getBaseDir() . '/' . $users_id;
    }

    /**
     * Only a plugin/global administrator (core config UPDATE right) may browse or
     * download another user's export files; everyone else is confined to their own
     * sub-directory. Mirrors the "elevated" notion used by
     * Exportconfig::validateExportInput() and plugin_autoexportsearches_addDefaultWhere().
     *
     * @return bool
     */
    public static function canAccessAllUsersFiles(): bool
    {
        return (bool) Session::haveRight('config', UPDATE);
    }

    /**
     * Resolve, and authorize, the owner sub-directory the caller may act on.
     * A non-elevated caller is always forced back to their own id regardless of
     * the requested value, so a forged users_id can never reach another user's files.
     *
     * @param mixed $requested requested owner id (from the request), may be null
     * @return int
     */
    public static function resolveOwnerId($requested = null): int
    {
        $current = (int) Session::getLoginUserID();
        if (!self::canAccessAllUsersFiles()) {
            return $current;
        }
        // Elevated callers may target any owner sub-directory, including the "0"
        // legacy bucket that holds pre-isolation files of unknown owner. A missing
        // or negative request falls back to the caller's own id.
        if ($requested === null || $requested === '') {
            return $current;
        }
        $requested = (int) $requested;
        return $requested >= 0 ? $requested : $current;
    }

    public function showMenu($owner_id = null)
    {
        $owner_id = self::resolveOwnerId($owner_id);
        TemplateRenderer::getInstance()->display(
            '@autoexportsearches/files_menu.html.twig',
            [
                'type_name' => self::getTypeName(),
                'types'     => array_values(self::getTypes($owner_id)),
                'base_url'  => PLUGINAUTOEXPORTSEARCH_WEBDIR . '/front/files.php',
                'owner_id'  => $owner_id,
            ],
        );
    }

    public function getTypes($owner_id = null)
    {
        $types = [];
        $owner_id = self::resolveOwnerId($owner_id);
        $dir = self::getUserDir($owner_id);
        //If the dir folder exist
        if (is_dir($dir)) {
            // Get all files in an array
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || is_dir($dir . "/" . $file)) {
                    continue;
                }
                $type = substr($file, 0, strpos($file, '_'));
                if ($type !== '' && !in_array($type, $types)) {
                    array_push($types, $type);
                }
            }
        }
        return $types;
    }

    /** Show all files of an owner sub-directory in an HTML table
     * @param string $type   folder/type prefix currently browsed
     * @param mixed  $owner_id requested owner id (authorized via resolveOwnerId)
     */
    public function showListFiles($type, $owner_id = null)
    {
        $owner_id   = self::resolveOwnerId($owner_id);
        $dir        = self::getUserDir($owner_id);
        $dir_exists = is_dir($dir);
        $files      = $dir_exists ? $this->processFiles("get", "", $type, $owner_id) : [];

        $limit_begin = (int) ($_GET['start'] ?? 0);
        $limit_nb    = (int) ($_SESSION['glpilist_limit'] ?? 0);
        $order_type  = isset($_GET['orderType']) && $_GET['orderType'] === 'ASC' ? 'DESC' : 'ASC';
        $start       = (int) ($_GET['start'] ?? 0);

        if ($files) {
            if (isset($_GET['orderCol'])) {
                switch ($_GET['orderCol']) {
                    case 'name':
                    case 'date':
                        $_GET['orderType'] === 'ASC' ? sort($files) : rsort($files);
                        break;
                    case 'month':
                        $_GET['orderType'] === 'ASC'
                            ? usort($files, [$this, 'sortArrayAsc'])
                            : usort($files, [$this, 'sortArrayDesc']);
                        break;
                }
            }
        }

        $target     = PLUGINAUTOEXPORTSEARCH_WEBDIR . '/front/files.php?type=' . rawurlencode($type)
                      . '&users_id=' . $owner_id;
        $parameters = isset($_GET['orderType'])
            ? 'orderCol=' . rawurlencode($_GET['orderCol'] ?? '') . '&orderType=' . rawurlencode($_GET['orderType'])
            : '';

        ob_start();
        Html::printPager($limit_begin, count($files ?: []), $target, $parameters);
        $pager = ob_get_clean();

        $lang        = $_SESSION['glpilanguage'] ?? '';
        $visible     = [];
        foreach (($files ?: []) as $key => $file) {
            if ($key < $limit_begin || ($limit_nb > 0 && $key >= $limit_nb + $limit_begin)) {
                continue;
            }
            $date_raw  = $this->getDateFile($file, 'YmdHis');
            $after     = substr($date_raw, 11);
            $is_csv    = str_contains($after, 'csv');
            if (!$is_csv && $lang === 'fr_FR') {
                $d = preg_replace("/(\d{4})-(\d{2})-(\d{2})/", '$3-$2-$1', substr($date_raw, 0, 10));
                $t = preg_replace("/(\d{2})-(\d{2})-(\d{2})/", '$1h$2min$3s', substr($date_raw, 11));
                $date_formatted = $d . ' ' . $t;
            } elseif (!$is_csv) {
                $date_formatted = substr($date_raw, 0, 10) . ' ' . str_replace('-', ':', substr($date_raw, 11));
            } elseif ($lang === 'fr_FR') {
                $date_formatted = preg_replace("/(\d{4})-(\d{2})-(\d{2})/", '$3-$2-$1', substr($date_raw, 0, 10));
            } else {
                $date_formatted = substr($date_raw, 0, 10);
            }
            $visible[] = [
                'name'           => $file,
                'date_formatted' => $date_formatted,
            ];
        }

        TemplateRenderer::getInstance()->display(
            '@autoexportsearches/files_list.html.twig',
            [
                'dir_exists'    => $dir_exists,
                'files'         => $files ?: [],
                'visible_files' => $visible,
                'pager'         => $pager,
                'can_download'  => (bool) self::canDownload(),
                'plugin_dir'    => PLUGINAUTOEXPORTSEARCH_WEBDIR,
                'base_url'      => PLUGINAUTOEXPORTSEARCH_WEBDIR . '/front/files.php',
                'type'          => $type,
                'order_toggle'  => $order_type,
                'start'         => $start,
                'owner_id'      => $owner_id,
                'form_action'   => PLUGINAUTOEXPORTSEARCH_WEBDIR . '/front/files.php',
            ],
        );
    }

    public function sortArrayAsc($a, $b)
    {
        $aMonth = substr($a, strpos($a, "_") + 5, 2);
        $bMonth = substr($b, strpos($b, "_") + 5, 2);
        // usort() expects an int (-1/0/1); a bare boolean comparison never yields the
        // negative case, producing an unstable sort. The spaceship operator is correct.
        return $aMonth <=> $bMonth;
    }

    public function sortArrayDesc($a, $b)
    {
        $aMonth = substr($a, strpos($a, "_") + 5, 2);
        $bMonth = substr($b, strpos($b, "_") + 5, 2);
        return $bMonth <=> $aMonth;
    }

    /** Get date in file name
     * @param        $file
     * @param string $formatOut
     *
     * @return bool|string
     */
    public function getDateFile($file, $formatOut = "Ymd")
    {
        // Default so $out is always defined even when $formatOut matches no case
        // below (the switch has no default branch). This also removes a
        // variable.undefined PHPStan flag whose occurrence count would otherwise
        // drift between the local and CI PHP versions.
        $out = "";
        switch ($formatOut) {
            case "Y":
                $out = substr($file, strpos($file, "_") + 1, 4);
                break;
            case "m":
                $out = substr($file, strpos($file, "_") + 6, 2);
                break;
            case "d":
                $out = substr($file, strpos($file, "_") + 9, 2);
                break;
            case "Ymd":
                $out = substr($file, strpos($file, "_") + 1, 10);
                break;
            case "YmdHis":
                $out = substr($file, strpos($file, "_") + 1, 19);
                break;
        }
        $out = str_replace("_", "-", $out);

        return $out;
    }

    /** Function that makes actions around files
     * @param        $action
     * @param string $file
     *
     * @return array|bool
     */
    public function processFiles($action, $file = "", $type = "", $owner_id = null)
    {
        // resolveOwnerId re-authorizes here too: a non-elevated caller can never
        // reach another user's sub-directory even if this method is reached directly.
        $owner_id = self::resolveOwnerId($owner_id);
        $dir = self::getUserDir($owner_id);

        $res = [];
        switch ($action) {
            case "get":
                $res = [];
                if (!is_dir($dir)) {
                    return $res;
                }
                // Get files in the owner sub-directory
                $files = scandir($dir);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }
                    // if the file is not a folder
                    // Use str_contains(): strpos() returns false (cast to 0) when the
                    // substring is absent, and "0 > -1" is always true, so the previous
                    // test never actually excluded files missing $type.
                    if ($type != "" && str_contains($file, $type)) {
                        if (!is_dir($dir . "/" . $file)) {
                            $res[] = $file;
                        }
                    }
                }
                break;
            case "delete":
                $safeDir  = realpath($dir);
                $safePath = realpath($dir . '/' . $file);
                if ($safePath !== false
                    && $safeDir !== false
                    && str_starts_with($safePath, $safeDir . DIRECTORY_SEPARATOR)) {
                    $res = unlink($safePath);
                } else {
                    $res = false;
                }
                break;
        }
        return $res;
    }

    /** Function for delete files after $nbMonths
     * @param $nbMonths
     */
    public function deleteByMonths($nbMonths)
    {
        $today = date("Ymd");
        $base  = self::getBaseDir();
        if (!is_dir($base)) {
            return;
        }
        // The purge cron runs without a session, so it cannot be scoped to one
        // owner: iterate every per-user sub-directory (plus the "0" legacy bucket).
        foreach (scandir($base) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $userDir = $base . '/' . $entry;
            // Only numeric owner sub-directories hold export files.
            if (!is_dir($userDir) || !ctype_digit((string) $entry)) {
                continue;
            }
            $safeUserDir = realpath($userDir);
            if ($safeUserDir === false) {
                continue;
            }
            foreach (scandir($userDir) as $file) {
                if ($file === '.' || $file === '..' || is_dir($userDir . '/' . $file)) {
                    continue;
                }
                $dateFile = strtotime($this->getDateFile($file));
                $nbMonthsToAdd = "+" . $nbMonths . " months";
                $dateDiff = strtotime($nbMonthsToAdd, $dateFile);
                $dateToDelete = date('Ymd', $dateDiff);
                if ($today > $dateToDelete) {
                    $safePath = realpath($userDir . '/' . $file);
                    if ($safePath !== false
                        && str_starts_with($safePath, $safeUserDir . DIRECTORY_SEPARATOR)) {
                        unlink($safePath);
                    }
                }
            }
        }
    }

    ////// CRON FUNCTIONS ///////
    //Cron action
    /**
     * @param $name
     *
     * @return array
     */
    public static function cronInfo($name)
    {
        switch ($name) {
            case 'DeleteFile':
                return [
                    'description' => __('Delete export files', 'autoexportsearches'),
                ];   // Optional
                break;
        }
        return [];
    }

    /**
     * Cron action
     *
     * @param  $task for log
     * @global $CFG_GLPI
     *
     * @global $DB
     */
    public static function cronDeleteFile($task = null)
    {
        $CronTask = new CronTask();
        if ($CronTask->getFromDBbyName(Files::class, "DeleteFile")) {
            if ($CronTask->fields["state"] == CronTask::STATE_DISABLE) {
                return 0;
            }
        } else {
            return 0;
        }

        $config = new Config();
        $config->getFromDB(1);
        $nbMonths = $config->getField('monthBeforePurge');

        $autoexportsearchesFiles = new self();
        $autoexportsearchesFiles->deleteByMonths($nbMonths);
        return 1;
    }
}
