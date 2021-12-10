<?php
/*
 -------------------------------------------------------------------------
 Autoexportsearches plugin for GLPI
 Copyright (C) 2003-2016 by the Autoexportsearches Development Team.

 -------------------------------------------------------------------------

 LICENSE

 This file is part of Autoexportsearches.

 Autoexportsearches is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Autoexportsearches is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Autoexportsearches. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die ("Sorry. You can't access directly to this file");
}

/**
 * Class PluginAutoexportsearchesFiles
 */
class PluginAutoexportsearchesFiles extends CommonDBTM {

   static $rightname = 'plugin_autoexportsearches_accessfiles';

   static function getTypeName($nb = 0) {
      return __('Download files', 'autoexportsearches');
   }

   function showMenu() {
      global $CFG_GLPI;

      echo "<div align='center'>";
      echo "<table class='tab_cadre' cellpadding='5' height='150'>";
      echo "<tr>";
      echo "<th colspan='6'>" . self::getTypeName() . "</th>";
      echo "</tr>";
      $types = Self::getTypes();
      $max = count($types);
      for ($i=0;$i<$max;$i=+3){
         echo "<tr>";
         if(($max-$i) >=3){
            $size = 2;
         } else if(($max-$i) == 2) {
            $size = 3;
         } else{
            $size = 6;
         }
         if(isset($types[$i])){
            $type = $types[$i];
            echo "<td class='center' colspan='$size'>";
            echo "<a href='./files.php?type=$type'>";
            echo "<i class=\"fas fa-folder-open fa-4x\"></i>";
            echo "<br>" . $type . "</a>";
            echo "</td>";

         }

         if(isset($types[$i+1])){
            $type = $types[$i+1];
            echo "<td class='center' colspan='$size'>";
            echo "<a href='./files.php?type=$type'>";
            echo "<i class=\"fas fa-folder-open fa-4x\"></i>";
            echo "<br>" . $type . "</a>";
            echo "</td>";
         }

         if(isset($types[$i+2])){
            $type = $types[$i+2];
            echo "<td class='center' colspan='$size'>";
            echo "<a href='./files.php?type=$type'>";
            echo "<i class=\"fas fa-folder-open fa-4x\"></i>";
            echo "<br>" . $type . "</a>";
            echo "</td>";
         }



         echo "</tr>";
      }
      if($max == 0){
         echo "<tr><td class='center tab_bg_1' colspan='6'>";
         echo __("No export files available",'autoexportsearches');
         echo "</td></tr>";
      }
      echo "</table></div>";
   }

