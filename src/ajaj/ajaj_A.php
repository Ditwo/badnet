<?php
/*****************************************************************************
!   Module     : Ajaj
!   File       : $Source: /cvsroot/aotb/badnet/src/ajaj/ajaj_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.8 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
!   Mailto     : cage@free.fr
******************************************************************************
!   License   : Licensed under GPL [http://www.gnu.org/copyleft/gpl.html]
!      This program is free software; you can redistribute it and/or
!      modify it under the terms of the GNU General Public License
!      as published by the Free Software Foundation; either version 2
!      of the License, or (at your option) any later version.
!
!      This program is distributed in the hope that it will be useful,
!      but WITHOUT ANY WARRANTY; without even the implied warranty of
!      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
!      GNU General Public License for more details.
!
!      You should have received a copy of the GNU General Public License
!      along with this program; if not, write to the Free Software
!      Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, 
!      USA.
******************************************************************************/

require_once "base_A.php";
require_once "utils/json.php";
require_once "ajaj.inc";

/**
* Module de gestion des appels asynchrone
*
* @author Gerard CANTEGRIL
* @see to follow
*
*/

class ajaj_A
{
  
  // {{{ properties
  
  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function ajaj_A()
    {
      $this->_dt = new ajajBase_A();
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
      switch ($page)
        {
	  // Logical delete a team
	case WBS_ACT_TEAMS:
	  $this->_updateTeam();
	  break;	  

	case WBS_ACT_REGI:
	  $this->_getAssocList();
	  break;	  

	case AJAJ_REGI1:
	  $this->_getDrawList();
	  break;	  

	case AJAJ_MAINT_SELECT_ASSO:
	  $this->_getAsso();
	  break;	  

	case AJAJ_MAINT_UPDATE_ASSO:
	  $this->_updateAsso();
	  break;	  

	case AJAJ_MAINT_MERGE_ASSO:
	  $this->_mergeAsso();
	  break;	  

	case AJAJ_MAINT_SELECT_MEMBER:
	  $this->_getMember();
	  break;	  

	case AJAJ_MAINT_UPDATE_MEMBER:
	  $this->_updateMember();
	  break;	  

	case AJAJ_MAINT_MERGE_MEMBER:
	  $this->_mergeMember();
	  break;	  

	case AJAJ_MAINT_LIST_MEMBER:
	  $this->_listMember();
	  break;	  

	case WBS_ACT_SCHEDU:
	  $this->_updateConvocParam();
	  break;	  

	case AJAJ_DRAW_SAVEROUND:
	  $this->_updateRound();
	  break;	  

	default:
	  echo "page $page demandée depuis ajaj_A<br>";
	  exit();
	}
    }
  // }}}

  // {{{ _updateRound()
  /**
   * Enregistre les donnees d'un tour ko
   *
   * @access private
   * @return void
   */
  function _updateRound()
    {
      $round['rund_id']        = kform::getInput('roundId');
      $round['rund_name']      = kform::getInput('roundName');
      $round['rund_stamp']     = kform::getInput('roundStamp');
      $round['rund_type']      = kform::getInput('roundType');
      $round['rund_entries']   = kform::getInput('roundEntries');
      $round['rund_drawId']    = kform::getInput('drawId');
      $round['rund_rge']       = kform::getInput('rundFinalPlace');
      $round['rund_group']     = kform::getInput('group');
      $round['rund_qual']      = 1;

      $tie['tie_nbms'] = 0;
      $tie['tie_nbws'] = 0;
      $tie['tie_nbmd'] = 0;
      $tie['tie_nbwd'] = 0;
      $tie['tie_nbxd'] = 0;
      $tie['tie_nbas'] = 0;
      $tie['tie_nbad'] = 0;
      $disci = kform::getInput('drawDisci');
      switch ($disci)
	{
	case WBS_MS : 
	  $tie['tie_nbms'] = 1;
	  break;
	case WBS_LS : 
	  $tie['tie_nbws'] = 1;
	  break;
	case WBS_MD : 
	  $tie['tie_nbmd'] = 1;
	  break;
	case WBS_LD : 
	  $tie['tie_nbwd'] = 1;
	  break;
	case WBS_MX : 
	  $tie['tie_nbxd'] = 1;
	  break;
	case WBS_AS : 
	  $tie['tie_nbas'] = 1;
	  break;
	case WBS_AD : 
	  $tie['tie_nbad'] = 1;
	  break;
	}

      require_once "utils/utround.php";
      $utr = new utround();
      $roundId = $utr->updateRound($round, $tie);

      $json = new Services_JSON();
      $res = $json->encode($roundId);
      echo $roundId;
      exit();
    }
  // }}}

