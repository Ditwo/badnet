<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

class Controller
{
	private $_projects = array();
	private $_actions = array();
	private $_defaultAction = -1;

	public function __construct()
	{
	}

	/**
	 * Execution
	 * @return 	string
	 */
	public function run()
	{
		Bn::setProject('');
		Bn::setModule('');

		$asked = Bn::getValue('bnAction', $this->_defaultAction);
		// Boucle infinie : stop quand le retour de la fonction appelé est vide
		while ( !empty($asked) )
		{
			// Traitement de l'action au niveau le plus haut : dans le controleur
			foreach ( $this->_actions as $action )
			{
				if ($asked == $action->getId() )
				{
					$res = $action->call();
				}
			}

			// Traitemement de l'action au niveau des projets
			$res = $asked;
			foreach ($this->_projects as $project)
			{
				$res = $project->run($res);
			}
			// Action non traitee: sortie forcee
			if ( $res == $asked )
			{
				Bn::trace('Action non traitée '. $asked, 'Controller');
				Bn::trace($this);
				$asked = $this->_defaultAction;
				if ( empty($asked) )
				{
					Bn::trace('Action non traitée '. $asked, 'Controller');
				}
			}
			else
			{
				$asked = $res;
			}
		}
		Bn_Db::closeDbs();
	}

	/**
	 * Ajouter un projet
	 * @param string	$aName	Nom du projet
	 * @param integer	$aMinimum	action minimale du projet
	 * @param integer	$aMaximum	action maximale du projet
	 * @return 	void
	 */
	public function addProject($aName, $aMinimum, $aMaximum)
	{
		$this->_projects[] = new Project($aName, $aMinimum, $aMaximum);
	}

	/**
	 * Ajouter un module
	 * @param string	$aName	Nom du module
	 * @param integer	$aMinimum	action minimale du module
	 * @param integer	$aMaximum	action maximale du module
	 * @return 	void
	 */
	public function addModule($aName, $aMinimum, $aMaximum)
	{
		// Tentative d'ajout du module a chaque projet.
		// Le projet decide si le module le concerne ou pas
		$nbProject = 0;
		foreach( $this->_projects as $project)
		{
			$nbProject += $project->addModule($aName, $aMinimum, $aMaximum);
		}
		// Aucun projet n'a retenu le module. C'est pas bon.
		// A reflechir : garde t on un module sans projet ?
		if (!$nbProject)
		{
			bn::trace('Pas de projet pour le module ' . $aName);
			bn::trace("min=$aMinimum:max=$aMaximum");
			exit;
		}
	}

	/**
	 * Definier l'action a activer si l'utilisateur n'est pas authtifie
	 * @param integer 	$aAction	Action a activer
	 * @return 	void
	 */
	public function setDefaultAuth($aAction)
	{
		// Tentative d'ajout de l'action a chaque projet.
		// Le projet decide si l'action le concerne ou pas
		$nbProject = 0;
		foreach( $this->_projects as $project)
		{
			$nbProject += $project->setDefaultAuth($aAction);
		}
		// Aucun projet n'a retenu le module. C'est pas bon.
		if (!$nbProject)
		{
			bn::trace('setDefaultAuth');
			bn::trace("Pas de projet pour l'action " . $aAction);
			bn::trace("min=$aMinimum;max=$aMaximum");
			exit;
		}
	}

	/**
	 * Definir la plage d'action exempte d'authentification
	 * @param integer 	$aMinimum	Valeur minimale
	 * @param integer 	$aMaximum	Valeur maximale
	 * @return 	void
	 */
	public function setBypassAuth($aMinimum, $aMaximum)
	{
		// Tentative d'ajout de la plage a chaque projet.
		// Le projet decide si la palge le concerne ou pas
		$nbProject = 0;
		foreach( $this->_projects as $project)
		{
			$nbProject += $project->setBypassAuth($aMinimum, $aMaximum);
		}
		// Aucun projet n'a retenu la plage.
		if (!$nbProject)
		{
			bn::trace('setBypassAuth');
			bn::trace("Pas de projet pour la plage " . $aMinimum . ','  . $aMaximum );
			exit;
		}
	}

	/**
	 * Definir l'action pour apres login
	 * @param integer	$aValue     Valeur numerique de l'action
	 * @param mixed		$aClasse    Classe a appeler
	 * @param string 	$aMethode	Methode a appeler
	 * @param array  	$aArgs 	    Parametre de la methode
	 * @return 	void
	 */
	public function setLoginAction($aValue, $aClasse, $aMethode, $aArgs=null)
	{
		// Tentative d'ajout de l'action a chaque projet.
		// Le projet decide si l'action le concerne ou pas
		$nbProject = 0;
		foreach( $this->_projects as $project)
		{
			$nbProject += $project->setLoginAction($aValue, $aClasse, $aMethode, $aArgs);
		}
		// Aucun projet n'a retenu l'action.
		if (!$nbProject)
		{
			Bn::trace('Pas de projet pour le login ' . $aValue, 'setLoginAction');
			Bn::trace($this);
		}
	}

