<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

require_once 'Object.php';
require_once 'Oaccount.inc';
require_once 'Oevent.inc';
require_once 'Oasso.inc';
$locale = BN::getLocale();
require_once 'Locale/' . $locale . '/Oaccount.inc';


class Oaccount extends Object
{
	/**
	 * Renvoi les id des tournois du joueur
	 * @param	integer	$aSeason Saison
	 * @return boolean  Vrai si gestionnaire
	 */
	public function getEvents($aSeason=null, $aType=OEVENT_TYPE_INDIVIDUAL)
	{
		$eventIds = array();
		if ($this->isuserPlayer() )
		{
			$q = new Bn_query('members');
			$q->addTable('registration', 'mber_id=regi_memberid');
			$q->addTable('events', 'evnt_id=regi_eventid');
			$q->setFields('evnt_id');
			$q->addWhere('mber_licence=' . sprintf('%08d', $this->getVal('login')));

			if (!empty($aSeason) ) $q->addWhere('evnt_season=' . $aSeason);
			if (!empty($aType) ) $q->addWhere('evnt_type=' . $aType);
			$q->addWhere("evnt_lastday >='" .date('Y-m-d') . "'");
			//$q->setOrder('evnt_lastday');
			$eventIds = $q->getCol();
		}
		return ($eventIds);
	}


