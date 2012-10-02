<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

include_once  "Badnetadm/Events/Events.inc";
@include_once  "Badnetclub/Cinline/Cinline.inc";

class Badnetadm
{
	public function __construct()
	{
	
		$controller = Bn::getController();
		// Declaration des modules
		$controller->addModule('Login',   BNETADM_LOGIN,      BNETADM_USERS);
		$controller->addModule('Users',   BNETADM_USERS,      BNETADM_INLINE);
		$controller->addModule('Inline',  BNETADM_INLINE,     BNETADM_PLAYERS);
		$controller->addModule('Players', BNETADM_PLAYERS,    BNETADM_ASSOCS);
		$controller->addModule('Assocs',  BNETADM_ASSOCS,     BNETADM_MY_ACCOUNT);
		$controller->addModule('User',    BNETADM_MY_ACCOUNT, BNETADM_EVENTS);
		$controller->addModule('Events',  BNETADM_EVENTS,     BNETADM_PARAMS);
		$controller->addModule('Params',  BNETADM_PARAMS,     BNETADM_STATS);
		$controller->addModule('Stats',   BNETADM_STATS,      BNETADM_END);
		

		// Ajout des actions globales (ie accessible depuis toutes les pages)
		$controller->addAction(BNETADM_LOGOUT, $this, 'logout'); // Deconnexion
		$controller->addAction(BNETADM_TO_LOGIN, $this, 'tologin'); // Retour a l'utilisateur connecte
		$controller->addAction(BNETADM_DISPATCH, $this, 'dispatch'); // Choix de la page a afficher
		
		// Configuration de l'authentification
		$controller->setDefaultAuth(BNETADM_LOGIN);
		$controller->setBypassAuth(BNETADM_LOGIN, BNETADM_USERS);
		$controller->setLoginAction(BNETADM_LOG, $this, 'login');
		$controller->setLogoutAction(BNETADM_LOGOUT, $this, 'logout', Bn::getValue('loginName'));
		$controller->setBeforeLoginAction(BNETADM_BEFORE_LOGIN, $this, 'beforeLogin');
	}
	
		/**
	 * Avant connection
	 *
	 * @return integer
	 */
	public function beforeLogin()
	{
		
		// @todo : provisoire a supprimer 
		// Verification que l'utilisateur connecte n'est pas celui de demo
		// qui est utilise dans la version 2 de BadNet pour le site visiteur
		if ( !empty($_SESSION['_authsession']['username']) &&
		      $_SESSION['_authsession']['username'] == 'demo')
		{
		    Bn::log('beforeLogin : depuis demo');
			require_once 'Bn/Auth.php';
			$auth = new Bn_Auth();
			$auth->check('txtLogin', 'txtPwd');
			$auth->logout();
		    Bn::log('beforeLogin : fin deconnexion demo');
		}
		return;
	}
	
	/**
	 * Connexion de badnet
	 *
	 * @return integer
	 */
	public function login()
	{
		$userName = Bn::getValue('user_name');
		$login = $_POST['txtLogin'];
		
		// Trace de la connexion et mise a jour du compteur pour l'utilisateur
		Bn::log('Connexion de ' . $userName, 'Cnx');
		$q = new Bn_query('users');
		$q->addWhere("user_login='" . $login . "'");
		$q->addField('user_nbcnx');
		$nbcnx = $q->getFirst();
		$q->addValue('user_lastvisit', date('Y-m-d H:i:s'));
		$q->addValue('user_nbcnx', $nbcnx+1);
		$q->updateRow();
		unset($q);
		
		// Memoriser les infos du compte connecte
		Bn::setValue('loginId',   Bn::getValue('user_id') );
		Bn::setValue('loginAuth', Bn::getValue('user_type') );
		Bn::setValue('loginName', Bn::getValue('user_name') );
		Bn::setValue('loginEmail', Bn::getValue('user_email') );
		Bn::setValue('theme',     'Badnet' );
		Bn::setValue('locale',    Bn::getValue('user_lang'), 'Fr'  );
		Bn::setValue('pwd', Bn::getValue('txtPwd') );
		
		if ( $login == 'demo' ) return LOGIN_PAGE_BAD_LOGIN;
				
		//@todo controler si l'utilisateur viens de se connecter demo depuis le site visiteur
		// Pour compatibilite avec l'ancien BadNet et Badnet30 a supprimer
		$this->setSessionVar('userAuth', Bn::getValue('user_type'));
		$this->setSessionVar('type',     Bn::getValue('user_type'));
		$this->setSessionVar('userId',   Bn::getValue('user_id'));
		$this->setSessionVar('theme',    0);
		$this->setSessionVar('themeId', -1);
		return BNETADM_DISPATCH;		
	}

