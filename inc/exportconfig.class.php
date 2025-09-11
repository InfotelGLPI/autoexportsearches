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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginautoexportsearchesTicket
 */
class PluginAutoexportsearchesExportconfig extends CommonDBTM
{

    const PERIODICITY_MINUTES = 3;
    const PERIODICITY_HOURS = 4;
    const PERIODICITY_DAYS = 0;
    const PERIODICITY_WEEKLY = 1;
    const PERIODICITY_MONTHLY = 2;

    static $rightname = 'plugin_autoexportsearches_exportconfigs';
    //   static $rightname = 'ticket';


    /**
     * functions mandatory
     * getTypeName(), canCreate(), canView()
     *
     * @param int $nb
     *
     * @return string
     */
    static function getTypeName($nb = 0)
    {
        return __('Auto export config', 'autoexportsearches');
    }

    /**
     * @return array
     */
    function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => self::getTypeName(2)
        ];
        $tab[] = [
            'id' => '1',
            'table' => self::getTable(),
            'field' => 'id',
            'name' => __('ID'),
            'massiveaction' => false,
            'datatype' => 'itemlink'
        ];

        $tab[] = [
            'id' => '2',
            'table' => User::getTable(),
            'field' => 'name',
            'name' => __('User who owns the saved search', 'autoexportsearches'),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '3',
            'table' => SavedSearch::getTable(),
            'field' => 'name',
            'name' => __('Saved search to export', 'autoexportsearches'),
            'datatype' => 'dropdown',
            'linkfield' => 'savedsearches_id',
        ];

