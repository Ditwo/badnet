<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Object/Oregi.inc';
require_once 'Object/Omatch.inc';
require_once 'Object/Oevent.inc';

$locale = BN::getLocale();
require_once "Object/Locale/$locale/Oplayer.inc";

class Oplayer extends Object
{
	// Pour le live scoring
	private $_service = 0;     // Numero de service
	private $_oMember = null;  // Partie membre du joueur

	static public function factory($aRegiId=-1)
	{
		if ( Bn::getConfigValue('squash', 'params') )
		{
			require_once 'Object/Oplayer_squash.php';
			$oPlayer = new Oplayer_squash();
		}
		else $oPlayer = new Oplayer($aRegiId);
		return $oPlayer;
	}


	/**
	 * Constructor
	 */
	public function __construct($aRegiId=-1, $aPoonaId=-1, $aLicense='')
	{
		$regi['id'] = -1;
		$regi['uniid'] = -1;
		$regi['poonaid'] = -1;
		$regi['familyname'] = '';
		$regi['firstname'] = '';
		$this->setValues($regi);
		if ($aRegiId==-1 && $aPoonaId==-1 && empty($aLicense)) return;
		if ($aRegiId>0) $this->_loadBadnetPlayer($aRegiId);
		else $this->_loadPlayer($aLicense, $aPoonaId);
	}

	private function _loadPlayer($aLicense, $aPoonaId)
	{
		// Initialiser la partie membre
		$this->_loadMember(-3, $aLicense, $aPoonaId);

		// initialiser la partie sportive
		$oPoona = new Opoona();
		$season = Oseason::getCurrent();

		$player = $oPoona->getPlayer($this->getValue('poonaid'));

		// Si non licencie, essayer avec la saison precedente
		if ( $player['instanceid'] == -1 )
		{
			$season--;
			$player = $oPoona->getPlayer($this->getValue('poonaid'), $season);
		}
		$player['season'] = $season;
		$player['date'] = date('Y-m-d H:M');

		// Infos classement : recuperer la classement Nc qui sera utilise par defaut
		// si le classement du joueur est inconnu
		$system = Bn::getConfigValue('ranking', 'params');
		$q = new bn_Query('rankdef');
		$q->setFields('rkdf_id, rkdf_point');
		$q->setWhere("rkdf_label='NC'");
		$q->addWhere('rkdf_system=' . $system );
		$rankNc = $q->getRow();

		$q->setWhere("rkdf_label='" . $player['levels'] . "'");
		$q->addWhere('rkdf_system=' . $system );
		$rank = $q->getRow();
		if (empty($rank)) $rank = $rankNc;
		$player['levelsid'] = $rank['rkdf_id'];
		$player['seuils'] = $rank['rkdf_point'];

		$q->setWhere("rkdf_label='" . $player['leveld'] . "'");
		$q->addWhere('rkdf_system=' . $system );
		$rank = $q->getRow();
		if (empty($rank)) $rank = $rankNc;
		$player['leveldid'] = $rank['rkdf_id'];
		$player['seuild'] = $rank['rkdf_point'];

		$q->setWhere("rkdf_label='" . $player['levelm'] . "'");
		$q->addWhere('rkdf_system=' . $system );
		$rank = $q->getRow();
		if (empty($rank)) $rank = $rankNc;
		$player['levelmid'] = $rank['rkdf_id'];
		$player['seuilm'] = $rank['rkdf_point'];

		// Infos tableau
		$player['drawids'] = -1;
		$player['draws'] = '';
		$player['drawidd'] = -1;
		$player['drawd'] = '';
		$player['pairidd'] = -1;
		$player['drawidm'] = -1;
		$player['drawm'] = '';
		$player['pairidm'] = -1;
		$player['partnerd'] = '';
		$player['partnerm'] = '';

		// Info association
		$q = new Bn_Query('assocs');
		$q->addField('asso_id');
		$q->addField('asso_fedeid');
		$q->addField('asso_name');
		$q->setWhere('asso_fedeid=' . $player['instanceid']);
		$asso = $q->getRow();
		if ( !empty($asso) )
		{
			$player['assoid'] = $asso['asso_id'];
			$player['instanceid'] = $asso['asso_fedeid'];
			$player['assoc'] = $asso['asso_name'];
		}
		$this->setValues($player);
	}

