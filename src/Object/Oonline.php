<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
require_once 'Oadhe.inc';
$locale = BN::getLocale();
require_once 'Locale/' . $locale . '/Oadhe.inc';
require_once 'Locale/' . $locale . '/Oaccount.inc';
require_once 'Locale/' . $locale . '/Oplayer.inc';


class Oonline extends Object
{

	/**
	 * Constructeur
	 */
	public function __construct($aLregId = -1)
	{
		$this->load('lineregi', 'lreg_id=' . $aLregId, '_asso');
		$this->setVal('labsurclasse', constant('LABEL_' . $this->getVal('surclasse')));
		$this->setVal('labcatage', constant('LABEL_' . $this->getVal('catage')));
		$this->setVal('smalabsurclasse', constant('SMA_LABEL_' . $this->getVal('surclasse')));
		$this->setVal('smalabcatage', constant('SMA_LABEL_' . $this->getVal('catage')));
		$q = new Bn_query('rankdef');
		$q->setFields('rkdf_label');
		$q->setWhere('rkdf_id=' . $this->getVal('singlelevelid', -1));
		$this->setVal('lablevels', 	$q->getFirst());
		$q->setWhere('rkdf_id=' . $this->getVal('doublelevelid', -1));
		$this->setVal('lableveld', 	$q->getFirst());
		$q->setWhere('rkdf_id=' . $this->getVal('mixedlevelid', -1));
		$this->setVal('lablevelm', 	$q->getFirst());
		unset($q);
	}

