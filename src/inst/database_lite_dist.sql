
CREATE TABLE bdnet_a2t (
  a2t_assoId bigint(21) NOT NULL,
  a2t_teamId bigint(21) NOT NULL,
  a2t_id  INTEGER PRIMARY KEY AUTOINCREMENT,
  a2t_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  a2t_pbl tinyint(4) NOT NULL DEFAULT '8',
  a2t_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  a2t_del tinyint(4) NOT NULL DEFAULT '0',
  a2t_rge tinyint(4) NOT NULL DEFAULT '0'
);

CREATE TABLE bdnet_accounts (
  cunt_eventId bigint(21) NOT NULL,
  cunt_name varchar(30) NOT NULL,
  cunt_code varchar(10) DEFAULT '0',
  cunt_status varchar(10) NOT NULL DEFAULT '0',
  cunt_dateClosed datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  cunt_id  INTEGER PRIMARY KEY AUTOINCREMENT,
  cunt_cmt text,
  cunt_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  cunt_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cunt_pbl tinyint(4) NOT NULL DEFAULT '8',
  cunt_del tinyint(4) NOT NULL DEFAULT '0',
  cunt_rge tinyint(4) NOT NULL DEFAULT '0',
  cunt_uniId varchar(200) NOT NULL
);

CREATE TABLE bdnet_addressbook (
  adbk_name varchar(30) NOT NULL,
  adbk_ownerId bigint(20) NOT NULL,
  adbk_id INTEGER PRIMARY KEY AUTOINCREMENT,
  adbk_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  adbk_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  adbk_cmt text,
  adbk_pbl tinyint(3) NOT NULL DEFAULT '2',
  adbk_del tinyint(4) NOT NULL DEFAULT '0',
  adbk_rge tinyint(4) NOT NULL DEFAULT '0',
  adbk_eventId bigint(20) NOT NULL DEFAULT '0'
);

CREATE TABLE bdnet_assocs (
  asso_name varchar(100) NOT NULL,
  asso_stamp varchar(25) NOT NULL,
  asso_type tinyint(4) NOT NULL DEFAULT '0',
  asso_id  INTEGER PRIMARY KEY AUTOINCREMENT,
  asso_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  asso_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  asso_cmt text,
  asso_pbl tinyint(4) NOT NULL DEFAULT '2',
  asso_del tinyint(4) NOT NULL DEFAULT '0',
  asso_rge tinyint(4) NOT NULL DEFAULT '0',
  asso_url varchar(200) DEFAULT NULL,
  asso_logo varchar(200) DEFAULT NULL,
  asso_number varchar(20) DEFAULT NULL,
  asso_dpt varchar(5) DEFAULT NULL,
  asso_pseudo varchar(50) NOT NULL DEFAULT '',
  asso_noc varchar(15) DEFAULT NULL,
  asso_fedeId bigint(21) NOT NULL DEFAULT -1,
  asso_lockid bigint(21)  NOT NULL DEFAULT '0',
  asso_uniId varchar(200) NOT NULL
);

CREATE TABLE bdnet_badges (
  bdge_name varchar(20) NOT NULL,
  bdge_width float DEFAULT '86',
  bdge_height float DEFAULT '54',
  bdge_border varchar(5) DEFAULT 'LTRB',
  bdge_borderSize float DEFAULT '1',
  bdge_id  INTEGER PRIMARY KEY AUTOINCREMENT,
  bdge_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  bdge_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  bdge_cmt text,
  bdge_pbl tinyint(4) NOT NULL DEFAULT '8',
  bdge_del tinyint(4) NOT NULL DEFAULT '0',
  bdge_rge tinyint(4) NOT NULL DEFAULT '0',
  bdge_topmargin float DEFAULT '0',
  bdge_leftmargin float DEFAULT '0',
  bdge_deltawidth float DEFAULT '0',
  bdge_deltaheight float DEFAULT '0'
);

CREATE TABLE bdnet_c2b (
  c2b_contactId bigint(20) NOT NULL,
  c2b_bookId bigint(20) NOT NULL,
  c2b_id  INTEGER PRIMARY KEY AUTOINCREMENT,
  c2b_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  c2b_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  c2b_cmt text,
  c2b_pbl tinyint(4) NOT NULL DEFAULT '2',
  c2b_del tinyint(4) NOT NULL DEFAULT '0',
  c2b_rge tinyint(4) NOT NULL DEFAULT '0'
);

CREATE TABLE bdnet_commands (
  cmd_name varchar(30) NOT NULL,
  cmd_itemId bigint(20) NOT NULL DEFAULT '0',
  cmd_regiId bigint(20) NOT NULL DEFAULT '0',
  cmd_accountId bigint(20) NOT NULL DEFAULT '0',
  cmd_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  cmd_discount double(14,4) NOT NULL DEFAULT '0.0000',
  cmd_payed double(14,4) NOT NULL DEFAULT '0.0000',
  cmd_value double(14,4) NOT NULL DEFAULT '0.0000',
  cmd_id INTEGER PRIMARY KEY AUTOINCREMENT,
  cmd_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  cmd_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cmd_cmt text,
  cmd_pbl tinyint(4) NOT NULL DEFAULT '8',
  cmd_del tinyint(4) NOT NULL DEFAULT '0',
  cmd_rge tinyint(4) NOT NULL DEFAULT '0',
  cmd_type smallint(6) NOT NULL DEFAULT '180'
);

