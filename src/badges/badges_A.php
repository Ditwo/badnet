<?php
/*****************************************************************************
 !   Module     : Badges
 !   File       : $Source: /cvsroot/aotb/badnet/src/badges/badges_A.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.3 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:24 $
 ******************************************************************************/
require_once "base_A.php";
require_once "utils/utpage_A.php";
require_once "badges.inc";

/**
 * Module de gestion des badges
 *
 * @author Gerard CANTEGRL <cage@free.fr>
 * @see to follow
 *
 */

class badges_A
{

	// {{{ properties

	/**
	 * Utils objet
	 *
	 * @var     object
	 * @access  private
	 */
	var $_ut;

	/**
	 * Database access object
	 *
	 * @var     object
	 * @access  private
	 */
	var $_dt;

	// }}}

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function badges_A()
	{
		$this->_ut = new utils();
		$this->_dt = new badgesBase_A();
	}
	// }}}

	// {{{ start()
	/**
	 * Start the connexion processus
	 *
	 * @access public
	 * @param  integer $action  what to do
	 * @return void
	 */
	function start($page)
	{
		if (utvars::_getSessionVar('userAuth')  !=  WBS_AUTH_ADMIN)
		{
			if ($page != BADGES_PLAYERS
			&& $page != BADGES_OFFOS
	  		&& $page !=  BADGES_OTHERS
	  		&& $page !=  BADGES_PDF
	  		&& $page !=  BADGES_PRINTED
	  		&& $page !=  BADGES_NOPRINTED)
			$page = BADGES_PLAYERS;
		}

		switch ($page)
		{
			case WBS_ACT_BADGES:
				$this->_displayFormBadges();
				break;

			case KID_SELECT:
				$this->_displayFormBadge();
				break;

			case KID_DELETE:
				$this->_deleteBadges();
				break;

			case KID_EDIT:
				$this->_displayEditBadge();
				break;

			case KID_UPDATE:
				$this->_updateBadge();
				break;

			case BADGES_ZONES:
				$this->_displayZones();
				break;
			case BADGES_ZONE:
				$this->_displayZone();
				break;
			case BADGES_ZONE_DETAIL:
				$this->_displayZoneDetail();
				break;
	  case BADGES_ZONE_UPDATE:
	  	$this->_updateZone();
	  	break;
	  case BADGES_ZONE_DELETE:
	  	$this->_deleteZone();
	  	break;
	  case BADGES_ZONE_ADD:
	  	$this->_addZone();
	  	break;
	  case BADGES_ZONE_REMOVE:
	  	$this->_removeZone();
	  	break;
	  	 
	  case BADGES_EDIT:
	  	$this->_displayEditEltb();
	  	break;

	  case BADGES_DELETE:
	  	$this->_deleteEltbs();
	  	break;

	  case BADGES_UPDATE:
	  	$this->_updateEltb();
	  	break;

	  case BADGES_PLAYERS:
	  	$this->_displayFormMembers('itPlayers', WBS_PLAYER, WBS_PLAYER, BADGES_PLAYERS);
	  	break;
	  case BADGES_OFFOS:
	  	$this->_displayFormMembers('itOfficials', WBS_REFEREE, WBS_DELEGUE, BADGES_OFFOS);
	  	break;
	  case BADGES_OTHERS:
	  	$this->_displayFormMembers('itOthers', WBS_VOLUNTEER, WBS_OTHERB, BADGES_OTHERS);
	  	break;

	  case BADGES_PDF:
	  	$this->_badges();
	  	break;

	  case BADGES_PRINTED:
	  	$this->_printed(true);
	  	break;
	  case BADGES_NOPRINTED:
	  	$this->_printed(false);
	  	break;

	  default:
	  	echo "page $page demand�e depuis badge_A<br>";
	  	exit;
		}
	}
	// }}}


	// {{{ _printed
	/**
	* Marque les badges selectionnes
	*/
	function _printed($aPrinted)
	{
		$dt = $this->_dt;

		$ids  = kform::getInput("members", NULL);
		if (!is_null($ids))
		{
			$dt->printed($ids, $aPrinted);
		}
		//Close the windows
		$page = new utPage('none');
		$page->close();
		exit;
	}

