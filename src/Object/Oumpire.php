<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Oumpire.inc';
require_once 'Object.php';

class Oumpire extends Object
{
	private $_regiId = -1;     // Identifiant
	private $_regiUniId = -1;     // Identifiant
	private $_umpiId = -1;     // Identifiant
	private $_eventId = -1;     // Identifiant du tournoi
	private $_familyName = ''; // Nom de famille
	private $_firstName = '';  // Prenom
	private $_group = 0;  // Groupe
	private $_order = 0;  // Ordre dans le groupe
	private $_court = 0;  // Numero de terrain de l'arbitre
	private $_function = OUMPIRE_FUNCTION_REST;  // Ordre dans le groupe

	/**
	 * Constructor
	 */
	function __construct($aRegiId)
	{
		// Donnees de l'arbitre
		$q = new bn_Query('members, registration, umpire');
		$q->leftJoin('umpire', 'umpi_regiid = regi_id');
		$q->addField('mber_firstname', 'firstname');
		$q->addField('mber_secondname', 'familyname');
		$q->addField('mber_sexe', 'gender');
		$q->addField('mber_licence', 'license');
		$q->addField('regi_id');
		$q->addField('regi_uniid');
		$q->addField('regi_eventid');
		$q->addField('umpi_function');
		$q->addField('umpi_court');
		$q->addField('umpi_order');
		$q->addField('umpi_currentcourt');
		$q->addField('umpi_id');
		$q->addWhere('mber_id = regi_memberid');
		if (strpos($aRegiId, ':') !== false)
		{
			if (strpos($aRegiId, ';') !== false) $q->addWhere ("regi_uniid = '" . $aRegiId . "'");
			else $q->addWhere ("regi_uniid = '" . $aRegiId . ";'");
		}
		else $q->addWhere ('regi_id =' . $aRegiId );

		$regi = $q->getRow();
		if (!empty($regi) )
		{
			$locale = BN::getLocale();
			require_once "Object/Locale/$locale/Omember.inc";
			$regi['labgender'] = constant('SMA_LABEL_' . $regi['gender'] );
			$this->_regi = $regi;
			$this->_regiId = $regi['regi_id'];
			$this->_regiUniId = $regi['regi_uniid'];
			$this->_eventId = $regi['regi_eventid'];
			$this->_group =  $regi['umpi_court'];
			$this->_order =  $regi['umpi_order'];
			$this->_court =  $regi['umpi_currentcourt'];
			$this->_function =  $regi['umpi_function'];
			$this->_umpiId =  empty($regi['umpi_id'])?-1:$regi['umpi_id'];
		}
	}

	Public function getId() { return $this->_regiId;}
	Public function getFamilyName() { return isset($this->_regi['familyname'])?$this->_regi['familyname']:'';}
	Public function getFirstName() { return isset($this->_regi['firstName'])?$this->_regi['firstName']:'';}
	Public function getFullName() { return isset($this->_regi['familyname'])? $this->_regi['familyname'] . ' ' . $this->_regi['firstname']:'';}
	Public function getFlag() { return '';}
	Public function getOrder() { return $this->_order;}
	Public function getGroup() { return $this->_group;}
	

	/**
	 * Acces aux champs
	 * @param string $aName	nom du champ a recuperer
	 *
	 */
	function getField($aName)
	{
		if ( isset($this->_regi[$aName]) ) return $this->_regi[$aName];
		else return 'unknow Oplayer::'.$aName;
	}

	/**
	 * Rajoute l'arbitre a la fin de la liste de sa non function
	 *
	 * @return mixed
	 */
	public function push()
	{
		if ($this->_court > 0)
		{
			
			// Mettre a jour l'arbitre dans sa nouvelle fonction
			$newFunction = $this->_function == OUMPIRE_FUNCTION_UMPIRE ? OUMPIRE_FUNCTION_SERVICE : OUMPIRE_FUNCTION_UMPIRE;
			$q = new Bn_query('umpire');
			$q->setWhere('umpi_id=' . $this->_umpiId);
			$q->setValue('umpi_order', 1000);
			$q->addValue('umpi_function', $newFunction);
			$q->addValue('umpi_currentcourt', 0);
			$q->updateRow();
			
			// Recuperer tous les arbitres du groupe avec la fonction
			$q->addTable('registration');
			$q->setFields('umpi_id');
			$q->setWhere('umpi_court =' . $this->_group);
			$q->addWhere('umpi_function =' . $newFunction);
			$q->addWhere('regi_eventid = ' . $this->_eventId);
			$q->addWhere('regi_id = umpi_regiid');
			$q->setOrder('umpi_order');
			$umpires = $q->getRows();

			// Renumeroter l'ordre de chacun
			$nb = 1;
			$q->setTables('umpire');
			foreach($umpires as $umpire)
			{
				$q->setWhere('umpi_id=' . $umpire['umpi_id']);
				$q->setValue('umpi_order', $nb++);
				$q->updateRow();
			}
			unset($q);
			}
		return true;
	}

