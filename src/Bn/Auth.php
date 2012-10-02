<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Auth/Auth.php';

/**
 * Fonction appelee apres la connexion
 */
function connectedUser($aLogin, $a)
{
	// Trace de la connexion et mise a jour du compteur pour l'utilisateur
    Bn::log('Connexion de ' . $aLogin, 'Cnx');
	$q = new Bn_query('users');
	$q->addWhere("user_login='" . $aLogin . "'");
	$q->addField('user_nbcnx');
	$nbcnx = $q->getFirst();
	$q->addValue('user_lastvisit', date('Y-m-d H:i:s'));
	$q->addValue('user_nbcnx', $nbcnx+1);
	$q->updateRow(); 
	unset($q);
}


/**
 * Gestion de controle des connexions et autorisation d'acces
 */
class BN_Auth
{
	var $_auth   = null;

	/**
	* Verifie la connexion
	*/
	function check($aLogin='login', $aPwd='pwd', $aCrypt = true)
	{
		//$db = Bn_DB::getDb();print_r($db);
		$dsn    = Bn_DB::getDsn(); // . '/' . Bn_Db::getName();
		$prefix = Bn_DB::getPrefix();
		$auth   = Bn::getAuth();

		// The user already login and don't logout
		$fields = explode(',', $auth['fields']);
		$options = array (
            'db_login'      =>  Bn_DB::getUser(),
             'db_pwd'        =>  Bn_DB::getPwd(),
			'dsn' => $dsn,
			'table' => $prefix . $auth['table'],
			'usernamecol'  => $auth['logincol'],
			'passwordcol'  => $auth['pwdcol'],
			'postUsername' => $aLogin,
			'postPassword' => $aPwd,
			'db_fields'    => $fields);

		if ($aCrypt) $options['cryptType'] = $auth['crypt'];
		else $options['cryptType'] = '';
		$this->_auth = new Auth("PDO", $options, '', false);
		//$this->_auth->setLoginCallback('connectedUser');
		$this->_auth->start();
		if ($this->_auth->checkAuth())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Enregistre une variable de session
	* @param string $aName  nom de la variable
	* @param string $aValue contenu de la variable
	* @return void
	*/
	function setValue($aName, $aValue)
	{
		$this->_auth->setAuthData($aName, $aValue);
	}
	
	/**
	* Retourne la valeur d'une variable de session
	* @param string $aName    nom de la variable
	* @param string $aDefault valeur par defaut
	* @return string Valeur de la variable
	*/
	function getValue($aName, $aDefault)
	{
		$value = $this->_auth->getAuthData($aName);
		if ( is_null($value) )
		{
			return $aDefault;
		}
		return $value;

	}
	
	/**
	* Deconnexion
	*
	*/
	function logout()
	{
		$this->_auth->logout();
	}

}
?>