	private function _loadMember($aMemberId, $aLicense = -1, $aPoonaId  = -1)
	{
		$this->_oMember = new Omember($aMemberId, $aPoonaId, $aLicense);
		$this->setVal('poonaid',    $this->_oMember->getVal('fedeid', -1));
		$this->setVal('familyname', $this->_oMember->getVal('secondname'));
		$this->setVal('firstname',  $this->_oMember->getVal('firstname'));
		$this->setVal('license',    $this->_oMember->getVal('licence'));
		$this->setVal('gender',     $this->_oMember->getVal('sexe'));
		$this->setVal('labgender',  $this->_oMember->getVal('labsexe'));
		$this->setVal('born',       $this->_oMember->getVal('born'));
		$this->setVal('memberid',   $this->_oMember->getVal('id'), -3);
		return;
	}

	private function _loadBadnetPlayer($aRegiId)
	{
		$regi['id'] = -1;
		$regi['uniid'] = -1;
		$regi['poonaid'] = -1;
		$regi['familyname'] = '';
		$regi['firstname'] = '';

		// Donnees du joueur
		$q = new bn_Query('registration, teams, a2t, assocs');
		$q->addField('regi_dateauto', 'dateauto');
		$q->addField('regi_datesurclasse', 'datesurclasse');
		$q->addField('regi_catage', 'catage');
		$q->addField('regi_numcatage', 'numcatage');
		$q->addField('regi_date', 'date');
		$q->addField('regi_noc', 'noc');
		$q->addField('regi_surclasse', 'surclasse');
		$q->addField('regi_uniid', 'uniid');
		$q->addField('regi_id', 'id');
		$q->addField('regi_memberid', 'memberid');
		$q->addField('regi_teamid', 'teamid');
		$q->addField('asso_id', 'assoid');
		$q->addField('asso_fedeid', 'instanceid');
		$q->addField('asso_name', 'assoc');

		$q->addWhere('team_id = a2t_teamid');
		$q->addWhere('a2t_assoid = asso_id');
		$q->addWhere('regi_id ='.$aRegiId);
		$regi = $q->getRow();
		if ( empty($regi) ) return;
		$this->setVal('labcatage', constant('LABEL_' . $regi['catage']));
		$this->setVal('labsurclasse', constant('LABEL_' . $regi['surclasse']));
		$this->_loadMember($regi['memberid']);

		// Infos classement :: Classement NC pour consolidation
		$system = Bn::getConfigValue('ranking', 'params');
		$q->setTables('rankdef');
		$q->setFields('rkdf_id, rkdf_point');
		$q->setWhere("rkdf_label='NC'");
		$q->addWhere('rkdf_system=' . $system );
		$rankNc = $q->getRow();
		$rankNc['rank_average'] = 0;
		$rankNc['rank_rank'] = 0;

		$q->setTables('ranks, rankdef');
		$q->setFields('rkdf_label, rank_disci, rank_average, rkdf_point, rkdf_id, rank_rank');
		$q->setWhere('rank_regiid=' . $aRegiId);
		$q->addWhere('rank_rankdefid = rkdf_id');
		$q->setOrder('rank_disci');
		$ranks = $q->getRows();
		$rank = reset($ranks);
		if ( empty($rank) ) $rank = $rankNc;
		$regi['levels'] = $rank['rkdf_label'];
		$regi['levelsid'] = $rank['rkdf_id'];
		$regi['points']   = $rank['rank_average'];
		$regi['ranks']    = $rank['rank_rank'];
		$regi['seuils']   = $rank['rkdf_point'];
		$rank = next($ranks);
		if ( empty($rank) ) $rank = $rankNc;
		$regi['leveld'] = $rank['rkdf_label'];
		$regi['leveldid'] = $rank['rkdf_id'];
		$regi['pointd']   = $rank['rank_average'];
		$regi['rankd']    = $rank['rank_rank'];
		$regi['seuild']   = $rank['rkdf_point'];
		$rank = next($ranks);
		if ( empty($rank) ) $rank = $rankNc;
		$regi['levelm'] = $rank['rkdf_label'];
		$regi['levelmid'] = $rank['rkdf_id'];
		$regi['pointm']   = $rank['rank_average'];
		$regi['rankm']    = $rank['rank_rank'];
		$regi['seuilm']   = $rank['rkdf_point'];

		$isSquash = Bn::getConfigValue('squash', 'params');
		if ( $isSquash )
		{
			$regi['level'] = $regi['levels'];
			$regi['rank']  = $regi['ranks'];
			$regi['point'] = $regi['points'];
		}
		else
		{
			$regi['level'] = $regi['levels'] . ',' . $regi['leveld'] . ',' . $regi['levelm'];
			$regi['rank']  = $regi['ranks'] . ',' . $regi['rankd'] . ',' . $regi['rankm'];
			$regi['point'] = $regi['points'] . ',' . $regi['pointd'] . ',' . $regi['pointm'];
		}

		// Infos tableau (tournoi de simple)
		$q->setTables('i2p');
		$q->leftjoin('pairs', 'i2p_pairid=pair_id');
		$q->leftjoin('draws', 'draw_id=pair_drawid');
		$q->setFields('draw_id, draw_stamp, pair_id');
		$q->setWhere('i2p_regiid =' . $aRegiId);
		$q->setOrder('pair_disci');
		$draws = $q->getRows();
		$draw = reset($draws);
		$regi['drawids'] = empty($draw['draw_id'])?-1:$draw['draw_id'];
		$regi['draws'] = empty($draw['draw_stamp'])?'':$draw['draw_stamp'];
		$draw = next($draws);
		$regi['drawidd'] = empty($draw['draw_id'])?-1:$draw['draw_id'];
		$regi['drawd'] = empty($draw['draw_stamp'])?'':$draw['draw_stamp'];
		$regi['pairidd'] = empty($draw['pair_id'])?-1:$draw['pair_id'];
		$draw = next($draws);
		$regi['drawidm'] = empty($draw['draw_id'])?-1:$draw['draw_id'];
		$regi['drawm'] = empty($draw['draw_stamp'])?'':$draw['draw_stamp'];
		$regi['pairidm'] = empty($draw['pair_id'])?-1:$draw['pair_id'];

		// Traitement du partenaire de double
		$q->setTables('registration, i2p');
		$q->setFields('regi_longname');
		$q->setWhere('i2p_pairid = ' . $regi['pairidd']);
		$q->addWhere('i2p_regiid = regi_id');
		$q->addWhere('regi_id != ' . $aRegiId);
		$name = $q->getOne();
		$regi['partnerd'] = empty($name)?'':$name;
		// Traitement du partenaire de mixte
		$q->setWhere('i2p_pairid = ' . $regi['pairidm']);
		$q->addWhere('i2p_regiid = regi_id');
		$q->addWhere('regi_id != ' . $aRegiId);
		$name = $q->getOne();
		$regi['partnerm'] = empty($name)?'':$name;

		$this->setValues($regi);
	}

