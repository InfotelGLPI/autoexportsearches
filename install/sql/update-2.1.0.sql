DROP TABLE IF EXISTS `glpi_plugin_autoexportsearches_customsearchcriterias`;
CREATE TABLE `glpi_plugin_autoexportsearches_customsearchcriterias`
(
    `id`               int unsigned NOT NULL AUTO_INCREMENT,
    `exportconfigs_id` int unsigned NOT NULL COMMENT 'RELATION to glpi_plugin_autoexportsearches_exportconfigs (id)',
    `criteria_field`   int unsigned NOT NULL,
    `criteria_value`   VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY                `exportconfigs_id` (`exportconfigs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

ALTER TABLE `glpi_plugin_autoexportsearches_exportconfigs` ADD `periodicity_type` VARCHAR(255);
ALTER TABLE `glpi_plugin_autoexportsearches_exportconfigs` ADD `periodicity_value` VARCHAR(255);
ALTER TABLE `glpi_plugin_autoexportsearches_exportconfigs` ADD `periodicity_open_days` tinyint;
