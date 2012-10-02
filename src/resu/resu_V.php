<?php
/*****************************************************************************
!   Module     : resu
!   File       : $Source: /cvsroot/aotb/badnet/src/resu/resu_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.16 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/04/03 06:49:03 $
******************************************************************************/
require_once "base_V.php";
require_once "resu.inc";
require_once "ties/ties.inc";
require_once "regi/regi.inc";
require_once "utils/utimg.php";

/**
* Module d'affichage des matchs des joueurs
*
* @author Romain JUBERT <romain.jubert@ifrance.com>
* @see to follow
*
*/

class resu_V
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
  function resu_V()
    {
      $this->_ut = new utils();
      $this->_dt = new resuBase_V();
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
  function start($action)
    {
      switch ($action)
        {
	case KID_SELECT: 
	  $id = kform::getData();
	  $this->_displayResu($id);
	  break;
	default:
	  echo "page $action demand�e depuis resu_V<br>";
	  exit();
	}
    }
  // }}}

  // {{{ _displayResu()
  /**
   * Display a page with the results of the player
   *
   * @access private
   * @return void
   */
  function _displayResu($regiId)
    {
      $utev = new utEvent();

      $eventId = utvars::getEventId();
      if ($utev->getEventType($eventId)== WBS_EVENT_INDIVIDUAL)
	$this->_displayResuIndiv($regiId);
      else
	$this->_displayFormList($regiId);
    }
  //}}}

  // {{{ _displayResuIndiv()
  /**
   * Display a page with the results of a player for an individual
   * event.
   *
   * @access private
   * @param
   * @return void
   */
  function _displayResuIndiv($regiId)
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Create a new page
      $utpage = new utPage_V('resu', true, 'itRegister');
      $content =& $utpage->getContentDiv();

      // Add a menu for different action
      $kdiv =& $content->addDiv('choix', 'onglet3');
      $items['itTeams']     = array(KAF_UPLOAD, 'regi', WBS_ACT_REGI);
      $items['itPlayers']   = array(KAF_UPLOAD, 'regi', REGI_PLAYER);
      $items['itOfficials'] = array(KAF_UPLOAD, 'regi', REGI_OFFICIAL);
      $kdiv->addMenu("menuType", $items, 'itPlayers');
      $kdiv =& $content->addDiv('teamDiv', 'cont3');
	  
      $khead =& $kdiv->addDiv('headCont3', 'headCont3');
      
      // Display players list
      $players = $dt->getPlayers();
      if (count($players))
	{
	  $kcombo =& $khead->addCombo('selectList', $players, $players[$regiId]);
	  $acts[1] = array(KAF_UPLOAD, 'resu', KID_SELECT);
	  $kcombo->setActions($acts);
	}

      $kdiv =& $kdiv->addDiv('corp3', 'corpCont3');
      $infos = $dt->getPlayer($regiId);
      if (isset($infos['errMsg']))
	{
	  $kdiv->addWng($infos['errMsg']);
	  $utpage->display(); 
	  exit();
	}
      require_once "utils/objplayer.php";
      $player = new objplayer($regiId); 

      //display general informations
      $div =& $kdiv->addDiv('playerNameV');
      $size['maxWidth'] = 100;
      $size['maxHeight'] = 100;
      $photo = utimg::getPathPhoto($infos['mber_urlphoto']);
      $div =& $kdiv->addDiv('photo');
      $kimg =& $div->addImg("playerPhoto", $photo, $size);

      $div =& $kdiv->addDiv('infos', 'blkInfo');
      $div->addMsg('tCivil', '', 'titre');
      $infoLicence =& $div->addInfo("mberLicence", $player->getLicence());
      $div->addInfo("mberIbfNumber", $player->getIbfNum());
      $div->addInfo("mberCatage",  $ut->getLabel($player->getCatage()));
      $div->addInfo("mberCompet",  $player->getDateCompet());
      $div->addInfo("mberSurclasse",  $ut->getLabel($player->getSurclasse()));
      $div->addInfo("mberDateSurclasse",  $player->getDateSurclasse());

      $isSquash = $ut->getParam('issquash', false);
      if ($isSquash) $ranks = $player->getRank(WBS_SINGLE) . ' (' . $player->getRange(WBS_SINGLE) . ')';
      else $ranks = $player->getRank(WBS_SINGLE).";".$player->getRank(WBS_DOUBLE).";".$player->getRank(WBS_MIXED);
      $kinfo =& $div->addInfo('mberRank', $ranks);
      //$kinfo->setUrl($ut->getParam("ffba_url").$infos['mber_licence']);

      $div =& $kdiv->addDiv('inscript', 'blkInfo');
      $div->addMsg('tInscription', '', 'titre');
      $kinfo =& $div->addInfo('mberAsso', $infos['asso_name']);
      $act[1] = array(KAF_UPLOAD, 'teams', KID_SELECT, 
		      $infos['asso_id']);
      $kinfo->setActions($act);
      $draws = $player->getDrawStamp(WBS_SINGLE).";".$player->getDrawStamp(WBS_DOUBLE).";".$player->getDrawStamp(WBS_MIXED);
      $kinfo =& $div->addInfo('mberDraws',  $draws);
      $kinfo =& $div->addInfo('mberPartnerD',  $player->getPartnerName(WBS_DOUBLE));
      $acts[1] = array(KAF_UPLOAD, 'resu', KID_SELECT, $player->getPartnerId(WBS_DOUBLE));
      $kinfo->setActions($acts);
      $kinfo =& $div->addInfo('mberPartnerM',  $player->getPartnerName(WBS_MIXED));
      $acts[1] = array(KAF_UPLOAD, 'resu', KID_SELECT, $player->getPartnerId(WBS_MIXED));
      $kinfo->setActions($acts);

      $convoc = $dt->getFirstMatch($regiId);

      $kinfo =& $div->addInfo('mberMatch',  $convoc['firstMatch']);
      $kinfo =& $div->addInfo('mberVenue',  $convoc['place']);
      $kinfo =& $div->addInfo('mberConvoc', $convoc['convocation']);
      $kdiv->addDiv('break', 'blkNewPage');

      // display list of the matchs
      $matches = $dt->getMatchsIndiv($regiId);
      if (isset($matches['errMsg']))
	$kdiv->addWng($matches['errMsg']);
      else
	{
	  $drawId = -1;
	  foreach($matches as $match)
	    {
	      if ($drawId != $match['draw_id'])
		{
		  $rows[] = array(KOD_BREAK, "title", $match['draw_name']);
		  $drawId = $match['draw_id'];
		}
	      $rows[] = $match;
	    }
	  $krow =& $kdiv->addRows("rowsResuIndiv", $rows);  
	  
	  $krow->setSort(0);

	  $sizes[7] = '0+';
	  $krow->setSize($sizes); 
	  
	  $img[4]= 'p2m_result';
	  $krow->setLogo($img);
	}
   
      $cache = "resu_".KID_SELECT;
      $cache .= "_{$regiId}";
      $cache .= ".htm";
      $utpage->display($cache); 
      exit();
    }
  // }}}

  // {{{ _displayFormList()
  /**
   * Display a page with the list of the matches
   *
   * @access private
   * @param
   * @return void
   */
  function _displayFormList($regiId)
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      // Create a new page
      $utpage = new utPage_V('resu', true, 'itRegister');
      $content =& $utpage->getContentDiv();

      // Add a menu for different action
      $kdiv =& $content->addDiv('choix', 'onglet3');
      $items['itTeams']     = array(KAF_UPLOAD, 'regi', WBS_ACT_REGI);
      $items['itPlayers']   = array(KAF_UPLOAD, 'regi', REGI_PLAYER);
      $items['itOfficials'] = array(KAF_UPLOAD, 'regi', REGI_OFFICIAL);
      $kdiv->addMenu("menuType", $items, 'itPlayers');
      $kdiv =& $content->addDiv('teamDiv', 'cont3');


      $players = $dt->getPlayers();
      if (count($players))
	{
	  $kcombo =& $kdiv->addCombo('selectList', $players, $players[$regiId]);
	  $acts[1] = array(KAF_UPLOAD, 'resu', KID_SELECT);
	  $kcombo->setActions($acts);
	}

      $infos = $dt->getPlayer($regiId);
      if (isset($infos['errMsg']))
	{
	  $kdiv->addWng($infos['errMsg']);
	  $utpage->display(); 
	  exit();
	}

      //display general informations
      require_once "utils/objplayer.php";
      $player = new objplayer($regiId); 

      $div =& $kdiv->addDiv('playerNameV');
      $size['maxWidth'] = 100;
      $size['maxHeight'] = 100;
      $photo = utimg::getPathPhoto($infos['mber_urlphoto']);
      //$kimg =& $div->addImg("playerPhoto", $photo, $size);
      //$kimg->setText($infos['mber_firstname'].'&nbsp;'.
      //		     $infos['mber_secondname']);
      $div =& $kdiv->addDiv('photo');
      $kimg =& $div->addImg("playerPhoto", $photo, $size);

      $div =& $kdiv->addDiv('infos', 'blkInfo');
      $div->addMsg('tCivil', '', 'titre');
      $infoLicence =& $div->addInfo("mberLicence", $player->getLicence());
      $div->addInfo("mberIbfNumber", $player->getIbfNum());
      $div->addInfo("mberCatage",  $ut->getLabel($player->getCatage()));
      $div->addInfo("mberCompet",  $player->getDateCompet());
      $div->addInfo("mberSurclasse",  $ut->getLabel($player->getSurclasse()));
      $div->addInfo("mberDateSurclasse",  $player->getDateSurclasse());
      
      $isSquash = $ut->getParam('issquash', false);
      if ($isSquash) $ranks = $player->getRank(WBS_SINGLE) . ' (' . $player->getRange(WBS_SINGLE) . ')';
      else $ranks = $player->getRank(WBS_SINGLE).";".$player->getRank(WBS_DOUBLE).";".$player->getRank(WBS_MIXED);
      $kinfo =& $div->addInfo('mberRank', $ranks);
      $kinfo =& $div->addInfo('mberAsso',     $infos['team_name']);
      $act[1] = array(KAF_UPLOAD, 'teams', KID_SELECT, 
		      $infos['team_id']);
      $kinfo->setActions($act);

      //$kinfo->setUrl($infos['asso_url']);

      // display list of the matchs
      $kdiv->addDiv('break', 'blkNewPage');
      $rows = $dt->getMatchs($regiId);
    
      if (isset($rows['errMsg']))
	$kdiv->addWng($rows['errMsg']);
      else
	{
	  $divName = '';
	  $tieName = '';
	  foreach($rows as $match)
	    {
	      if ($divName != $match[1])
		{
		  $divName = $match[1];                  
		  $matches[] = array(KOD_BREAK, 'fixTitle', 
				     $divName, 'titleRow1');
		}
	      if ($tieName != $match[2])
		{
		  $tieName = $match[2];                  
		  $matches[] = array(KOD_BREAK, 'fixTitle', 
				     $tieName, 'titleRow2');
		}
	      unset($match[1]);
	      unset($match[2]);
	      $matches[] = $match;
	    }
	  $krow =& $kdiv->addRows("rowsResuV", $matches);  
	  
	  $column[4] = 0;
	  $krow->setSortAuth($column);

	  $sizes[6] = '0+';
	  $krow->setSize($sizes); 
	  
	  $img[4]= 14;
	  $img[5]= 13;
	  $krow->setLogo($img);
	}
   
      $cache = "resu_".KID_SELECT;
      $cache .= "_{$regiId}";
      $cache .= ".htm";
      $utpage->display($cache); 
      exit();
    }
  // }}}
  
}
?>