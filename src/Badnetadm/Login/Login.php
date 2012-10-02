<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Badnetadm/Events/Events.inc';

require_once 'Accueil/Main/Main.inc';
require_once 'Login.inc';


/**
 * Gestion des demandes d'adhesions
 */
class login
{
	// {{{ properties
	/**
	* Constructeur: initialisation du controller
	*
	*/
	public function __construct()
	{
		$controller = Bn::getController();
		$controller->addAction(LOGIN_FILL_PASSWORD,   $this, 'fillPassword');
		$controller->addAction(LOGIN_SEND_PASSWORD,   $this, 'sendPassword');
		$controller->addAction(LOGIN_SENDED_PASSWORD, $this, 'sendedPassword');
		$controller->addAction(LOGIN_PAGE_ACCOUNT,    $this, 'pageAccount');
		$controller->addAction(LOGIN_SELECT_ACCOUNT,  $this, 'selectAccount');
		$controller->addAction(LOGIN_PAGE_ACCOUNT_PLAYER,    $this, 'pageAccountPlayer');
		$controller->addAction(LOGIN_ASK_ACCOUNT,     $this, 'askAccount');
		$controller->addAction(LOGIN_PAGE_ACCEPT,     $this, 'pageAccept');
		$controller->addAction(LOGIN_CONFIRM_ACCOUNT, $this, 'confirmAccount');
		$controller->addAction(LOGIN_PAGE_BAD_LOGIN,  $this, 'pageBadlogin');
		$controller->addAction(LOGIN_REQUEST,         $this, 'request');
	}

	/**
	 * Fonction appelé lors de la connexion a partir d'un lien dans un email
	 * Le lien contient une reference codee (c) a un enregistrement de la table asso_request
	 * dans l'enregistrement, on retrouve le user_id de l'utilisateur qui sert a se connecter
	 * L'action a accomplir apres connection est passe deans le lien (a)
	 *
	 */
	public function request()
	{
		// Supprimer les requetes de plus de trois jours
		$q = new Bn_query('request', '_asso');
		$now = date('U');
		$now -= 3 * 24 * 3600;
		$q->setWhere("requ_cre < '" . date('Y-m-d h:n:s', $now) . "'");
		$q->deleteRow();

		// Recherche du code
		$code = Bn::getValue('c');
		$q->setFields('requ_userid, requ_args');
		$q->setWhere("requ_code='" . $code . "'");
		$req = $q->getRow();
		$userId = $req['requ_userid'];
		if ( empty($userId) )
		{
			Bn::log('-----------Requete non trouvée ----------');
			Bn::log("-----------code=$code----------");
			Bn::log($q);
			Bn::log($q->getQuery());
			return Bn::getConfigValue('accueil', 'params');
		}

		// Arguments
		$str = $req['requ_args'];
		$vals = explode('&', $str);
		foreach($vals as $val)
		{
			$token = explode('=', $val);
			$_POST[$token[0]] = $token[1];
		}

		// Connection de utilisateur
		$oUser = new Oaccount($userId);
		$_POST['txtLogin'] = $oUser->getVal('login');
		$_POST['txtPwd'] = $oUser->getVal('pass');
		$_GET['logPage'] = Bn::getValue('a');
		$auth = new BN_Auth();
		$auth->check();
		$auth->logout();
		if ( $auth->check('txtLogin', 'txtPwd', false))
		{
			$userId = Bn::getValue('user_id');
			if ( empty($userId) )
			{
				Bn::log('-----------Requete: check ok; userId vide !!! ----------');
				Bn::log($oUser);
				Bn::log($auth);
				$mailer = BN::getMailer();
				$mailer->subject('Erreur request');
				$expediteur = 'no-reply@badnet.org';
				$mailer->from($expediteur);
				$mailer->ReplyTo($expediteur);
				$body = "Requete : $code";
				$mailer->body($body);
				$mailer->send('cage@badnet.org', false);
				return Bn::getConfigValue('accueil', 'params');
			}
			else
			{
				$q->deleteRow();
				return BNETADM_LOG;
			}
		}
		else
		{
			Bn::log('-----------Requete: pb connexion ----------');
			Bn::log($oUser);
			Bn::log($_POST);
			return Bn::getConfigValue('accueil', 'params');
		}
	}

