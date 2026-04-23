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

use Glpi\Application\View\TemplateRenderer;
use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Autoexportsearches\Customsearchcriteria;

header("Content-Type: text/html; charset=UTF-8");

Html::header_nocache();

Session::checkLoginUser();

$savedSearchId = null;
if (isset($_POST['savedsearches_id']) && $_POST['savedsearches_id']) {
    $savedSearchId = (int) $_POST['savedsearches_id'];
}
if (Session::haveRight("plugin_autoexportsearches_exportconfigs", READ)) {
    if ($savedSearchId) {
        $translations = [
            'equals'      => __('is'),
            'notequals'   => __('is not'),
            'lessthan'    => __('before'),
            'morethan'    => __('after'),
            'contains'    => __('contains'),
            'notcontains' => __('not contains'),
        ];

        $rows          = [];
        $itemtype_label = '';
        $search         = new SavedSearch();

        if ($search->getFromDB($savedSearchId)) {
            $url_components = parse_url("?" . $search->fields["query"]);
            parse_str($url_components['query'], $p);

            if (isset($p['itemtype'])) {
                $item = getItemForItemtype($p['itemtype']);
                if ($item instanceof CommonITILObject) {
                    $itemtype_label = __($p['itemtype']);
                    $fields         = $item->getSearchOptionsMain();
                    $dateFields     = array_filter($fields, fn($f) => ($f['datatype'] ?? '') === 'datetime');
                    $dateFieldsIds  = array_column($dateFields, 'id');

                    foreach ($p['criteria'] as $index => $criteria) {
                        if (!in_array($criteria['field'], $dateFieldsIds)) {
                            continue;
                        }
                        $value = $criteria['value'];
                        if (!str_starts_with($value, '-')
                            || (!str_contains($value, 'MONTH') && !str_contains($value, 'WEEK'))) {
                            continue;
                        }

                        $customCriteria = new Customsearchcriteria();
                        $customValue    = null;
                        if ($customCriteria->getFromDBByCrit([
                            'savedsearches_id'   => $savedSearchId,
                            'exportconfigs_id'   => (int) $_POST['exportconfigs_id'],
                            'criteria_field'     => $criteria['field'],
                            'criteria_searchtype' => $criteria['searchtype'],
                        ])) {
                            $customValue = $customCriteria->fields['criteria_value'];
                        }

                        $matching  = array_filter($dateFields, fn($f) => $f['id'] == $criteria['field']);
                        $field     = reset($matching);
                        $timeValue = str_contains($value, 'MONTH') ? 'month' : 'week';
                        $inputValue = $timeValue === 'month'
                            ? Customsearchcriteria::CRITERIA_FIRST_DAY_OF_MONTH
                            : Customsearchcriteria::CRITERIA_FIRST_DAY_OF_WEEK;

                        $rows[] = [
                            'index'              => $index,
                            'field_name'         => $field['name'] ?? '',
                            'search_value'       => ($translations[$criteria['searchtype']] ?? '')
                                . ' : -' . sprintf(_n("%d $timeValue", "%d {$timeValue}s", $value[1]), $value[1]),
                            'label'              => $timeValue === 'month' ? __('Beginning of the month') : __('Monday'),
                            'savedsearches_id'   => $savedSearchId,
                            'criteria_field'     => $criteria['field'],
                            'criteria_searchtype' => $criteria['searchtype'],
                            'input_value'        => $inputValue,
                            'checked'            => $customValue === $inputValue,
                        ];
                    }
                }
            }
        }

        TemplateRenderer::getInstance()->display(
            '@autoexportsearches/customsearchcriterias.html.twig',
            [
                'rows'           => $rows,
                'itemtype_label' => $itemtype_label,
            ]
        );
    }
} else {
    throw new AccessDeniedHttpException();
}

