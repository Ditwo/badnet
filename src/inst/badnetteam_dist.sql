
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE DATABASE `badnetteam` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

--
-- Structure de la table `tnet_event`
--

CREATE TABLE IF NOT EXISTS `tnet_event` (`evnt_id` bigint(20) NOT NULL AUTO_INCREMENT, `evnt_numid` bigint(20) NOT NULL, `evnt_cre` datetime NOT NULL, `evnt_pbl` tinyint(4) NOT NULL, `evnt_name` varchar(100) NOT NULL, `evnt_date` varchar(50) NOT NULL, `evnt_place` varchar(50) NOT NULL, `evnt_step` varchar(20) NOT NULL, `evnt_rest` int(11) NOT NULL DEFAULT '20', `evnt_nbcourt` int(11) NOT NULL DEFAULT '7', `evnt_scoringsystem` int(11) NOT NULL DEFAULT '333', `evnt_rankingsystem` smallint(6) NOT NULL DEFAULT '321', `evnt_allowaddplayer` int(11) NOT NULL DEFAULT '161', `evnt_delayaddplayer` tinyint(4) NOT NULL DEFAULT '0', `evnt_nbmatchmax` int(11) NOT NULL DEFAULT '2' COMMENT 'Nombre maximum de match pour un joueur', `evnt_warming` int(11) NOT NULL DEFAULT '5' COMMENT 'Temps d''echauffement', PRIMARY KEY (`evnt_id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `tnet_match`
--

CREATE TABLE IF NOT EXISTS `tnet_match` ( `mtch_id` bigint(20) NOT NULL AUTO_INCREMENT, `mtch_cre` datetime NOT NULL, `mtch_pbl` tinyint(4) NOT NULL, `mtch_numid` bigint(20) NOT NULL COMMENT 'id dans la base origine', `mtch_disci` smallint(6) NOT NULL, `mtch_discipline` smallint(6) NOT NULL, `mtch_order` tinyint(4) NOT NULL COMMENT 'ordre de deroulement',  `mtch_rank` tinyint(4) NOT NULL COMMENT 'numero du match dans sa discipline', `mtch_begin` datetime NOT NULL, `mtch_end` datetime NOT NULL, `mtch_playh1id` bigint(20) NOT NULL DEFAULT '0', `mtch_playh2id` bigint(20) NOT NULL DEFAULT '0', `mtch_playv1id` bigint(20) NOT NULL DEFAULT '0', `mtch_playv2id` bigint(20) NOT NULL DEFAULT '0', `mtch_score` varchar(20) NOT NULL, `mtch_tieid` bigint(20) NOT NULL, `mtch_court` int(11) NOT NULL DEFAULT '0', `mtch_status` int(11) NOT NULL DEFAULT '30', `mtch_resulth` int(11) NOT NULL DEFAULT '80', `mtch_resultv` int(11) NOT NULL DEFAULT '80', PRIMARY KEY (`mtch_id`), KEY `mtch_numid` (`mtch_numid`), KEY `mtch_tieid` (`mtch_tieid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `tnet_player`
--

CREATE TABLE IF NOT EXISTS `tnet_player` ( `play_id` bigint(20) NOT NULL AUTO_INCREMENT, `play_cre` datetime NOT NULL, `play_pbl` tinyint(4) NOT NULL, `play_famname` varchar(30) NOT NULL, `play_firstname` varchar(30) NOT NULL, `play_gender` tinyint(4) NOT NULL, `play_license` varchar(10) NOT NULL, `play_numid` bigint(50) NOT NULL COMMENT 'id dans la base origine', `play_catage` int(11) NOT NULL, `play_numcatage` int(11) NOT NULL, `play_surclasse` int(11) NOT NULL, `play_mute` int(11) NOT NULL, `play_stranger` int(11) NOT NULL, `play_born` date NOT NULL, `play_teamid` bigint(20) NOT NULL, `play_levels` varchar(10) NOT NULL, `play_leveld` varchar(10) NOT NULL, `play_levelm` varchar(10) NOT NULL, `play_points` double NOT NULL, `play_pointd` double NOT NULL, `play_pointm` double NOT NULL, `play_ranks` int(11) NOT NULL, `play_rankd` int(11) NOT NULL, `play_rankm` int(11) NOT NULL, `play_ranges` int(11) NOT NULL, `play_ranged` int(11) NOT NULL, `play_rangem` int(11) NOT NULL, `play_rest` datetime NOT NULL, `play_ispresent` int(11) NOT NULL DEFAULT '161', `play_court` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`play_id`), KEY `play_teamid` (`play_teamid`), KEY `play_famname` (`play_famname`), KEY `play_numid` (`play_numid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `tnet_team`
--

CREATE TABLE IF NOT EXISTS `tnet_team` (`team_id` bigint(20) NOT NULL AUTO_INCREMENT, `team_cre` datetime NOT NULL, `team_pbl` tinyint(4) NOT NULL, `team_name` varchar(100) NOT NULL, `team_stamp` varchar(50) NOT NULL, `team_numid` bigint(50) NOT NULL COMMENT 'id dans la base origine', `team_pos` tinyint(4) NOT NULL COMMENT 'position de l''equiê dans le groupe', PRIMARY KEY (`team_id`), KEY `team_name` (`team_name`), KEY `team_numid` (`team_numid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `tnet_tie`
--

CREATE TABLE IF NOT EXISTS `tnet_tie` ( `tie_id` bigint(20) NOT NULL AUTO_INCREMENT, `tie_cre` datetime NOT NULL,  `tie_pbl` tinyint(4) NOT NULL, `tie_division` varchar(50) NOT NULL, `tie_group` varchar(50) NOT NULL, `tie_teamvid` bigint(20) NOT NULL, `tie_step` varchar(10) NOT NULL, `tie_teamhid` bigint(20) NOT NULL, `tie_schedule` datetime NOT NULL, `tie_sporthall` varchar(60) NOT NULL, `tie_pos` int(11) NOT NULL COMMENT 'position dans le groupe', `tie_numid` bigint(20) NOT NULL COMMENT 'id dans la base origine', `tie_pointh` int(11) NOT NULL DEFAULT '0', `tie_pointv` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`tie_id`),  KEY `tie_numid` (`tie_numid`), KEY `tie_pos` (`tie_pos`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