	/**
	 * Enregistre une inscription en mettant a jour les partenaires
	 *
	 * @param array $aData
	 */
	public function register($aData, $aFees)
	{
		$newEntrie = $aData;
		$fees = $aFees;

		//---- Verifier les donnees du double ----
		// Mettre a jour l'ex partenaire
		$doubleId = $this->getVal('doubleid', -1);
		if ($doubleId > 0 && $doubleId != $newEntrie['doubleid'])
		{
			// l'ex partenaire n'est plus partenaire
			$oPartner = new Oonline($doubleId);
			$fee = 0; $nb = 0;
			if($oPartner->getVal('singledrawid', -1) > 0) {$fee += $fees['IS']; $nb++;}
			if($oPartner->getVal('mixeddrawid', -1) > 0 && strlen($oPartner->getVal('mixedpartnername', '')))  {$fee += $fees['IM']; $nb++;}
			$oPartner->setVal('doublepartner',  '');
			$oPartner->setVal('doublepartnername', '');
			$oPartner->setVal('doublepoonaid', -1);
			$oPartner->setVal('doubleid', -1);
			$oPartner->setVal('doubleassoid', -1);
			$oPartner->setVal('datesoumis', '0000-00-00 00:00:00');
			$oPartner->setVal('datesreg', '0000-00-00 00:00:00');
			$oPartner->setVal('fees', $fee + $fees["I{$nb}"]);
			$oPartner->save();
			unset($oPartner);
		}

		// Chercher le partenaire s'il est renseigne de facon manuelle
		if ($newEntrie['doubleid'] == -1 &&  strlen($newEntrie['doublepartnername']) )
		{
			$newEntrie['doubleid'] = $this->searchPartner($newEntrie['doublepartnername'], $newEntrie['doublepartner']);
		}

		// Mettre a jour le nouveau partenaire
		if ($newEntrie['doubleid'] > 0)
		{
			$oPartner = new Oonline($newEntrie['doubleid']);

			// Mettre a jour l'ancien partenaire du nouveau partenaire
			// En principe ca ne doit pas arriver
			$id = $oPartner->getVal($id);
			if ($id > 0)
			{
				$o = new Oonline($oPartner->getVal('doubleid', -1));
				$o->setVal('doublepartner', '');
				$o->setVal('doublepartnername', '');
				$o->setVal('doublepoonaid', -1);
				$o->setVal('doubleid', -1);
				$o->setVal('doubleassoid', -1);
				$o->setVal('datesoumis', '0000-00-00 00:00:00');
				$o->setVal('datesreg', '0000-00-00 00:00:00');
				$o->save();
				unset($o);
			}
			// Mettre a jour le nouveau partenaire
			$fee = 0; $nb = 0;
			if($oPartner->getVal('singledrawid', -1) > 0) {$fee += $fees['IS']; $nb++;}
			$fee += $fees['ID']; $nb++;
			if($oPartner->getVal('mixeddrawid', -1) > 0 && strlen($oPartner->getVal('mixedpartnername', '')))  {$fee += $fees['IM']; $nb++;}
			$oPartner->setVal('doublepartner',     $this->getVal('firstname'));
			$oPartner->setVal('doublepartnername', $this->getVal('familyname'));
			$oPartner->setVal('doublepoonaid',     $this->getVal('poonaid'));
			$oPartner->setVal('doubleid',          $this->getVal('id'));
			$oPartner->setVal('doubleassoid',      $this->getVal('assoid'));
			$oPartner->setVal('datesoumis', '0000-00-00 00:00:00');
			$oPartner->setVal('datesreg', '0000-00-00 00:00:00');
			$oPartner->setVal('fees', $fee + $fees["I{$nb}"]);
			$oPartner->save();

			// Recuperer les infos du partenaire
			$newEntrie['doublepartner']     = $oPartner->getVal('firstname');
			$newEntrie['doublepartnername'] = $oPartner->getVal('familyname');
			$newEntrie['doublepoonaid']     = $oPartner->getVal('poonaid');
			$newEntrie['doubleassoid']      = $oPartner->getVal('assoid');
			unset($oPartner);
		}

		//---- Verifier les donnees du mixte ----
		// Mettre a jour l'ex partenaire
		$mixedId = $this->getVal('mixedid', -1);
		if ($mixedId > 0 && $mixedId != $newEntrie['mixedid'])
		{
			$oPartner = new Oonline($mixedId);
			$fee = 0; $nb = 0;
			if($oPartner->getVal('singledrawid', -1) > 0) {$fee += $fees['IS']; $nb++;}
			if($oPartner->getVal('doubledrawid', -1) > 0 && strlen($oPartner->getVal('doublepartnername', '')))  {$fee += $fees['ID']; $nb++;}
			// l'ex partenaire n'est plus partenaire
			$oPartner->setVal('mixedpartner', '');
			$oPartner->setVal('mixedpartnername', '');
			$oPartner->setVal('mixedpoonaid', -1);
			$oPartner->setVal('mixedid', -1);
			$oPartner->setVal('mixedassoid', -1);
			$oPartner->setVal('datesoumis', '0000-00-00 00:00:00');
			$oPartner->setVal('datesreg', '0000-00-00 00:00:00');
			$oPartner->setVal('fees', $fee + $fees["I{$nb}"]);
			$oPartner->save();
			unset($oPartner);
		}

		// Chercher le partenaire s'il est est renseigne de facon manuelle
		if ($newEntrie['mixedid'] == -1 &&  strlen($newEntrie['mixedpartnername']) )
		{
			$newEntrie['mixedid'] = $this->searchPartner($newEntrie['mixedpartnername'], $newEntrie['mixedpartner']);
		}

		// Mettre a jour le nouveau partenaire
		if ($newEntrie['mixedid'] > 0)
		{
			$oPartner = new Oonline($newEntrie['mixedid']);

			// Mettre a jour l'ancien partenaire du nouveau partenaire
			// En principe ca ne doit pas arriver
			$id = $oPartner->getVal($id);
			if ($id > 0)
			{
				$o = new Oonline($oPartner->getVal('mixedid'));
				$o->setVal('mixedpartner', '');
				$o->setVal('mixedpartnername', '');
				$o->setVal('mixedpoonaid', -1);
				$o->setVal('mixedid', -1);
				$o->setVal('mixedassoid', -1);
				$o->setVal('datesoumis', '0000-00-00 00:00:00');
				$o->setVal('datesreg', '0000-00-00 00:00:00');
				$o->save();
				unset($o);
			}
			// Mettre a jour le nouveau partenaire
			$fee = 0; $nb = 0;
			if($oPartner->getVal('singledrawid', -1) > 0) {$fee += $fees['IS']; $nb++;}
			if($oPartner->getVal('doubledrawid', -1) > 0 && strlen($oPartner->getVal('doublepartnername')))  {$fee += $fees['ID']; $nb++;}
			$fee += $fees['IM']; $nb++;
			$oPartner->setVal('mixedpartner',     $this->getVal('firstname'));
			$oPartner->setVal('mixedpartnername', $this->getVal('familyname'));
			$oPartner->setVal('mixedpoonaid',     $this->getVal('poonaid'));
			$oPartner->setVal('mixedid',          $this->getVal('id'));
			$oPartner->setVal('mixedassoid',      $this->getVal('assoid'));
			$oPartner->setVal('datesoumis', '0000-00-00 00:00:00');
			$oPartner->setVal('datesreg', '0000-00-00 00:00:00');
			$oPartner->setVal('fees', $fee + $fees["I{$nb}"]);
			$oPartner->save();

			// Recuperer les infos du partenaire
			$newEntrie['mixedpartner'] =     $oPartner->getVal('firstname');
			$newEntrie['mixedpartnername'] = $oPartner->getVal('familyname');
			$newEntrie['mixedpoonaid'] =     $oPartner->getVal('poonaid');
			$newEntrie['mixedassoid']  =     $oPartner->getVal('assoid');
			unset($oPartner);
		}

		//Calculer les frais d'inscritpion
		$fee = 0; $nb = 0;
		if($newEntrie['singledrawid'] > 0) {$fee += $fees['IS']; $nb++;}
		if($newEntrie['doubledrawid'] > 0 && strlen($newEntrie['doublepartnername'])) {$fee += $fees['ID']; $nb++;}
		if($newEntrie['mixeddrawid'] > 0 && strlen($newEntrie['mixedpartnername']))  {$fee += $fees['IM']; $nb++;}
		$newEntrie['fees'] = $fee + $fees["I{$nb}"];

		// Mettre a jour l'inscription
		$this->setValues($newEntrie);
		$this->save();

	}