	public function save()
	{
		$famname = strtoupper($this->getVal('familyname'));
		$firstname = ucwords(strtolower($this->getVal('firstname')));

		// Sauvegarde partie membre
		$oMember = $this->_oMember;
		$oMember->setVal('id', $this->getVal('memberid', -3));
		$oMember->setVal('firstname', $firstname);
		$oMember->setVal('secondname', $famname);
		$oMember->setVal('sexe', $this->getVal('gender'));
		$oMember->setVal('born', $this->getVal('born'));
		$oMember->setVal('licence', $this->getVal('license'));
		$oMember->setVal('fedeid', $this->getVal('poonaid'));
		$memberId = $oMember->save();

		// Sauvegarde inscription
		$this->setVal('memberid', $memberId);
		$this->setVal('longname', $famname . ' ' . $firstname);
		$this->setVal('shortname', $famname . ' ' . $firstname[0] . '.');
		$this->setVal('type', OREGI_TYPE_PLAYER);
		$regiId = $this->getVal('id', -1);
		if ($regiId > 0) $where = 'regi_id=' . $regiId;
		else
		{
			$where = 'regi_eventid=' . $this->getVal('eventid').
			 ' AND regi_memberid=' . $memberId.
			 ' AND regi_type=' . OREGI_TYPE_PLAYER;
			$this->delVal('id');
		}
		$regiId = $this->update('registration', 'regi_', $this->getValues(), $where);
		$uniId  = $this->getVal('uniid', -1);
		if (  $uniId == -1 )
		{
			// Identifiant unique
			$where = 'regi_id=' . $regiId;
			$uniId = Bn::getUniId($regiId);
			$this->setVal('uniid', $uniId);
			$regiId = $this->update('registration', 'regi_', $this->getValues(), $where);
		}

		// Sauvegarde des classements
		$q = new bn_Query('ranks');
		$disci = $this->getVal('gender') == OMEMBER_GENDER_MALE?OMATCH_DISCI_MS:OMATCH_DISCI_WS;
		$q->setValue('rank_regiid', $regiId);
		$q->addValue('rank_rankdefid', $this->getVal('levelsid'));
		$q->addValue('rank_disci', $disci);
		$q->addValue('rank_discipline', OMATCH_DISCIPLINE_SINGLE);
		$q->addValue('rank_average', $this->getVal('points', 0));
		$q->addValue('rank_rank', $this->getVal('ranks', 0));
		$q->setWhere("rank_disci=" . $disci);
		$q->addWhere('rank_regiid=' .  $regiId);
		$q->replaceRow();

		$disci = $this->getVal('gender') == OMEMBER_GENDER_MALE?OMATCH_DISCI_MD:OMATCH_DISCI_WD;
		$q->setValue('rank_regiid', $regiId);
		$q->addValue('rank_rankdefid', $this->getVal('leveldid'));
		$q->addValue('rank_disci', $disci);
		$q->addValue('rank_discipline', OMATCH_DISCIPLINE_DOUBLE);
		$q->addValue('rank_average', $this->getVal('pointd', 0));
		$q->addValue('rank_rank', $this->getVal('rankd', 0));
		$q->setWhere("rank_disci=" . $disci);
		$q->addWhere('rank_regiid=' .  $regiId);
		$q->replaceRow();

		$q->setValue('rank_regiid', $regiId);
		$q->addValue('rank_rankdefid', $this->getVal('levelmid'));
		$q->addValue('rank_disci', OMATCH_DISCI_XD);
		$q->addValue('rank_discipline', OMATCH_DISCIPLINE_MIXED);
		$q->addValue('rank_average', $this->getVal('pointm', 0));
		$q->addValue('rank_rank', $this->getVal('rankm', 0));
		$q->setWhere("rank_disci=" . OMATCH_DISCI_XD);
		$q->addWhere('rank_regiid=' .  $regiId);
		$q->replaceRow();
		return $regiId;
	}

