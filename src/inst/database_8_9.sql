
ALTER TABLE `bdnet_draws` ADD `draw_discipline` SMALLINT( 6 ) NOT NULL DEFAULT '0' AFTER `draw_disci`;

ALTER TABLE `bdnet_events` ADD `evnt_catage` INT( 11 ) NOT NULL DEFAULT '440';

ALTER TABLE `bdnet_eventsextra` ADD `evxt_promoimg` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `bdnet_eventsextra` ADD `evxt_livescoring` SMALLINT( 6 ) NOT NULL DEFAULT '161';
ALTER TABLE `bdnet_eventsextra` ADD `evxt_liveupdate` SMALLINT( 6 ) NOT NULL DEFAULT '161';
ALTER TABLE `bdnet_eventsextra` ADD `evxt_email` VARCHAR( 255 )  NOT NULL;
ALTER TABLE `bdnet_eventsextra` ADD `evxt_delaycaptain` INT( 11 ) NOT NULL DEFAULT '5';

ALTER TABLE `bdnet_i2p` ADD `i2p_rankdefid` BIGINT( 20) NOT NULL;
ALTER TABLE `bdnet_i2p` CHANGE `i2p_classe` `i2p_classe` INT( 11 );
ALTER TABLE `bdnet_i2p` ADD KEY `i2p_rankdefid_idx` (`i2p_rankdefid`);

ALTER TABLE `bdnet_matchs` ADD `mtch_disci` SMALLINT( 6 ) NOT NULL;
ALTER TABLE `bdnet_matchs` ADD `mtch_catage` SMALLINT( 6 ) NOT NULL;

ALTER TABLE `bdnet_pairs` ADD KEY `pair_drawid_idx` (`pair_drawid`);

ALTER TABLE `bdnet_rankdef` ADD `rkdf_serial` VARCHAR( 10 ) NOT NULL;

ALTER TABLE `bdnet_ranks` ADD `rank_discipline` SMALLINT( 6 ) NOT NULL DEFAULT '110';
ALTER TABLE `bdnet_ranks` ADD `rank_rank` INT( 11 ) NOT NULL;

ALTER TABLE `bdnet_registration` ADD KEY `regi_teamId_idx` (`regi_teamId`);
ALTER TABLE `bdnet_registration` ADD KEY `regi_accountid_idx` (`regi_accountId`);

ALTER TABLE `bdnet_rounds` ADD `rund_group` VARCHAR( 30 ) NOT NULL DEFAULT 'Principal';
ALTER TABLE `bdnet_rounds` DROP rund_ancestorId;
ALTER TABLE `bdnet_rounds` DROP rund_soonId;

ALTER TABLE `bdnet_teams` ADD `team_captainid` BIGINT( 20 ) NOT NULL;
ALTER TABLE `bdnet_teams` ADD KEY `team_captainid_idx` (`team_captainid`);

ALTER TABLE `bdnet_ties` ADD `tie_convoc` DATETIME NOT NULL;
ALTER TABLE `bdnet_ties` ADD `tie_name` VARCHAR( 30 ) NOT NULL;
ALTER TABLE `bdnet_ties` ADD `tie_looserdrawid` BIGINT( 20 ) NOT NULL DEFAULT '-1';
ALTER TABLE `bdnet_ties` CHANGE `tie_entryid` `tie_entryid` BIGINT( 20 );
ALTER TABLE `bdnet_ties` CHANGE `tie_validid` `tie_validid` BIGINT( 20 );
ALTER TABLE `bdnet_ties` CHANGE `tie_controlid` `tie_controlid` BIGINT( 20 );
ALTER TABLE `bdnet_ties` ADD KEY `tie_looserdrawid_idx` (`tie_looserdrawid`);