CREATE TABLE bdnet_connections (
  cnx_login varchar(20) NOT NULL,
  cnx_eventId bigint(21) NOT NULL DEFAULT '0',
  cnx_status tinyint(4) NOT NULL DEFAULT '0',
  cnx_browser varchar(150) NOT NULL DEFAULT '',
  cnx_referer varchar(150) DEFAULT NULL,
  cnx_remoteAdr varchar(20) DEFAULT NULL,
  cnx_id INTEGER PRIMARY KEY AUTOINCREMENT,
  cnx_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  cnx_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cnx_cmt text,
  cnx_pbl tinyint(4) NOT NULL DEFAULT '8',
  cnx_del tinyint(4) NOT NULL DEFAULT '0',
  cnx_rge tinyint(4) NOT NULL DEFAULT '0'
);

CREATE TABLE bdnet_contacts (
  ctac_memberId bigint(20) NOT NULL DEFAULT '0',
  ctac_assocId bigint(20) NOT NULL DEFAULT '0',
  ctac_type tinyint(4) NOT NULL DEFAULT '0',
  ctac_value varchar(50) NOT NULL,
  ctac_contact varchar(50) NOT NULL,
  ctac_id INTEGER PRIMARY KEY AUTOINCREMENT,
  ctac_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  ctac_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ctac_cmt text,
  ctac_pbl tinyint(4) DEFAULT '2',
  ctac_del tinyint(4) DEFAULT '0',
  ctac_rge tinyint(4) DEFAULT '0'
);

CREATE TABLE bdnet_countries (
  cntr_name varchar(20) NOT NULL,
  cntr_flag char(3) NOT NULL DEFAULT '',
  cntr_id INTEGER PRIMARY KEY AUTOINCREMENT,
  cntr_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  cntr_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cntr_cmt text,
  cntr_pbl tinyint(4) NOT NULL DEFAULT '2',
  cntr_del tinyint(4) NOT NULL DEFAULT '0',
  cntr_rge tinyint(4) NOT NULL DEFAULT '0'
);