	/**
	 * Definir l'action pour avant  login
	 * @param integer	$aValue     Valeur numerique de l'action
	 * @param mixed		$aClasse    Classe a appeler
	 * @param string 	$aMethode	Methode a appeler
	 * @param array  	$aArgs 	    Parametre de la methode
	 * @return 	void
	 */
	public function setBeforeLoginAction($aValue, $aClasse, $aMethode, $aArgs=null)
	{
		// Tentative d'ajout de l'action a chaque projet.
		// Le projet decide si l'action le concerne ou pas
		$nbProject = 0;
		foreach( $this->_projects as $project)
		{
			$nbProject += $project->setBeforeLoginAction($aValue, $aClasse, $aMethode, $aArgs);
		}
		// Aucun projet n'a retenu l'action.
		if (!$nbProject)
		{
			Bn::trace('Pas de projet pour beforeLogin ' . $aValue, 'setLoginAction');
			Bn::trace($this);
		}
	}

	/**
	 * Definir l'action pour le logout
	 * @param integer	$aValue     Valeur numerique de l'action
	 * @param mixed		$aClasse    Classe a appeler
	 * @param string 	$aMethode	Methode a appeler
	 * @param array  	$aArgs 	    Parametre de la methode
	 * @return 	void
	 */
	public function setLogoutAction($aValue, $aClasse, $aMethode, $aArgs=null)
	{
		// Tentative d'ajout de l'action a chaque projet.
		// Le projet decide si l'action le concerne ou pas
		$nbProject = 0;
		foreach( $this->_projects as $project)
		{
			$nbProject += $project->setLogoutAction($aValue, $aClasse, $aMethode, $aArgs);
		}
		// Aucun projet n'a retenu l'action.
		if (!$nbProject)
		{
			Bn::trace('Pas de projet pour le logout ' . $aValue, 'setLogoutAction');
			Bn::trace($this);
		}
	}

	/**
	 * Fixer l'action par defaut
	 * @param integer	$aAction    action par defaut
	 * @return 	void
	 */
	public function setDefaultAction($aAction)
	{
		$this->_defaultAction = $aAction;
	}

	/**
	 * Ajouter une action
	 * @param integer	$aValue     Valeur numerique de l'action
	 * @param mixed	$aClasse    Classe a appeler
	 * @param string 	$aMethode	Methode a appeler
	 * @param array  	$aArgs 	    Parametre de la methode
	 * @return 	void
	 */
	public function addAction($aValue, $aClass, $aMethod, $aArgs=null)
	{
		// Tentative d'ajout de l'action a chaque projet.
		// Le projet decide si l'action le concerne ou pas
		$nbProject = 0;
		foreach( $this->_projects as $project)
		{
			$nbProject += $project->addAction($aValue, $aClass, $aMethod, $aArgs);
		}
		// Aucun projet n'a retenu l'action, elle est retenu au niveau global
		if (!$nbProject)
		{
			$this->_actions[] = new action($aValue, $aClass, $aMethod, $aArgs);
		}
	}

}

class Project
{
	private $_name;
	private $_minimum;
	private $_maximum;
	private $_isInit  = false;
	private $_modules = array();
	private $_actions = array();
	private $_defaultAuth = 0;
	private $_authLogin = 'txtLogin';
	private $_authPassword = 'txtPwd';
	private $_authMin = 0;
	private $_authMax = 0;
	private $_actLogin = null;
	private $_actLogout = null;
	private $_actBeforeLogout = null;

	public function __construct($aName, $aMinimum, $aMaximum)
	{
		$this->_name = $aName;
		$this->_minimum = $aMinimum;
		$this->_maximum = $aMaximum;
		$this->_authMin = $aMinimum;
		$this->_authMax = $aMinimum;
	}

	/**
	 * Ajouter d'une action  au projet
	 * @return 	string
	 */
	public function addAction($aValue, $aClasse, $aFunction, $aArgs)
	{
		// Verification que l'action concerne le projet
		if ( $aValue < $this->_minimum || $aValue >= $this->_maximum)
		{
			return 0;
		}

		// Tentative d'ajout de l'action a chaque module
		// Le module decide si l'action le concerne ou pas
		$nbModule = 0;
		foreach($this->_modules as $module)
		{
			$nbModule += $module->addAction($aValue, $aClasse, $aFunction, $aArgs);
		}

		// Aucun module ne veut l'action, elle est globale au projet
		if ( !$nbModule )
		{
			$this->_actions[] = new Action($aValue, $aClasse, $aFunction, $aArgs);
		}
		return 1;
	}

