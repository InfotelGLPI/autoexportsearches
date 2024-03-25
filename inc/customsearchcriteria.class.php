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
    die("Sorry. You can't access directly to this file");
}

class PluginAutoexportsearchesCustomsearchcriteria extends CommonDBTM
{
    const CRITERIA_FIRST_DAY_OF_MONTH = 'first day of ';
    const CRITERIA_FIRST_DAY_OF_WEEK = 'last monday';

    public static function createCriterias(PluginAutoexportsearchesExportConfig $exportConfig)
    {
        global $DB;
        // clear old relations (in case of update with the saved search criterias changed)
        $DB->delete(
            'glpi_plugin_autoexportsearches_customsearchcriterias',
            [
                'exportconfigs_id' => $exportConfig->fields['id'],
                'savedsearches_id' => $exportConfig->fields['savedsearches_id']
            ]
        );

        if (isset($exportConfig->input['custom_criterias'])) {
            $customCriterias = $exportConfig->input['custom_criterias'];
            if (is_array($customCriterias)) {
                foreach($customCriterias as $criteria) {
                    $criteria['exportconfigs_id'] = $exportConfig->fields['id'];
                    $self = new self();
                    $self->add($criteria);
                }
            }
        }
    }
}
