<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 autoexportsearches plugin for GLPI
 Copyright (C) 2020-2022 by the autoexportsearches Development Team.

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

include('../../../inc/includes.php');
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

switch ($_POST['action']) {
   //display locations
   case 'loadSearches':
      if (isset($_POST["users_id"])) {
         $val = $_POST['searches_id'];
         if($_POST['users_id'] != $_POST["current_user"]){
            $val = 0;
         }
         $rand = mt_rand();
         SavedSearch::dropdown([
                                  'name'   => 'searches_id',
                                  'value'  => $val,
                                  'condition' => ['users_id' => $_POST['users_id']],
                                  'rand'   => $_POST["rand"]
                               ]);
      }
      break;
}


