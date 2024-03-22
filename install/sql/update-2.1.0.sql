DROP TABLE IF EXISTS `glpi_plugin_autoexportsearches_customsearchcriterias`;
CREATE TABLE `glpi_plugin_autoexportsearches_customsearchcriterias`
(
    `id`                  int unsigned NOT NULL AUTO_INCREMENT,
    `exportconfigs_id`    int unsigned NOT NULL COMMENT 'RELATION to glpi_plugin_autoexportsearches_exportconfigs (id)',
    `savedsearches_id`    int unsigned NOT NULL COMMENT 'RELATION to glpi_savedsearches (id)',
    `criteria_field`      int unsigned NOT NULL,
    `criteria_value`      VARCHAR(255) NOT NULL,
    `criteria_searchtype` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY                   `exportconfigs_id` (`exportconfigs_id`),
    KEY                   `savedsearches_id` (`savedsearches_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

ALTER TABLE `glpi_plugin_autoexportsearches_exportconfigs`
    ADD `periodicity_type` int;
ALTER TABLE `glpi_plugin_autoexportsearches_exportconfigs`
    ADD `periodicity_open_days` tinyint;

UPDATE `glpi_plugin_autoexportsearches_exportconfigs`
SET `periodicity_type` = 0
WHERE 1;
UPDATE `glpi_plugin_autoexportsearches_exportconfigs`
SET `periodicity_open_days` = 0
WHERE 1;