	Public function getId() { return $this->getValue('id');}
	Public function getUniId() { return $this->getValue('uniid');}
	Public function getPoonaId() { return $this->getValue('poonaid');}
	Public function getFamilyName() { return $this->getValue('familyname');}
	Public function getFirstName() { return $this->getValue('firstName');}
	Public function getFullName() { return $this->getValue('familyname') . ' ' . $this->getValue('firstname');}

	Public function getService() { return $this->_service;}
	Public function setService($aService) { $this->_service=$aService;}

	/**
	 * Retourne le nombre de match
	 *
	 */
	public function getNbMatch()
	{
		$q = new Bn_query('matchs');
		$q->addTable('p2m', 'mtch_id=p2m_matchid');
		$q->addTable('i2p', 'i2p_pairid=p2m_pairid');
		$q->addWhere('i2p_regiid=' . $this->getVal('id', -1));
		return $q->getCount();
	}

	/**
	 * Equipe du joueur
	 *
	 */
	public function getTeam()
	{
		return new Oteam($this->getVal('teamid', -1));
	}

	/**
	 * Membre du joueur
	 *
	 */
	public function getMember()
	{
		return new Omember($this->getVal('memberid', -1));
	}


	/**
	 * Acces au drapeau du joueur
	 *
	 * @return string
	 */
	Public function getFlag()
	{
		if ( empty($this->_flag) )
		{
			$q = new Bn_query('teams, a2t, assocs');
			$q->setfields('team_logo, asso_logo');
			$q->addWhere('asso_id=a2t_id');
			$q->addwhere('a2t_teamid=team_id');
			$q->addWhere('team_id='.$this->getVal('teamid'));
			$tmp = $q->getRow();
			unset($q);
			if (!empty($tmp['team_logo'])) $this->_flag = $tmp['team_logo'];
			else if (!empty($tmp['asso_logo'])) $this->_flag = $tmp['asso_logo'];
			else $this->_flag = '';
		}
		return $this->_flag;
	}

	/**
	 * Renvoi l'association du joueur
	 */
	public function getAssociation()
	{
		$assoId = $this->getVal('assoid', -1);
		$oasso = new Oasso($assoId);
		return $oasso;
	}