   function getTypes(){
      $types = [];
      $config = new PluginAutoexportsearchesConfig();
      $config->getFromDB(1);
      $dir = GLPI_PLUGIN_DOC_DIR . $config->getField('folder');
      //If the dir folder exist
      if (is_dir($dir)) {
         // Get all files in an array
         $files = scandir($dir);
         foreach ($files as $file){
            $type = substr($file,0,strpos($file,'_'));
            if(!in_array($type,$types) && !is_dir($dir . "/" . $file)){
               array_push($types,$type);
            }
         }

      }
      return $types;
   }
   /** Show All files in a HTML table
    * @param $dir
    */
   function showListFiles($dir,$type) {
      global $CFG_GLPI;

      echo "<div class='center'>";
      echo "<h1>" . __('Files', 'autoexportsearches') . "</h1>";
      //If the dir folder exist
      if (is_dir($dir)) {
         // Get all files in an array
         $files = $this->processFiles("get","",$type);

         // If there is files in the folder
         if($files == true){
            //Pagination
            $limitBegin = 0;
            $nbRows = count($files);
            if (isset($_GET['start'])) {
               $limitBegin = $_GET['start'];
            }
            if (isset($_SESSION['glpilist_limit'])) {
               $limitNb = $_SESSION['glpilist_limit'];
            } else {
               $limitNb = 0;
            }
            $target = $CFG_GLPI['root_doc'] . '/plugins/autoexportsearches/front/files.php?type='.$type;
            if(isset($_GET['orderType'])){
               $parameters = "orderCol=".$_GET['orderCol']."&orderType=".$_GET['orderType'];
            } else{
               $parameters = "";
            }
            Html::printPager($limitBegin, $nbRows, $target, $parameters);
            echo "<form name='deleteCSV' method='post'>";

            echo "<table id='tableCsv' class='tab_cadrehov'><thead>";
            echo "<tr>";
            // Checkbox colomn for select delete datas
            echo "<th>
                   <div class='form-group-checkbox'>
                      <input title='". __("Delete") . "' type='checkbox' class='new_checkbox' name='checkall_delete' id='checkall_delete' 
                      onclick='checkAll(this.checked);' >";
            echo "<script>
               function checkAll(state) {
                  var cases = document.getElementsByTagName('input');
                  for(var i=0; i<cases.length; i++){
                    if(cases[i].type == 'checkbox'){
                         cases[i].checked = state;   
                     } 
                  }
              }       
            </script>";
            echo "<label class='label-checkbox' for='checkall_delete' title='" . __("Check all") . "'>
                         <span class='check'></span>
                         <span class='box'></span>
                      </label>
                   </div>
                 </th>";
            $type = "ASC";
            if(isset($_GET['orderType'])){
               if($_GET['orderType'] == "ASC"){
                  $type = "DESC";
               } else{
                  $type = "ASC";
               }
            }

            $start = 0;
            if(isset($_GET['start'])){
               $start = $_GET['start'];
            }

            echo "<th><a href='files.php?orderCol=name&orderType=$type&start=$start'>" . __('File name', 'autoexportsearches') . "</a></th>";
            echo "<th><a href='files.php?orderCol=date&orderType=$type&start=$start'>" . __('Generation date', 'autoexportsearches') . "</a></th>";
            echo "<th><a href='files.php?orderCol=month&orderType=$type&start=$start'>" . __('Generation month', 'autoexportsearches') . "</a></th>";

            echo "</thead></tr>";

            //Sort table order with headers
            if(isset($_GET['orderCol'])){
               switch ($_GET['orderCol']){
                  case 'name' :
                  case 'date' :
                     if(isset($_GET['orderType'])){
                        if($_GET['orderType'] == 'ASC'){
                           sort($files);
                        } else if($_GET['orderType'] == 'DESC'){
                           rsort($files);
                        }
                     }
                     break;
                  case 'month' :
                     if(isset($_GET['orderType'])){
                        if($_GET['orderType'] == 'ASC'){
                           usort($files,[$this,'sortArrayAsc']);
                        } else if($_GET['orderType'] == 'DESC'){
                           usort($files,[$this,'sortArrayDesc']);
                        }
                     }
                     break;
               }
            }
            foreach ($files as $key => $file) {
               if($key >= $limitBegin &&
                  $key < ($limitNb+$limitBegin)) {
                  //Show datas from file name
                  echo "<tr>";
                  $year     = $this->getDateFile($file, 'Y');
                  $month    = $this->getDateFile($file, 'm');
                  $day      = $this->getDateFile($file, 'd');
                  $dateFile = $this->getDateFile($file);

                  //Set language for mounth in str
                  $langue = $CFG_GLPI["languages"][$_SESSION['glpilanguage']][4];
                  setlocale(LC_TIME, $langue);

                  $monthStr = utf8_encode(strftime('%B', strtotime($dateFile)));

                  $date = $day . "/" . $month . "/" . $year;

                  echo "<td width='10' valign='top'>";
                  echo Html::showCheckbox(["name" => "filedelete[$file]"]);
                  echo "</td>";
                  $config = new PluginAutoexportsearchesConfig();
                  $config->getFromDB(1);

                  $folder = $config->getField("folder");
                  echo "<td><a href='" . $CFG_GLPI['root_doc'] . "/plugins/autoexportsearches/front/document.send.php?file=_plugins" . $folder. "" . $file . "' target='_blank'>" . $file . "</a></td>";
                  echo "<td>" . $date . "</td>";
                  echo "<td>" . $monthStr . "</td>";
                  echo "</tr>";
               }
            }
            echo "</table>";
            echo "<br />";
            echo Html::submit(__("Delete"),['confirm' => __('Confirm the final deletion?')]);
            echo Html::closeForm(false);
            echo "</div>";
         } else {
            echo "<div class='center b'>" . __('No file to download in the directory','autoexportsearches') . "</div>";
         }
      } else {
         echo "<div class='center b'>" . __('The folder doesn\'t exist','autoexportsearches') . "</div>";
      }
   }

