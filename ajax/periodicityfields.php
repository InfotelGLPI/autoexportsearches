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

include('../../../inc/includes.php');
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

$id = 0;
if (isset($_POST['id']) && $_POST['id']) {
    $id = $_POST['id'];
}
if (Session::haveRight("plugin_autoexportsearches_exportconfigs", READ)
    && Session::haveRight("plugin_autoexportsearches_exportconfigs", UPDATE)) {
    $exportConfig = null;
    if ($id > 0) {
        $exportConfig = new PluginAutoexportsearchesExportconfig();
        $exportConfig->getFromDB($id);
    }
    switch ($_POST['periodicity_type']) {
     case PluginAutoexportsearchesExportconfig::PERIODICITY_MINUTES:
         echo "<td>" . __('Periodicity (in minutes)', 'autoexportsearches') . "</td><td><div>";

    $rand = mt_rand();

    Dropdown::showNumber(
        'periodicity',
        [
            'value' => $exportConfig ? $exportConfig->fields['periodicity'] : 1,
            'rand' => $rand,
            'min' => 1,
            'max' => 59
        ]
    );
    echo "</div></td>";
    break;
        case PluginAutoexportsearchesExportconfig::PERIODICITY_HOURS:
            echo "<td>" . __('Periodicity (in hours)', 'autoexportsearches') . "</td><td><div>";
            $rand = mt_rand();
            Dropdown::showNumber(
                'periodicity',
                [
                    'value' => $exportConfig ? $exportConfig->fields['periodicity'] : 1,
                    'rand' => $rand,
                    'min' => 1,
                    'max' => 23
                ]
            );
            echo "</div></td>";
            break;
        case PluginAutoexportsearchesExportconfig::PERIODICITY_DAYS:
            echo "<td>" . __('Periodicity (in days)', 'autoexportsearches') . "</td>";
            echo "<td><div>";
            $rand = mt_rand();
            Dropdown::showNumber(
                'periodicity',
                [
                    'value' => $exportConfig ? $exportConfig->fields['periodicity'] : 1,
                    'rand' => $rand,
                    'min' => 1
                ]
            );
            $openDaysLabel = __('Work day only', 'autoexportsearches');
            $checked = $exportConfig ? $exportConfig->fields['periodicity_open_days'] == 1 ? 'checked' : '' : '';
            $openDaysExplanation = __(
                'If this option is checked, the export will be done only on worked day',
                'autoexportsearches'
            );
            echo "
            </div>
            <div id='periodicity_open_days'>
                <div style='position:relative'>
                <label for='periodicity_open_days' class='me-2'>
                    $openDaysLabel <span class='fas fa-info fa-fw info-icon'></span>
                  <small class='info-text'>$openDaysExplanation</small>
                </label>
                <input name='periodicity_open_days' type='checkbox' class='form-check-input' value='1' $checked>
                </div>
            </div>
            <style>
                .info-text {
                    display: none;
                    position: absolute;
                    top: 0;
                    right: 0;
                    background-color: white; /* Optional: Adding a background color for better visibility */
                    padding: 5px; /* Optional: Adding padding for better spacing */
                    border: 1px solid #ccc; /* Optional: Adding a border for better separation */
                }
                .info-icon:hover + .info-text {
                    display: block;
                }
            </style>
        ";
            echo "
            <script>
                if (!window.autoexportsearches) window.autoexportsearches = {};
                autoexportsearches.periodicitySelect = $('#dropdown_periodicity$rand');
                autoexportsearches.openDaysContainer = $('#periodicity_open_days');
                autoexportsearches.periodicitySelect.change(e => {
                    if (e.target.options[e.target.selectedIndex].value == 1) {
                        autoexportsearches.openDaysContainer[0].style.display = '';
                    } else {
                        autoexportsearches.openDaysContainer[0].style.display = 'none';
                    }
                })
                autoexportsearches.periodicitySelect.trigger('change');
            </script>
         ";
            echo "</td>";
            break;

        case PluginAutoexportsearchesExportconfig::PERIODICITY_WEEKLY:
            echo "<td>" . __('Weekday', 'autoexportsearches') . "</td><td>";

            $rand = mt_rand();
            Dropdown::showFromArray(
                'periodicity',
                Toolbox::getDaysOfWeekArray(),
                [
                    'value' => $exportConfig ? $exportConfig->fields['periodicity'] : 1,
                    'rand' => $rand
                ]
            );
            echo "</td>";
            break;

        case PluginAutoexportsearchesExportconfig::PERIODICITY_MONTHLY:
            echo "<td>" . __('Day of the month', 'autoexportsearches') . "</td><td><div>";
            $rand = mt_rand();
            Dropdown::showNumber(
                'periodicity',
                [
                    'value' => $exportConfig ? $exportConfig->fields['periodicity'] : 1,
                    'rand' => $rand,
                    'min' => 1,
                    'max' => 31
                ]
            );
            echo '<small class="ms-2">' . __(
                    'For months having less days than the selected day, the export will be done on the last day of the month.',
                    'autoexportsearches'
                ) . '</small>';
            echo '</div>';
            $openDaysLabel = __('Work day only', 'autoexportsearches');
            $checked = $exportConfig ? $exportConfig->fields['periodicity_open_days'] == 1 ? 'checked' : '' : '';
            $openDaysExplanation = __(
                'If this option is checked, the export will be done the first work day from the selected day',
                'autoexportsearches'
            );
            echo "
            </div>
            <div id='periodicity_open_days'>
                <div style='position:relative'>
                <label for='periodicity_open_days' class='me-2'>
                    $openDaysLabel <span class='fas fa-info fa-fw info-icon'></span>
                  <small class='info-text'>$openDaysExplanation</small>
                </label>
                <input name='periodicity_open_days' type='checkbox' class='form-check-input' value='1' $checked>
                </div>
            </div>
            <style>
                .info-text {
                    display: none;
                    position: absolute;
                    top: 0;
                    right: 0;
                    background-color: white; /* Optional: Adding a background color for better visibility */
                    padding: 5px; /* Optional: Adding padding for better spacing */
                    border: 1px solid #ccc; /* Optional: Adding a border for better separation */
                }
                .info-icon:hover + .info-text {
                    display: block;
                }
            </style>
        ";
            echo "</td>";
            break;
    }
} else {
    Html::displayRightError();
}