	/**
	 * Page pour saisie de l'email pour une demande de mot de passe
	 */
	public function fillPassword()
	{
		// Preparer les champs de saisie
		$body = new Body();
		$form = $body->addForm('frmPwd', LOGIN_SEND_PASSWORD, 'targetDlg');

		$msg = Bn::getUserMsg();
		if ( !empty($msg) )
		{
			$form->addP('', $msg, 'bn-error');
		}
		$form->addP('', LOC_P_ASK_PWD);
		$form->addEditEmail('txtEmail', LOC_LABEL_EMAIL, null, 50);
		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancelpwd', LOC_BTN_CANCEL);
		$d->addButtonValid('btnValidPwd', LOC_BTN_SEND);

		// Envoi des donnees
		$body->display();
		return false;
	}

	/**
	 * Envoi d'un nouveau mot de passe
	 */
	public function sendPassword()
	{
		// Controle email
		require_once 'Object/Oaccount.php';
		if (empty($_POST['txtEmail']) )
		{
			Bn::setUserMsg("L'email est obligatoire");
			return LOGIN_FILL_PASSWORD;
		}
		
		$email = $_POST['txtEmail'];
		$account = new OAccount();
		$rows  = $account->checkEmail($email, null);
		foreach($rows as $row) $logins[] = $row['user_login'];
		if ( empty($logins) )
		{
			Bn::setUserMsg(LOC_MSG_BAD_EMAIL);
			return LOGIN_FILL_PASSWORD;
		}

		// Creation du nouveau mot de passe
		require_once "Text/Password.php";
		$pwd = Text_Password::create();

		// Preparation email de notification
		$locale = Bn::getLocale();
		require_once "Badnetadm/Locale/$locale/Login.inc";
		$mailer = BN::getMailer();
		$mailer->subject(LOC_EMAIL_OBJECT_PWD);
		$mailer->from("no-reply@badnet.org");
		$mailer->bcc(Bn::getConfigValue('mailadmin', 'email'));
		$msg = new Bn_balise('', '', LOC_EMAIL_BODY);
		$msg->addMetaContent(implode(', ', $logins));
		$msg->addMetaContent($pwd);

		$mailer->body($msg->toHtml());
		// Envoi email de notification
		$ret =  $mailer->send($email, false);
		$action = LOGIN_FILL_PASSWORD;
		if ($mailer->isError($res))
		{
			Bn::setUserMsg($mailer->getMsg());
		}
		else
		{
			// Modification du mot de passse
			$account->newPassword($email, $pwd);
			if ( $account->isError() )
			{
				Bn::setUserMsg( $account->getMsg() );
			}
			else
			{
				$action = LOGIN_SENDED_PASSWORD;
			}
		}
		return $action;
	}

	/**
	 * Nouveau mot de passe envoye
	 */
	public function sendedPassword()
	{
		// Preparer les champs de saisie
		$body = new Body();
		$body->addTitle('', LOC_TITLE_PASSWORD, 4);
		$body->addP('', LOC_MSG_SEND_PWD, 'bn-error');
		$body->addButtonCancel('btnCancelpwd', LOC_BTN_CLOSE);
		// Envoi des donnees
		$body->display();
		return false;
	}

