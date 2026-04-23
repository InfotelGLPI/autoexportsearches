<?php

namespace GlpiPlugin\Autoexportsearches\Tests;

use Glpi\Tests\DbTestCase;
use GlpiPlugin\Autoexportsearches\Exportconfig;

class ExportconfigTest extends DbTestCase
{
    public function testTableNameMatchesConvention(): void
    {
        $this->assertSame('glpi_plugin_autoexportsearches_exportconfigs', Exportconfig::getTable());
    }

    public function testRightnameIsCorrect(): void
    {
        $this->assertSame('plugin_autoexportsearches_exportconfigs', Exportconfig::$rightname);
    }

    public function testGetTypeNameReturnsNonEmptyString(): void
    {
        $this->assertNotEmpty(Exportconfig::getTypeName(1));
    }

    public function testPeriodicityConstantsAreDistinct(): void
    {
        $constants = [
            Exportconfig::PERIODICITY_DAYS,
            Exportconfig::PERIODICITY_WEEKLY,
            Exportconfig::PERIODICITY_MONTHLY,
            Exportconfig::PERIODICITY_MINUTES,
            Exportconfig::PERIODICITY_HOURS,
        ];

        $this->assertSame(count($constants), count(array_unique($constants)));
    }

    public function testCreateExportconfigWithDefaults(): void
    {
        $this->login('glpi', 'glpi');

        $item = $this->createItem(Exportconfig::class, [
            'users_id'          => 0,
            'savedsearches_id'  => 0,
            'periodicity_type'  => Exportconfig::PERIODICITY_DAYS,
            'periodicity'       => 1,
            'is_active'         => 1,
        ]);

        $this->assertGreaterThan(0, $item->getID());
    }

    public function testIsActiveDefaultIsOne(): void
    {
        $this->login('glpi', 'glpi');

        $item = $this->createItem(Exportconfig::class, [
            'users_id'         => 0,
            'savedsearches_id' => 0,
            'periodicity_type' => Exportconfig::PERIODICITY_DAYS,
            'periodicity'      => 1,
            'is_active'        => 1,
        ]);

        $this->assertSame('1', $item->getField('is_active'));
    }

    public function testIsDeletedDefaultIsZero(): void
    {
        $this->login('glpi', 'glpi');

        $item = $this->createItem(Exportconfig::class, [
            'users_id'         => 0,
            'savedsearches_id' => 0,
            'periodicity_type' => Exportconfig::PERIODICITY_DAYS,
            'periodicity'      => 1,
            'is_active'        => 1,
        ]);

        $this->assertSame('0', $item->getField('is_deleted'));
    }

    public function testSendtoFieldIsStoredCorrectly(): void
    {
        $this->login('glpi', 'glpi');

        $item = $this->createItem(Exportconfig::class, [
            'users_id'         => 0,
            'savedsearches_id' => 0,
            'periodicity_type' => Exportconfig::PERIODICITY_DAYS,
            'periodicity'      => 1,
            'is_active'        => 1,
            'sendto'           => 'test@example.com',
        ]);

        $this->assertSame('test@example.com', $item->getField('sendto'));
    }

    public function testUpdatePeriodicityType(): void
    {
        $this->login('glpi', 'glpi');

        $item = $this->createItem(Exportconfig::class, [
            'users_id'         => 0,
            'savedsearches_id' => 0,
            'periodicity_type' => Exportconfig::PERIODICITY_DAYS,
            'periodicity'      => 1,
            'is_active'        => 1,
        ]);

        $this->updateItem(Exportconfig::class, $item->getID(), [
            'periodicity_type' => Exportconfig::PERIODICITY_MONTHLY,
        ]);

        $item->getFromDB($item->getID());
        $this->assertSame((string) Exportconfig::PERIODICITY_MONTHLY, $item->getField('periodicity_type'));
    }

    public function testRawSearchOptionsContainsIdOption(): void
    {
        $this->login('glpi', 'glpi');

        $item = new Exportconfig();
        $options = $item->rawSearchOptions();

        $ids = array_column($options, 'id');
        $this->assertContains('1', $ids);
    }

    public function testCronInfoReturnsDescriptionForKnownTask(): void
    {
        $info = Exportconfig::cronInfo('AutoexportsearchesExportconfigExport');

        $this->assertArrayHasKey('description', $info);
        $this->assertNotEmpty($info['description']);
    }

    public function testCronInfoReturnsEmptyArrayForUnknownTask(): void
    {
        $info = Exportconfig::cronInfo('UnknownTask');

        $this->assertSame([], $info);
    }

    public function testDeleteExportconfig(): void
    {
        $this->login('glpi', 'glpi');

        $item = $this->createItem(Exportconfig::class, [
            'users_id'         => 0,
            'savedsearches_id' => 0,
            'periodicity_type' => Exportconfig::PERIODICITY_DAYS,
            'periodicity'      => 1,
            'is_active'        => 1,
        ]);

        $id = $item->getID();
        $this->deleteItem(Exportconfig::class, $id, true);

        $reloaded = new Exportconfig();
        $this->assertFalse($reloaded->getFromDB($id));
    }
}