   function sortArrayAsc($a,$b){
      $aMonth = substr($a,strpos($a,"_")+5,2);
      $bMonth = substr($b,strpos($b,"_")+5,2);
      return $aMonth > $bMonth;
   }

   function sortArrayDesc($a,$b){
      $aMonth = substr($a,strpos($a,"_")+5,2);
      $bMonth = substr($b,strpos($b,"_")+5,2);
      return $aMonth < $bMonth;
   }

   /** Get date in file name
    * @param        $file
    * @param string $formatOut
    *
    * @return bool|string
    */
   function getDateFile($file,$formatOut ="Ymd"){
      switch ($formatOut){
         case "Ymd" :
            $out = substr($file, strpos($file, "_") + 1, 8);
            break;
         case "Y" :
            $out = substr($file, strpos($file, "_") + 1, 4);
            break;
         case "m" :
            $out = substr($file, strpos($file, "_") + 5, 2);
            break;
         case "d" :
            $out = substr($file, strpos($file, "_") + 7, 2);
            break;
      }

      return $out;
   }

   /** Function that makes actions around files
    * @param        $action
    * @param string $file
    *
    * @return array|bool
    */
   function processFiles($action,$file ="",$type =""){
      $config = new PluginAutoexportsearchesConfig();
      $config->getFromDB(1);
      $dir = GLPI_PLUGIN_DOC_DIR . $config->getField('folder');

      switch ($action){
         case "get" :
               $res = [];
               // Get files in defined dir
               $files = scandir($dir);
               foreach ($files as $file) {
                  // if the file is not a folder
                  if($type != "" && strpos($file,$type)>-1){
                     if (!is_dir($dir . "/" . $file)) {
                        $res[] = $file;
                     }
                  }
               }
            break;
         case "delete" :
            // delete file
            $res =  unlink($dir."/".$file);
            break;
      }
      return $res;
   }

   /** Function for delete files after $nbMonths
    * @param $nbMonths
    */
   function deleteByMonths($nbMonths){
      $today = date("Ymd");
      $files = $this->processFiles("get");
      if(is_array($files)){
         foreach ($files as $file) {
            $dateFile = strtotime($this->getDateFile($file));
            $nbMonthsToAdd = "+" . $nbMonths . " months";
            $dateDiff = strtotime($nbMonthsToAdd, $dateFile);
            $dateToDelete = date('Ymd', $dateDiff);
            if($today > $dateToDelete){
               $this->processFiles("delete",$file);
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
   static function cronInfo($name) {

      switch ($name) {
         case 'DeleteFile':
            return [
               'description' => __('Delete export files', 'autoexportsearches')];   // Optional
            break;
      }
      return [];
   }

   /**
    * Cron action
    *
    * @global $DB
    * @global $CFG_GLPI
    *
    * @param  $task for log
    */
   static function cronDeleteFile($task = NULL) {

      $CronTask = new CronTask();
      if ($CronTask->getFromDBbyName("PluginAutoexportsearchesFiles", "DeleteFile")) {
         if ($CronTask->fields["state"] == CronTask::STATE_DISABLE) {
            return 0;
         }
      } else {
         return 0;
      }

      $config = new PluginAutoexportsearchesConfig();
      $config->getFromDB(1);
      $nbMonths = $config->getField('monthBeforePurge');

      $autoexportsearchesFiles = new self();
      $autoexportsearchesFiles->deleteByMonths($nbMonths);
      return 1;
   }

}