	/**
	 * Rajoute l'arbitre au debut de la liste de sa non function
	 *
	 * @return mixed
	 */
	public function pop()
	{
		if ($this->_court > 0 )
		{
			$q = new Bn_query('umpire, registration');
			// Recuperer tous les arbitres du groupe avec une autre fonction
			$q->setWhere('umpi_court =' . $this->_group);
			$q->addWhere('umpi_regiid !=' . $this->_regiId);
			$q->addWhere('umpi_function !=' . $this->_function);
			$q->addWhere('umpi_function !=' . OUMPIRE_FUNCTION_REST);
			$q->addWhere('regi_eventid = ' . $this->_eventId);
			$q->addWhere('regi_id = umpi_regiid');
			$q->setOrder('umpi_order');
			$umpires = $q->getRows();
			
			
			// Mettre a jour l'arbitre dans sa nouvelle fonction
			$nb = 1;
			$newFunction = $this->_function == OUMPIRE_FUNCTION_UMPIRE ? OUMPIRE_FUNCTION_SERVICE : OUMPIRE_FUNCTION_UMPIRE;
			$q->setWhere('umpi_id=' . $this->_umpiId);
			$q->setValue('umpi_order', $nb++);
			$q->setValue('umpi_function', $newFunction);
			$q->setValue('umpi_currentcourt', 0);
			$q->updateRow();

			// Renumeroter l'ordre des autres
			foreach($umpires as $umpire)
			{
				$q->setWhere('umpi_id=' . $umpire['umpi_id']);
				$q->setValue('umpi_order', $nb++);
				$q->updateRow();
			}

			unset($q);
		}
		return true;
	}

	/**
	 * Deplace l'arbitre d'un groupe
	 *
	 * @param integer $aSens sens de deplacemnt 1 ou -1
	 */
	public function moveGroup($aSens)
	{
		
		// Renumeroter les arbitre du groupe d'origine
		// Recuperer tous les arbitres du groupe avec la meme fonction
		// sauf l'arbitre courant
		$q = new Bn_query('umpire, registration');
		$q->setFields('umpi_id, umpi_order');
		$q->setWhere('umpi_court =' . $this->_group);
		$q->addWhere('umpi_function =' . $this->_function);
		$q->addWhere('regi_eventid =' . $this->_eventId);
		$q->addWhere('regi_id = umpi_regiid');
		$q->addWhere('umpi_id != '. $umpire['umpi_id']);
		$q->setOrder('umpi_order');
		$umpires = $q->getRows();
		
		// Renumeroter l'ordre
		$nb = 1;
		foreach($umpires as $umpire)
		{
			$q->setWhere('umpi_id=' . $umpire['umpi_id']);
			$q->setValue('umpi_order', $nb++);
			$q->updateRow();
		}
		
		// Nouveau group pour l'arbitre
		$this->_group += $aSens;
		if ($this->_group < 1) $this->_group = 1;
		$data = array('court' => $this->_group, 'order' => 1000);
		$this->update('umpire', 'umpi_', $data, 'umpi_id='.$this->_umpiId);
		
		// Recuperer tous les arbitres du groupe avec la meme fonction
		$q = new Bn_query('umpire, registration');
		$q->setFields('umpi_id, umpi_order');
		$q->setWhere('umpi_court =' . $this->_group);
		$q->addWhere('umpi_function =' . $this->_function);
		$q->addWhere('regi_eventid =' . $this->_eventId);
		$q->addWhere('regi_id = umpi_regiid');
		$q->setOrder('umpi_order');
		$umpires = $q->getRows();
		
		// Renumeroter l'ordre
		$nb = 1;
		foreach($umpires as $umpire)
		{
			if ($umpire['umpi_id'] == $this->_umpiId) $this->_order  = $nb;
			$q->setWhere('umpi_id=' . $umpire['umpi_id']);
			$q->setValue('umpi_order', $nb++);
			$q->updateRow();
		}
		
	}

