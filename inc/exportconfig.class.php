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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginautoexportsearchesTicket
 */
class PluginAutoexportsearchesExportconfig extends CommonDBTM {

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
   static function getTypeName($nb = 0) {
      return __('Auto export config', 'autoexportsearches');
   }

   //   /**
   //    * @return bool|int
   //    */
   //   static function canView() {
   //      return Session::haveRight(self::$rightname, READ);
   //   }
   //
   //   /**
   //    * @return bool
   //    */
   //   static function canCreate() {
   //      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   //   }

   /**
    * @return array
    */
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(2)
      ];
      $tab[] = [
         'id'            => '1',
         'table'         => self::getTable(),
         'field'         => 'id',
         'name'          => __('ID'),
         'massiveaction' => false,
         'datatype'      => 'itemlink'
      ];

      $tab[] = [
         'id'       => '2',
         'table'    => User::getTable(),
         'field'    => 'name',
         'name'     => __('User who owns the saved search', 'autoexportsearches'),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'        => '3',
         'table'     => SavedSearch::getTable(),
         'field'     => 'name',
         'name'      => __('Saved search to export', 'autoexportsearches'),
         'datatype'  => 'dropdown',
         'linkfield' => 'searches_id',
      ];

      $tab[] = [
         'id'       => '4',
         'table'    => self::getTable(),
         'field'    => 'periodicity',
         'name'     => __('Periodicity (in days)', 'autoexportsearches'),
         'datatype' => 'number'
      ];

      $tab[] = [
         'id'       => '5',
         'table'    => self::getTable(),
         'field'    => 'last_export',
         'name'     => __('Last export', 'autoexportsearches'),
         'datatype' => 'date'
      ];
      $tab[] = [
         'id'       => '6',
         'table'    => self::getTable(),
         'field'    => 'is_active',
         'name'     => __('Active'),
         'datatype' => 'bool'
      ];


      return $tab;

   }

   function showForm($ID, $options = []) {
      global $CFG_GLPI;


      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('User who owns the saved search', 'autoexportsearches') . "</td>";
      echo "<td>";

      $rand = mt_rand();
      User::dropdown([
                        'name'  => 'users_id',
                        'value' => $this->fields["users_id"],
                        //                        'entity' => $this->fields["entities_id"],
                        'right' => 'own_ticket',
                        'rand'  => $rand
                     ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Saved search to export', 'autoexportsearches') . "</td>";
      echo "<td id='savedSearches'>";
      //      echo "<div id='savedSearches'>";
      SavedSearch::dropdown([
                               'name'      => 'searches_id',
                               'value'     => $this->fields["searches_id"],
                               'condition' => ['users_id' => $this->fields["users_id"]],
                               'rand'      => $rand
                            ]);
      //      echo "</div>";
      echo "</td>";
      echo "</tr>";
      $params = [
         "users_id"     => '__VALUE__',
         "current_user" => $this->fields['users_id'],
         'searches_id'  => $this->fields["searches_id"],
         "rand"         => $rand,
         "action"       => "loadSearches"
      ];
      $url    = Plugin::getWebDir('autoexportsearches') . "/ajax/dropdownsavedsearches.php";
      Ajax::updateItemOnSelectEvent("dropdown_users_id$rand", "savedSearches", $url, $params);

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Periodicity (in days)', 'autoexportsearches') . "</td>";
      echo "<td>";

      $rand = mt_rand();
      Dropdown::showNumber('periodicity',
                           [
                              'value' => $this->fields['periodicity'],
                              'rand'  => $rand
                           ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
       echo "<td>" . __('Send mail to','autoexportsearches') . "</td>";
       echo "<td>";
       echo Html::input('sendto',['type' => 'mail','value' => $this->fields['sendto']]);
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
   static function getMenuContent() {

      $menu['title']           = self::getMenuName(2);
      $menu['page']            = self::getSearchURL(false);
      $menu['links']['search'] = self::getSearchURL(false);

      $menu['icon'] = static::getIcon();
      if (self::canCreate()) {
         $menu['links']['add'] = PLUGIN_AUTOEXPORTSEARCH_DIR_NOFULL . "/front/exportconfig.form.php";
      }


      return $menu;
   }


   static function getIcon() {
      return "ti ti-tags";
   }


   /**
    * Display datas extracted from DB
    *
    * @param array $data Array of search datas prepared to get datas
    *
    * @return void
    **/
   static function createCSVFile(array $data, $filename) {

      global $CFG_GLPI;
      $file = fopen($filename, "w");
      fwrite($file, pack("CCC", 0xef, 0xbb, 0xbf));
      $item = null;
      if (class_exists($data['itemtype'])) {
         $item = new $data['itemtype']();
      }
      $data['display_type'] = Search::CSV_OUTPUT;
      if (!isset($data['data']) || !isset($data['data']['totalcount'])) {
         return false;
      }
      // Contruct Pager parameters
      $globallinkto
                  = Toolbox::append_params(['criteria'
                                            => Toolbox::stripslashes_deep($data['search']['criteria']),
                                            'metacriteria'
                                            => Toolbox::stripslashes_deep($data['search']['metacriteria'])],
                                           '&amp;');
//      $parameters = "sort=" . $data['search']['sort'] . "&amp;order=" . $data['search']['order'] . '&amp;' .
//                    $globallinkto;
//
//      if (isset($_GET['_in_modal'])) {
//         $parameters .= "&amp;_in_modal=1";
//      }

//            print_r($data);

      // If the begin of the view is before the number of items
      if ($data['data']['count'] > 0) {
         // Display pager only for HTML

         // Define begin and end var for loop
         // Search case
         $begin_display = $data['data']['begin'];
         $end_display   = $data['data']['end'];


         if ($data['search']['as_map'] == 0) {
            $massformid = 'massform' . $data['itemtype'];


            // Compute number of columns to display
            // Add toview elements
            $nbcols = count($data['data']['cols']);


            // New Line for Header Items Line
            $headers_line        = '';
            $headers_line_top    = '';
            $headers_line_bottom = '';

            $header_num = 1;


            // Display column Headers for toview items
            $metanames = [];
            foreach ($data['data']['cols'] as $val) {
               $linkto = '';
               if (!$val['meta']
                   && !$data['search']['no_sort']
                   && (!isset($val['searchopt']['nosort'])
                       || !$val['searchopt']['nosort'])) {

                  $linkto = $data['search']['target'] . (strpos($data['search']['target'], '?') ? '&amp;' : '?') .
                            "itemtype=" . $data['itemtype'] . "&amp;sort=" .
                            $val['id'] . "&amp;order=" .
                            (($data['search']['order'] == "ASC") ? "DESC" : "ASC") .
                            "&amp;start=" . $data['search']['start'] . "&amp;" . $globallinkto;
               }

               $name = $val["name"];

               // prefix by group name (corresponding to optgroup in dropdown) if exists
               if (isset($val['groupname'])) {
                  $groupname = $val['groupname'];
                  if (is_array($groupname)) {
                     //since 9.2, getSearchOptions has been changed
                     $groupname = $groupname['name'];
                  }
                  $name = "$groupname - $name";
               }

               // Not main itemtype add itemtype to display
               if ($data['itemtype'] != $val['itemtype']) {
                  if (!isset($metanames[$val['itemtype']])) {
                     if ($metaitem = getItemForItemtype($val['itemtype'])) {
                        $metanames[$val['itemtype']] = $metaitem->getTypeName();
                     }
                  }
                  $name = sprintf(__('%1$s - %2$s'), $metanames[$val['itemtype']],
                                  $val["name"]);
               }

               $headers_line .= Search::showHeaderItem($data['display_type'],
                                                       $name,
                                                       $header_num, $linkto,
                  (!$val['meta']
                   && ($data['search']['sort'] == $val['id'])),
                                                       $data['search']['order']);
            }

            // Add specific column Header
            if (isset($CFG_GLPI["union_search_type"][$data['itemtype']])) {
               $headers_line .= Search::showHeaderItem($data['display_type'], __('Item type'),
                                                       $header_num);
            }
            // End Line for column headers
            $headers_line .= Search::showEndLine($data['display_type']);

            $headers_line_top .= $headers_line;

            fwrite($file, $headers_line_top);

            // Num of the row (1=header_line)
            $row_num = 1;


            $typenames = [];
            // Display Loop
            foreach ($data['data']['rows'] as $rowkey => $row) {
               $line = "";
               // Column num
               $item_num = 1;
               $row_num++;
               // New line
               $line .= Search::showNewLine($data['display_type'], ($row_num % 2),
                                            $data['search']['is_deleted']);

               $current_type = (isset($row['TYPE']) ? $row['TYPE'] : $data['itemtype']);


               // Add item in item list
               Session::addToNavigateListItems($current_type, $row["id"]);


               // Print other toview items
               foreach ($data['data']['cols'] as $col) {
                  $colkey = "{$col['itemtype']}_{$col['id']}";
                  if (!$col['meta']) {
                     //TODO cgange to write CSV
                     $line .= Search::showItem($data['display_type'], $row[$colkey]['displayname'],
                                               $item_num, $row_num,
                                               Search::displayConfigItem($data['itemtype'], $col['id'],
                                                                         $row, $colkey));
                  } else { // META case
                     $line .= Search::showItem($data['display_type'], $row[$colkey]['displayname'],
                                               $item_num, $row_num);
                  }
               }

               if (isset($CFG_GLPI["union_search_type"][$data['itemtype']])) {
                  if (!isset($typenames[$row["TYPE"]])) {
                     if ($itemtmp = getItemForItemtype($row["TYPE"])) {
                        $typenames[$row["TYPE"]] = $itemtmp->getTypeName();
                     }
                  }
                  $line .= Search::showItem($data['display_type'], $typenames[$row["TYPE"]],
                                            $item_num, $row_num);
               }
               // End Line
               $line .= Search::showEndLine($data['display_type']);

               fwrite($file, $line);

            }


         }
      }

      fclose($file);
   }

   static function executeExport($plugin_exportconfigs_id) {
      global $CFG_GLPI;


      $export = new self();
      $export->getFromDB($plugin_exportconfigs_id);

      $search = new SavedSearch();
      if ($search->getFromDB($export->fields['searches_id'])) {
         $url            = "?" . $search->fields["query"];
         $url_components = parse_url($url);
         parse_str($url_components['query'], $p);
         $p["display_type"] = Search::CSV_OUTPUT;
         $p["export_all"]   = 1;
         $itemtype          = $search->fields["itemtype"];
         $params            = Search::manageParams($itemtype, $p, 1, 1);
         $name              = Dropdown::getDropdownName('glpi_savedsearches', $export->fields['searches_id']);
         $name              .= "_" . date('Ymd') . ".csv";
          $titleMail = $name;
          $filename          = GLPI_PLUGIN_DOC_DIR . "/autoexportsearches/" . $name;
         self::createCSVFile(Search::getDatas($itemtype, $params), $filename);
          if(!empty($export->fields['sendto'])){
              self::sendMail($titleMail,$export->fields['sendto'], $name, $filename);
          }
      }


   }

   // Cron action

   /**
    * @param $name
    *
    * @return array
    */
   static function cronInfo($name) {

      switch ($name) {
         case 'AutoexportsearchesExportconfigExport':
            return [
               'description' => __('Export saved searches', 'autoexportsearches')];   // Optional
            break;
      }
      return [];
   }

    static function sendMail($title,$recipient, $filename, $filepath) {
        global $CFG_GLPI;

        $mmail = new GLPIMailer();

        $mmail->AddCustomHeader("Auto-Submitted: auto-generated");
        // For exchange
        $mmail->AddCustomHeader("X-Auto-Response-Suppress: OOF, DR, NDR, RN, NRN");
        $mmail->SetFrom($CFG_GLPI["from_email"], $CFG_GLPI["from_email_name"], false);

        $text = __('Mail autoexportsearches');

        $mmail->AddAddress($recipient, $recipient);
        $mmail->Subject = "[GLPI] ". $title;
        $mmail->Body    = $text;

//      $mmail->AddEmbeddedImage($filepath,
//                               0,
//                               $filename,
//                               'base64',
//                               'text/csv');
        $mmail->addAttachment($filepath,
            $filename
        );


        if (!$mmail->Send()) {
            Session::addMessageAfterRedirect(__('Failed to send email to '.$recipient), false,
                ERROR);
            GLPINetwork::addErrorMessageAfterRedirect();
            return false;
        } else {
            Session::addMessageAfterRedirect(__('Mail send to '.$recipient));
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
   static function cronAutoexportsearchesExportconfigExport($task = null) {
      global $DB, $CFG_GLPI;


      $cron_status = 0;
       $old_memory = ini_set("memory_limit", "-1");
       $old_execution = ini_set("max_execution_time", "0");
      $exportConfig  = new PluginAutoexportsearchesExportconfig();
      $exportConfigs = $exportConfig->find(['is_deleted' => 0, 'is_active' => 1]);
      $count         = 0;
       $user_id_back = Session::getLoginUserID();
       $user = new User();
      foreach ($exportConfigs as $export) {
         $dateActual = strtotime(date("Y-m-d"));
         $delay      = DAY_TIMESTAMP * intval($export['periodicity']);
         if ($export['last_export'] != null) {
            $dateEnd = strtotime($export['last_export']) + $delay;
            if ($dateEnd <= $dateActual) {
               $_SESSION["glpicronuserrunning"] = $export['users_id'];
               $_SESSION['glpidefault_entity']  = 0;
               Session::initEntityProfiles($export['users_id']);
                $user = new User();
                $user->getFromDB($export['users_id']);
                $profile = new Profile();
                $savedProfile = $_SESSION['glpiactiveprofile'];
                if($profile->getFromDB($user->fields['profiles_id'])) {
                    $_SESSION['glpiactiveprofile'] =$profile->fields;
                }
                Toolbox::logInFile('test_autoexport',print_r($_SESSION['glpiactiveprofile'],true),true);
               $_SESSION['glpiname']           = 'crontab';
               $_SESSION['glpiactiveentities'] = getSonsOf('glpi_entities', 0);
               //               Session::initEntityProfiles(Session::getLoginUserID());
                $user->getFromDB($export['users_id']);
                $auth = new Auth();
                $auth->auth_succeded = true;
                $auth->user = $user;
                Session::init($auth);
               Session::loadGroups();
               $user = new  User();
               $user->getFromDB($export['users_id']);
               $_SESSION["glpiID"]              = $export['users_id'];
               $_SESSION["glpicronuserrunning"] = $export['users_id'];
//               Session::changeProfile($user->fields['profiles_id']);

               self::executeExport($export['id']);
                $_SESSION['glpiactiveprofile'] = $savedProfile;
                $export['last_export'] = date("Y-m-d");
               $exportConfig->update($export);
               $count++;
            }
         } else {
            $_SESSION["glpicronuserrunning"] = $export['users_id'];
            $_SESSION['glpidefault_entity']  = 0;
            Session::initEntityProfiles($export['users_id']);
             $user = new User();
             $user->getFromDB($export['users_id']);
             $profile = new Profile();
             $savedProfile = $_SESSION['glpiactiveprofile'];
             if($profile->getFromDB($user->fields['profiles_id'])) {
                 $_SESSION['glpiactiveprofile'] =$profile->fields;
             }
             Toolbox::logInFile('test_autoexport',print_r($_SESSION['glpiactiveprofile'],true),true);
            $_SESSION['glpiname']           = 'crontab';
            $_SESSION['glpiactiveentities'] = getSonsOf('glpi_entities', 0);
            //               Session::initEntityProfiles(Session::getLoginUserID());
             $user->getFromDB($export['users_id']);
             $auth = new Auth();
             $auth->auth_succeded = true;
             $auth->user = $user;
             Session::init($auth);
            Session::loadGroups();
            $user = new  User();
            $user->getFromDB($export['users_id']);
            $_SESSION["glpiID"]              = $export['users_id'];
            $_SESSION["glpicronuserrunning"] = $export['users_id'];

//            Session::changeProfile($user->fields['profiles_id']);
            self::executeExport($export['id']);
             $_SESSION['glpiactiveprofile'] = $savedProfile;
             $export['last_export'] = date("Y-m-d");
            $exportConfig->update($export);
            $count++;
         }


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
