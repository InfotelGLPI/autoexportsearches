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

$savedSearchId = null;
if (isset($_POST['savedsearches_id']) && $_POST['savedsearches_id']) {
    $savedSearchId = $_POST['savedsearches_id'];
}
if ($savedSearchId) {
    $translations = [
        'equals' => __('is'),
        'notequals' => __('is not'),
        'lessthan' => __('before'),
        'morethan' => __('after'),
        'contains' => __('contains'),
        'notcontains' => __('not contains')
    ];

    echo "<table style='width:100%'><tbody style='width:100%'>";
    $search = new SavedSearch();
    if ($search->getFromDB($savedSearchId)) {
        $url = "?" . $search->fields["query"];
        $url_components = parse_url($url);
        parse_str($url_components['query'], $p);
        if (isset($p['itemtype'])) {
            $item = getItemForItemtype($p['itemtype']);
            if ($item instanceof CommonITILObject) {
                $fields = $item->getSearchOptionsMain();
                $dateFields = array_filter($fields, function ($f) {
                    if (array_key_exists('datatype', $f)) {
                        return $f['datatype'] === 'datetime';
                    }
                    return false;
                });
                $dateFieldsIds = array_map(function ($f) {
                    return $f['id'];
                }, $dateFields);
                $headerAdded = false;
                foreach ($p['criteria'] as $index => $criteria) {
                    if (in_array($criteria['field'], $dateFieldsIds)) {
                        $value = $criteria['value'];
                        if (str_starts_with($value, '-')
                            && (str_contains($value, 'MONTH') || str_contains($value, 'WEEK'))) {
                            if (!$headerAdded) {
                                echo "<tr class='tab_bg_1'>";
                                echo "<td colspan='3'><h4>" . __(
                                        'Customise the export of : ',
                                        'autoexportsearches'
                                    ) . __(
                                        $p['itemtype']
                                    ) . "</h4></td>";
                                echo "</tr>";
                                echo "<tr class='tab_bg_1 text-center'>";
                                echo "<td><h5>" . __('Criterion') . "</h5></td>";
                                echo "<td><h5>" . __('Value', 'autoexportsearches') . "</h5></td>";
                                echo "<td><h5>" . __('Customise', 'autoexportsearches') . "</h5></td>";
                                echo "</tr>";
                                $headerAdded = true;
                            }

                            $customValue = null;
                            $customCriteria = new PluginAutoexportsearchesCustomsearchcriteria();
                            if ($customCriteria->getFromDBByCrit([
                                'savedsearches_id' => $savedSearchId,
                                'exportconfigs_id' => $_POST['exportconfigs_id'],
                                'criteria_field' => $criteria['field'],
                                'criteria_searchtype' => $criteria['searchtype']
                            ])) {
                                $customValue = $customCriteria->fields['criteria_value'];
                            }

                            $field = array_filter($dateFields, function ($f) use ($criteria) {
                                return $f['id'] == $criteria['field'];
                            });
                            $field = reset($field);

                            $timeValue = str_contains($value, 'MONTH') ? 'month' : 'week';
                            $searchValue = $translations[$criteria['searchtype']] . ' : -' . sprintf(
                                    _n("%d $timeValue", "%d $timeValue" . 's', $value[1]),
                                    $value[1]
                                );
                            $label = $timeValue === 'month' ? __('Beginning of the month') : __('Monday');
                            $inputValue = $timeValue === 'month' ? PluginAutoexportsearchesCustomsearchcriteria::CRITERIA_FIRST_DAY_OF_MONTH : PluginAutoexportsearchesCustomsearchcriteria::CRITERIA_FIRST_DAY_OF_WEEK;
                            $checked = $customValue === $inputValue ? 'checked' : '';
                            echo "
                                <tr class='tab_bg_1 text-center'>
                                    <td>
                                        <label>{$field['name']}</label>
                                    </td>
                                    <td class='text-center'>
                                        $searchValue
                                    </td>
                                    <td>
                                        <label for='custom_criterias[$index][criteria_value]'>$label</label>
                                        <input type='hidden' name='custom_criterias[$index][savedsearches_id]' value='$savedSearchId'>
                                        <input type='hidden' name='custom_criterias[$index][criteria_field]' value='{$criteria['field']}'>
                                        <input type='hidden' name='custom_criterias[$index][criteria_searchtype]' value='{$criteria['searchtype']}'>
                                        <input type='checkbox' name='custom_criterias[$index][criteria_value]' value='$inputValue' $checked>
                                    </td>
                                </tr>
                            ";
                        }
                    }
                }
            }
        }
    }
    echo "</tbody></table>";
}

