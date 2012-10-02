ALTER TABLE `bdnet_events`  ADD `evnt_teamweight` VARCHAR(25) NOT NULL AFTER `evnt_catage`;
ALTER TABLE `bdnet_teams` ADD `team_poids` INT NOT NULL DEFAULT '0' COMMENT 'nombre de points de l''equipe' AFTER `team_textconvoc`;
ALTER TABLE `bdnet_rounds` ADD `rund_formula` INT NOT NULL DEFAULT '15' COMMENT 'Formule du groupe ; poule, poule AR, tableau, tableau+plateau' AFTER `rund_rge`;
ALTER TABLE `bdnet_t2t` CHANGE `t2t_result` `t2t_result` TINYINT( 4 ) NOT NULL DEFAULT '103';