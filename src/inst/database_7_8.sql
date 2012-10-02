ALTER TABLE `bdnet_draws` ADD `draw_numcatage` TINYINT( 4 ) NOT NULL DEFAULT '0';

ALTER TABLE `bdnet_eventsmeta` ADD `evmt_top` INT( 11 ) NOT NULL DEFAULT '28';
ALTER TABLE `bdnet_eventsmeta` ADD `evmt_left` INT( 11 ) NOT NULL DEFAULT '5';
ALTER TABLE `bdnet_eventsmeta` ADD `evmt_width` INT( 11 ) NOT NULL DEFAULT '70';
ALTER TABLE `bdnet_eventsmeta` ADD `evmt_height` INT( 11 ) NOT NULL DEFAULT '20';

ALTER TABLE `bdnet_registration` ADD `regi_numcatage` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `bdnet_registration` ADD `regi_prise` TIME;
ALTER TABLE `bdnet_registration` ADD `regi_transportcmt` varchar(250);
ALTER TABLE `bdnet_registration` CHANGE `regi_arrcmt` `regi_arrcmt` VARCHAR( 250 );
ALTER TABLE `bdnet_registration` CHANGE `regi_depcmt` `regi_depcmt` VARCHAR( 250 );
