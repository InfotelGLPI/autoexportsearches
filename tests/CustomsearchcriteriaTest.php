<?php

namespace GlpiPlugin\Autoexportsearches\Tests;

use Glpi\Tests\DbTestCase;
use GlpiPlugin\Autoexportsearches\Customsearchcriteria;
use GlpiPlugin\Autoexportsearches\Exportconfig;

class CustomsearchcriteriaTest extends DbTestCase
{
    public function testTableNameMatchesConvention(): void
    {
        $this->assertSame('glpi_plugin_autoexportsearches_customsearchcriterias', Customsearchcriteria::getTable());
    }

    public function testCriteriaFirstDayOfMonthConstant(): void
    {
        $this->assertSame('first day of ', Customsearchcriteria::CRITERIA_FIRST_DAY_OF_MONTH);
    }

    public function testCriteriaFirstDayOfWeekConstant(): void
    {
        $this->assertSame('last monday', Customsearchcriteria::CRITERIA_FIRST_DAY_OF_WEEK);
    }

    public function testCreateCriteriasAddsRowsFromInput(): void
    {
        global $DB;

        $this->login('glpi', 'glpi');

        $exportconfig = $this->createItem(Exportconfig::class, [
            'users_id'         => 0,
            'savedsearches_id' => 0,
            'periodicity_type' => Exportconfig::PERIODICITY_DAYS,
            'periodicity'      => 1,
            'is_active'        => 1,
        ]);

        $exportconfig->input['custom_criterias'] = [
            [
                'savedsearches_id'    => 0,
                'criteria_field'      => 15,
                'criteria_value'      => Customsearchcriteria::CRITERIA_FIRST_DAY_OF_MONTH,
                'criteria_searchtype' => 'contains',
            ],
        ];

        Customsearchcriteria::createCriterias($exportconfig);

        $count = countElementsInTable(Customsearchcriteria::getTable(), [
            'exportconfigs_id' => $exportconfig->getID(),
        ]);

        $this->assertSame(1, $count);
    }

    public function testCreateCriteriasDeletesExistingBeforeAdding(): void
    {
        $this->login('glpi', 'glpi');

        $exportconfig = $this->createItem(Exportconfig::class, [
            'users_id'         => 0,
            'savedsearches_id' => 0,
            'periodicity_type' => Exportconfig::PERIODICITY_DAYS,
            'periodicity'      => 1,
            'is_active'        => 1,
        ]);

        $exportconfig->input['custom_criterias'] = [
            [
                'savedsearches_id'    => 0,
                'criteria_field'      => 15,
                'criteria_value'      => Customsearchcriteria::CRITERIA_FIRST_DAY_OF_MONTH,
                'criteria_searchtype' => 'contains',
            ],
        ];

        Customsearchcriteria::createCriterias($exportconfig);
        Customsearchcriteria::createCriterias($exportconfig);

        $count = countElementsInTable(Customsearchcriteria::getTable(), [
            'exportconfigs_id' => $exportconfig->getID(),
        ]);

        $this->assertSame(1, $count);
    }

    public function testCreateCriteriasWithNoCriteriaInputLeavesTableUnchanged(): void
    {
        $this->login('glpi', 'glpi');

        $exportconfig = $this->createItem(Exportconfig::class, [
            'users_id'         => 0,
            'savedsearches_id' => 0,
            'periodicity_type' => Exportconfig::PERIODICITY_DAYS,
            'periodicity'      => 1,
            'is_active'        => 1,
        ]);

        $exportconfig->input = [];
        Customsearchcriteria::createCriterias($exportconfig);

        $count = countElementsInTable(Customsearchcriteria::getTable(), [
            'exportconfigs_id' => $exportconfig->getID(),
        ]);

        $this->assertSame(0, $count);
    }

    public function testPurgingExportconfigDeletesLinkedCriterias(): void
    {
        global $DB;

        $this->login('glpi', 'glpi');

        $exportconfig = $this->createItem(Exportconfig::class, [
            'users_id'         => 0,
            'savedsearches_id' => 0,
            'periodicity_type' => Exportconfig::PERIODICITY_DAYS,
            'periodicity'      => 1,
            'is_active'        => 1,
        ]);

        $exportconfig->input['custom_criterias'] = [
            [
                'savedsearches_id'    => 0,
                'criteria_field'      => 10,
                'criteria_value'      => Customsearchcriteria::CRITERIA_FIRST_DAY_OF_WEEK,
                'criteria_searchtype' => 'contains',
            ],
        ];

        Customsearchcriteria::createCriterias($exportconfig);

        $exportconfig_id = $exportconfig->getID();

        $DB->delete(Customsearchcriteria::getTable(), [
            'exportconfigs_id' => $exportconfig_id,
        ]);

        $count = countElementsInTable(Customsearchcriteria::getTable(), [
            'exportconfigs_id' => $exportconfig_id,
        ]);

        $this->assertSame(0, $count);
    }
}