CREATE TABLE bdnet_database (
  db_eventId bigint(21) NOT NULL DEFAULT '0',
  db_externEventId bigint(21) NOT NULL DEFAULT '0',
  db_baseId char(21) NOT NULL DEFAULT '0',
  db_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  db_pbl tinyint(4) NOT NULL DEFAULT '8',
  db_id INTEGER PRIMARY KEY AUTOINCREMENT,
  db_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  db_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE  bdnet_draws (
  draw_eventId bigint(20) NOT NULL,
  draw_name varchar(50) NOT NULL,
  draw_serial varchar(25) NOT NULL,
  draw_disci tinyint(4) NOT NULL DEFAULT '1',
  draw_discipline smallint(6) NOT NULL DEFAULT '110',
  draw_catage smallint(6) NOT NULL DEFAULT '306',
  draw_numcatage tinyint(4) NOT NULL DEFAULT '0',
  draw_id INTEGER PRIMARY KEY AUTOINCREMENT,
  draw_rankdefId bigint(21) NOT NULL,
  draw_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  draw_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  draw_cmt text,
  draw_pbl tinyint(4) NOT NULL DEFAULT '2',
  draw_del tinyint(4) NOT NULL DEFAULT '0',
  draw_rge tinyint(4) NOT NULL DEFAULT '0',
  draw_type bigint(20) NOT NULL DEFAULT '0',
  draw_stamp varchar(10) NOT NULL,
  draw_nbGroupStep smallint(6) NOT NULL DEFAULT '0',
  draw_nbKoStep smallint(6) NOT NULL DEFAULT '0',
  draw_nbMaxStep float NOT NULL DEFAULT '0',
  draw_nbMatchStep smallint(6) NOT NULL DEFAULT '0',
  draw_noBefore datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  draw_uniId varchar(200) NOT NULL
);

CREATE TABLE  bdnet_eltbadges (
  eltb_badgeId bigint(20) NOT NULL,
  eltb_font varchar(200) DEFAULT 'arial',
  eltb_size tinyint(4) DEFAULT '22',
  eltb_top float DEFAULT '0',
  eltb_left float DEFAULT '5',
  eltb_width float DEFAULT '81',
  eltb_height float DEFAULT '13',
  eltb_bold smallint(6) DEFAULT '0',
  eltb_underline tinyint(4) DEFAULT '0',
  eltb_italic tinyint(4) DEFAULT '0',
  eltb_align char(2) DEFAULT 'L',
  eltb_border varchar(5) DEFAULT 'LTRB',
  eltb_borderSize float DEFAULT '1',
  eltb_field smallint(6) DEFAULT '0',
  eltb_id INTEGER PRIMARY KEY AUTOINCREMENT,
  eltb_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  eltb_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  eltb_cmt text,
  eltb_pbl tinyint(4) DEFAULT '8',
  eltb_del tinyint(4) DEFAULT '0',
  eltb_rge tinyint(4) DEFAULT '0',
  eltb_value varchar(50) DEFAULT NULL,
  eltb_textcolor varchar(15) DEFAULT '0;0;0',
  eltb_fillcolor varchar(15) DEFAULT '255;255;255',
  eltb_drawcolor varchar(15) DEFAULT '0;0;0',
  eltb_zoneId bigint(20) DEFAULT NULL
);

CREATE TABLE  bdnet_events (
  evnt_name varchar(255) NOT NULL,
  evnt_date varchar(25) DEFAULT NULL,
  evnt_place varchar(25) DEFAULT NULL,
  evnt_organizer varchar(75) DEFAULT NULL,
  evnt_datedraw date DEFAULT '0000-00-00',
  evnt_deadline date DEFAULT '0000-00-00',
  evnt_ownerId bigint(20) DEFAULT '0',
  evnt_id INTEGER PRIMARY KEY AUTOINCREMENT,
  evnt_rankSystem smallint(6) NOT NULL DEFAULT '320',
  evnt_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  evnt_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  evnt_cmt text,
  evnt_pbl tinyint(4) DEFAULT '2',
  evnt_del tinyint(4) DEFAULT '0',
  evnt_rge tinyint(4) DEFAULT '0',
  evnt_type smallint(6) NOT NULL DEFAULT '0',
  evnt_nature smallint(6) NOT NULL DEFAULT '0',
  evnt_level smallint(6) NOT NULL DEFAULT '0',
  evnt_numauto varchar(50) DEFAULT NULL,
  evnt_zone varchar(10) DEFAULT NULL,
  evnt_firstday date DEFAULT '0000-00-00',
  evnt_lastday date DEFAULT '0000-00-00',
  evnt_nbvisited bigint(20) NOT NULL DEFAULT '0',
  evnt_url varchar(200) DEFAULT NULL,
  evnt_poster varchar(200) DEFAULT NULL,
  evnt_lastupdate date DEFAULT NULL,
  evnt_scoringSystem smallint(6) NOT NULL DEFAULT '330',
  evnt_archived smallint(6) DEFAULT '2',
  evnt_delay smallint(6) NOT NULL DEFAULT '60',
  evnt_convoc smallint(6) NOT NULL DEFAULT '360',
  evnt_lieuconvoc varchar(50) DEFAULT NULL,
  evnt_textconvoc text DEFAULT NULL,
  evnt_nbdrawmax tinyint(4) NOT NULL DEFAULT '3',
  evnt_urlrank varchar(255) DEFAULT NULL,
  evnt_dpt tinyint(4) DEFAULT NULL,
  evnt_status smallint(6) NOT NULL DEFAULT '390',
  evnt_liveentries smallint(6) NOT NULL DEFAULT '0',
  evnt_season tinyint(4) NOT NULL DEFAULT '1',
  evnt_catage int(11) NOT NULL DEFAULT '440'
);

CREATE TABLE  bdnet_eventsextra (
  evxt_eventId bigint(20) DEFAULT '0',
  evxt_deptid bigint(20) DEFAULT NULL,
  evxt_id INTEGER PRIMARY KEY AUTOINCREMENT,
  evxt_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  evxt_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  evxt_regionid bigint(20) DEFAULT NULL,
  evxt_pbl tinyint(4) DEFAULT '8',
  evxt_del tinyint(4) DEFAULT '0',
  evxt_serial varchar(20) DEFAULT NULL,
  evxt_catage varchar(50) DEFAULT NULL,
  evxt_disci varchar(10) DEFAULT NULL,
  evxt_fedeid bigint(21) NOT NULL DEFAULT -1,
  evxt_promoted smallint(6) NOT NULL DEFAULT '161',
  evxt_promoimg varchar(255) NOT NULL DEFAULT '',
  evxt_livescoring smallint(6) NOT NULL DEFAULT '161',
  evxt_liveupdate smallint(6) NOT NULL DEFAULT '161',
  evxt_email varchar(255) NOT NULL DEFAULT '',
  evxt_delaycaptain int(11) NOT NULL DEFAULT '5'
);

CREATE TABLE  bdnet_eventsmeta (
  evmt_eventId bigint(20) DEFAULT '0',
  evmt_logo varchar(200) DEFAULT NULL,
  evmt_id INTEGER PRIMARY KEY AUTOINCREMENT,
  evmt_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  evmt_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  evmt_cmt text,
  evmt_pbl tinyint(4) DEFAULT '8',
  evmt_del tinyint(4) DEFAULT '0',
  evmt_titleFont varchar(200) DEFAULT 'mtcorsva',
  evmt_titleSize tinyint(4) DEFAULT '22',
  evmt_badgeId bigint(20) DEFAULT NULL,
  evmt_skin varchar(20) DEFAULT 'base',
  evmt_badgeLogo varchar(200) DEFAULT NULL,
  evmt_badgeSpon varchar(200) DEFAULT NULL,
  evmt_urlStream varchar(200) DEFAULT NULL,
  evmt_urlLiveScore varchar(200) DEFAULT NULL,
  evmt_top int(11) NOT NULL DEFAULT '28',
  evmt_left int(11) NOT NULL DEFAULT '10',
  evmt_width int(11) NOT NULL DEFAULT '70',
  evmt_height int(11) NOT NULL DEFAULT '20'
);

CREATE TABLE  bdnet_i2p (
  i2p_regiId bigint(21) NOT NULL,
  i2p_pairId bigint(21) NOT NULL,
  i2p_cppp bigint(21) NOT NULL DEFAULT '0',
  i2p_classe int(11) DEFAULT NULL,
  i2p_rankdefid bigint(20) NOT NULL DEFAULT '0',
  i2p_id INTEGER PRIMARY KEY AUTOINCREMENT,
  i2p_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  i2p_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  i2p_cmt text,
  i2p_pbl tinyint(4) DEFAULT '8',
  i2p_del tinyint(4) DEFAULT '0',
  i2p_rge tinyint(4) DEFAULT '0'
);

CREATE TABLE  bdnet_items (
  item_name varchar(30) NOT NULL,
  item_code varchar(10) NOT NULL DEFAULT '',
  item_ref varchar(10) NOT NULL DEFAULT '',
  item_rubrikId bigint(21) NOT NULL DEFAULT '0',
  item_value double(14,4) NOT NULL DEFAULT '0.0000',
  item_id INTEGER PRIMARY KEY AUTOINCREMENT,
  item_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  item_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  item_cmt text,
  item_pbl tinyint(4) DEFAULT '8',
  item_slt tinyint(4) DEFAULT '0',
  item_del tinyint(4) DEFAULT '0',
  item_rge tinyint(4) DEFAULT '0',
  item_count bigint(21) NOT NULL DEFAULT '0',
  item_isFollowed tinyint(4) NOT NULL DEFAULT '0',
  item_eventId bigint(21) NOT NULL DEFAULT '0',
  item_isCreditable tinyint(4) NOT NULL DEFAULT '0',
  item_uniId varchar(200) NOT NULL
);

CREATE TABLE  bdnet_matchs (
  mtch_tieId bigint(20) NOT NULL,
  mtch_num bigint(20) NOT NULL DEFAULT '0',
  mtch_begin datetime DEFAULT NULL,
  mtch_end datetime DEFAULT NULL,
  mtch_score varchar(30) DEFAULT NULL,
  mtch_id INTEGER PRIMARY KEY AUTOINCREMENT,
  mtch_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  mtch_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  mtch_cmt text,
  mtch_pbl tinyint(4) DEFAULT '2',
  mtch_del tinyint(4) DEFAULT '0',
  mtch_rge tinyint(4) DEFAULT '0',
  mtch_status tinyint(4) NOT NULL DEFAULT '0',
  mtch_court tinyint(4) NOT NULL DEFAULT '0',
  mtch_discipline tinyint(4) NOT NULL DEFAULT '1',
  mtch_disci smallint(6) NOT NULL DEFAULT '110',
  mtch_catage smallint(6) NOT NULL DEFAULT '306',
  mtch_order tinyint(4) NOT NULL DEFAULT '1',
  mtch_nbpoint tinyint(4) NOT NULL DEFAULT '0',
  mtch_nbset tinyint(4) NOT NULL DEFAULT '0',
  mtch_prolongation tinyint(4) NOT NULL DEFAULT '0',
  mtch_umpireId bigint(20) NOT NULL DEFAULT '-1',
  mtch_type tinyint(4) NOT NULL DEFAULT '1',
  mtch_rank tinyint(4) NOT NULL DEFAULT '1',
  mtch_serviceId bigint(20) NOT NULL DEFAULT '-1',
  mtch_uniId varchar(200) NOT NULL
);

CREATE TABLE  bdnet_members (
  mber_firstname varchar(30) NOT NULL,
  mber_secondname varchar(30) NOT NULL,
  mber_sexe tinyint(4) NOT NULL DEFAULT '6',
  mber_countryId bigint(20) NOT NULL DEFAULT '-1',
  mber_born date DEFAULT '0000-00-00',
  mber_ibfnumber varchar(10) DEFAULT NULL,
  mber_licence varchar(10) DEFAULT NULL,
  mber_id INTEGER PRIMARY KEY AUTOINCREMENT,
  mber_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  mber_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  mber_cmt text,
  mber_pbl tinyint(4) DEFAULT '2',
  mber_del tinyint(4) DEFAULT '0',
  mber_rge tinyint(4) DEFAULT '0',
  mber_urlphoto varchar(200) DEFAULT NULL,
  mber_fedeId bigint(21) NOT NULL DEFAULT '-1',
  mber_lockid bigint(21)  NOT NULL DEFAULT '0',
  mber_uniId varchar(200) NOT NULL,
  mber_pwd varchar(250) DEFAULT NULL,
  mber_email varchar(250) DEFAULT NULL
);

CREATE TABLE  bdnet_meta (
  meta_name varchar(255) NOT NULL,
  meta_value varchar(255) NOT NULL,
  meta_maj timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  meta_updt date DEFAULT NULL,
  meta_cre date DEFAULT NULL,
  meta_id INTEGER PRIMARY KEY AUTOINCREMENT,
  meta_pbl tinyint(4) DEFAULT '8'
);

CREATE TABLE  bdnet_news (
  news_eventId bigint(21) NOT NULL DEFAULT '0',
  news_page varchar(20) DEFAULT NULL,
  news_text varchar(200) DEFAULT NULL,
  news_id INTEGER PRIMARY KEY AUTOINCREMENT,
  news_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  news_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  news_cmt text,
  news_pbl tinyint(4) DEFAULT '8',
  news_del tinyint(4) DEFAULT '0',
  news_rge tinyint(4) DEFAULT '0'
);

CREATE TABLE  bdnet_numreg (
  cptr_eventId bigint(21) NOT NULL DEFAULT '0',
  cptr_regId bigint(21) NOT NULL DEFAULT '0',
  cptr_table varchar(21) NOT NULL DEFAULT '0',
  cptr_numReg smallint(6) NOT NULL DEFAULT '0',
  cptr_pbl tinyint(4) DEFAULT '8',
  cptr_del tinyint(4) DEFAULT '0',
  cptr_id INTEGER PRIMARY KEY AUTOINCREMENT,
  cptr_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  cptr_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE  bdnet_p2m (
  p2m_pairId bigint(21) NOT NULL,
  p2m_matchId bigint(21) NOT NULL,
  p2m_result tinyint(4) NOT NULL DEFAULT '80',
  p2m_posmatch smallint(6) NOT NULL DEFAULT '0',
  p2m_id INTEGER PRIMARY KEY AUTOINCREMENT,
  p2m_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  p2m_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  p2m_cmt text,
  p2m_pbl tinyint(4) DEFAULT '8',
  p2m_del tinyint(4) DEFAULT '0',
  p2m_rge tinyint(4) DEFAULT '0'
);

CREATE TABLE  bdnet_pairs (
  pair_drawId bigint(21) NOT NULL DEFAULT '-1',
  pair_disci tinyint(4) NOT NULL DEFAULT '1',
  pair_ibfNum bigint(10) NOT NULL DEFAULT '',
  pair_natNum bigint(10) NOT NULL DEFAULT '',
  pair_natRank smallint(6) NOT NULL DEFAULT '0',
  pair_intRank smallint(6) NOT NULL DEFAULT '0',
  pair_rankId bigint(21)  NOT NULL DEFAULT '0',
  pair_average double(12,4) NOT NULL DEFAULT '0.0000',
  pair_status tinyint(4) NOT NULL DEFAULT '99',
  pair_order tinyint(4) NOT NULL DEFAULT 0,
  pair_wo tinyint(4) NOT NULL DEFAULT '0',
  pair_datewo datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  pair_id INTEGER PRIMARY KEY AUTOINCREMENT,
  pair_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  pair_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  pair_cmt text,
  pair_pbl tinyint(4) DEFAULT '8',
  pair_del tinyint(4) DEFAULT '0',
  pair_rge tinyint(4) DEFAULT '0',
  pair_state smallint(6) NOT NULL DEFAULT '371',
  pair_uniId varchar(200) NOT NULL
);

CREATE TABLE  bdnet_postits (
  psit_eventId bigint(21) NOT NULL DEFAULT '0',
  psit_title varchar(30) NOT NULL,
  psit_texte text NOT NULL,
  psit_page varchar(15) NOT NULL DEFAULT '0',
  psit_action int(8) NOT NULL DEFAULT '0', 
  psit_type smallint(6) NOT NULL DEFAULT '380',
  psit_data varchar(15) NOT NULL DEFAULT '',
  psit_width int(8) NOT NULL DEFAULT '400',
  psit_height int(8) NOT NULL DEFAULT '500',
  psit_pbl tinyint(4) DEFAULT '8',
  psit_del tinyint(4) DEFAULT '0',
  psit_id INTEGER PRIMARY KEY AUTOINCREMENT,
  psit_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  psit_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  psit_userId bigint(21) NOT NULL DEFAULT '0',
  psit_function varchar(15) NOT NULL DEFAULT 'newWin'
);

CREATE TABLE bdnet_prefs (
  pref_name varchar(30) NOT NULL,
  pref_value varchar(30) NOT NULL,
  pref_userId bigint(20) NOT NULL,
  pref_id INTEGER PRIMARY KEY AUTOINCREMENT,
  pref_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  pref_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  pref_cmt text,
  pref_eventId bigint(21) NOT NULL DEFAULT '-1',
  pref_pbl tinyint(4) DEFAULT '8',
  pref_del tinyint(4) DEFAULT '0',
  pref_rge tinyint(4) DEFAULT '0'
);

CREATE TABLE  bdnet_rankdef (
  rkdf_label varchar(10) NOT NULL,
  rkdf_point double NOT NULL DEFAULT '0',
  rkdf_seuil double NOT NULL DEFAULT '0',
  rkdf_serial varchar(10) NOT NULL,
  rkdf_id INTEGER PRIMARY KEY AUTOINCREMENT,
  rkdf_system smallint(6) NOT NULL DEFAULT '320',
  rkdf_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  rkdf_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  rkdf_cmt text,
  rkdf_pbl tinyint(4) DEFAULT '8',
  rkdf_del tinyint(4) DEFAULT '0',
  rkdf_rge tinyint(4) DEFAULT '0'
);

CREATE TABLE bdnet_ranks (
  rank_rankdefId bigint(21) NOT NULL,
  rank_disci tinyint(4) NOT NULL DEFAULT '1',
  rank_discipline smallint(6) NOT NULL DEFAULT '110',
  rank_average double(12,4) NOT NULL DEFAULT '0.0000',
  rank_regiId bigint(21) NOT NULL DEFAULT '-1',
  rank_id INTEGER PRIMARY KEY AUTOINCREMENT,
  rank_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  rank_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  rank_cmt text,
  rank_pbl tinyint(4) DEFAULT '8',
  rank_del tinyint(4) DEFAULT '0',
  rank_rge tinyint(4) DEFAULT '0',
  rank_isFede tinyint(4) DEFAULT '0',
  rank_dateFede datetime DEFAULT NULL,
  rank_rank int(11) NOT NULL DEFAULT '0'
);

CREATE TABLE  bdnet_registration (
  regi_eventId bigint(21) NOT NULL,
  regi_memberId bigint(21) NOT NULL,
  regi_date date NOT NULL DEFAULT '0000-00-00',
  regi_longName varchar(40) NOT NULL,
  regi_shortName varchar(15) NOT NULL,
  regi_type smallint(6) NOT NULL DEFAULT '60',
  regi_numReg smallint(6) NOT NULL DEFAULT '1',
  regi_catage smallint(6) NOT NULL DEFAULT '306',
  regi_numcatage tinyint(4) NOT NULL DEFAULT '0',
  regi_id INTEGER PRIMARY KEY AUTOINCREMENT,
  regi_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  regi_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  regi_cmt text,
  regi_pbl tinyint(4) DEFAULT '2',
  regi_del tinyint(4) DEFAULT '0',
  regi_rge tinyint(4) DEFAULT '0',
  regi_teamId bigint(20) NOT NULL,
  regi_rest datetime DEFAULT NULL DEFAULT '0000-00-00 00:00:00',
  regi_delay datetime DEFAULT NULL DEFAULT '0000-00-00 00:00:00',
  regi_wo smallint(6) NOT NULL DEFAULT '161',
  regi_present smallint(6) NOT NULL DEFAULT '160',
  regi_court smallint(6) NOT NULL DEFAULT '0',
  regi_status smallint(6) DEFAULT '150',
  regi_accountId bigint(21) DEFAULT NULL,
  regi_arrival datetime DEFAULT NULL,
  regi_departure datetime DEFAULT NULL,
  regi_arrcmt varchar(250) NOT NULL DEFAULT '',
  regi_depcmt varchar(250) NOT NULL DEFAULT '',
  regi_prise time NOT NULL DEFAULT '00:00:00',
  regi_transportcmt varchar(250) NOT NULL DEFAULT '',
  regi_function varchar(40) NOT NULL DEFAULT '',
  regi_noc varchar(15) DEFAULT NULL DEFAULT 'FRA',
  regi_datesurclasse date NOT NULL DEFAULT '0000-00-00',
  regi_dateauto date NOT NULL DEFAULT '0000-00-00',
  regi_surclasse smallint(6) NOT NULL DEFAULT '350',
  regi_uniId varchar(200) NOT NULL,
  regi_datewo datetime DEFAULT '0000-00-00 00:00:00',
  regi_badgeprinted smallint(6) NOT NULL DEFAULT '161'
) ;

CREATE TABLE  bdnet_rights (
  rght_userId bigint(20) NOT NULL,
  rght_themeId bigint(20) NOT NULL DEFAULT '0',
  rght_theme tinyint(4) NOT NULL DEFAULT '1',
  rght_status char(1) DEFAULT 'V',
  rght_id INTEGER PRIMARY KEY AUTOINCREMENT,
  rght_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  rght_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  rght_cmt text,
  rght_pbl tinyint(4) DEFAULT '2',
  rght_del tinyint(4) DEFAULT '0',
  rght_rge tinyint(4) DEFAULT '0'
);

CREATE TABLE  bdnet_rounds (
  rund_drawId bigint(21) NOT NULL,
  rund_name varchar(30) NOT NULL,
  rund_group varchar(30) NOT NULL DEFAULT 'Principal',
  rund_size tinyint(4) NOT NULL DEFAULT '0',
  rund_entries tinyint(4) NOT NULL DEFAULT '0',
  rund_byes tinyint(4) NOT NULL DEFAULT '0',
  rund_qualPlace tinyint(4) NOT NULL DEFAULT '0',
  rund_qual tinyint(4) NOT NULL DEFAULT '0',
  rund_id INTEGER PRIMARY KEY AUTOINCREMENT,
  rund_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  rund_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  rund_cmt text,
  rund_pbl tinyint(4) DEFAULT '8',
  rund_del tinyint(4) DEFAULT '0',
  rund_rge tinyint(4) DEFAULT '0',
  rund_type smallint(6) NOT NULL DEFAULT '0',
  rund_rankType smallint(6) NOT NULL DEFAULT '141',
  rund_tieWin tinyint(4) NOT NULL DEFAULT '3',
  rund_tieEqual tinyint(4) NOT NULL DEFAULT '2',
  rund_tieLoose tinyint(4) NOT NULL DEFAULT '1',
  rund_tieWO tinyint(4) NOT NULL DEFAULT '0',
  rund_matchWin tinyint(4) NOT NULL DEFAULT '1',
  rund_matchLoose tinyint(4) NOT NULL DEFAULT '0',
  rund_matchWO tinyint(4) NOT NULL DEFAULT '0',
  rund_matchRtd tinyint(4) NOT NULL DEFAULT '0',
  rund_stamp varchar(10) DEFAULT NULL,
  rund_nbms tinyint(4) NOT NULL DEFAULT '0',
  rund_nbws tinyint(4) NOT NULL DEFAULT '0',
  rund_nbas tinyint(4) NOT NULL DEFAULT '0',
  rund_nbmd tinyint(4) NOT NULL DEFAULT '0',
  rund_nbwd tinyint(4) NOT NULL DEFAULT '0',
  rund_nbad tinyint(4) NOT NULL DEFAULT '0',
  rund_nbxd tinyint(4) NOT NULL DEFAULT '0',
  rund_uniId varchar(200) NOT NULL
);

CREATE TABLE  bdnet_rubriks (
  rubi_name varchar(30) NOT NULL,
  rubi_code varchar(10) NOT NULL DEFAULT '',
  rubi_parentId bigint(21) NOT NULL DEFAULT '0',
  rubi_id INTEGER PRIMARY KEY AUTOINCREMENT,
  rubi_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  rubi_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  rubi_cmt text,
  rubi_pbl tinyint(4) DEFAULT '8',
  rubi_del tinyint(4) DEFAULT '0',
  rubi_rge tinyint(4) DEFAULT '0'
);

CREATE TABLE  bdnet_subscriptions (
  subs_userId bigint(21) NOT NULL DEFAULT '0',
  subs_eventId bigint(21) NOT NULL DEFAULT '0',
  subs_type tinyint(4) NOT NULL DEFAULT '0',
  subs_subId bigint(21) NOT NULL DEFAULT '0',
  subs_id INTEGER PRIMARY KEY AUTOINCREMENT,
  subs_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  subs_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  subs_cmt text,
  subs_pbl tinyint(4) DEFAULT '8',
  subs_del tinyint(4) DEFAULT '0',
  subs_rge tinyint(4) DEFAULT '0',
  subs_email varchar(50) NOT NULL DEFAULT ''
);

CREATE TABLE  bdnet_t2r (
  t2r_teamId bigint(21) NOT NULL DEFAULT '0',
  t2r_pairId bigint(21) NOT NULL DEFAULT '0',
  t2r_roundId bigint(21) NOT NULL,
  t2r_posRound tinyint(4) NOT NULL DEFAULT '0',
  t2r_tds smallint(6) NOT NULL DEFAULT '0',
  t2r_status tinyint(4) NOT NULL DEFAULT '0',
  t2r_id INTEGER PRIMARY KEY AUTOINCREMENT,
  t2r_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  t2r_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  t2r_cmt text,
  t2r_pbl tinyint(4) DEFAULT '8',
  t2r_del tinyint(4) DEFAULT '0',
  t2r_rge tinyint(4) DEFAULT '0',
  t2r_rank tinyint(4) NOT NULL DEFAULT '0',
  t2r_tieW tinyint(4) NOT NULL DEFAULT '0',
  t2r_tieL tinyint(4) NOT NULL DEFAULT '0',
  t2r_tieE tinyint(4) NOT NULL DEFAULT '0',
  t2r_tieWO tinyint(4) NOT NULL DEFAULT '0',
  t2r_points smallint(6) DEFAULT '0',
  t2r_penalties smallint(6) NOT NULL DEFAULT '0'
);

CREATE TABLE  bdnet_t2t (
  t2t_teamId bigint(21) NOT NULL,
  t2t_tieId bigint(21) NOT NULL,
  t2t_posRound tinyint(4) NOT NULL DEFAULT '0',
  t2t_matchW tinyint(4) NOT NULL DEFAULT '0',
  t2t_matchL tinyint(4) NOT NULL DEFAULT '0',
  t2t_setW tinyint(4) NOT NULL DEFAULT '0',
  t2t_setL tinyint(4) NOT NULL DEFAULT '0',
  t2t_pointW smallint(6) NOT NULL DEFAULT '0',
  t2t_pointL smallint(6) NOT NULL DEFAULT '0',
  t2t_scoreW smallint(6) NOT NULL DEFAULT '0',
  t2t_id INTEGER PRIMARY KEY AUTOINCREMENT,
  t2t_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  t2t_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  t2t_cmt text,
  t2t_pbl tinyint(4) DEFAULT '8',
  t2t_del tinyint(4) DEFAULT '0',
  t2t_rge tinyint(4) DEFAULT '0',
  t2t_result tinyint(4) NOT NULL DEFAULT '0',
  t2t_posTie smallint(6) NOT NULL DEFAULT '0',
  t2t_scoreL smallint(6) NOT NULL DEFAULT '0',
  t2t_penalties smallint(6) NOT NULL DEFAULT '0',
  t2t_penaltiesO smallint(6) NOT NULL DEFAULT '0'
);

CREATE TABLE  bdnet_teams (
  team_eventId bigint(21) NOT NULL,
  team_name varchar(50) NOT NULL,
  team_captain varchar(30) NOT NULL DEFAULT '',
  team_captainid bigint(20) NOT NULL DEFAULT '0',
  team_stamp varchar(30) NOT NULL,
  team_id INTEGER PRIMARY KEY AUTOINCREMENT,
  team_numReg smallint(6) NOT NULL DEFAULT '1',
  team_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  team_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  team_cmt text,
  team_pbl tinyint(4) DEFAULT '8',
  team_del tinyint(4) DEFAULT '0',
  team_rge tinyint(4) DEFAULT '0',
  team_drawId bigint(21) NOT NULL DEFAULT '-1',
  team_date datetime DEFAULT NULL,
  team_accountId bigint(21) DEFAULT '-1',
  team_url varchar(200) DEFAULT NULL,
  team_logo varchar(200) DEFAULT NULL,
  team_photo varchar(200) DEFAULT NULL,
  team_noc varchar(15) DEFAULT NULL,
  team_textconvoc text NOT NULL DEFAULT '',
  team_uniId varchar(200) NOT NULL
);

CREATE TABLE  bdnet_ties (
  tie_roundId bigint(21) NOT NULL,
  tie_isBye tinyint(4) NOT NULL DEFAULT '0',
  tie_nbms tinyint(4) NOT NULL DEFAULT '0',
  tie_nbws tinyint(4) NOT NULL DEFAULT '0',
  tie_nbas tinyint(4) NOT NULL DEFAULT '0',
  tie_nbmd tinyint(4) NOT NULL DEFAULT '0',
  tie_nbwd tinyint(4) NOT NULL DEFAULT '0',
  tie_nbad tinyint(4) NOT NULL DEFAULT '0',
  tie_nbxd tinyint(4) NOT NULL DEFAULT '0',
  tie_id INTEGER PRIMARY KEY AUTOINCREMENT,
  tie_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  tie_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tie_cmt text,
  tie_pbl tinyint(4) DEFAULT '8',
  tie_del tinyint(4) DEFAULT '0',
  tie_rge tinyint(4) DEFAULT '0',
  tie_posRound int(11) NOT NULL DEFAULT '-1',
  tie_schedule datetime DEFAULT '0000-00-00 00:00:00',
  tie_convoc datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  tie_name varchar(30) NOT NULL DEFAULT '',
  tie_place varchar(30) NOT NULL DEFAULT '',
  tie_step varchar(30) NOT NULL DEFAULT '',
  tie_court varchar(10) DEFAULT NULL,
  tie_entrydate datetime DEFAULT NULL,
  tie_validdate datetime DEFAULT NULL,
  tie_controldate datetime DEFAULT NULL,
  tie_entryid bigint(20) DEFAULT NULL,
  tie_validid bigint(20) DEFAULT NULL,
  tie_controlid bigint(20) DEFAULT NULL,
  tie_entrycomment text,
  tie_validcomment text,
  tie_controlcomment text,
  tie_looserdrawid bigint(20) NOT NULL DEFAULT '-1'
);

CREATE TABLE  bdnet_traduction (
  trad_table varchar(50) NOT NULL,
  trad_field varchar(50) NOT NULL,
  trad_regId bigint(20) NOT NULL DEFAULT '0',
  trad_lang varchar(10) NOT NULL DEFAULT 'Fr',
  trad_value varchar(50) NOT NULL DEFAULT '',
  trad_id INTEGER PRIMARY KEY AUTOINCREMENT,
  trad_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  trad_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  trad_cmt text,
  trad_pbl tinyint(4) DEFAULT '8',
  trad_del tinyint(4) DEFAULT '0',
  trad_rge tinyint(4) DEFAULT '0'
);

CREATE TABLE  bdnet_umpire (
  umpi_regiId bigint(21)  NOT NULL,
  umpi_court tinyint(4) NOT NULL DEFAULT '0',
  umpi_function smallint(6) NOT NULL DEFAULT '0',
  umpi_order smallint(6) NOT NULL DEFAULT '0',
  umpi_currentcourt tinyint(4) NOT NULL DEFAULT '0',
  umpi_id INTEGER PRIMARY KEY AUTOINCREMENT,
  umpi_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  umpi_pbl tinyint(4) DEFAULT '8',
  umpi_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE  bdnet_users (
  user_name varchar(50) NOT NULL,
  user_email varchar(50) NOT NULL,
  user_login varchar(20) NOT NULL,
  user_pass varchar(255) NOT NULL,
  user_type char(1) DEFAULT 'U',
  user_lastvisit datetime DEFAULT NULL,
  user_nbcnx bigint(20) DEFAULT '0',
  user_lang varchar(10) DEFAULT 'FRA',
  user_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  user_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  user_cmt text,
  user_pbl tinyint(4) DEFAULT '2',
  user_pseudo varchar(20) NOT NULL,
  user_del tinyint(4) DEFAULT '0'
);

CREATE TABLE  bdnet_z2r (
  z2r_zoneId bigint(21) NOT NULL,
  z2r_typeRegi int(11) NOT NULL DEFAULT '0',
  z2r_id INTEGER PRIMARY KEY AUTOINCREMENT,
  z2r_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  z2r_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  z2r_pbl tinyint(4) DEFAULT '8'
);

CREATE TABLE  bdnet_zone (
  zone_eventId bigint(21) NOT NULL,
  zone_name varchar(20) NOT NULL,
  zone_id INTEGER PRIMARY KEY AUTOINCREMENT,
  zone_cre datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  zone_updt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  zone_cmt text,
  zone_pbl tinyint(4) DEFAULT '8'
);