	/**
	 * Page pour la creation de compte : etape 1 choix du type de compte
	 */
	public function pageAccount()
	{
		$body = new Body();
		$image = Bn::getCaptchaImage();

		// Afficher le message si necessaire
		$msg = Bn::getUserMsg();
		if ( !empty($msg) )
		{
			$body->addP('', $msg, 'bn-error');
		}
		
		// Titre
		$t = $body->addP('tltAcc', LOC_TITLE_NEW_ACCOUNT, 'bn-title-1');
		$t->addBalise('span', '',  LOC_TITLE_ACCOUNT_TYPE);

		// Indicateur d'etape
		Body::addLgdStep($body, 3, 1, LOC_TITLE_ACCOUNT2);
		
		$p = $body->addP('', LOC_P_ACCOUNT, 'bn-p-info');

		$form = $body->addForm('frmAccount', LOGIN_SELECT_ACCOUNT, 'targetBody');

		// Type de privilege
		$d = $form->addDiv('', 'bn-div-clear');
		$type = Bn::getValue('rdoType', AUTH_USER);
		$radio = $d->addRadio('rdoType', 'rdoPlayer', LOC_LABEL_PLAYER, AUTH_PLAYER, $type==AUTH_PLAYER);
		$radio->getLabel()->addBalise('span', 'pPlayer',  LOC_LABEL_PLAYER_DESC, 'bn-p-info');
		$radio = $d->addRadio('rdoType', 'rdoClub',   LOC_LABEL_CLUB, AUTH_CLUB, $type==AUTH_USER);
		$radio->getLabel()->addBalise('span', 'pClub',  LOC_LABEL_CLUB_DESC, 'bn-p-info');
		$radio = $d->addRadio('rdoType', 'rdoOther',  LOC_LABEL_OTHER, AUTH_USER, $type==AUTH_USER);
		$radio->getLabel()->addBalise('span', 'pOther',  LOC_LABEL_OTHER_DESC, 'bn-p-info');
		
		// Bouttons
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnBack', LOC_BTN_CANCEL, Bn::getConfigValue('accueil', 'params'), 'targetBody');
		$d->addButtonValid('btnValid', LOC_BTN_NEXT);
		$body->display();
		return false;
	}

	/**
	 * Ventile vers la bonne page suivant le type de compte a creer
	 *
	 */
	public function selectAccount()
	{
		$type = Bn::getValue('rdoType', AUTH_USER);
		if ($type == AUTH_PLAYER) return $this->_pagePlayer();
		else return $this->_pageAccountStandart();
	}
	
	/**
	 * Page pour la creation de compte standart
	 */
	private function _pageAccountStandart()
	{
		$msg = Bn::getUserMsg();
		$body = new Body();
		$image = Bn::getCaptchaImage();
		
		// Titre
		$t = $body->addP('tltAcc', LOC_TITLE_NEW_ACCOUNT, 'bn-title-1');
		$t->addBalise('span', '',  LOC_TITLE_SAISIE);

		// Indicateur d'etape
		Body::addLgdStep($body, 3, 2, LOC_TITLE_ACCOUNT_OTHER);
		
		$body->addP('', LOC_P_ACCOUNT_OTHER, 'bn-p-info');
		
		$form = $body->addForm('frmAccount', LOGIN_ASK_ACCOUNT, 'targetBody');
		$form->addHidden('back', LOGIN_SELECT_ACCOUNT);
		$form->addHidden('captcha', basename($image));
		$form->addHidden('locale', Bn::getLocale());
		$form->addHidden('rdoType', AUTH_USER);
		
		if ( !empty($msg) ) $form->addError($msg);
		$dl = $form->addRichDiv('', 'bn-div-left');
		$dr = $form->addRichDiv('', 'bn-div-right');
		$div = $dl->addRichDiv('divNewAccount');
		$t = $div->addP('', '', 'bn-title-3');
		$t->addBalise('span', '',  LOC_TITLE_ACCOUNT);

		$div2 = $div->addDiv('divName');
		$div2->addEdit('txtName',  LOC_LABEL_NAME, null, 50);
		$edit = $div2->addEditEmail('txtMail',    LOC_LABEL_EMAIL, null, 50);
		$div2->addEdit('txtLogin',    LOC_LABEL_LOGIN, null, 20);

		$div3 = $div->addDiv('divPseudo');
		$div2->addEdit('txtPseudo', LOC_LABEL_PSEUDO, null, 20);
		$div2->addEditPwd('txtPassword', LOC_LABEL_PWD, 15);
		$div2->addEditPwd('txtConfirm', LOC_LABEL_CONFIRM, 15, "txtPassword");
		$div->addP('pPseudo', LOC_P_PSEUDO, 'bn-p-info');

		$div = $dr->addRichDiv('divCaptcha');
		$t = $div->addP('', '', 'bn-title-3');
		$t->addBalise('span', '',  LOC_TITLE_CAPTCHA);

		$div->addP('pMandatory', LOC_P_CAPTCHA, 'bn-p-info');
		$div->addImage('imgCaptcha', "../Temp/Tmp/".basename($image), Bn::getCaptcha());
		$div->addEdit('txtCaptcha', LOC_LABEL_CAPTCHA, '', 8);
		$div->addP('pSend', LOC_P_EMAIL, 'bn-p-info');

		// Bouttons
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnBack', LOC_BTN_CANCEL, Bn::getConfigValue('accueil', 'params'), 'targetBody');
		$d->addButtonValid('btnValid', LOC_BTN_SEND);
		$body->display();
		return false;
	}
	