	/**
	 * Choix de la page a afficher pour un utilisateur connecte
	 *
	 */
	public function dispatch()
	{
		// Page a afficher apres le login
		// @todo charger la page prefere de l'utilisateur ou la derniere page affichee.. a voir
		// @todo revoir en fonction des nouveaux types d'utilisateur : fede, ligue, codep asso, club, ja, joueur, autre
		$action = BNETADM_LOGIN;
		// Joueur : page du joueur
		if ( Oaccount::isUserPlayer() ) $action = PROJECT_BADNETPLAYER;
		// Club prive (squash) : page du championnat NC		
		else if ( Oaccount::isUserPrivate() )
		{
			include_once 'Squashrank/Squashrank.inc';
			$action = SQUASHRANK_CLUB;
		}
		else //if ( Oaccount::isUserClub() )
		{
			// Page demande en ligne de commande
			$action = Bn::getValue('logPage', -1);
			
			// Sinon page parametree dans le fichier de conf
			if ($action == -1) $action  = Bn::getConfigValue('loginpage', 'params');
			
			if ($action == -1)
			{
				// Si un tournoi est selectionne : page d'inscrption en ligne du tournoi
				if (Bn::getValue('eventId', -1) > 0) $action = CINLINE_DISP_PLAYERS;
				// Sinon page du club
				else $action = PROJECT_BADNETCLUB;
			}
		}/*
		else
		{
			$action = Bn::getValue('logPage', PROJECT_BADNETUSER);
		}*/
		return $action;
	}
	

	/**
	 * Deconnexion de badnet
	 *
	 * @return integer
	 */
	public function logout($aLoginName)
	{
		//Bn::log('Déconnexion de ' . $userName, 'Cnx');
		Bn::log('Déconnexion de ' . $aLoginName, 'Cnx');
		return Bn::getConfigValue('accueil', 'params');
	}

	/**
	 * Retour a l'utilisateur effectivement connnecte
	 * Quand on est administrateur, on a la possibilite de
	 * permuter sur le compte de n'importe quel autre utilisateur pour
	 * verifier ce qu'il a comme information a sa disposition
	 * Ici on revient au compte de l'utilisateur affectivement connecte
	 *
	 * @return integer page a afficher
	 */
	public function tologin()
	{
		Bn::setValue('user_id',   Bn::getValue('loginId') );
		Bn::setValue('user_type', Bn::getValue('loginAuth') );
		Bn::setValue('user_name', Bn::getValue('loginName') );
		Bn::setValue('user_email', Bn::getValue('loginEmail') );
		return BNETADM_EVENTS;
	}

	
		// @todo Pour compatibilite A supprimer
	function setSessionVar($attribut, $value)
	{
		$_SESSION['wbs'][$attribut] = $value;
	}

	static function isBadnet()
	{
		return is_dir('Badnetadm/Adhe');
	}
	
}

/*

$message = null;
$action = Bn::getValue('bnAction', null);

// Determination du module appele
$iModule = ($action & 0xFFFF00);
Bn::trace($iModule, 'Module');

if ($action == BNET_LOGOUT)
{
require_once 'Bn/Auth.php';
$auth = new Bn_Auth();
$auth->check('txtLogin', 'txtPassword');
$auth->logout();
}
if ($action == BNET_TO_LOGIN)
{
Bn::setValue('user_id',   Bn::getValue('loginId') );
Bn::setValue('user_type', Bn::getValue('loginAuth') );
Bn::setValue('user_name', Bn::getValue('loginName') );
Bn::setValue('user_email', Bn::getValue('loginEmail') );
}


// Si l'authentification est necessaire
if ( Bn::getconfigValue('auth', 'Badnetadm') && $iModule != BNET_ACCUEIL)
{
require_once 'Bn/Auth.php';
$auth = new Bn_Auth();
if ( !$auth->check('txtLogin', 'txtPassword') )
{
Bn::trace('Echec login', 'Badnetadm');
$action = 0;
$iModule = BNET_ACCUEIL;
}
else
{
if (Bn::getValue('isLogin') == false)
{
Bn::setValue('isLogin',   true);
Bn::setValue('loginId',   Bn::getValue('user_id') );
Bn::setValue('loginAuth', Bn::getValue('user_type') );
Bn::setValue('loginName', Bn::getValue('user_name') );
Bn::setValue('loginEmail', Bn::getValue('user_email') );
Bn::setValue('theme',     'Badnet' );
Bn::setValue('locale',    Bn::getValue('user_lang')  );
}
}
}

// Determination du module appele
$sModule = empty($sModules[$iModule]) ? reset($sModules) : $sModules[$iModule];

// Verification de l'existence du module demande
$file = 'Badnetadm/' . $sModule . '/' . $sModule . '.php';
if (!file_exists($file ))
{
echo "pas bon $file";
exit();
}

// C'est parti : appel du module demande
Bn::trace($file, "Module");
require_once "$file";
$page = new $sModule();
$page->start($action);
exit();
*/
?>