  // {{{ _mergeMember()
  /** 
   * Fussionne deux membres
   *
   * @access private
   * @return void
   */
  function _mergeMember()
    {
      $src  = kform::getInput('srcMemberList');
      $dest = kform::getInput('destMemberList');
      if ($src != $dest)
	$this->_dt->mergeMember($src, $dest);

      $mber = $this->_dt->getMember($dest);
      $mber['dest'] = $dest;
      $mber['src'] = $src;
      $json = new Services_JSON();
      $res = $json->encode($mber);
      echo $res;
      exit();
    }
  // }}}

  // {{{ _updateMember()
  /**
   * Enregistre les donnees d'un membre
   *
   * @access private
   * @return void
   */
  function _updateMember()
    {
      $cols['mber_id']          = kform::getInput('mberId');
      $cols['mber_secondname']  = kform::getInput('mberSecondName');
      $cols['mber_firstname']   = kform::getInput('mberFirstName');
      $cols['mber_sexe']        = kform::getInput('mberSexe');
      $cols['mber_countryid']   = kform::getInput('mberCountryId');
      $cols['mber_born']        = kform::getInput('mberBorn');
      $cols['mber_ibfnumber']   = kform::getInput('mberIbfNumber');
      $cols['mber_licence']     = kform::getInput('mberLicence');
      $cols['mber_fedeid']      = kform::getInput('mberFedeId');
      $cols['mber_urlphoto']    = kform::getInput('mberUrlPhoto');
      $cols['mber_lockid']      = kform::getInput('mberLockId');

      $this->_dt->updateMember($cols);
      $json = new Services_JSON();
      $res = $json->encode('Ok');
      echo $res;
      exit();
    }
  // }}}


  // {{{ _listMember()
  /** 
   * Renvoie les membres commencant par une lettre donne
   *
   * @access private
   * @return void
   */
  function _listMember()
    {
      $src = kform::getData();
      $members = $this->_dt->getMembers(kform::getInput($src));
      //      print_r($members);
      $json = new Services_JSON();
      $res = $json->encode($members);
      echo $res;
      exit();
    }
  // }}}

  // {{{ _getMember()
  /** 
   * Renvoie les champs d'un membre
   *
   * @access private
   * @return void
   */
  function _getMember()
    {
      $src = kform::getData();
      $asso = $this->_dt->getMember(kform::getInput($src));
      $json = new Services_JSON();
      $res = $json->encode($asso);
      echo $res;
      exit();
    }
  // }}}


  // {{{ _mergeAsso()
  /** 
   * Fussionne deux associations
   *
   * @access private
   * @return void
   */
  function _mergeAsso()
    {
      $src  = kform::getInput('srcAssoList');
      $dest = kform::getInput('destAssoList');
      if ($src != $dest)
	$this->_dt->mergeAsso($src, $dest);

      $asso = $this->_dt->getAsso($dest);
      $asso['dest'] = $dest;
      $asso['src'] = $src;
      $json = new Services_JSON();
      $res = $json->encode($asso);
      echo $res;
      exit();
    }
  // }}}