//        $tab[] = [
//            'id' => '4',
//            'table' => self::getTable(),
//            'field' => 'periodicity_type',
//            'name' => __('Periodicity type', 'autoexportsearches'),
//            'datatype' => 'text'
//        ];

        $tab[] = [
            'id' => '5',
            'table' => self::getTable(),
            'field' => 'last_export',
            'name' => __('Last export', 'autoexportsearches'),
            'datatype' => 'datetime'
        ];
        $tab[] = [
            'id' => '6',
            'table' => self::getTable(),
            'field' => 'is_active',
            'name' => __('Active'),
            'datatype' => 'bool'
        ];


        return $tab;
    }

    function showForm($ID, $options = [])
    {
        global $CFG_GLPI;


        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'><h3>" . __('Search to export', 'autoexportsearches') . "</h3></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";

        echo "<td>" . __('User who owns the saved search', 'autoexportsearches') . "</td>";
        echo "<td>";

        $rand = mt_rand();
        User::dropdown([
            'name' => 'users_id',
            'value' => $this->fields["users_id"],
            //                        'entity' => $this->fields["entities_id"],
            'right' => 'own_ticket',
            'rand' => $rand
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";

        echo "<td>" . __('Saved search to export', 'autoexportsearches') . "</td>";
        echo "<td id='savedSearches'>";
        echo "</td>";
        echo "</tr>";
        $params = [
            "users_id" => '__VALUE__',
            "current_user" => $this->fields['users_id'],
            'savedsearches_id' => $this->fields["savedsearches_id"],
            'exportconfigs_id' => $ID,
            "rand" => $rand,
            "action" => "loadSearches"
        ];
        $url = $CFG_GLPI['root_doc'] . "/plugins/autoexportsearches/ajax/dropdownsavedsearches.php";
        Ajax::updateItemOnSelectEvent("dropdown_users_id$rand", "savedSearches", $url, $params);

        echo "
            <script>
                $(document).ready(function() {
                   $('#dropdown_users_id$rand').trigger('change');
                });
            </script>
        ";

        echo "<tr class='tab_bg_1'><td id='custom_search_criterias' colspan='2'>";
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'><h3>" . __('Periodicity') . "</h3></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Periodicity type', 'autoexportsearches') . "</td>";
        echo "<td>";

        $rand = mt_rand();
        Dropdown::showFromArray(
            'periodicity_type',
            [

                self::PERIODICITY_DAYS => __('Every x days', 'autoexportsearches'),
                self::PERIODICITY_WEEKLY => _x('periodicity', 'Weekly'),
                self::PERIODICITY_MONTHLY => _x('periodicity', 'Monthly'),
                self::PERIODICITY_MINUTES => __('Every x minutes', 'autoexportsearches'),
                self::PERIODICITY_HOURS => __('Every x hours', 'autoexportsearches'),
            ],
            [
                'value' => $this->fields['periodicity_type'],
                'rand' => $rand
            ]
        );
        echo "</td></tr>";

        echo "<tr class='tab_bg_1' id='periodicity_value'></tr>";
        $url = $CFG_GLPI['root_doc'] . "/plugins/autoexportsearches/ajax/periodicityfields.php";
        // let ajax determine the fields shown depending on the choosen periodicity_type
        echo "
            <script>
                $(document).ready(function() {
                    const selectType = $('#dropdown_periodicity_type$rand');
                    const periodicityRow = $('#periodicity_value');
                    selectType.change(e => {
                        periodicityRow.load('$url', {
                            'id' : $ID,
                            'periodicity_type' : e.target.selectedIndex
                        });
                    })
                    selectType.trigger('change');
                });
            </script>
        ";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'><h3>" . __('Options', 'autoexportsearches') . "</h3></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Send mail to', 'autoexportsearches') . "</td>";
        echo "<td>";
        echo Html::input('sendto', ['type' => 'mail', 'value' => $this->fields['sendto']]);
        echo "</td>";

        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Active') . "</td>";
        echo "<td>";
        Dropdown::showYesNo("is_active", $this->fields['is_active']);
        echo "</td>";

        echo "</tr>";


        $this->showFormButtons($options);

        return true;
    }

    /**
     * @return array
     */
    static function getMenuContent()
    {
        $menu['title'] = self::getMenuName(2);
        $menu['page'] = self::getSearchURL(false);
        $menu['links']['search'] = self::getSearchURL(false);

        $menu['icon'] = static::getIcon();
        if (self::canCreate()) {
            $menu['links']['add'] = PLUGIN_AUTOEXPORTSEARCH_DIR_NOFULL . "/front/exportconfig.form.php";
        }


        return $menu;
    }


    static function getIcon()
    {
        return "ti ti-tags";
    }


    /**
     * Display datas extracted from DB
     *
     * @param array $data Array of search datas prepared to get datas
     *
     * @return void
     **/
    static function createCSVFile(array $data, $filename)
    {
        global $CFG_GLPI;

        $file = fopen($filename, "w");
        if (!$file) {
            return false;
        }

        // UTF-8 BOM pour Excel
        fwrite($file, pack("CCC", 0xef, 0xbb, 0xbf));

        $headers = [];
        $metanames = [];


        foreach ($data['data']['cols'] as $col) {
            $name = $col['name'];

            // Gestion du groupname
            if (isset($col['groupname'])) {
                if (is_array($col['groupname'])) {
                    $col['groupname'] = $col['groupname']['name'];
                }
                $name = $col['groupname'] . ' - ' . $name;
            }


            if ($data['itemtype'] != $col['itemtype']) {
                if (!isset($metanames[$col['itemtype']])) {
                    if ($metaitem = getItemForItemtype($col['itemtype'])) {
                        $metanames[$col['itemtype']] = $metaitem->getTypeName();
                    } else {
                        $metanames[$col['itemtype']] = $col['itemtype'];
                    }
                }
                $name = $metanames[$col['itemtype']] . ' - ' . $name;
            }

            $headers[] = $name;
        }


        if (isset($CFG_GLPI["union_search_type"][$data['itemtype']])) {
            $headers[] = __('Item type');
        }


        fputcsv($file, $headers, ';');


        foreach ($data['data']['rows'] as $row) {
            $line = [];

            foreach ($data['data']['cols'] as $col) {
                $colkey = "{$col['itemtype']}_{$col['id']}";
                $value = $row[$colkey]['displayname'] ?? '';


                $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $value = preg_replace("/[\r\n]+/", ' ', $value);

                $line[] = trim($value);
            }


            if (isset($CFG_GLPI["union_search_type"][$data['itemtype']])) {
                $type = $row['TYPE'] ?? $data['itemtype'];
                $typename = '';
                if ($item = getItemForItemtype($type)) {
                    $typename = $item->getTypeName();
                }
                $line[] = $typename;
            }

            fputcsv($file, $line, ';');
        }

        fclose($file);
        return true;
    }




    static function executeExport($plugin_exportconfigs_id)
    {
        global $CFG_GLPI;


        $export = new self();
        $export->getFromDB($plugin_exportconfigs_id);

        $search = new SavedSearch();
        $customSearchCriteria = new PluginAutoexportsearchesCustomsearchcriteria();
        if ($search->getFromDB($export->fields['savedsearches_id'])) {
            $url = "?" . $search->fields["query"];
            $url_components = parse_url($url);
            parse_str($url_components['query'], $p);
            $p["display_type"] = Search::CSV_OUTPUT;
            $p["export_all"] = 1;
            $itemtype = $search->fields["itemtype"];
            $weekday = date('w');

            $customCriterias = $customSearchCriteria->find(['exportconfigs_id' => $plugin_exportconfigs_id]);
            foreach ($customCriterias as $customCriteria) {
                $criteria = array_filter($p['criteria'], function ($c) use ($customCriteria) {
                    return $c['field'] == $customCriteria['criteria_field'] && $c['searchtype'] == $customCriteria['criteria_searchtype'];
                });
                $criteria = reset($criteria);
                if (preg_match('/^-\d+MONTH$/', $criteria['value'])
                    && $customCriteria['criteria_value'] == PluginAutoexportsearchesCustomsearchcriteria::CRITERIA_FIRST_DAY_OF_MONTH) {
                    $normalValue = strtotime($criteria['value']);
                    $monthYearString = date('F Y', $normalValue);
                    $newValue = strtotime(
                        PluginAutoexportsearchesCustomsearchcriteria::CRITERIA_FIRST_DAY_OF_MONTH,
                        strtotime($monthYearString)
                    );
                    $criteria['value'] = date('Y-m-d', $newValue). '00:00:00';
                }
                if (preg_match('/^-\d+WEEK$/', $criteria['value'])
                    && $customCriteria['criteria_value'] == PluginAutoexportsearchesCustomsearchcriteria::CRITERIA_FIRST_DAY_OF_WEEK) {
                    // don't need to adjust if its already monday
                    if ($weekday != 1) {
                        $normalValue = strtotime($criteria['value']);
                        $newValue = strtotime(
                            PluginAutoexportsearchesCustomsearchcriteria::CRITERIA_FIRST_DAY_OF_WEEK,
                            $normalValue
                        );
                        $criteria['value'] = date('Y-m-d', $newValue). '00:00:00';
                    }
                }
            }

            $params = Search::manageParams($itemtype, $p, 1, 1);
            $name = Dropdown::getDropdownName('glpi_savedsearches', $export->fields['savedsearches_id']);
            $name .= "_" . date('Y_m_d_H_i_s') . ".csv";
            $titleMail = $name;
            $filename = GLPI_PLUGIN_DOC_DIR . "/autoexportsearches/" . $name;
            self::createCSVFile(Search::getDatas($itemtype, $params), $filename);
            if (!empty($export->fields['sendto'])) {
                self::sendMail($titleMail, $export->fields['sendto'], $name, $filename);
            }
        }
    }

    // Cron action

    /**
     * @param $name
     *
     * @return array
     */
    static function cronInfo($name)
    {
        switch ($name) {
            case 'AutoexportsearchesExportconfigExport':
                return [
                    'description' => __('Export saved searches', 'autoexportsearches')
                ];   // Optional
                break;
        }
        return [];
    }

    static function sendMail($title, $recipient, $filename, $filepath)
    {
        global $CFG_GLPI;

        $mmail = new GLPIMailer();

        $mmail->AddCustomHeader("Auto-Submitted: auto-generated");
        // For exchange
        $mmail->AddCustomHeader("X-Auto-Response-Suppress: OOF, DR, NDR, RN, NRN");
        if (empty($CFG_GLPI["from_email"])) {
            $config = new Config();
            $results = $config->find(array('name' => 'from_email'));

            foreach ($results as $result) {
                $mmail->SetFrom($result['value'], $CFG_GLPI["from_email_name"], false);
            }
        } else {
            $mmail->SetFrom($CFG_GLPI["from_email"], $CFG_GLPI["from_email_name"], false);
        }

        $text = __('Mail autoexportsearches');

        $mmail->AddAddress($recipient, $recipient);
        $mmail->Subject = "[GLPI] " . $title;
        $mmail->Body = $text;

//      $mmail->AddEmbeddedImage($filepath,
//                               0,
//                               $filename,
//                               'base64',
//                               'text/csv');
        $mmail->addAttachment(
            $filepath,
            $filename
        );


        if (!$mmail->Send()) {
            Session::addMessageAfterRedirect(
                __('Failed to send email to ' . $recipient),
                false,
                ERROR
            );
            GLPINetwork::addErrorMessageAfterRedirect();
            return false;
        } else {
            Session::addMessageAfterRedirect(__('Mail send to ' . $recipient));
            return true;
        }
    }

    /**
     * Cron action on badges : ExpiredBadges or BadgesWhichExpire
     *
     * @param $task for log, if NULL display
     *
     *
     * @return int
     */
    static function cronAutoexportsearchesExportconfigExport($task = null)
    {
        global $DB, $CFG_GLPI;
        if (!isset($CFG_GLPI['planning_work_days'])) {
            $CFG_GLPI['planning_work_days'] = importArrayFromDB($CFG_GLPI['planning_work_days']);
        }

        $cron_status = 0;
        $old_memory = ini_set("memory_limit", "-1");
        $old_execution = ini_set("max_execution_time", "0");
        $dateActual = strtotime(date("Y-m-d"));
        $day = date('j'); // 1 to 31, today
        $weekday = date('w'); // 0 to 6, today
        $month = date('m'); // 01 to 12, current month
        $monthLength = date('t'); // 28 to 31, length of the current month
        $exportConfig = new PluginAutoexportsearchesExportconfig();
        $exportConfigs = $exportConfig->find([
            'is_deleted' => 0,
            'is_active' => 1,
        ]);
        $count = 0;
        $user_id_back = Session::getLoginUserID();
        $user = new User();
        foreach ($exportConfigs as $export) {
            // check if export has to be done
        if ($export['periodicity_type'] == self::PERIODICITY_MINUTES) {
                // Frequency in minutes
            $dateActual = strtotime(date("Y-m-d H:i:s"));
            $delay = 60 * intval($export['periodicity']); // Delay calculation in seconds

            if ($export['last_export'] != null) {
                $dateEnd = strtotime($export['last_export']) + $delay;
                if ($dateEnd > $dateActual) {
                    continue;
                }
            }
        }
        elseif ($export['periodicity_type'] == self::PERIODICITY_HOURS) {
                // Periodicity in hours
            $dateActual = strtotime(date("Y-m-d H:i:s"));
            $delay = 3600 * intval($export['periodicity']); // Delay calculation in seconds
            if ($export['last_export'] != null) {
                $dateEnd = strtotime($export['last_export']) + $delay;
                if ($dateEnd > $dateActual) {
                    continue;
                }
            }
        }
        elseif ($export['periodicity_type'] == self::PERIODICITY_DAYS) {
                // every worked day
                if ($export['periodicity'] == 1 && $export['periodicity_open_days'] == 1) {
                    if (!in_array($weekday, $CFG_GLPI['planning_work_days'])) {
                        continue;
                    }
                }
                // regular every x days
                $delay = DAY_TIMESTAMP * intval($export['periodicity']);
                if ($export['last_export'] != null) {
                    $dateEnd = strtotime($export['last_export']) + $delay;
                    if ($dateEnd > $dateActual) {
                        continue;
                    }
                }
            } elseif ($export['periodicity_type'] == self::PERIODICITY_WEEKLY) {
                if ($weekday != $export['periodicity']) {
                    continue;
                }
            } elseif ($export['periodicity_type'] == self::PERIODICITY_MONTHLY) {
                if ($export['last_export'] != null) {
                    $exportMonth = date('m', strtotime($export['last_export']));
                    // already done for this month
                    if ($exportMonth == $month) {
                        continue;
                    }
                    // too early in the month,
                    // second condition for shorter months
                    if ($day < $export['periodicity']
                        && $export['periodicity'] <= $monthLength) {
                        continue;
                    }
                    // for shorter month, do the export on the last day of the month if periodicity > duration of the current month
                    if ($export['periodicity'] > $monthLength
                        && $day != $monthLength) {
                        continue;
                    }
                    // second condition ensure that it happens at least once a month even if the last day isn't a workday
                    if ($export['periodicity_open_days'] == 1 && $day != $monthLength) {
                        // today's not a work day,
                        if (!in_array($weekday, $CFG_GLPI['planning_work_days'])) {
                            continue;
                        }
                    }
                } else {
                    // for the first export, only done on the exact date or the last day of the month
                    if ($day != $export['periodicity'] && $export['periodicity'] <= $monthLength) {
                        continue;
                    }
                    if ($export['periodicity'] > $monthLength && $day != $monthLength) {
                        continue;
                    }
                    if ($export['periodicity_open_days'] == 1 && $day != $monthLength) {
                        if (!in_array($weekday, $CFG_GLPI['planning_work_days'])) {
                            continue;
                        }
                    }
                }
            }

            $_SESSION["glpicronuserrunning"] = $export['users_id'];
            $_SESSION['glpidefault_entity'] = 0;
            Session::initEntityProfiles($export['users_id']);
            $user = new User();
            $user->getFromDB($export['users_id']);
            $profile = new Profile();
            $savedProfile = $_SESSION['glpiactiveprofile'] ?? 0;
            if ($profile->getFromDB($user->fields['profiles_id'])) {
                $_SESSION['glpiactiveprofile'] = $profile->fields;
            }
            $_SESSION['glpiname'] = 'crontab';
            $_SESSION['glpiactiveentities'] = getSonsOf('glpi_entities', 0);
            $user->getFromDB($export['users_id']);
            $auth = new Auth();
            $auth->auth_succeded = true;
            $auth->user = $user;
            Session::init($auth);
            Session::loadGroups();
            $user = new  User();
            $user->getFromDB($export['users_id']);
            $_SESSION["glpiID"] = $export['users_id'];
            $_SESSION["glpicronuserrunning"] = $export['users_id'];
            self::executeExport($export['id']);
            $_SESSION['glpiactiveprofile'] = $savedProfile;

            // add custom criterias for call to createCriterias
            $customSearchCriteria = new PluginAutoexportsearchesCustomsearchcriteria();
            $customCriterias = $customSearchCriteria->find(['exportconfigs_id' => $export['id']]);
            $export['custom_criterias'] = $customCriterias;
            if ($export['periodicity_type'] == self::PERIODICITY_MINUTES || $export['periodicity_type'] == self::PERIODICITY_HOURS) {
                $export['last_export'] = date("Y-m-d H:i:s");
            }else{
                $export['last_export'] = date("Y-m-d");
            }

            $exportConfig->update($export);
            $count++;
        }
        $user->getFromDB($user_id_back);
        $auth = new Auth();
        $auth->auth_succeded = true;
        $auth->user = $user;
        Session::init($auth);
        ini_set("memory_limit", $old_memory);
        ini_set("max_execution_time", $old_execution);
        $task->addVolume($count);

        return true;
    }
}
