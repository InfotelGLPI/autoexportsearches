--
-- Structure de la table 'glpi_plugin_autoexportsearches_exportconfigs'
-- gestion des droits pour le plugin
--

DROP TABLE IF EXISTS `glpi_plugin_autoexportsearches_exportconfigs`;
CREATE TABLE `glpi_plugin_autoexportsearches_exportconfigs` (
  `id`          INT(11)    NOT NULL        AUTO_INCREMENT, -- id du profil
  `users_id`    INT(11)    NOT NULL        DEFAULT '0'
  COMMENT 'RELATION to glpi_users (id)',
  `searches_id` INT(11)    NOT NULL        DEFAULT '0'
  COMMENT 'RELATION to glpi_savedsearches (id)',
  `periodicity` INT(11)    NOT NULL        DEFAULT '0',
  `last_export` timestamp  NULL DEFAULT NULL,
  `is_active`   tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted`  tinyint(1) NOT NULL DEFAULT '0',
  `sendto` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_autoexportsearches_configs`;
CREATE TABLE `glpi_plugin_autoexportsearches_configs` (
   `id` int(11) NOT NULL auto_increment,
   `folder` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `monthBeforePurge` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_autoexportsearches_configs`(`id`,`folder`,`monthBeforePurge`) VALUES (1,'/autoexportsearches/',3);