	// {{{ _addZone
	/**
	* Supression des badges selectionnes
	*/
	function _addZone()
	{
		$dt = $this->_dt;

		$id  = kform::getInput("listNok", NULL);
		$zoneId  = kform::getInput("zoneId", NULL);
		if (!is_null($id))
		{
			$dt->addZone($zoneId, $id);
		}
		//Afficher le resultat
		$this->_displayZoneDetail($zoneId);
	}
	//}}}

	// {{{ _removeZone
	/**
	* Supression des badges selectionnes
	*/
	function _removeZone()
	{
		$dt = $this->_dt;

		$id  = kform::getInput("listOk", NULL);
		$zoneId  = kform::getInput("zoneId", NULL);
		if (!is_null($id))
		{
			$dt->removeZone($zoneId, $id);
		}
		//Afficher le resultat
		$this->_displayZoneDetail($zoneId);
	}
	//}}}

	// {{{ _displayZoneDetail()
	/**
	* Display a page to modify a zone
	*
	* @access private
	* @param  integer $tieId  Id of the tie
	* @return void
	*/
	function _displayZoneDetail($zoneId = null)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Creating a new page
		$utpage = new utPage('badges');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tZoneDetail', 'badges', BADGES_ZONE_ADD);

		// Obtaining informations
		if (empty($zoneId))
		$zoneId = kform::getData();
		$form->addHide('zoneId', $zoneId);
		$data = $dt->getZone($zoneId);
		$detail = $dt->getZoneDetail($zoneId);

		//$form->addTitle('zoneName', 'title', $data['zone_name']);
		$ut = new utils();
		$ok = array();
		$nok = array();
		for ($i=WBS_PLAYER; $i<=WBS_COACH; $i++)
		{
			if ( in_array($i, $detail) )
			{
				$ok[$i] = $ut->getLabel($i);
			}
			else
			{
				$nok[$i] = $ut->getLabel($i);
			}
		}
		for ($i=WBS_VOLUNTEER; $i<=WBS_EXHIBITOR; $i++)
		{
			if ( in_array($i, $detail) )
			{
				$ok[$i] = $ut->getLabel($i);
			}
			else
			{
				$nok[$i] = $ut->getLabel($i);
			}
		}
		// Liste des categories non autorisees
		$combo =& $form->addCombo('listNok', $nok);
		$combo->setLength(8);
		// Boutons de commandes
		$items = array();
		$items['btnAdd']  = array(KAF_UPLOAD, 'badges', BADGES_ZONE_ADD);
		$items['btnRemove'] = array(KAF_UPLOAD, 'badges', BADGES_ZONE_REMOVE);
		$form->addMenu('menuZone', $items, -1, 'classMenuBtn');
		// Liste des categories autorisees
		$combo =& $form->addCombo('listOk', $ok);
		$combo->setLength(8);