	/**
	 * Verifie l'inscription a un tournoi a partir de la licence
	 *
	 * @param int $aLicense
	 * @param int $aEventId
	 *
	 * */
	public function checkRegi($aLicense, $aEventId)
	{
		// chercher si le joueur est inscrit pour ce tournoi
		$q = new Bn_query('lineregi', '_asso');
		$q->addfield('lreg_id');
		$q->addWhere('lreg_licence='.intval($aLicense));
		$q->addwhere('lreg_eventid='.$aEventId);
		$lregId = $q->getFirst();
		return $lregId;
	}

	/**
	 * Recupere l'inscription a partir de la licence
	 *
	 * @param int $aLicense
	 * @param int $aEventId
	 *
	 * */
	public function initFromLicense($aLicense, $aEventId)
	{
		// chercher si le joueur est inscrit pour ce tournoi
		$q = new Bn_query('lineregi', '_asso');
		$q->addfield('*');
		$q->addWhere('lreg_licence='.intval($aLicense));
		$q->addwhere('lreg_eventid='.$aEventId);
		$fields = $q->getRow();
		if ( !empty($fields) )
		{
			foreach($fields as $field =>$value)
			{
				$token = explode('_', $field);
				$this->setVal($token[1],  $value);
			}
		}
		// Initialiser avec les donnees Poona
		else
		{
			$oExt = Oexternal::factory();
			$member = $oExt->getMember($aLicense);
			$season = Oseason::getCurrent();
			$player = $oExt->getPlayer($aLicense, $season);
			if($player['instanceid'] == -1) $player = $oExt->getPlayer($aLicense, $season-1);
				
			$this->setVal('gender', $member['gender']);
			$this->setVal('familyname', $member['familyname']);
			$this->setVal('firstname', $member['firstname']);
			$this->setVal('licence', $member['license']);
			$this->setVal('eventid', $aEventId);
			$this->setVal('catage', $player['catage']);
			$this->setVal('surclasse', $player['surclasse']);
			$this->setVal('poonaid', $member['fedeid']);
			$this->setVal('assoid', $player['assoid']);
			$this->setVal('instance', $player['instanceid']);
			$this->setVal('singlelevelid', $player['levelsid']);
			$this->setVal('singlepoints', $player['points']);
			$this->setVal('doublelevelid', $player['leveldid']);
			$this->setVal('doublepoints', $player['pointd']);
				
			$this->setVal('singlerank', $player['ranks']);
			$this->setVal('doublerank', $player['rankd']);
				
			// Rechercher si un joueur est deja inscrit avec celui-ci en double
			$q->setWhere('lreg_assoid=' . $player['assoid']);
			$q->addWhere("lreg_eventid=" . $aEventId);
			$q->addWhere("lreg_doublepartner = '" . addslashes($member['firstname']) . "'");
			$q->addWhere("lreg_doublepartnername = '" . addslashes($member['familyname']) . "'");
			$partner = $q->getRow();
				
			$this->setVal('doubledrawid', $partner['lreg_doubledrawid']);
			$this->setVal('doublepoonaid', $partner['lreg_poonaid']);
			$this->setVal('doubleassoid', $partner['lreg_assoid']);
			$this->setVal('doublepartner', $partner['lreg_firstname']);
			$this->setVal('doublepartnername', $partner['lreg_familyname']);
			$this->setVal('doubleid',$partner['lreg_id']);
				
			$this->setVal('mixedlevelid', $player['levelmid']);
			$this->setVal('mixedpoints', $player['pointm']);
			$this->setVal('mixedrank', $ranks['rankm']);

			// Rechercher si un joueur est deja inscrit avec celui-ci en mixte
			$q->setWhere('lreg_assoid=' . $player['assoid']);
			$q->addWhere("lreg_eventid=" . $aEventId);
			$q->addWhere("lreg_mixedpartner='" . addslashes($member['firstname']) . "'");
			$q->addWhere("lreg_mixedpartnername='" . addslashes($member['familyname'])  . "'");
			$partner = $q->getRow();
				
			$this->setVal('mixeddrawid', $partner['lreg_mixeddrawid']);
			$this->setVal('mixedpoonaid', $partner['lreg_poonaid']);
			$this->setVal('mixedassoid', $partner['lreg_assoid']);
			$this->setVal('mixedpartner', $partner['lreg_firstname']);
			$this->setVal('mixedpartnername', $partner['lreg_familyname']);
			$this->setVal('mixedid',$partner['lreg_id']);
		}
		$this->setVal('season', Oseason::getCurrent());
	}