	/**
	 * Definir l'action pour le login
	 * @param integer	$aValue     Valeur numerique de l'action
	 * @param mixed		$aClasse    Classe a appeler
	 * @param string 	$aMethode	Methode a appeler
	 * @param array  	$aArgs 	    Parametre de la methode
	 * @return 	void
	 */
	public function setLoginAction($aValue, $aClasse, $aMethode, $aArgs)
	{
		// Verification que l'action concerne le projet
		if ( $aValue < $this->_minimum || $aValue >= $this->_maximum)
		{
			return 0;
		}
		$this->_actLogin = new action($aValue, $aClasse, $aMethode, $aArgs);
		return 1;
	}

	/**
	 * Definir l'action pour avant login
	 * @param integer	$aValue     Valeur numerique de l'action
	 * @param mixed		$aClasse    Classe a appeler
	 * @param string 	$aMethode	Methode a appeler
	 * @param array  	$aArgs 	    Parametre de la methode
	 * @return 	void
	 */
	public function setBeforeLoginAction($aValue, $aClasse, $aMethode, $aArgs)
	{
		// Verification que l'action concerne le projet
		if ( $aValue < $this->_minimum || $aValue >= $this->_maximum)
		{
			return 0;
		}
		$this->_actBeforeLogin = new action($aValue, $aClasse, $aMethode, $aArgs);
		return 1;
	}

	/**
	 * Definir l'action pour le login
	 * @param integer	$aValue     Valeur numerique de l'action
	 * @param mixed		$aClasse    Classe a appeler
	 * @param string 	$aMethode	Methode a appeler
	 * @param array  	$aArgs 	    Parametre de la methode
	 * @return 	void
	 */
	public function setLogoutAction($aValue, $aClasse, $aMethode, $aArgs)
	{
		// Verification que l'action concerne le projet
		if ( $aValue < $this->_minimum || $aValue >= $this->_maximum)
		{
			return 0;
		}
		$this->_actLogout = new action($aValue, $aClasse, $aMethode, $aArgs);
		return 1;
	}

	/**
	 * Fixer l'action a activer si l'utilisateur n'est pas authentifie
	 * @return 	string
	 */
	public function setDefaultAuth($aValue)
	{
		// Verification que l'action concerne le projet
		if ( $aValue < $this->_minimum || $aValue >= $this->_maximum)
		{
			return 0;
		}

		$this->_defaultAuth = $aValue;
		return 1;
	}

	/**
	 * Definie la plage d'action exempte d'authentification
	 * @param integer 	$aMinimum	Valeur minimale
	 * @param integer 	$aMaximum	Valeur maximale
	 * @return 	void
	 */
	public function setBypassAuth($aMinimum, $aMaximum)
	{
		// Verification que l'action concerne le projet
		if ( $aMaximum < $this->_minimum || $aMinimum >= $this->_maximum)
		{
			return 0;
		}
		$this->_authMin = $aMinimum;
		$this->_authMax = $aMaximum;
		return 1;
	}

	/**
	 * Lance l'action du projet ou du module
	 * @return 	string
	 */
	public function run($aAction)
	{
		Bn::setProject($this->_name);
		$asked = $aAction;
		// Verification que l'action concerne le projet
		if ( $asked < $this->_minimum || $asked >= $this->_maximum)
		{
			return $aAction;
		}

		// Initialisation du projet
		if (! $this->_isInit )
		{
			$this->_isInit = true;
			require_once $this->_name . '/' . $this->_name . '.inc';
			require_once $this->_name . '/' . $this->_name . '.php';
			$a = new $this->_name($aAction);
		}

		// Verifier la deconnexion
		if ( !empty($this->_actLogout) &&
		$asked == $this->_actLogout->getId() )
		{
			require_once 'Bn/Auth.php';
			$auth = new Bn_Auth();
			$auth->check($this->_authLogin, $this->_authPassword);
			$auth->logout();
			return $this->_actLogout->call();
		}

		// Verifier l'authentification
		if ( Bn::getconfigValue('auth', $this->_name) &&
		($asked < $this->_authMin || $asked >= $this->_authMax) )
		{
			// Appel de l'action  beforeLogin
			if ( !empty($this->_actBeforeLogin) )
			{
				$this->_actBeforeLogin->call();
			}
			// Controle du login
			require_once 'Bn/Auth.php';
			$auth = new Bn_Auth();
			if ( !$auth->check($this->_authLogin, $this->_authPassword) )
			{
				Bn::log('erreur login : ' . Bn::getValue($this->_authLogin) . ',' . Bn::getValue($this->_authPassword));
				$asked = $this->_defaultAuth;
				if ( empty($asked) )
				{
					bn::trace($this->_name . ": echec authentification et pas d'action par défaut.");
				}
			}
			// login ok
			else
			{
				// Appel du callback d'apres login
				if ( !empty($this->_actLogin) &&
				$asked == $this->_actLogin->getId() )
				return $this->_actLogin->call();
			}
		}


		// Rechercher l'action au niveau du projet
		foreach ($this->_actions as $action)
		{
			if ( $action->getId() == $asked )
			{
				return $action->call();
			}
		}

		// Pas d'action au niveau projet, recherche dans les modules
		foreach ($this->_modules as $module)
		{
			$asked = $module->run($asked);
		}

		if ( $asked < 0 )
		{
			$str = sprintf('Projet %s : action non trouvée %d; 0x%04X <br>',  $this->_name, $aAction, $aAction );
			bn::trace($str);
		}
		return $asked;
	}