  // {{{ _updateAsso()
  /**
   * Enregistre les donnees d'une association
   *
   * @access private
   * @return void
   */
  function _updateAsso()
    {
      $cols['asso_id']       = kform::getInput('assoId');
      $cols['asso_name']     = kform::getInput('assoName');
      $cols['asso_stamp']    = kform::getInput('assoStamp');
      $cols['asso_type']     = kform::getInput('assoType');
      $cols['asso_fedeid']   = kform::getInput('assoFedeId');
      $cols['asso_cmt']      = kform::getInput('assoCmt');
      $cols['asso_url']      = kform::getInput('assoUrl');
      $cols['asso_logo']     = kform::getInput('assoLogo');
      $cols['asso_number']   = kform::getInput('assoNumber');
      $cols['asso_dpt']      = kform::getInput('assoDpt');
      $cols['asso_pseudo']   = kform::getInput('assoPseudo');
      $cols['asso_noc']      = kform::getInput('assoNoc');
      $cols['asso_lockid']   = kform::getInput('assoLockId');

      $this->_dt->updateAsso($cols);
      $json = new Services_JSON();
      $res = $json->encode('Ok');
      echo $res;
      exit();
    }
  // }}}

  // {{{ _getAsso()
  /** 
   * Renvoie les champs d'une association
   *
   * @access private
   * @return void
   */
  function _getAsso()
    {
      $src = kform::getData();
      $asso = $this->_dt->getAsso(kform::getInput($src));
      $json = new Services_JSON();
      $res = $json->encode($asso);
      echo $res;
      exit();
    }
  // }}}

  // {{{ _updateConvocparam()
  /**
   * Enregistre les donnees de convocation
   *
   * @access private
   * @return void
   */
  function _updateConvocParam()
    {

      $ute = new utevent();
      $cols['evnt_id'] = utvars::getEventId();
      $cols['evnt_delay'] = kform::getInput('delayConvoc');
      $cols['evnt_convoc'] = kform::getInput('typeConvoc');
      $cols['evnt_lieuconvoc'] = kform::getInput('lieuConvoc');
      $cols['evnt_textconvoc'] = kform::getInput('textConvoc');
      $ute->update($cols);
      $json = new Services_JSON();
      $res = $json->encode('Ok');
      echo $res;
      exit();
    }
  // }}}

  // {{{ _updateTeam()
  /**
   * Enregitre les donnes d'une equipe
   *
   * @access private
   * @return void
   */
  function _updateTeam()
    {
      $dt =& $this->_dt;
      $team = array('team_name'   =>kform::getInput('teamName'),
		    'team_stamp'  =>kform::getInput('teamStamp'),
		    'team_noc'    =>kform::getInput('teamNoc'),
		    'team_url'    =>kform::getInput('teamUrl'),
		    'team_cmt'    =>kform::getInput('teamCmt'),
		    'team_date'   =>kform::getInput('teamDate'),
		    'team_id'     =>kform::getInput('teamId'),
		    'team_captain'=>kform::getInput('teamCaptain'),
		    'team_textconvoc'=>kform::getInput('teamText')
		    );
      $utd = new utdate();
      $utd->setFrDate($team['team_date']);
      $team['team_date'] = $utd->getIsoDateTime();
      $dt->updateTeam($team);
      $json = new Services_JSON();
      $res = $json->encode('Ok');
      echo $res;
      exit();
    }
  // }}}
  
  // {{{ _getAssocList()
  /** 
   * Renvoie la liste des associations dun departement
   *
   * @access private
   * @return void
   */
  function _getAssocList()
    {
      $critere = array('asso_name'   => '',
		       'asso_pseudo' => '',
		       'asso_stamp'  => '',
		       'asso_dpt'    => kform::getInput('assoDpt'),
		       'asso_type'   => WBS_CLUB);
      require_once "import/imppoona.php";
      $poona = new ImpPoona();
      $instances = $poona->searchInstance($critere);
      $assos['-1;-1'] = '----';
      if (isset($instances['errMsg']))
	$assos['errMsg'] = 'msgPoonaNotAvailable';
      else if ( !empty($instances) )
      {
		foreach($instances as $instance)
	  		$assos[$instance['asso_id']] = utf8_encode($instance['asso_name']);
      }
      $json = new Services_JSON();
      $res = $json->encode($assos);
      //print_r($critere);
      echo $res;
      exit();
    }
  // }}}

