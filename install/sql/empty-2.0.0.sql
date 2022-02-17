--
-- Structure de la table 'glpi_plugin_autoexportsearches_exportconfigs'
-- gestion des droits pour le plugin
--

DROP TABLE IF EXISTS `glpi_plugin_autoexportsearches_exportconfigs`;
CREATE TABLE `glpi_plugin_autoexportsearches_exportconfigs` (
  `id`          int unsigned    NOT NULL        AUTO_INCREMENT, -- id du profil
  `users_id`    int unsigned    NOT NULL        DEFAULT '0'
  COMMENT 'RELATION to glpi_users (id)',
  `searches_id` int unsigned    NOT NULL        DEFAULT '0'
  COMMENT 'RELATION to glpi_savedsearches (id)',
  `periodicity` int unsigned    NOT NULL        DEFAULT '0',
  `last_export` timestamp  NULL DEFAULT NULL,
  `is_active`   tinyint NOT NULL DEFAULT '1',
  `is_deleted`  tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


DROP TABLE IF EXISTS `glpi_plugin_autoexportsearches_configs`;
CREATE TABLE `glpi_plugin_autoexportsearches_configs` (
   `id` int unsigned NOT NULL auto_increment,
   `folder` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
   `monthBeforePurge` int unsigned NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_plugin_autoexportsearches_configs`(`id`,`folder`,`monthBeforePurge`) VALUES (1,'/autoexportsearches/',3);