	/**
	 * Affichage de la page de saisie du numero de licence et date denaissance pour un compte joueur
	 *
	 */
	private function _pagePlayer()
	{
		$msg = Bn::getUserMsg();
		$body = new Body();
		$image = Bn::getCaptchaImage();
		
		// Titre
		$t = $body->addP('tltAcc', LOC_TITLE_NEW_ACCOUNT, 'bn-title-1');
		$t->addBalise('span', '',  LOC_TITLE_ACCOUNT_PLAYER);

		// Indicateur d'etape
		Body::addLgdStep($body, 3, 2, LOC_TITLE_ACCOUNT_LICENSE);
		
		$body->addP('', LOC_P_ACCOUNT_PLAYER, 'bn-p-info');
		
		$form = $body->addForm('frmAccount', LOGIN_PAGE_ACCOUNT_PLAYER, 'targetBody');
		$form->addHidden('rdoType', AUTH_PLAYER);
		
		$d = $form->addRichDiv();
		if ( !empty($msg) ) $d->addError($msg);
		$d->addEdit('license',  LOC_LABEL_LICENSE, null, 50);
		$d->addEditDate('born',    LOC_LABEL_BORN, Bn::date(bn::getValue('born'), 'd-m-Y'));

		// Bouttons
		$d = $form->addDiv('', 'bn-div-btn');
		$btn = $d->addButtonCancel('btnBack', LOC_BTN_CANCEL, Bn::getConfigValue('accueil', 'params'), 'targetBody');
		$d->addButtonValid('btnValid', LOC_BTN_NEXT);
		$body->display();
		return false;
		
	}
	
		/**
	 * Affichage de la page de saisie de compte joueur
	 */
	public function pageAccountPlayer()
	{
		$msg = Bn::getUserMsg();
		$license = Bn::getValue('license');
		$oExt = Oexternal::factory();
		$member = $oExt->getMember($license);
				
		// Verification de l'existence du joueur
		if (empty($member)) 
		{
			Bn::setUserMsg(LOC_MSG_UNKNOW_PLAYER);
			return 	LOGIN_SELECT_ACCOUNT;
		}
		
		// Verification des informations licence et date de naissance
		$born = Bn::getValue('born');		 
		if ($member['born'] != $born) 
		{
			Bn::setUserMsg(LOC_MSG_BAD_BORN);
			return 	LOGIN_SELECT_ACCOUNT;
		}
		
		// Verification que le compte n'existe pas
		$check = ltrim(trim($license), '0');
		if (Oaccount::checkLogin($check))
		{
			Bn::setUserMsg(LOC_MSG_ACCOUNT_EXIST);
			return 	LOGIN_SELECT_ACCOUNT;
		}
		
		// Creation de la page
		$body = new Body();

		$t = $body->addP('', LOC_TITLE_NEW_ACCOUNT, 'bn-title-1');
		$t->addBalise('span', '',  LOC_TITLE_SAISIE);

		// Indicateur d'etape
		Body::addLgdStep($body, 3, 3, LOC_TITLE_ACCOUNT_PLAYER);
		
		// Message d'information
		$body->addP('', LOC_P_ACCOUNT_INFOS, 'bn-p-info');

		$form = $body->addForm('frmAccount', LOGIN_ASK_ACCOUNT, 'targetBody');
		//$form->addHidden('captcha', basename($image));
		$form->addHidden('back', LOGIN_PAGE_ACCOUNT_PLAYER);
		$form->addHidden('locale', Bn::getLocale());
		$form->addHidden('rdoType', AUTH_PLAYER);
		$form->addHidden('txtName', $member['familyname'] . ' ' . $member['firstname']);
		$form->addHidden('txtLogin', $license);
		$form->addHidden('license', $license);
		$form->addHidden('born', $born);
		$form->addHidden('txtPseudo', $member['firstname']);
		
		if ( !empty($msg) ) $form->addError($msg);
		$d = $form->addRichDiv('', 'bn-div-left');
		$d->SetAttribute('style', 'width:380px;');
		$t = $d->addP('', '', 'bn-title-3');
		$t->addBalise('span', '', LOC_TITLE_ACCOUNT);
		$d->addEditEmail('txtMail', LOC_LABEL_EMAIL, null, 250);
		$d->addEditPwd('txtPassword', LOC_LABEL_PWD, 25);
		$d->addEditPwd('txtConfirm', LOC_LABEL_CONFIRM,  25, 'txtPassword');
		
		$d = $form->addRichDiv('', 'bn-div-left-last');
		$d->SetAttribute('style', 'width:380px;');
		$t = $d->addP('', '', 'bn-title-3');
		$t->addBalise('span', '',  LOC_TITLE_CAPTCHA);
		$image = Bn::getCaptchaImage();
		$d->addP('', LOC_P_CAPTCHA, 'bn-p-info');
		$d->addImage('imgCaptcha', "../Temp/Tmp/".basename($image), Bn::getCaptcha());
		$d->addEdit('txtCaptcha', LOC_LABEL_CAPTCHA, '', 8);
		
		// Bouttons
		$d = $form->addDiv('', 'bn-div-btn');
		$d->addButtonCancel('btnCancel', LOC_BTN_CANCEL, Bn::getConfigValue('accueil', 'params'), 'targetBody');
		$d->addButtonValid('btnValid', LOC_BTN_VALID);
		$body->addBreak();
		$body->display();
		return false;
	}

