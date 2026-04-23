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

namespace GlpiPlugin\Autoexportsearches;

use CommonDBTM;
use CronTask;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Migration;
use ProfileRight;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Files
 */
class Files extends CommonDBTM
{

    static $rightname = 'plugin_autoexportsearches_accessfiles';

    static function getTypeName($nb = 0)
    {
        return __('Download files', 'autoexportsearches');
    }

    public static function install(Migration $migration)
    {
        CronTask::Register(
            Files::class,
            'DeleteFile',
            MONTH_TIMESTAMP,
            ['state' => CronTask::STATE_DISABLE]
        );
    }

    static function canDownload()
    {
        return ProfileRight::getProfileRights(
            $_SESSION['glpiactiveprofile']['id'],
            ['plugin_autoexportsearches_accessfiles']
        );
    }

    function showMenu()
    {
        TemplateRenderer::getInstance()->display(
            '@autoexportsearches/files_menu.html.twig',
            [
                'type_name' => self::getTypeName(),
                'types'     => array_values(self::getTypes()),
                'base_url'  => PLUGINAUTOEXPORTSEARCH_WEBDIR . '/front/files.php',
            ]
        );
    }

    function getTypes()
    {
        $types = [];
        $config = new Config();
        $config->getFromDB(1);
        $dir = GLPI_PLUGIN_DOC_DIR . '/'.$config->getField('folder');
        //If the dir folder exist
        if (is_dir($dir)) {
            // Get all files in an array
            $files = scandir($dir);
            foreach ($files as $file) {
                $type = substr($file, 0, strpos($file, '_'));
                if (!in_array($type, $types) && !is_dir($dir . "/" . $file)) {
                    array_push($types, $type);
                }
            }
        }
        return $types;
    }

    /** Show All files in a HTML table
     * @param $dir
     */
    function showListFiles($dir, $type)
    {
        $dir_exists = is_dir($dir);
        $files      = $dir_exists ? $this->processFiles("get", "", $type) : [];

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

        $target     = PLUGINAUTOEXPORTSEARCH_WEBDIR . '/front/files.php?type=' . rawurlencode($type);
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
                'form_action'   => PLUGINAUTOEXPORTSEARCH_WEBDIR . '/front/files.php',
            ]
        );
    }

    function sortArrayAsc($a, $b)
    {
        $aMonth = substr($a, strpos($a, "_") + 5, 2);
        $bMonth = substr($b, strpos($b, "_") + 5, 2);
        return $aMonth > $bMonth;
    }

    function sortArrayDesc($a, $b)
    {
        $aMonth = substr($a, strpos($a, "_") + 5, 2);
        $bMonth = substr($b, strpos($b, "_") + 5, 2);
        return $aMonth < $bMonth;
    }

    /** Get date in file name
     * @param        $file
     * @param string $formatOut
     *
     * @return bool|string
     */
    function getDateFile($file, $formatOut = "Ymd")
    {
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
    function processFiles($action, $file = "", $type = "")
    {
        $config = new Config();
        $config->getFromDB(1);
        $dir = GLPI_PLUGIN_DOC_DIR .'/'. $config->getField('folder');

        switch ($action) {
            case "get":
                $res = [];
                // Get files in defined dir
                $files = scandir($dir);
                foreach ($files as $file) {
                    // if the file is not a folder
                    if ($type != "" && strpos($file, $type) > -1) {
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
    function deleteByMonths($nbMonths)
    {
        $today = date("Ymd");
        $files = $this->processFiles("get");
        if (is_array($files)) {
            foreach ($files as $file) {
                $dateFile = strtotime($this->getDateFile($file));
                $nbMonthsToAdd = "+" . $nbMonths . " months";
                $dateDiff = strtotime($nbMonthsToAdd, $dateFile);
                $dateToDelete = date('Ymd', $dateDiff);
                if ($today > $dateToDelete) {
                    $this->processFiles("delete", $file);
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
    static function cronInfo($name)
    {
        switch ($name) {
            case 'DeleteFile':
                return [
                    'description' => __('Delete export files', 'autoexportsearches')
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
    static function cronDeleteFile($task = null)
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