	/**
	 * Affichage du cartouche joueur
	 *
	 * @return string
	 */
	Public function display(Bn_balise $aDiv)
	{
		$locale = BN::getLocale();
		require_once "Object/Locale/$locale/Oplayer.inc";
		$d = $aDiv->addDiv('', 'badnetPlayer');
		$player = $this->getValues();

		$ranks = Opoona::getRanks($this->getVal('gender'), $this->getVal('points', 0),
		$this->getVal('pointd', 0), $this->getVal('pointm', 0));

		// Tableau pour les infos sportives
		$dp = $d->addDiv('divCivil');
		$dp->addP('pName', $player['familyname'] . ' ' . $player['firstname']);
		$ul = $dp->addBalise('ul');
		$li = $ul->addBalise('li', '', LOC_OP_LABEL_NUMLICENSE);
		$li->addBalise('span','playerLicense', $player['license']);
		$li = $ul->addBalise('li', '', LOC_OP_LABEL_CATAGE);
		$li->addBalise('span','', $player['labcatage']);
		$li = $ul->addBalise('li', '', LOC_OP_LABEL_SURCLASSE);
		$li->addBalise('span','', $player['labsurclasse']);
		$li = $ul->addBalise('li', '', LOC_OP_LABEL_ASSOC);
		$li->addBalise('span','', $player['assoc']);
		$season = Oseason::getCurrent();
		if ($season != $player['season'])
		{
			$year = Oseason::getYear($player['season']);
			$dp->addP('pSeason', $year .'-' . ($year+1));
		}

		// Tableau des infos sportives
		$t = $d->addBalise('table', 'tabSport');
		$r = $t->addBalise('tr');
		$r->setAttribute('class', 'rowtitle');
		$r->addBalise('td');
		$r->addBalise('td', '', LOC_OP_TITLE_SINGLE);
		$r->addBalise('td', '', LOC_OP_TITLE_DOUBLE);
		$r->addBalise('td', '', LOC_OP_TITLE_MIXED);

		$r = $t->addBalise('tr');
		$c = $r->addBalise('td', '', LOC_OP_COLUMN_LEVEL);
		$c->setAttribute('class', 'celltitle');
		$c = $r->addBalise('td', '', $player['levels']);
		$c->setAttribute('class', 'celllevel');
		$c = $r->addBalise('td', '', $player['leveld']);
		$c->setAttribute('class', 'celllevel');
		$c = $r->addBalise('td', '', $player['levelm']);
		$c->setAttribute('class', 'celllevel');

		$r = $t->addBalise('tr');
		$c = $r->addBalise('td', '', LOC_OP_COLUMN_RANK);
		$c->setAttribute('class', 'celltitle');
		$r->addBalise('td', '', $ranks['ranks']);
		$r->addBalise('td', '', $ranks['rankd']);
		$r->addBalise('td', '', $ranks['rankm']);

		$r = $t->addBalise('tr');
		$c = $r->addBalise('td', '', LOC_OP_COLUMN_UP);
		$c->setAttribute('class', 'celltitle');
		$c = $r->addBalise('td');
		$c->addContent($this->getBornedAverage($player['points']));
		$c = $r->addBalise('td');
		$c->addContent($this->getBornedAverage($player['pointd']));
		$c = $r->addBalise('td');
		$c->addContent($this->getBornedAverage($player['pointm']));

		$r = $t->addBalise('tr');
		$c = $r->addBalise('td', '', LOC_OP_COLUMN_DOWN);
		$c->setAttribute('class', 'celltitle');
		$r->addBalise('td', '', sprintf('%.02f', $player['downs']));
		$r->addBalise('td', '', sprintf('%.02f', $player['downd']));
		$r->addBalise('td', '', sprintf('%.02f', $player['downm']));

		$r = $t->addBalise('tr');
		$c = $r->addBalise('td', '', LOC_OP_COLUMN_VIRTUAL);
		$c->setAttribute('class', 'celltitle');
		$virtual = $this->getVirtualRank($player['levels'], $player['points'], $player['downs']);
		$c = $r->addBalise('td', '', $virtual['label']);
		$c->setAttribute('style', $virtual['style']);
		$virtual = $this->getVirtualRank($player['leveld'], $player['pointd'], $player['downd']);
		$c = $r->addBalise('td', '', $virtual['label']);
		$c->setAttribute('style', $virtual['style']);
		$virtual = $this->getVirtualRank($player['levelm'], $player['pointm'], $player['downm']);
		$c = $r->addBalise('td', '', $virtual['label']);
		$c->setAttribute('style', $virtual['style']);

		$filename = '../Img/Players/'. $player['license'] . '.png';
		if ( !file_exists($filename) )
		{
			$filename = '../Img/Players/'. $player['license'] . '.jpg';
			if ( !file_exists($filename) )
			$filename = '../Img/Players/none_'. $player['gender'] . '.png';
		}
		$di = $d->addDiv('divPhoto');
		$di->addP()->addImage('', $filename, '', array('width'=>100, 'height'=>120));
		$d->addBreak();
	}

