<?php

namespace GlpiPlugin\Autoexportsearches\Tests;

use Glpi\Tests\DbTestCase;
use GlpiPlugin\Autoexportsearches\Config;

class ConfigTest extends DbTestCase
{
    public function testTableNameMatchesConvention(): void
    {
        $this->assertSame('glpi_plugin_autoexportsearches_configs', Config::getTable());
    }

    public function testRightnameIsCorrect(): void
    {
        $this->assertSame('plugin_autoexportsearches_configs', Config::$rightname);
    }

    public function testInstallCreatesRowWithId1(): void
    {
        $this->login('glpi', 'glpi');

        $config = new Config();
        $found = $config->getFromDB(1);

        $this->assertTrue($found);
    }

    public function testDefaultFolderValue(): void
    {
        $this->login('glpi', 'glpi');

        $config = new Config();
        $config->getFromDB(1);

        $this->assertSame('autoexportsearches', $config->getField('folder'));
    }

    public function testDefaultMonthBeforePurgeValue(): void
    {
        $this->login('glpi', 'glpi');

        $config = new Config();
        $config->getFromDB(1);

        $this->assertSame(3, $config->getField('monthBeforePurge'));
    }

    public function testUpdateMonthBeforePurge(): void
    {
        $this->login('glpi', 'glpi');

        $config = new Config();
        $config->getFromDB(1);

        $config->update([
            'id'               => 1,
            'folder'           => $config->getField('folder'),
            'monthBeforePurge' => 6,
        ]);

        $config->getFromDB(1);
        $this->assertSame(6, $config->getField('monthBeforePurge'));
    }

    public function testUpdateFolder(): void
    {
        $this->login('glpi', 'glpi');

        $config = new Config();
        $config->getFromDB(1);

        $config->update([
            'id'               => 1,
            'folder'           => 'custom_folder',
            'monthBeforePurge' => $config->getField('monthBeforePurge'),
        ]);

        $config->getFromDB(1);
        $this->assertSame('custom_folder', $config->getField('folder'));
    }
}