  // {{{ _getDrawList()
  /** 
   * Renvoie la liste des tableaux possibles
   *
   * @access private
   * @return void
   */
  function _getDrawList()
    {
      $dt =& $this->_dt;
      $select = array('mber_sexe'    =>kform::getInput("mberSexe", ''),
		      'regi_catage'    =>kform::getInput("regiCatage", WBS_CATAGE_SEN),
		      'regi_numcatage' =>kform::getInput("regiNumcatage", 0),
      		  'regi_dateauto'  =>kform::getInput("regiDateAuto", ''),
		      'regi_surclasse' =>kform::getInput("regiSurclasse", WBS_SURCLASSE_NONE),
		      'regi_datesurclasse' =>kform::getInput("regiDateSurclasse", ''));

      // Surclassement possible en fonction
      // de la categorie d'age
      // Aucun, simple, double, va, se, sp
      $surclasse = array( WBS_CATAGE_POU => array(1, 0, 0, 0, 0, 1),
			  WBS_CATAGE_BEN => array(1, 1, 1, 0, 0, 0),
			  WBS_CATAGE_MIN => array(1, 1, 1, 0, 1, 0),
			  WBS_CATAGE_CAD => array(1, 1, 1, 0, 0, 0),
			  WBS_CATAGE_JUN => array(1, 1, 0, 0, 0, 0),
			  WBS_CATAGE_SEN => array(1, 0, 0, 0, 0, 0),
			  WBS_CATAGE_VET => array(1, 0, 0, 1, 0, 0)
			  );
      $infos['surclasse'] = $surclasse[$select['regi_catage']];
      $regiId = kform::getInput("regiId");

      // Numero des categories d'age
      switch($select['regi_catage'])
      {
      	case WBS_CATAGE_POU: $max = 0; break; 
      	case WBS_CATAGE_SEN: $max = 0; break; 
      	case WBS_CATAGE_VET: $max = 5; break;
      	default: $max = 2; break;
      }
      for ($i=0; $i<= $max;$i++) $numcatage["$i"] = array('value'=>$i);
      $infos['numcatage'] = $numcatage;
      
      // Liste des tableaux autorises
      require_once "utils/utdraw.php";
      $utd = new utdraw();
      $select['rank']  = kform::getInput("rankRankS");
      $draws = $utd->getDrawsList($select, WBS_SINGLE, true);
      $draws[-1] = "----";
      $infos['single'] = $draws;

      $select['rank']  = kform::getInput("rankRankD");
      $draws = $utd->getDrawsList($select, WBS_DOUBLE, true);
      $draws[-1] = "----";
      $infos['double'] = $draws;

      $draw = kform::getInput("double");
      if (!isset($draws[$draw]['disable']) ||
	  $draws[$draw]['disable'])
	$draw = -1;
      $pairs = $utd->getFreePlayers($draw, $select['mber_sexe'], $regiId, true);
      $pairs[-1] = "----";
      $infos['doublePair'] = $pairs;


      $select['rank']  = kform::getInput("rankRankM");
      $draws = $utd->getDrawsList($select, WBS_MIXED, true);
      $draws[-1] = "----";
      $infos['mixed'] = $draws;

      $draw = kform::getInput("mixed");
      if (!isset($draws[$draw]['disable']) ||
	  $draws[$draw]['disable'])
	$draw = -1;
      if ($select['mber_sexe'] == WBS_MALE)
	$pairs = $utd->getFreePlayers($draw, WBS_FEMALE, $regiId, true);
      else
	$pairs = $utd->getFreePlayers($draw, WBS_MALE, $regiId, true);
      $pairs[-1] = "----";
      $infos['mixedPair'] = $pairs;


      $json = new Services_JSON();
      $res = $json->encode($infos);
      echo $res;
      exit();
    }
  // }}}
    
}

?>