	/**
	 * Suppression d'une inscription
	 */
	public function delete()
	{
		// Montant d'inscription
		$eventId =  $this->getVal('eventid', -1);
		$oEvent = new Oevent($eventId);
		$fees = $oEvent->getFees();
		unset($oEvent);

		// Mise a jour du partenaire de double
		if ($this->getVal('doubleid') > 0)
		{
			// l'ex partenaire n'est plus partenaire
			$oPartner = new Oonline($this->getVal('doubleid'));
			$fee = 0; $nb = 0;
			if($oPartner->getVal('singledrawid') > 0) {$fee += $fees['IS']; $nb++;}
			if($oPartner->getVal('mixeddrawid') > 0 && strlen($oPartner->getVal('mixedpartnername')))  {$fee += $fees['IM']; $nb++;}
			$oPartner->setVal('doublepartner', '');
			$oPartner->setVal('doublepartnername', '');
			$oPartner->setVal('doublepoonaid', -1);
			$oPartner->setVal('doubleid', -1);
			$oPartner->setVal('doubleassoid', -1);
			$oPartner->setVal('datesoumis', '0000-00-00 00:00:00');
			$oPartner->setVal('fees', $fee + $fees["I{$nb}"]);
			$oPartner->save();
			unset($oPartner);
		}

		// Mise a jour du partenaire de mixte
		if ($this->getVal('mixedid') > 0)
		{
			// l'ex partenaire n'est plus partenaire
			$oPartner = new Oonline($this->getVal('mixedid'));
			$fee = 0; $nb = 0;
			if($oPartner->getVal('singledrawid') > 0) {$fee += $fees['IS']; $nb++;}
			if($oPartner->getVal('doubledrawid') > 0 && strlen($oPartner->getVal('doublepartnername')))  {$fee += $fees['ID']; $nb++;}
			$oPartner->setVal('mixedpartner', '');
			$oPartner->setVal('mixedpartnername', '');
			$oPartner->setVal('mixedpoonaid', -1);
			$oPartner->setVal('mixedid', -1);
			$oPartner->setVal('mixedassoid', -1);
			$oPartner->setVal('datesoumis', '0000-00-00 00:00:00');
			$oPartner->setVal('fees', $fee + $fees["I{$nb}"]);
			$oPartner->save();
			unset($oPartner);
		}

		$q = new Bn_query('lineregi', '_asso');
		$q->deleteRow('lreg_id=' . $this->getVal('id', -1));
		unset($q);
	}