	/**
	 * Gestion de la demande de compte
	 */
	public function askAccount()
	{
		$back = Bn::getValue('back', LOGIN_SELECT_ACCOUNT);
		// Controle du code
		$code  = Bn::getCaptcha();
		if ( empty($code) || (BN::getValue('txtCaptcha', 'none') != $code ) )
		{
			Bn::setUserMsg(LOC_MSG_BAD_CODE);
			return $back;
		}

		// Controle mot de passe
		$password = BN::getValue('txtPassword');
		if ( empty($password) || $password != BN::getValue('txtConfirm') )
		{
			Bn::setUserMsg(LOC_MSG_BAD_DIFFPWD);
			return $back;
		}

		// Controle du login
		$login = BN::getValue('txtLogin');
		$pos = strpos($login, ' ');
		if ( $pos !== false)
		{
			Bn::setUserMsg(LOC_MSG_LOGIN_NOT_BLANK);
			return $back;
		}

		if ( $login == $password )
		{
			Bn::setUserMsg(LOC_MSG_LOGIN_NOT_PWD);
			return $back;
		}
		if ( Oaccount::checkLogin($login) )
		{
			Bn::setUserMsg(LOC_MSG_LOGIN_ALREADY_USED);
			return $back;
		}

		// Enregistrement de la demande
		$email = BN::getValue('txtMail');
		$code  = Bn::getCaptcha();
		$user['code']   = md5($code);
		$user['name']   = BN::getValue('txtName');
		$user['login']  = $login;
		$user['lang']   = BN::getValue('cboLocale', 'Fr');
		$user['email']  = $email;
		$user['pseudo'] = BN::getValue('txtPseudo');
		$user['password'] = $password;
		$user['type'] = BN::getValue('rdoType');;
		$oUser = new Oaccount();
		$oUser->newAccount($user);
		if ( $oUser->isError() )
		{
			Bn::setUserMsg($oUser->getMsg());
			return $back;
		}

		// Envoi du mail de notification
		$locale = Bn::getLocale();
		require_once "Badnetadm/Locale/$locale/Login.inc";
		$mailer = BN::getMailer();
		$mailer->subject(LOC_OBJECT_DEMANDE);
		$mailer->from("no-reply@badnet.org");
		$mailer->bcc(Bn::getConfigValue('mailadmin', 'email'));
		$url = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?bnAction=" . LOGIN_CONFIRM_ACCOUNT;
		$eventId = BN::getValue('eventId', -1);
		if ($eventId > 0)
		{
			$url .= "&eventId=" . $eventId;
		}
		$url .= '&codecaptcha='.md5($code);
		$url .= '&locale=' . $user['lang'];
		$msg = new Bn_balise('', '', LOC_BODY_DEMANDE);
		$msg->addMetaContent($url);

		$mailer->body($msg->toHtml());
		$mailer->send($email);
		if ($mailer->isError())
		{
			Bn::setUserMsg(LOC_MSG_ERR_EMAIL_ACCOUNT);
			return $back;
		}
		return LOGIN_PAGE_ACCEPT;
	}

