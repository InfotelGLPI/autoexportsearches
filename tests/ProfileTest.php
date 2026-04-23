<?php

namespace GlpiPlugin\Autoexportsearches\Tests;

use Glpi\Tests\DbTestCase;
use GlpiPlugin\Autoexportsearches\Exportconfig;
use GlpiPlugin\Autoexportsearches\Profile;

class ProfileTest extends DbTestCase
{
    public function testGetAllRightsWithoutAllReturnsOneEntry(): void
    {
        $this->assertCount(1, Profile::getAllRights(false));
    }

    public function testGetAllRightsWithAllReturnsThreeEntries(): void
    {
        $this->assertCount(3, Profile::getAllRights(true));
    }

    public function testGetAllRightsContainsExportconfigsField(): void
    {
        $fields = array_column(Profile::getAllRights(false), 'field');
        $this->assertContains('plugin_autoexportsearches_exportconfigs', $fields);
    }

    public function testGetAllRightsWithAllContainsAccessFilesField(): void
    {
        $fields = array_column(Profile::getAllRights(true), 'field');
        $this->assertContains('plugin_autoexportsearches_accessfiles', $fields);
    }

    public function testGetAllRightsWithAllContainsConfigsField(): void
    {
        $fields = array_column(Profile::getAllRights(true), 'field');
        $this->assertContains('plugin_autoexportsearches_configs', $fields);
    }

    public function testGetAllRightsExportconfigsItemtypeIsExportconfig(): void
    {
        $rights = Profile::getAllRights(false);
        $this->assertSame(Exportconfig::class, $rights[0]['itemtype']);
    }

    public function testTranslateARightReturnsZeroForEmptyString(): void
    {
        $this->assertSame(0, Profile::translateARight(''));
    }

    public function testTranslateARightReturnsReadForR(): void
    {
        $this->assertSame(READ, Profile::translateARight('r'));
    }

    public function testTranslateARightReturnsFullRightsForW(): void
    {
        $expected = ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
        $this->assertSame($expected, Profile::translateARight('w'));
    }

    public function testTranslateARightReturnsZeroForZeroString(): void
    {
        $this->assertSame('0', Profile::translateARight('0'));
    }

    public function testTranslateARightReturnsOneForOneString(): void
    {
        $this->assertSame('1', Profile::translateARight('1'));
    }

    public function testTranslateARightReturnsZeroForUnknownValue(): void
    {
        $this->assertSame(0, Profile::translateARight('x'));
    }

    public function testGetTabNameForItemReturnsEmptyForNonCentralProfile(): void
    {
        $this->login('glpi', 'glpi');

        $plugin_profile = new Profile();
        $glpi_profile   = new \Profile();
        $glpi_profile->fields['interface'] = 'helpdesk';

        $this->assertSame('', $plugin_profile->getTabNameForItem($glpi_profile));
    }

    public function testGetTabNameForItemReturnsNonEmptyForCentralProfile(): void
    {
        $this->login('glpi', 'glpi');

        $plugin_profile = new Profile();
        $glpi_profile   = new \Profile();
        $glpi_profile->fields['interface'] = 'central';

        $this->assertNotEmpty($plugin_profile->getTabNameForItem($glpi_profile));
    }

    public function testGetTabNameForItemReturnsEmptyForNonProfileItem(): void
    {
        $this->login('glpi', 'glpi');

        $plugin_profile = new Profile();
        $ticket         = new \Ticket();

        $this->assertSame('', $plugin_profile->getTabNameForItem($ticket));
    }
}
