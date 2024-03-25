<?php
/*
 -------------------------------------------------------------------------
 autoexportsearches plugin for GLPI
 Copyright (C) 2016-2024 by the autoexportsearches Development Team.

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

include('../../../inc/includes.php');
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

switch ($_POST['action']) {
   case 'loadSearches':
      if (isset($_POST["users_id"])) {
         $val = $_POST['savedsearches_id'];
         if($_POST['users_id'] != $_POST["current_user"]){
            $val = 0;
         }
         $rand = mt_rand();
         SavedSearch::dropdown([
                                  'name'   => 'savedsearches_id',
                                  'value'  => $val,
                                  'condition' => ['users_id' => $_POST['users_id']],
                                  'rand'   => $_POST["rand"]
                               ]);
         $url = Plugin::getWebDir('autoexportsearches') . "/ajax/customsearchcriterias.php";
         $exportConfigId = isset($_POST['exportconfigs_id']) ? $_POST['exportconfigs_id'] : 0;
         echo "
            <script>
                if (!window.autoexportsearches) window.autoexportsearches = {};
                autoexportsearches.searchSelect = $('#dropdown_savedsearches_id{$_POST["rand"]}');
                autoexportsearches.searchSelect.change(e => {
                    $('#custom_search_criterias').load('$url', {
                        'savedsearches_id' : e.target.options[e.target.selectedIndex].value,
                        'exportconfigs_id' : $exportConfigId
                    })
                })
                autoexportsearches.searchSelect.trigger('change');
            </script>
         ";
      }
      break;
}