	/**
	 * Page pour l'acceptation de la demande de creation de compte
	 */
	public function pageAccept()
	{
		$body = new Body();

		$t = $body->addP('tltAcc', LOC_TITLE_NEW_ACCOUNT, 'bn-title-1');
		$t->addBalise('span', '',  LOC_TITLE_SAISIE);
			
		$body->addP('', bn::getUserMsg(), 'bn-error');

		$body->addWarning(LOC_MSG_AQUIT_DEMANDE);
		$body->addLink('lnkBack', Bn::getConfigValue('accueil', 'params'), LOC_LNK_BACK_HOME, 'targetBody');

		$body->display();
		return false;
	}

	/**
	 * Confirmation de creation d'un compte
	 */
	public function confirmAccount()
	{
		$eventId = BN::getValue('event', -1);
		$code    = BN::getValue('codecaptcha', 'none');
		$locale  = Bn::getLocale();

		$account = new Oaccount();
		$res = $account->confirmAccount($code);
		if ($account->isError())
		{
			Bn::setUserMsg($account->getMsg());
			return LOGIN_PAGE_ACCOUNT;
		}

		$_POST['txtLogin'] = $res['crea_login'];
		$_POST['txtPwd'] = $res['crea_password'];
		$auth = new BN_Auth();
		$auth->check('txtLogin', 'txtPwd');

		// Envoi d'un email de confirmation
		$locale = Bn::getLocale();
		require_once "Badnetadm/Locale/$locale/Login.inc";
		$mailer = BN::getMailer();
		$mailer->subject(LOC_MSG_ACCOUNT_CONFIRM);
		$mailer->from("no-reply@badnet.org");
		$mailer->bcc(Bn::getConfigValue('mailadmin', 'email'));
		$msg = new Bn_balise('', '', LOC_MSG_ACCOUNT_VALID);
		$msg->addMetaContent($res['crea_login']);
		$msg->addMetaContent($res['crea_password']);
		$mailer->body($msg->toHtml());
		$mailer->send($res['crea_email']);

		// Redirection après le login
		return BNETADM_DISPATCH;
	}

	/**
	 * Page pour l'erreur de login
	 */
	public function pageBadlogin()
	{
		$body = new Body();
		$t = $body->addP('tltTrn', LOC_TITLE_LOGIN, 'bn-title-1');
		$t->addBalise('span', '',  LOC_TITLE_BADLOGIN);

		//--- Bloc de connexion ---
		$div = $body->addRichDiv('divLogin'); //, 'bloc');
		$form = $div->addForm('frmLogin', BNETADM_LOG, 'targetBody');

		$form->addP('', LOC_MSG_ERRORLOGIN, 'bn-error');
		$form->addEdit('txtLogin',  LOC_LABEL_LOGIN, null, 20);
		$form->addEditPwd('txtPwd', LOC_LABEL_PWD, 20);

		$block = $form->addDiv('', '');
		$action = BN::getValue('bnActiontr', BNETADM_LOG);
		$block->addButtonCancel('btnBack', LOC_BTN_CANCEL, Bn::getConfigValue('accueil', 'params'), 'targetBody');
		$block->addButtonValid('btnLogin', LOC_BTN_CONNECT);
		
		$lnk = $form->addLink('lnkPwd',  LOGIN_FILL_PASSWORD, LOC_LNK_PWD, 'targetDlg');
		$lnk->completeAttribute('class', 'bn-dlg');
		$lnk->addMetaData('title', "'". LOC_TITLE_NEW_PASSWORD . "'");
		$lnk->addMetaData('width', 355);
		$lnk->addMetaData('height', 180);
		//$form->addLink('lnkBack', Bn::getConfigValue('accueil', 'params'), LOC_LNK_BACK_HOME, 'targetBody');

		$body->display();
		return false;
	}

}
?>