	/**
	 * Ajouter un module au projet
	 * @return 	string
	 */
	public function addModule($aName, $aMinimum, $aMaximum)
	{
		// Verification que le module concerne le projet
		if ( $aMinimum < $this->_minimum || $aMaximum >= $this->_maximum)
		{
			return 0;
		}
		$this->_modules[] = new Module($this->_name, $aName, $aMinimum, $aMaximum);
		return 1;
	}

	public function getName()
	{
		return $this->_name;
	}
}

class Module
{
	private $_project;
	private $_name;
	private $_minimum;
	private $_maximum;
	private $_isInit  = false;
	private $_actions = array();

	public function __construct($aProject, $aName, $aMinimum, $aMaximum)
	{
		$this->_project = $aProject;
		$this->_name = $aName;
		$this->_minimum = $aMinimum;
		$this->_maximum = $aMaximum;
	}

	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Ajouter une action au module
	 * @return 	string
	 */
	public function addAction($aValue, $aClass, $aFunction, $aArgs)
	{
		// verification que l'action concerne le module
		if ( $aValue < $this->_minimum || $aValue >= $this->_maximum)
		{
			return 0;
		}
		// Enregistrement de l'action
		else
		{
			$this->_actions[] = new Action($aValue, $aClass, $aFunction, $aArgs);
			return 1;
		}
	}

	/**
	 * Lance l'action du module
	 * @return 	string
	 */
	public function run($aAction)
	{
		Bn::setModule($this->_name);
		$asked = $aAction;
		// Verification que l'action concerne le module
		if ( $asked < $this->_minimum || $asked >= $this->_maximum)
		{
			return $aAction;
		}

		// Initialisation du module
		if (! $this->_isInit )
		{
			$this->_isInit = true;
			require_once $this->_project .'/' . $this->_name . '/' . $this->_name . '.inc';
			require_once $this->_project .'/' . $this->_name . '/' . $this->_name . '.php';
			$a = new $this->_name($aAction);
		}

		// Rechercher de l'action au niveau du module
		foreach ($this->_actions as $action)
		{
			if ( $action->getId() == $asked )
			{
				return $action->call();
			}
		}

		bn::trace('Module ' . $this->_name . ': action non trouvée ' . $aAction );
		return -2;
	}
}

class Action
{
	private $_id;
	private $_class;
	private $_method;
	private $_args;

	public function __construct($aId, $aClass, $aMethod, $aArgs)
	{
		$this->_id = $aId;
		$this->_class = $aClass;
		$this->_method = $aMethod;
		if ( is_array($aArgs) )
		{
			$this->_args = $aArgs;
		}
		else
		{
			$this->_args[] = $aArgs;
		}

	}
	public function getId()
	{
		return $this->_id;
	}
	public function call()
	{
		if ( method_exists($this->_class, $this->_method) )
		{
			// enregistrement de l'action pour les stats
			if (Bn::getConfigValue('stats', 'param'))
			{
				$q = new Bn_query('stats', '_stats');
				if ($q)
				{
					$q->setFields('stat_nb');
					$q->addWhere('stat_action =' . $this->_id);
					$q->addWhere("stat_day ='" . date('Y-m-d') . "'");

					$nb = $q->getFirst(); $nb++;
					$q->addValue('stat_nb', $nb);
					$q->addValue('stat_action', $this->_id);
					$q->addValue('stat_module', get_class($this->_class));
					$q->addValue('stat_function', $this->_method);
					$q->addValue('stat_day', date('Y-m-d'));
					$q->replaceRow();
					unset($q);
				}
			}
			return call_user_func_array( array($this->_class, $this->_method), $this->_args);
		}
		else
		{
			bn::trace('function inconnue ' . $this->_method );
			return -2;
		}
	}
}
?>