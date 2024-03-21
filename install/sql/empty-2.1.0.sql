--
-- Structure de la table 'glpi_plugin_autoexportsearches_exportconfigs'
-- gestion des droits pour le plugin
--

DROP TABLE IF EXISTS `glpi_plugin_autoexportsearches_exportconfigs`;
CREATE TABLE `glpi_plugin_autoexportsearches_exportconfigs`
(
    `id`                    int unsigned NOT NULL AUTO_INCREMENT,
    `users_id`              int unsigned NOT NULL DEFAULT '0'
        COMMENT 'RELATION to glpi_users (id)',
    `savedsearches_id`      int unsigned NOT NULL DEFAULT '0'
        COMMENT 'RELATION to glpi_savedsearches (id)',
    `periodicity_type`      int     NOT NULL DEFAULT '0',
    `periodicity`           int unsigned NOT NULL DEFAULT '1',
    `periodicity_open_days` tinyint NOT NULL DEFAULT '0',
    `is_active`             tinyint NOT NULL DEFAULT '1',
    `is_deleted`            tinyint NOT NULL DEFAULT '0',
    `sendto`                VARCHAR(255)     DEFAULT '',
    PRIMARY KEY (`id`),
    KEY                     `users_id` (`users_id`),
    KEY                     `savedsearches_id` (`savedsearches_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


DROP TABLE IF EXISTS `glpi_plugin_autoexportsearches_configs`;
CREATE TABLE `glpi_plugin_autoexportsearches_configs`
(
    `id`               int unsigned NOT NULL auto_increment,
    `folder`           varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    `monthBeforePurge` int unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `glpi_plugin_autoexportsearches_customsearchcriterias`;
CREATE TABLE `glpi_plugin_autoexportsearches_customsearchcriterias`
(
    `id`               int unsigned NOT NULL AUTO_INCREMENT,
    `exportconfigs_id` int unsigned NOT NULL
        COMMENT 'RELATION to glpi_plugin_autoexportsearches_exportconfigs (id)',
    `savedsearches_id` int unsigned NOT NULL
        COMMENT 'RELATION to glpi_savedsearches (id)',
    `criteria_field`   int unsigned NOT NULL,
    `criteria_value`   VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY                `exportconfigs_id` (`exportconfigs_id`),
    KEY                `savedsearches_id` (`savedsearches_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_plugin_autoexportsearches_configs`(`id`, `folder`, `monthBeforePurge`)
VALUES (1, '/autoexportsearches/', 3);