	/**
	 * Destructeur
	 */
	public function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * Marquer les inscriptions comme envoye a l'organisateur
	 * @param array	$aLregIds	Identifiant des inscriptions
	 * @return void
	 */
	public function sendEntries($aLregIds)
	{
		if(is_array($aLregIds) ) $lregIds = $aLregIds;
		else $lregIds[] = intval($aLregIds);

		$q = new Bn_query('lineregi', '_asso');
		$q->setValue('lreg_datesoumis',  date('Y-m-d H:i:s'));
		$q->addWhere('lreg_id IN ('. implode(',', $lregIds) .')');
		$q->updateRow();
		unset($q);
		return true;
	}

	/**
	 * Chercher un partenaire inscrit a partir de son nom, prenom
	 * @param string	$aFamilyName nom de famille
	 * @param string	$aFirstName prenom
	 * @return void
	 */
	public function searchPartner($aFamilyname, $aFirstname)
	{
		$q = new Bn_query('lineregi', '_asso');
		$q->setFields('lreg_id');
		$q->addWhere('lreg_eventid=' . $this->getVal('eventid', -1));
		$q->addWhere('lreg_assoid=' . $this->getVal('assoid', -1));
		$q->addWhere("lreg_familyname='" . addslashes($aFamilyname) . "'");
		$q->addWhere("lreg_firstname='" . addslashes($aFirstname) . "'");
		$id = $q->getFirst();
		unset($q);
		return $id;
	}

	/**
	 * Renvoie les partenaire possibles en double
	 * @param integer	$aDraw  tableau
	 * @param integer	$aGender  genre du demandeur
	 * @param integer	$aDraw  equipe du demandeur
	 * @return void
	 */
	public function getPartnersDouble($aDrawId, $aGender, $aAssoId, $aEventId)
	{
		$id = $this->getVal('id', -1);

		$q = new Bn_query('lineregi', '_asso');
		$q->setFields('lreg_id, lreg_familyname, lreg_firstname');
		$q->addWhere('lreg_id<>' . $id);
		$q->addWhere('lreg_assoid=' . $aAssoId);
		$q->addWhere('lreg_gender=' . $aGender);
		$q->addWhere('lreg_doubledrawid=' . $aDrawId);
		$q->addWhere('lreg_eventid=' . $aEventId);
		$q->addWhere("(lreg_doublepartnername = '' OR lreg_doubleid=". $id .')');
		$partners = $q->getRows();
		//print_r($q);
		unset($q);
		return $partners;
	}

	/**
	 * Renvoie les partenaire possibles en mixte
	 * @param integer	$aDraw  tableau
	 * @param integer	$aGender  genre du demandeur
	 * @param integer	$aDraw  equipe du demandeur
	 * @return void
	 */
	public function getPartnersMixed($aDrawId, $aGender, $aAssoId, $aEventId)
	{
		$id = $this->getVal('id', -1);

		$q = new Bn_query('lineregi', '_asso');
		$q->setFields('lreg_id, lreg_familyname, lreg_firstname');
		$q->addWhere('lreg_assoid=' . $aAssoId);
		$q->addWhere('lreg_gender<>' . $aGender);
		$q->addWhere('lreg_mixeddrawid=' . $aDrawId);
		$q->addWhere('lreg_eventid=' . $aEventId);
		$q->addWhere("(lreg_mixedpartnername = '' OR lreg_mixedid=". $id .')');
		$partners = $q->getRows();
		unset($q);
		return $partners;
	}

	/**
	 * Enregistre les modifications d'une inscription
	 * @param array	$aData  liste des champs
	 * @return void
	 */
	public function save($aData = null)
	{
		if (empty($aData))	$data = $this->getValues();
		else $data = $aData;
		$where = 'lreg_id=' . $this->getVal('id', -1);
		return $this->update('lineregi', 'lreg_', $data, $where, '_asso');
	}