	/**
	 * Calcule le classement virtuel d'un joueur
	 *
	 * @param string $aCurrent Classement actuel
	 * @param float $aUp   Moyenne courante
	 * @param float $aDown Moyenne de descente
	 * @return unknown
	 */
	public function getVirtualRank($aCurrent, $aUp, $aDown)
	{
		require_once 'Object/Oevent.inc';
		$q = new Bn_query('rankdef');
		$q->setFields('rkdf_label, rkdf_seuil');
		$q->addWhere('rkdf_system=' . OEVENT_RANK_FR2);
		$q->addWhere("rkdf_label <> '--'");
		$q->setOrder('rkdf_point');
		$ranks = $q->getRows();
		unset ($q);
		$current = reset($ranks);
		$up = reset($ranks);
		$down = reset($ranks);
		$prec = reset($ranks);
		foreach($ranks as $rank)
		{
			if ( $aCurrent == $rank['rkdf_label'] )
			{
				$current = $rank;
				$desc = $prec;
				// Pas de redescente en NC : si le classement courant et
				if( $current['rkdf_seuil'] && !$desc['rkdf_seuil'])
				$desc = $ranks[1];

			}
			if ( $aUp >= $rank['rkdf_seuil'] ) $up = $rank;
			if ( $aDown >= $rank['rkdf_seuil'] ) $down = $rank;
			$prec = $rank;
		}
		if ( $up['rkdf_seuil'] > $current['rkdf_seuil'])
		{
			$res['label'] = $up['rkdf_label'];
			$res['style'] = 'color:green;font-weight:bold;font-size:1.2em;';
		}
		else if ( $down['rkdf_seuil'] < $current['rkdf_seuil'])
		{
			$res['label'] = $desc['rkdf_label'];
			$res['style'] = 'color:red;font-weight:bold;font-size:1.2em;';
		}
		else
		{
			$res['label'] = $current['rkdf_label'];
			$res['style'] = '';
		}
		return $res;
	}

	/**
	 * Renvoi la moyenne bornée par les classements superieur et inferieur
	 *
	 * @param float $aUp   Moyenne courante
	 * @return strin
	 */
	public function getBornedAverage($aAverage)
	{
		$q = new Bn_query('rankdef');
		$q->setFields('rkdf_label, rkdf_seuil');
		$q->addWhere('rkdf_system=' . OEVENT_RANK_FR2);
		$q->addWhere("rkdf_label <> '--'");
		$q->setOrder('rkdf_point');
		$ranks = $q->getRows();
		unset ($q);
		$min = reset($ranks);
		foreach($ranks as $rank)
		{
			if ($rank['rkdf_seuil'] <= $aAverage) $min = $rank;
			if ($rank['rkdf_seuil'] >= $aAverage) break;
		}
		$b = new Bn_balise();
		$sp = $b->addBalise('span','', $min['rkdf_label'] );
		$sp->setAttribute('title', $min['rkdf_seuil']. ' pts');
		$sp->completeAttribute('class', 'bn-tooltip');
		$b->addContent(sprintf(' < %.2f < ', $aAverage));
		$sp = $b->addBalise('span','', $rank['rkdf_label'] );
		$sp->setAttribute('title', $rank['rkdf_seuil']. ' pts');
		$sp->completeAttribute('class', 'bn-tooltip');
		return $b;
	}

	/**
	 * Liste des categories d'age
	 * @return array
	 */
	public function getLovCatage()
	{
		for ($i=OPLAYER_CATAGE_POU; $i<=OPLAYER_CATAGE_VET; $i++)
		{
			$catages[$i] = constant('LABEL_' . $i);
		}
		return $catages;
	}

	/**
	 * Liste des surclassements
	 * @return array
	 */
	public function getLovSurclasse()
	{
		for ($i=OPLAYER_SURCLASSE_NONE; $i<=OPLAYER_SURCLASSE_SP; $i++)
		{
			$surclasses[$i] = constant('LABEL_' . $i);
		}
		return $surclasses;
	}

}
?>