		$form->addBtn('btnCancel');
		$elts = array('btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}


	// {{{ _deleteZone
	/**
	* Supression des badges selectionnes
	*/
	function _deleteZone()
	{
		$dt = $this->_dt;

		$ids  = kform::getInput("zoneList", NULL);
		if (!is_null($ids))
		{
			$dt->deleteZone($ids);
		}
		//Close the windows
		$page = new utPage('none');
		$page->close();
		exit;
	}


	// {{{ _updateZone()
	/**
	* Register an area definition
	*
	* @access private
	* @return void
	*/
	function _updateZone()
	{
		$dt = $this->_dt;

		$data['zone_name']    = kform::getInput('zoneName');
		$data['zone_id']      = kform::getInput('zoneId');
		$data['zone_eventid'] = utvars::getEventId();

		$dt->updateZone($data);

		//Close the windows
		$page = new utPage('none');
		$page->close();
		exit;
	}
	// }}}

	// {{{ _displayZone()
	/**
	* Display a page to modify a zone
	*
	* @access private
	* @param  integer $tieId  Id of the tie
	* @return void
	*/
	function _displayZone()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Creating a new page
		$utpage = new utPage('badges');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tZone', 'badges', BADGES_ZONE_UPDATE);

		// Obtaining informations
		$zoneId = kform::getData();
		$form->addHide('zoneId', $zoneId);
		$data = $dt->getZone($zoneId);

		$kedit =& $form->addEdit('zoneName', $data['zone_name'], 20);
		$elts = array('zoneName');
		$form->addBlock("blkEltb", $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayZones()
	/**
	* Display a page with the list of the areas
	*
	* @access private
	* @return void
	*/
	function _displayZones()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$kdiv =& $this->_displayHead('itZones');
		$kform =& $kdiv->addForm('fZones');

		$items['itNew'] = array(KAF_NEWWIN, 'badges',
		BADGES_ZONE, -1, 350, 150);
		$items['itErase'] = array(KAF_NEWWIN, 'badges',
		BADGES_ZONE_DELETE, 0, 450, 300);

		$kform->addMenu('menuLeft', $items, -1);
		$kform->addDiv('page', 'blkNewPage');

		// Get badges
		$zones = $dt->getZones();
		if (isset($zones['errMsg']))
		{
	  $kform->addWng($zones['errMsg']);
	  $utpage->display();
	  exit;
		}

		// Display the list of areas
		$krow =& $kform->addRows("zoneList", $zones);
		$krow->displaySelect(false);
		//$krow->displayTitle(false);
		//$krow->displayNumber(false);
		$acts[0] = array(KAF_NEWWIN, 'badges', BADGES_ZONE, 0, 350,150);
		$acts[1] = array(KAF_NEWWIN, 'badges', BADGES_ZONE_DETAIL, 0, 600, 300);
		$krow->setActions($acts);
		$krow->displaySelect(true);

		$this->_utpage->display();
		exit;
	}
	// }}}


	// {{{ _deleteEltb
	/**
	 * Supression des badges selectionnes
	 */
	function _deleteEltbs()
	{
		$dt = $this->_dt;

		$ids  = kform::getInput("badgeElts", NULL);
		if (!is_null($ids))
		$dt->deleteEltbs($ids);

		//Close the windows
		$page = new utPage('none');
		$page->close();
		exit;
	}

	// {{{ _deleteBadges
	/**
	 * Supression des badges selectionnes
	 */
	function _deleteBadges()
	{
		$dt = $this->_dt;

		$ids  = kform::getInput("badgeList", NULL);
		if (!is_null($ids))
		$dt->deleteBadges($ids);

		//Close the windows
		$page = new utPage('none');
		$page->close();
		exit;
	}

	// {{{ _badges
	/**
	 * Select ids registration for badge
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function _badges()
	{
		$dt = $this->_dt;

		require_once "pdf/badges.php";
		$pdf = new badgesPdf();
		$ids  = kform::getInput("members", NULL);
		if (!is_null($ids))
		{
			$dt->printed($ids, WBS_YES);
			$pdf->badges($ids, kform::getInput("selectList", WBS_PLAYER));
		}
		else
		{
			//Close the windows
			$page = new utPage('none');
			$page->close();
		}
		exit;
	}

	// {{{ _displayFormMembers()
	/**
	 * Display a page with the list of the members
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormMembers($items, $first, $last, $action)
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$kdiv =& $this->_displayHead($items);
		$form =& $kdiv->addForm('badges');

		// Type of badges
		$selectId = kform::getInput('selectList', -1);
		$selects[-1] = '----';
		for($i=$first; $i<=$last; $i++)
		{
			$selects[$i] = $ut->getLabel($i);
		}
		$kcombo=& $form->addCombo('selectList', $selects, $selects[$selectId]);
		$acts[1] = array(KAF_UPLOAD, 'badges', $action);
		$kcombo->setActions($acts);

		$itms['itPrint'] = array(KAF_NEWWIN, 'badges', BADGES_PDF, 0,  500, 500);
		$itms['itPrinted'] = array(KAF_NEWWIN, 'badges', BADGES_PRINTED, 0,  500, 500);
		$itms['itNoPrinted'] = array(KAF_NEWWIN, 'badges', BADGES_NOPRINTED, 0,  500, 500);
		$form->addMenu('menuLeft', $itms, -1);
		$form->addDiv('page', 'blkNewPage');

		// Get badges
		$members = $dt->getMembers($first, $last, $selectId);
		if (isset($members['errMsg']))
		{
			$kdiv->addWng($members['errMsg']);
			$this->_utpage->display();
			exit;
		}


		// Display the list of badges
		$krow =& $form->addRows("members", $members);
		$size[9] = '0+';
		$krow->setSize($size);

		$img[7] = 'asso_logo';
		$krow->setLogo($img);

		$krow->displaySelect(true);
		//$acts[0] = array(KAF_NEWWIN, 'badges', BADGES_EDIT, 0, 300,300);
		//$krow->setActions($acts);

		$this->_utpage->display();
		exit;
	}
	// }}}

	// {{{ _updateeltb()
	/**
	 * Register a badge definition
	 *
	 * @access private
	 * @return void
	 */
	function _updateEltb()
	{
		$dt = $this->_dt;

		$data['eltb_font'] = kform::getInput('eltbFont');
		$data['eltb_size'] = kform::getInput('eltbSize');
		$data['eltb_bold'] = kform::getInput('eltbBold');
		$data['eltb_top'] = kform::getInput('eltbTop');
		$data['eltb_left'] = kform::getInput('eltbLeft');
		$data['eltb_align'] = kform::getInput('eltbAlign');
		$data['eltb_width'] = kform::getInput('eltbWidth');
		$data['eltb_height'] = kform::getInput('eltbHeight');
		$data['eltb_border'] = kform::getInput('eltbBorder');
		$data['eltb_borderSize'] = kform::getInput('eltbBorderSize');
		$data['eltb_value'] = kform::getInput('eltbValue');
		$data['eltb_field'] = kform::getInput('eltbField');
		$data['eltb_textColor'] = kform::getInput('eltbTextColor');
		$data['eltb_fillColor'] = kform::getInput('eltbFillColor');
		$data['eltb_drawColor'] = kform::getInput('eltbDrawColor');
		$data['eltb_id'] = kform::getInput('eltbId');
		$data['eltb_badgeId']= kform::getInput('badgeId');
		$data['eltb_zoneid']= kform::getInput('eltbZone');

		$dt->updateEltb($data);

		//Close the windows
		$page = new utPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _displayEditElt()
	/**
	 * Display a page to set the schedule of a tie
	 *
	 * @access private
	 * @param  integer $tieId  Id of the tie
	 * @return void
	 */
	function _displayEditEltb()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Creating a new page
		$utpage = new utPage('badges');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tEltbEdit', 'badges', BADGES_UPDATE);

		// Obtaining informations
		$data = kform::getData();
		list($badgeId, $eltbId) = explode(';', $data);
		$form->addHide('eltbId', $eltbId);
		$form->addHide('badgeId', $badgeId);
		$data = $dt->getEltb($eltbId);

		$handle=@opendir("Tcpdf/fonts");
		$fonts=array('helvetica' => 'helvetica',
		   'courier' => 'courier',
		   'symbol' => 'symbol',
		   'times' => 'times',
		   'zapfdingbats' =>'zapfdingbats');
		if ($handle)
		{
	  $masque = "|(.*).z|";
	  while ($file = readdir($handle))
	  {
	  	if (preg_match($masque, $file, $match))
	  	{
	  		$fonts[$match[1]] = $match[1];
	  	}
	  }
	  closedir($handle);
		}
		$kedit =& $form->addCombo('eltbFont', $fonts, $data['eltb_font']);

		$kedit =& $form->addEdit('eltbSize', $data['eltb_size'], 20);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('eltbBold', $data['eltb_bold'], 20);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('eltbAlign', $data['eltb_align'], 20);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('eltbTop', $data['eltb_top'], 20);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('eltbLeft', $data['eltb_left'], 20);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('eltbWidth', $data['eltb_width'], 20);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('eltbHeight', $data['eltb_height'], 20);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('eltbBorder', $data['eltb_border'], 20);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('eltbBorderSize', $data['eltb_borderSize'], 20);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('eltbValue', $data['eltb_value'], 20);
		$kedit->noMandatory();
		//liste des types d'elements
		for ($i=WBS_FIELD_TITLE; $i<=WBS_FIELD_ZONE; $i++)
		$fields[$i] = $this->_ut->getLabel($i);
		$fields[-1] = "---Aucun---";
		$form->addCombo('eltbField', $fields, $data['eltb_field']);
		// Rajouter les zones
		$zones = $dt->getZones();
		$fieldzones  =array();
		foreach($zones as $zone)
		{
			$fieldzones[$zone['zone_id']] = $zone['zone_name'];
		}
		if (count($fieldzones))
		{
			$form->addCombo('eltbZone', $fieldzones, $data['eltb_zoneid']);
		}
		$kedit =& $form->addEdit('eltbTextColor', $data['eltb_textColor'], 20);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('eltbFillColor', $data['eltb_fillColor'], 20);
		$kedit->noMandatory();
		$kedit =& $form->addEdit('eltbDrawColor', $data['eltb_drawColor'], 20);
		$kedit->noMandatory();

		$elts = array('eltbFont', 'eltbSize', 'eltbBold', 'eltbAlign', 'eltbTop',
		    'eltbLeft', 'eltbWidth', 'eltbHeight', 'eltbBorder', 
		    'eltbBorderSize', 'eltbField', 'eltbValue', 'eltbZone',
		    'eltbTextColor', 'eltbFillColor', 'eltbDrawColor');
		$form->addBlock("blkEltb", $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}


	// {{{ _updateBadge()
	/**
	 * Register a badge definition
	 *
	 * @access private
	 * @return void
	 */
	function _updateBadge()
	{
		$dt = $this->_dt;

		$data['bdge_name'] = kform::getInput('badgeName');
		$data['bdge_width'] = kform::getInput('badgeWidth');
		$data['bdge_height'] = kform::getInput('badgeHeight');
		$data['bdge_border'] = kform::getInput('badgeBorder');
		$data['bdge_borderSize'] = kform::getInput('badgeBorderSize');
		$data['bdge_topMargin'] = kform::getInput('badgeTopMargin');
		$data['bdge_leftMargin'] = kform::getInput('badgeLeftMargin');
		$data['bdge_deltaWidth'] = kform::getInput('badgeDeltaWidth');
		$data['bdge_deltaHeight'] = kform::getInput('badgeDeltaHeight');
		$data['bdge_id'] = kform::getInput('badgeId');

		$dt->updateBadge($data);

		//Close the windows
		$page = new utPage('none');
		$page->close();
		exit;
	}
	// }}}


	// {{{ _displayFormBadge()
	/**
	 * Display a page with the list of the badges models
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormBadge()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$kdiv =& $this->_displayHead('itModels');
		$kform =& $kdiv->addForm('fBadges');
		$badgeId = kform::getData();
		$bagde = $dt->getBadge($badgeId);

		$items['itNew'] = array(KAF_NEWWIN, 'badges',
		BADGES_EDIT, "{$badgeId};-1", 450, 450);
		$items['itErase'] = array(KAF_NEWWIN, 'badges',
		BADGES_DELETE, 0, 450, 300);
		$kform->addMenu('menuLeft', $items, -1);
		$kform->addDiv('page', 'blkNewPage');

		// Get badges
		$badgeElts = $dt->getElts($badgeId);
		if (isset($badgeElts['errMsg']))
		{
	  $kform->addWng($badgeElts['errMsg']);
	  $this->_utpage->display();
	  exit;
		}

		// Display the list of badges
		$krow =& $kform->addRows("badgeElts", $badgeElts);
		$krow->displaySelect(true);
		$acts[0] = array(KAF_NEWWIN, 'badges', BADGES_EDIT, 0, 450,450);
		$krow->setActions($acts);

		$this->_utpage->display();
		exit;
	}
	// }}}


	// {{{ _displayFormBadges()
	/**
	 * Display a page with the list of the badges models
	 *
	 * @access private
	 * @return void
	 */
	function _displayFormBadges()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$kdiv =& $this->_displayHead('itModels');
		$kform =& $kdiv->addForm('fBadges');

		$items['itNew'] = array(KAF_NEWWIN, 'badges',
		KID_EDIT, -1, 450, 300);
		$items['itErase'] = array(KAF_NEWWIN, 'badges',
		KID_DELETE, 0, 450, 300);

		$kform->addMenu('menuLeft', $items, -1);
		$kform->addDiv('page', 'blkNewPage');

		// Get badges
		$badges = $dt->getBadges();
		if (isset($badges['errMsg']))
		{
	  $kform->addWng($badges['errMsg']);
	  $utpage->display();
	  exit;
		}

		// Display the list of badges
		$krow =& $kform->addRows("badgeList", $badges);
		$krow->displaySelect(false);
		//$krow->displayTitle(false);
		//$krow->displayNumber(false);
		$acts[0] = array(KAF_NEWWIN, 'badges', KID_EDIT, 0, 300,300);
		$acts[1] = array(KAF_UPLOAD, 'badges', KID_SELECT);
		$krow->setActions($acts);
		$krow->displaySelect(true);

		$this->_utpage->display();
		exit;
	}
	// }}}


	// {{{ _displayBadge()
	/**
	 * Display a page to set the schedule of a tie
	 *
	 * @access private
	 * @param  integer $tieId  Id of the tie
	 * @return void
	 */
	function _displayEditBadge()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		$eventId = utvars::getEventId();
		// Creating a new page
		$utpage = new utPage('badges');
		$content =& $utpage->getPage();
		$form =& $content->addForm('tBadgeEdit', 'badges', KID_UPDATE);

		// Obtaining informations
		$badgeId = kform::getData();

		$form->addHide('badgeId', $badgeId);
		$data = $dt->getBadge($badgeId);

		$kedit =& $form->addEdit('badgeName', $data['bdge_name'], 20);
		$kedit->setMaxLength(20);
		$kedit =& $form->addEdit('badgeWidth', $data['bdge_width'], 20);
		$kedit->setMaxLength(6);
		$kedit =& $form->addEdit('badgeHeight', $data['bdge_height'], 20);
		$kedit->setMaxLength(6);
		$kedit =& $form->addEdit('badgeBorder', $data['bdge_border'], 20);
		$kedit->setMaxLength(6);
		$kedit =& $form->addEdit('badgeBorderSize', $data['bdge_borderSize'], 20);
		$kedit->setMaxLength(6);
		$kedit =& $form->addEdit('badgeTopMargin', $data['bdge_topMargin'], 20);
		$kedit->setMaxLength(6);
		$kedit =& $form->addEdit('badgeLeftMargin', $data['bdge_leftMargin'], 20);
		$kedit->setMaxLength(6);
		$kedit =& $form->addEdit('badgeDeltaWidth', $data['bdge_deltaWidth'], 20);
		$kedit->setMaxLength(6);
		$kedit =& $form->addEdit('badgeDeltaHeight', $data['bdge_deltaHeight'], 20);
		$kedit->setMaxLength(6);

		$elts = array('badgeName', 'badgeWidth', 'badgeHeight', 'badgeBorder',
		    'badgeBorderSize', 'badgeTopMargin', 'badgeLeftMargin',
		    'badgeDeltaWidth', 'badgeDeltaHeight');
		$form->addBlock("blkBadge", $elts);

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		$utpage->display();
		exit;
	}
	// }}}


	// {{{ _displayHead()
	/**
	 * Display header of the page
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function & _displayHead($select)
	{
		// Create a new page
		$this->_utpage = new utPage_A('badges', true, 'itBadges');
		$content =& $this->_utpage->getContentDiv();

		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		if (utvars::_getSessionVar('userAuth')  ==  WBS_AUTH_ADMIN)
		{
			$items['itModels']    = array(KAF_UPLOAD,  'badges', WBS_ACT_BADGES);
			$items['itZones']     = array(KAF_UPLOAD, 'badges', BADGES_ZONES);
		}
		$items['itPlayers']   = array(KAF_UPLOAD, 'badges', BADGES_PLAYERS);
		$items['itOfficials'] = array(KAF_UPLOAD, 'badges', BADGES_OFFOS);
		$items['itOthers']    = array(KAF_UPLOAD, 'badges', BADGES_OTHERS);
		$kdiv->addMenu("menuType", $items, $select);
		$kdiv =& $content->addDiv('register', 'cont3');
		return $kdiv;
	}
	// }}}

}

?>