	public function getRegis($aUserId, $aAssoId, $aEventId, $aStatus=0)
	{

		//Joueurs inscrits
		// Inscription en preparation
		$q = new Bn_query('lineregi', '_asso');
		$q->addField('lreg_id', 'id');
		if ($aStatus == 1) $q->addField('lreg_datesoumis', 'datesoumis');
		$q->addField('lreg_familyname', 'familyname');
		$q->addField('lreg_singlelevelid', 'singlelevelid');
		$q->addField('lreg_singledrawid',  'singledrawid');
		$q->addField('lreg_doubledrawid',  'doubledrawid');
		$q->addField('lreg_doublepartnername', 'doublepartnername');
		$q->addField('lreg_mixeddrawid',       'mixeddrawid');
		$q->addField('lreg_mixedpartnername',  'mixedpartnername');
		$q->addField('lreg_fees', 'fees');

		$q->addField('lreg_firstname',  'firstname');
		$q->addField('lreg_mixedpartner',      'mixedpartner');
		$q->addField('lreg_doublepartner', 'doublepartner');
		$q->addField('lreg_assoid',     'assoid');
		$q->addField('lreg_eventid',    'eventid');
		$q->addField('lreg_catage',     'catage');
		$q->addField('lreg_surclasse',  'surclasse');
		$q->addField('lreg_season',     'season');
		$q->addField('lreg_gender',     'gender');
		$q->addField('lreg_licence',    'licence');

		$q->addField('lreg_doublelevelid', 'doublelevelid');
		$q->addField('lreg_mixedlevelid',      'mixedlevelid');

		$q->addWhere('lreg_eventid=' . $aEventId);
		//$q->addWhere('lreg_assoid=' . $aAssoId);
		$q->addWhere('(lreg_userid=' . $aUserId . ' OR lreg_assoid=' . $aAssoId . ')');
		
		if ($aStatus == 1)
		{
			$q->addWhere("lreg_datesoumis > '0000-00-00 00:00:00'");
		}
		else
		{
			$q->addWhere("lreg_datesoumis = '0000-00-00 00:00:00'");
		}
		return $q;
	}

	/**
	 * Affichage du cartouche joueur
	 *
	 * @return string
	 */
	Public function display(Bn_balise $aDiv)
	{
		$d = $aDiv->addDiv('', 'badnetPlayer');
		$player = $this->getValues();

		// Tableau pour les infos sportives
		$dp = $d->addDiv('divCivil');
		$dp->addP('pName', $this->getVal('familyname') . ' ' . $this->getVal('firstname'));
		$ul = $dp->addBalise('ul');
		$li = $ul->addBalise('li', '', LOC_OP_LABEL_NUMLICENSE);
		$li->addBalise('span','playerLicense', $this->getVal('licence'));
		$li = $ul->addBalise('li', '', LOC_OP_LABEL_CATAGE);
		$li->addBalise('span','', $this->getVal('labcatage'));
		$li = $ul->addBalise('li', '', LOC_OP_LABEL_SURCLASSE);
		$li->addBalise('span','', $this->getVal('labsurclasse'));
		$year = Oseason::getYear($player['season']);
		$dp->addP('pSeason', Bn::date($this->getVal(updt), 'd-m-Y'));

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
		$c = $r->addBalise('td', '', $this->getVal('lablevels'));
		$c->setAttribute('class', 'celllevel');
		$c = $r->addBalise('td', '', $this->getVal('lableveld'));
		$c->setAttribute('class', 'celllevel');
		$c = $r->addBalise('td', '', $this->getVal('lablevelm'));
		$c->setAttribute('class', 'celllevel');

		$r = $t->addBalise('tr');
		$c = $r->addBalise('td', '', LOC_OP_COLUMN_RANK);
		$c->setAttribute('class', 'celltitle');
		$r->addBalise('td', '', $this->getVal('singlerank'));
		$r->addBalise('td', '', $this->getVal('doublerank'));
		$r->addBalise('td', '', $this->getVal('mixedrank'));

		$r = $t->addBalise('tr');
		$c = $r->addBalise('td', '', LOC_OP_COLUMN_UP);
		$c->setAttribute('class', 'celltitle');
		$c = $r->addBalise('td');
		$c->addContent($this->getVal('singlepoints'));
		$c = $r->addBalise('td');
		$c->addContent($this->getVal('doublepoints'));
		$c = $r->addBalise('td');
		$c->addContent($this->getVal('mixedpoints'));

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

}
?>