	/**
	 * Retourne l'inscription pour un tournoi
	 *
	 * @param unknown_type $aEventId
	 * @return unknown
	 */
	public function getPartner($aEventId)
	{
		if ( !$this->isuserPlayer() ) return false;

		$q = new Bn_query('members');
		$q->addTable('registration', 'mber_id=regi_memberid');
		$q->addTable('events', 'evnt_id=regi_eventid');
		$q->addTable('i2p', 'i2p_regiid=regi_id');
		$q->addTable('pairs', 'i2p_pairid=pair_id');
		$q->addTable('draws', 'pair_drawid=draw_id');

		$q->setFields('draw_id, draw_name, draw_stamp, pair_id, pair_disci, regi_id, evnt_name, evnt_firstday');

		$q->addWhere('mber_licence=' . sprintf('%08d', $this->getVal('login')));
		$q->addWhere('regi_eventid=' . $aEventId);

		$draws = $q->getRows();
		$q->setTables('registration');
		$q->addTable('i2p', 'i2p_regiid=regi_id');
		$q->setFields('regi_id, regi_longname');

		foreach($draws as $draw)
		{
			$disci = $pair['pair_disci'];
			$row['evnt_name']  = $draw['evnt_name'];
			$row['evnt_firstday']  = $draw['evnt_firstday'];
			$row['draw_name']  = $draw['draw_name'];
			$row['draw_stamp'] = $draw['draw_stamp'];
			$row['draw_id']    = $draw['draw_id'];
			if ($disci != OMATCH_DISCIPLINE_SINGLE)
			{
				// Recherche du partenaire
				$q->setWhere('i2p_regiid=regi_id');
				$q->addWhere('i2p_regiId !=' . $draw['regi_id']);
				$q->addWhere('i2p_pairid =' . $draw['pair_id']);
				$partner = $q->getRow();
				if (!empty($partner))
				{
					$row['partner_id']   = $partner['regi_id'];
					$row['partner_name'] = $partner['regi_longname'];
				}
				else
				{
					$row['partner_id']   = -1;
					$row['partner_name'] = 'Recherche partenaire';
				}
			}
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * Enregistre l'identite du compte
	 *
	 */
	public function updateIdentity($aOidentity)
	{
		$aOidentity->save();
		$adheId = $aOidentity->getVal('id', -1);
		$q = new Bn_query('u2a', '_asso');
		$q->addValue('u2a_adherentid', $adheId);
		$q->addValue('u2a_userid', $this->getVal('id', -1));
		$q->addWhere('u2a_userid='. $this->getVal('id', -1));
		$q->replaceRow();
		//print_r($q);
	}

	/**
	 * Renvoi l'identité associe a ce compte
	 *
	 */
	public function getIdentity()
	{
		return new Oadhe($this->getVal('id', -1));
	}

	/**
	 * Supression de l'identite associe a ce compte
	 *
	 */
	public function delIdentity()
	{
		// Verifier l'adhesion
		$q = new Bn_query('u2a', '_asso');
		$q->setFields('u2a_adherentid');
		$q->addWhere('u2a_userid='. $this->getVal('id', -1));
		$adheId = $q->getOne();
		$q->deleteRow();
		$q->setTables('adherents');
		$q->deleteRow('adhe_id=' . $adheId);
	}

	public function delete($aUserId)
	{
		// Verifier les droits sur tournoi
		$q = new Bn_query('rights');
		$q->addTable('events', 'evnt_id=rght_themeid');
		$q->addWhere('rght_userid='. $aUserId);
		$q->addWhere('rght_theme='. THEME_EVENT);
		$q->setFields('evnt_name, rght_status');
		$events = $q->getRows();
		if ( !empty($events) ) return $events;

		// Verifier les droits sur club
		$q->setTables('rights');
		$q->addTable('assocs', 'asso_id=rght_themeid');
		$q->addWhere('rght_userid='. $aUserId);
		$q->addWhere('rght_theme='. THEME_ASSOS);
		$q->setFields('asso_name, rght_status');
		$assos = $q->getRows();
		if ( !empty($assos) ) return $assos;

		// Verifier l'adhesion
		$qa = new Bn_query('u2a', '_asso');
		$qa->addTable('adherents', 'u2a_adherentid=adhe_id');
		$qa->addWhere('u2a_userid='. $aUserId);
		$qa->setFields('adhe_id, adhe_name');
		$adhes = $qa->getRows();
		if ( !empty($adhe) ) return $adhe;

		// Verifier les commandes
		if ( ! empty($adhes) )
		{
			$adhe = reset($adhes);
			$qa->setTables('commands');
			$qa->addWhere('comd_adherentid=' . $adhe['adhe_id']);
			$qa->setFields('comd_datecommand, comd_name, comd_value, comd_paid');
			$commands = $qa->getRows();
			if ( !empty($commands) ) return $commands;
		}

		// Supprimer le compte
		$q->setTables('users');
		$q->deleteRow('user_id=' . $aUserId);
		return null	;
	}

	/**
	 * Constructeur
	 */
	public function __construct($aAccountId=-1)
	{
		if($aAccountId > 0)
		{
			$q = new Bn_query('users');
			$q->addField('user_name',   'name');
			$q->addField('user_email',  'email');
			$q->addField('user_login',  'login');
			$q->addField('user_pseudo', 'pseudo');
			$q->addField('user_lang',   'lang');
			$q->addField('user_cre',    'creation');
			$q->addField('user_nbcnx',  'nbcnx');
			$q->addField('user_type',   'type');
			$q->addField('user_cmt',    'cmt');
			$q->addField('user_pass',   'pass');
			$q->addField('user_id',     'id');
			$q->addField('user_lastvisit',   'lastvisit');
			$q->addWhere('user_id=' . $aAccountId);
			$user = $q->getRow();
			$this->setValues($user, true);
		}
	}

	/**
	 * Verifie si un utilisateur est capitaine d'une equipe d'un tournoi
	 * @param	integer	$aEventId	Identifiant du tournoi
	 * @return boolean  Vrai si gestionnaire
	 */
	public function isCaptain($aEventId)
	{
		$q = new Bn_query('teams');
		$q->addField('team_id');
		$q->addWhere('team_eventid=' . $aEventId);
		$q->addWhere('team_captainid=' . $this->getVal('id', -1));
		$right = $q->getCol();
		return (!empty($right));
	}

	/**
	 * Verifie si un utilisateur est gestionaire d'un tournoi
	 * @param	integer	$aEventId	Identifiant du tournoi
	 * @return boolean  Vrai si gestionnaire
	 */
	public function isEventManager($aEventId)
	{

		$q = new Bn_query('rights');
		$q->addField('rght_status');
		$q->addWhere('rght_userid=' . $this->getVal('id', -1));
		$q->addWhere('rght_theme=' . THEME_EVENT);
		$q->addWhere('rght_themeid=' . $aEventId);
		$q->addWhere("rght_status='" . AUTH_MANAGER . "'");
		$right = $q->getFirst();
		return (!empty($right));
	}

	/**
	 * Verifie si un utilisateur est assistant d'un tournoi
	 * @param	integer	$aEventId	Identifiant du tournoi
	 * @return boolean  Vrai si gestionnaire
	 */
	public function isAssistant($aEventId)
	{

		$q = new Bn_query('rights');
		$q->addField('rght_status');
		$q->addWhere('rght_userid=' . $this->getVal('id', -1));
		$q->addWhere('rght_theme=' . THEME_EVENT);
		$q->addWhere('rght_themeid=' . $aEventId);
		$q->addWhere("rght_status='" . AUTH_ASSISTANT . "'");
		$right = $q->getFirst();
		return (!empty($right));
	}

	/**
	 * Liste de valeur des civilites
	 * @return array
	 */
	public function getLovCivilite()
	{
		for ($i=OACCOUNT_MADAME; $i<=OACCOUNT_MONSIEUR; $i++)
		{
			$civilites[$i] = constant('LABEL_' . $i);
		}
		return $civilites;
	}

	/**
	 * Liste de valeur des langues
	 * @return array
	 */
	public function getLovLocale()
	{
		$locales['De'] = 'De';
		$locales['En'] = 'En';
		$locales['Fr'] = 'Fr';
		return $locales;
	}

	/**
	 * Verifie la presence d'un email
	 * @param string $aEmail	email a controler
	 * @return boolean  Vrai si un compte avec l'email existe
	 */
	public function checkEmail($aEmail, $aType = 'U', $aId = -1)
	{
		$q = new Bn_query('users');
		$q->setFields('user_login, user_id');
		$q->setWhere("user_email LIKE '%" . $aEmail . "%'");
		if (!empty($aType))	$q->addWhere("user_type='" . $aType . "'");
		if ($aId > 0) $q->addWhere('user_id<>' . $aId);
		$emails = $q->getRows();
		return $emails;
	}

	/**
	 * Verifie l'existence d'un login
	 * @param string $aLogin	login a controler
	 * @return boolean  Vrai si un compte avec le login existe
	 */
	static public function checkLogin($aLogin, $aId = -1)
	{
		$q = new Bn_query('users');
		$q->setFields('user_id');
		$q->addWhere("user_login='" . $aLogin . "'");
		if ($aId > 0) $q->addWhere('user_id<>' . $aId);
		$id = $q->getOne();
		return !is_null($id);
	}

	/**
	 * Verifie si l'utilisateur courant est administrateur (pas celui qui s'est connecte
	 * @return boolean  Vrai si l'utilisateur est administrateur
	 */
	public function isUserAdmin()
	{
		return (Bn::getValue('user_type') == 'A');
	}

	/**
	 * Verifie si l'utilisateur courant est manager (pas celui qui s'est connecte
	 * @return boolean  Vrai si l'utilisateur est manager
	 */
	public function isUserManager()
	{
		return (Bn::getValue('user_type') == 'M');
	}
	
	
	/**
	 * Verifie si l'utilisateur courant a un compte joueur
	 * @return boolean  Vrai si le compte est de type joueur
	 */
	public function isUserPlayer()
	{
		return (Bn::getValue('user_type') == 'P');
	}

	/**
	 * Verifie si l'utilisateur courant a un compte club
	 * @return boolean  Vrai si le compte est de type club
	 */
	public function isUserClub()
	{
		$q = new BN_query('rights');
		$q->addTable('assocs', 'rght_themeid = asso_id');
		$q->addField('count(*)');
		$q->addWhere('rght_theme = ' . THEME_ASSOS);
		$q->addWhere("rght_status ='" . AUTH_MANAGER . "'");
		$q->addWhere('rght_userid =' . Bn::getValue('user_id', -1));
		$nb = $q->getFirst();
		return (Bn::getValue('user_type') == AUTH_ASSO || $nb);
	}
	
	/**
	 * Verifie si l'utilisateur courant a un compte ligue
	 * @return boolean  Vrai si le compte est de type club
	 */
	public function isUserLigue()
	{
		$q = new BN_query('rights');
		$q->addTable('assocs', 'rght_themeid = asso_id');
		$q->addField('count(*)');
		$q->addWhere('rght_theme = ' . THEME_ASSOS);
		$q->addWhere("rght_status ='" . AUTH_MANAGER . "'");
		$q->addWhere('rght_userid =' . Bn::getValue('user_id', -1));
		$q->addWhere('asso_type =' . OASSO_TYPE_LIGUE);
		$nb = $q->getFirst();
		return (Bn::getValue('user_type') == AUTH_ASSO || $nb);
	}
	
	/**
	 * Verifie si l'utilisateur courant a un compte club prive (squash)
	 * @return boolean  Vrai si le compte est de type club
	 */
	public function isUserPrivate()
	{
		$q = new BN_query('rights');
		$q->addTable('assocs', 'rght_themeid = asso_id');
		$q->addField('count(*)');
		$q->addWhere('rght_theme = ' . THEME_ASSOS);
		$q->addWhere("rght_status ='" . AUTH_MANAGER . "'");
		$q->addWhere('rght_userid =' . Bn::getValue('user_id', -1));
		$q->addWhere('asso_type =' . OASSO_TYPE_PRIVATE);
		$nb = $q->getFirst();
		return (Bn::getValue('user_type') == AUTH_PRIVATE || $nb);
	}
	
	/**
	 * Verifie si un utilisateur est connecte
	 * @return boolean  Vrai si connexion
	 */
	public function isLogin()
	{
		$type = Bn::getValue('user_type');
		return (!empty($type));
	}

	/**
	 * Verifie si l'utilisateur connecte est adminitrateur
	 * @return boolean  Vrai si l'utilisateur est administrateur
	 */
	public function isLoginAdmin()
	{
		return (Bn::getValue('loginAuth') == 'A');
	}

	/**
	 * newPassword
	 */
	public function newPassword($aEmail, $aPwd)
	{
		if ( empty($aEmail) )
		{
			$this->_error(LOC_MSG_EMAIL_EMPTY);
		}
		else
		{
			$q = new Bn_query('users');
			$q->setFields('user_id');
			$q->addWhere("user_email='" . $aEmail . "'");
			$q->addWhere("user_type!='" . AUTH_PRIVATE . "'");
			$id = $q->getOne();
			if ( is_null($id) )
			{
				$this->_error(LOC_MSG_EMAIL_UNKNOW);
			}
			else
			{
				$q->addValue('user_pass',  md5($aPwd));
				$q->updateRow();
			}
		}
	}

	/**
	 * Change le mot de passe d'un utilisateur
	 * @param string $aOldPwd	Ancien mot de passe en clair
	 * @param stringc$aPwd		Nouveau mot de passe en clair
	 */
	public function changePassword($aOldPwd, $aPwd)
	{
		// Verifier l'ancien mot de passe
		$q = new Bn_query('users');
		$q->setFields('user_login');
		$q->addWhere('user_id=' . $this->getVal('id'));
		$q->addWhere("user_pass='" . md5($aOldPwd) ."'");
		$login = $q->getOne();
		if ( is_null($login) ) $this->_error(LOC_MSG_BAD_PWD);

		// Enregistrer le nouveau mot de passe
		else
		{
			if ($login == $aPwd)$this->_error(LOC_MSG_LOGIN_NOT_PWD);
			else
			{
				$q->addValue('user_pass',  md5($aPwd));
				$q->updateRow();
			}
		}
		return;
	}

	/**
	 * Enregistre en bdd les donnees utilisateurs
	 */
	public function save()
	{
		// Verifier le login
		$login = $this->getValue('login');
		$id    = $this->getValue('id');
		if ( $this->checkLogin($login, $id) )
		{
			$this->_error(LOC_MSG_LOGIN_EXIST . '(' . $login . ')');
			return false;
		}

		// Enregistrer les donnees
		$where = 'user_id=' . $id;
		$id = $this->update('users', 'user_', $this->getValues(), $where);
		return $id;
	}

	/**
	 * Enregistre en bdd une demande de compte
	 */
	public function newAccount($aFields)
	{
		//  Verification de l'existence du login
		if ($this->checkLogin($aFields['login']))
		{
			$this->_error(LOC_MSG_LOGIN_EXIST);
			return false;
		}

		// Enregistrement de la demande dans la base
		$q = new Bn_query('creations', '_asso');
		foreach( $aFields as $key => $value)
		{
			$q->addValue('crea_'.$key, $value);
		}
		$id = $q->addRow();
		if ( $q->isError() )
		{
			$this->_error($q->getMsg());
		}
		return $id;
	}

	/**
	 * Confirmation d'une creation de compte
	 */
	public function confirmAccount($aCode)
	{
		//  Verification de l'existence
		$q = new Bn_query('creations', '_asso');
		$q->addWhere("crea_code='" . $aCode ."'");
		$q->addField('*');
		$creation = $q->getRow();
		if ( $q->isError() )
		{
			$this->_error($q->getMsg());
			return false;
		}
		if ( empty($creation) )
		{
			$this->_error(LOC_MSG_BAD_DEMANDE);
			return false;
		}
		// Verifier le login
		if ($this->checkLogin($creation['crea_login']))
		{
			$this->_error(LOC_MSG_LOGIN_EXIST . '(' . $creation['crea_login'] . ')');
			return false;
		}

		// Creation de l'utilisateur
		$q2 = new Bn_query('users');
		$q2->addValue('user_name',   $creation['crea_name']);
		$q2->addValue('user_email',  $creation['crea_email']);
		$q2->addValue('user_login',  $creation['crea_login']);
		$q2->addValue('user_pseudo', $creation['crea_pseudo']);
		$q2->addValue('user_lang',   $creation['crea_lang']);
		$q2->addValue('user_type',   $creation['crea_type']);
		$q2->addValue('user_pass',   md5($creation['crea_password']));
		$creation['id'] = $q2->addRow();

		// Supression de la demande et des demande pour le meme email
		if ( !$q2->isError() )
		{
			$q->deleteRow();
			$q->setWhere("crea_email='" . $creation['crea_email'] ."'");
			$q->deleteRow();
		}
		else
		{
			$this->_error($q2->getMsg());
		}
		unset($q);
		unset($q2);
		return $creation;
	}

}
?>