	/**
	 * Deplace l'arbitre dans son groupe
	 *
	 * @param integer $aSens sens de deplacemnt 1 ou -1
	 */
	public function moveOrder($aSens)
	{
		// Recuperer tous les arbitres du groupe avec la meme fonction
		$q = new Bn_query('umpire, registration');
		$q->setFields('umpi_id, umpi_order');
		$q->setWhere('umpi_court =' . $this->_group);
		$q->addWhere('umpi_function =' . $this->_function);
		$q->addWhere('regi_eventid =' . $this->_eventId);
		$q->addWhere('regi_id = umpi_regiid');
		$q->setOrder('umpi_order');
		$umpires = $q->getRows();
		
		// Mettre a jour l'ordre
		foreach($umpires as &$umpire)
		{
			if ($umpire['umpi_id'] == $this->_umpiId) $umpire['umpi_order'] += ($aSens * 1.5);
			$sort[] = $umpire['umpi_order'];
		}
			
		// Trier par ordre
		array_multisort($sort, $umpires);
		
		// Renumeroter l'ordre
		$nb = 1;
		$q->setTables('umpire');
		foreach($umpires as &$umpire)
		{
			if ($umpire['umpi_id'] == $this->_umpiId) $this->_order = $nb;
			$q->setWhere('umpi_id=' . $umpire['umpi_id']);
			$q->setValue('umpi_order', $nb++);
			$q->updateRow();
		}
		return true;
	}
	
	/**
	 * Chnage la fonction d'un arbitre
	 *
	 * @param integer $aFunction nouvelle fonction
	 * @param integer $aGroup    groupe
	 * @param integer $aOrder    position dans le groupe
	 *
	 */
	public function moveFunction($aFunction, $aGroup, $aOrder)
	{
		// Recuperer tous les arbitres du groupe d'origine avec la meme fonction
		// sauf celui concerne et les renumaroter
		$q = new Bn_query('umpire, registration');
		$q->setFields('umpi_id, umpi_order');
		$q->setWhere('umpi_court =' . $this->_group);
		$q->addWhere('umpi_function =' . $this->_function);
		$q->addWhere('regi_eventid =' . $this->_eventId);
		$q->addWhere('regi_id = umpi_regiid');
		$q->addWhere('umpi_id !='  . $this->_umpiId);
		$q->setOrder('umpi_order');
		$umpires = $q->getRows();

		// Renumeroter l'ordre
		$nb = 1;
		foreach($umpires as $umpire)
		{
			$q->setWhere('umpi_id=' . $umpire['umpi_id']);
			$q->setValue('umpi_order', $nb++);
			$q->updateRow();
		}
		
		
		$this->_function = $aFunction;
		$this->_group    = $aGroup;
		$data = array('function' => $aFunction, 
		'court' => $aGroup, 
		'order' => $aOrder - 0.5,
		'regiid' => $this->_regiId);
		$this->update('umpire', 'umpi_', $data, 'umpi_id='.$this->_umpiId);
		
		// Recuperer tous les arbitres du groupe avec la meme fonction
		$q = new Bn_query('umpire, registration');
		$q->setFields('umpi_id, umpi_order');
		$q->setWhere('umpi_court =' . $this->_group);
		$q->addWhere('umpi_function =' . $this->_function);
		$q->addWhere('regi_eventid =' . $this->_eventId);
		$q->addWhere('regi_id = umpi_regiid');
		$q->setOrder('umpi_order');
		$umpires = $q->getRows();

		// Renumeroter l'ordre
		$nb = 1;
		foreach($umpires as $umpire)
		{
			if ($umpire['umpi_id'] == $this->_umpiId) $this->_order  = $nb;
			$q->setWhere('umpi_id=' . $umpire['umpi_id']);
			$q->setValue('umpi_order', $nb++);
			$q->updateRow();
		}
		return true;
	}
	
}
?>