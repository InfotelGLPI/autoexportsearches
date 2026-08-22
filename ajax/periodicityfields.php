<?php

/**
 * -------------------------------------------------------------------------
 * autoexportsearches plugin for GLPI
 * Copyright (C) 2025-2026 by the autoexportsearches Development Team.
 *
 * https://github.com/InfotelGLPI/autoexportsearches
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of autoexportsearches.
 *
 * autoexportsearches is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * autoexportsearches is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with autoexportsearches. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

use Glpi\Application\View\TemplateRenderer;
use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Autoexportsearches\Exportconfig;

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkRight('plugin_autoexportsearches_exportconfigs', UPDATE);

$id = 0;
if (isset($_POST['id']) && $_POST['id']) {
    $id = $_POST['id'];
}
if (Session::haveRight("plugin_autoexportsearches_exportconfigs", READ)
    && Session::haveRight("plugin_autoexportsearches_exportconfigs", UPDATE)) {
    $exportConfig = null;
    if ($id > 0) {
        $exportConfig = new Exportconfig();
        if ($exportConfig->getFromDB($id)) {
            // Ownership guard: mirror the sibling ajax endpoints
            // (customsearchcriterias.php / dropdownsavedsearches.php) and
            // Exportconfig::validateExportInput(). This is a self-service feature, so a
            // user holding exportconfigs UPDATE normally only owns their own configs;
            // without this guard they could POST another user's exportconfig id and read
            // back its periodicity / worked-days flag. config UPDATE keeps the
            // export-on-behalf capability.
            if ((int) $exportConfig->fields['users_id'] !== Session::getLoginUserID()
                && !Session::haveRight('config', UPDATE)) {
                throw new AccessDeniedHttpException();
            }
        } else {
            $exportConfig = null;
        }
    }
    $periodicity_type = (int) ($_POST['periodicity_type'] ?? 0);
    $rand             = mt_rand();
    $value            = $exportConfig ? $exportConfig->fields['periodicity'] : 1;
    $dropdown         = '';
    $monthly_note     = '';
    $show_days_script = false;

    // Capture the GLPI dropdowns rendered by the framework so they can be handed to the
    // Twig template as pre-rendered HTML (see the migration recipe for showForm -> Twig).
    switch ($periodicity_type) {
        case Exportconfig::PERIODICITY_MINUTES:
            ob_start();
            Dropdown::showNumber('periodicity', [
                'value' => $value,
                'rand'  => $rand,
                'min'   => 30,
                'max'   => 59,
            ]);
            $dropdown = ob_get_clean();
            break;

        case Exportconfig::PERIODICITY_HOURS:
            ob_start();
            Dropdown::showNumber('periodicity', [
                'value' => $value,
                'rand'  => $rand,
                'min'   => 1,
                'max'   => 23,
            ]);
            $dropdown = ob_get_clean();
            break;

        case Exportconfig::PERIODICITY_DAYS:
            ob_start();
            Dropdown::showNumber('periodicity', [
                'value' => $value,
                'rand'  => $rand,
                'min'   => 1,
            ]);
            $dropdown         = ob_get_clean();
            $show_days_script = true;
            break;

        case Exportconfig::PERIODICITY_WEEKLY:
            ob_start();
            Dropdown::showFromArray('periodicity', Toolbox::getDaysOfWeekArray(), [
                'value' => $value,
                'rand'  => $rand,
            ]);
            $dropdown = ob_get_clean();
            break;

        case Exportconfig::PERIODICITY_MONTHLY:
            ob_start();
            Dropdown::showNumber('periodicity', [
                'value' => $value,
                'rand'  => $rand,
                'min'   => 1,
                'max'   => 31,
            ]);
            $dropdown     = ob_get_clean();
            $monthly_note = __(
                'For months having less days than the selected day, the export will be done on the last day of the month.',
                'autoexportsearches',
            );
            break;
    }

    // The "work day only" explanation differs between the daily and monthly cases.
    $open_days_explanation = '';
    if ($periodicity_type === Exportconfig::PERIODICITY_DAYS) {
        $open_days_explanation = __(
            'If this option is checked, the export will be done only on worked day',
            'autoexportsearches',
        );
    } elseif ($periodicity_type === Exportconfig::PERIODICITY_MONTHLY) {
        $open_days_explanation = __(
            'If this option is checked, the export will be done the first work day from the selected day',
            'autoexportsearches',
        );
    }

    TemplateRenderer::getInstance()->display('@autoexportsearches/periodicityfields.html.twig', [
        'periodicity_type'      => $periodicity_type,
        'PERIODICITY_MINUTES'   => Exportconfig::PERIODICITY_MINUTES,
        'PERIODICITY_HOURS'     => Exportconfig::PERIODICITY_HOURS,
        'PERIODICITY_DAYS'      => Exportconfig::PERIODICITY_DAYS,
        'PERIODICITY_WEEKLY'    => Exportconfig::PERIODICITY_WEEKLY,
        'PERIODICITY_MONTHLY'   => Exportconfig::PERIODICITY_MONTHLY,
        'dropdown'              => $dropdown,
        'monthly_note'          => $monthly_note,
        'open_days_label'       => __('Work day only', 'autoexportsearches'),
        'open_days_explanation' => $open_days_explanation,
        'open_days_checked'     => $exportConfig ? ($exportConfig->fields['periodicity_open_days'] == 1) : false,
    ]);

    // Inline JS stays in PHP (echoed after the template): it toggles the "work day only"
    // option depending on the selected daily periodicity value.
    if ($show_days_script) {
        echo Html::scriptBlock("
            if (!window.autoexportsearches) window.autoexportsearches = {};
            autoexportsearches.periodicitySelect = $('#dropdown_periodicity{$rand}');
            autoexportsearches.openDaysContainer = $('#periodicity_open_days');
            autoexportsearches.periodicitySelect.change(e => {
                if (e.target.options[e.target.selectedIndex].value == 1) {
                    autoexportsearches.openDaysContainer[0].style.display = '';
                } else {
                    autoexportsearches.openDaysContainer[0].style.display = 'none';
                }
            });
            autoexportsearches.periodicitySelect.trigger('change');
        ");
    }
} else {
    throw new AccessDeniedHttpException();
}
