<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

require_once 'Object.php';
require_once('Text/Password.php');
require_once('Badnetadm/Login/Login.inc');


class Orequest extends Object
{

	/**
	 * Constructeur
	 */
	public function __construct()
	{
	
	}
	
	/**
	 * Renvoi  le lien pour une requete dans un email
	 *
	 * @param int $aUserId  Id de l'utilisateur qui se connectera
	 * @param string $aArgs  argument de l'url de la forme : a=x&b=y&c=..
	 * @param int $aAction   Action a affectuer a la connection
	 */
	public function getLink($aUserId, $aArgs, $aAction)
	{
		$q = new Bn_query('request', '_asso');
		$u = new Text_Password();
		$code = $u->create();
		$q->setValue('requ_code', md5($code));
		$q->addValue('requ_args', $aArgs);
		$q->addValue('requ_userid', $aUserId);
		$q->addRow();
		$lnk = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?bnAction=" . LOGIN_REQUEST . '&c=' . md5($code);
		$lnk .= '&a=' . $aAction;
		return $lnk;
	}
	
}