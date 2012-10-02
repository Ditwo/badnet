<?php
/*****************************************************************************
!   Module     : Utils
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utbarcod.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.1 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2005/09/07 20:51:00 $
******************************************************************************/
require_once "utbase.php";

/**
* Classe utilitaire pour la gestion des codes barres
* un code barre est constitue de
*  <id tournoi><type><soustype><identifiant>
*  <id tournoi> 6 caracteres numeriques;
*               champ de la table Event pour le tournoi courant.
*  <type>       2 caracteres numeriques;
*                01 : Article
*                02 : Equipe (ou club)
*                03 : Membre
*                04 : Compte
*                05 : Code special
*  <sous type>  3 caracteres;
*               depend du type.
*               type = 01: rubrique de l'article 
*                          champ item_rubrikId de la table items
*               type = 02: type de l'equipe 
*                       WBS_FEDE   050 federation
*                       WBS_UMASOS 051 Association d'arbitre
*                       WBS_LIGUE  052
*                       WBS_CODEP  053
*                       WBS_CLUB   054                         
*                       WBS_ECOLE  055
*                       ...
*                type = 03: type de l'inscription
*                       WBS_PLAYER    060
*                       WBS_REFEREE   061
*                       WBS_DEPUTY    062
*                       WBS_UMPIRE    063
*                       WBS_LINEJUDGE 064
*                       WBS_COACH     065
*                       WBS_CONSEILLER     066
*                       WBS_DELEGUE     067
*                        WBS_VOLUNTEER    170
*                       WBS_ORGANISATION 171
*                       WBS_VIP          172
*                       WBS_PRESS        173
*                       WBS_GUEST        174
*                       WBS_MEDICAL      175
*                       WBS_EXHIBITOR    176
*                type = 04: type du compte
*                       toujours 000
*                type = 05: code special
*                       111 mise a jour du stock
*                       200 Annulation derniere saisie
*                       201 Abandon de la saisie
*                       300 Validation normale (sans paiement)
*                       301 Validation avec paiement liquide
*                       302 Validation avec paiement cheque
*                       303 Validation avec paiement CB
*                       400 Solde individuel paiement liquide
*                       401 Solde individuel paiement cheque
*                       402 Solde individuel paiement CB
*                       450 Solde compte  paiement liquide
*                       451 Solde compte  paiement cheque
*                       452 Solde compte  paiement CB
*    <identifiant> 10 carateres
*                  id de l'enregistrement correspondant dans la base
*                  type=01: champ item_id de la table items
*                  type=02: champ team_id de la table teams
*                  type=03: champ regi_id de la table registration
*                  type=04: champ cunt_id de la table account
*                  type=05: toujours 0
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class utBarCod extends utBase
{

  // {{{ properties
  // }}}

  // {{{ getSpecialCode
  /**
   * Return array of special code for printing
   *
   * @access public
   * @return void
   */
  function getSpecialCode()
    {
      $codes = array( 
	      array('name'=>'Abandon commande', 'barcod'=> '052011111111'),
	      array('name'=>'Annulation dernier', 'barcod'=> '052001111111'),
	      array('name'=>'En compte',       'barcod'=> '053001111111'),
	      array('name'=>'Paiement esp�ce', 'barcod'=> '053011111111'),
	      array('name'=>'Paiement ch�que', 'barcod'=> '053021111111'),
	      array('name'=>'Solde esp�ce',    'barcod'=> '054001111111'),
	      array('name'=>'Solde ch�que',    'barcod'=> '054011111111'),
	      array('name'=>'Impression',      'barcod'=> '055001111111'),
	      );

      return $codes;
    }  


  // {{{ getRegiId
  /**
   * Return the member registration id if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function getRegiId($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ( $values['type'] == 3 )
	return $values['id'];
      return -1;
    }  

  // {{{ getTeamId
  /**
   * Return the team id if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function getTeamId($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ( $values['type'] == 2 )
	return $values['id'];
      return -1;
    }  

  // {{{ getItemId
  /**
   * Return the item id if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function getItemId($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ($values['type'] == 1 )
	return $values['id'];
      return -1;
    }  

  // {{{ getAccountId
  /**
   * Return the account id if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function getAccountId($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ($values['type'] == 4 )
	return $values['id'];
      return -1;
    }  

  // {{{ isSoldeIndiMoney
  /**
   * Return true if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function isSoldeIndiMoney($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ($values['type'] == 5 &&
	  $values['subtype'] == 400  )
	return true;
      return false;
    }  

  // {{{ isSoldeIndiCheck
  /**
   * Return true if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function isSoldeIndiCheck($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ($values['type'] == 5 &&
	  $values['subtype'] == 401  )
	return true;
      return false;
    }  

  // {{{ isStock
  /**
   * Return true if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function isStock($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ($values['type'] == 5 &&
	  $values['subtype'] == 111  )
	return true;
      return false;
    }  

  // {{{ isCancel
  /**
   * Return true if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function isCancel($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ($values['type'] == 5 &&
	  $values['subtype'] == 200  )
	return true;
      return false;
    }  

  // {{{ isAbort
  /**
   * Return true if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function isAbort($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ($values['type'] == 5 &&
	  $values['subtype'] == 201  )
	return true;
      return false;
    }  

  // {{{ isPayedCb
  /**
   * Return true if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function isPayedCB($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ($values['type'] == 5 &&
	  $values['subtype'] == 303  )
	return true;
      return false;
    }  

  // {{{ isPayedMoney
  /**
   * Return true if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function isPayedMoney($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ($values['type'] == 5 &&
	  $values['subtype'] == 301  )
	return true;
      return false;
    }  

  // {{{ isPayedCheck
  /**
   * Return true if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function isPayedCheck($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ($values['type'] == 5 &&
	  $values['subtype'] == 302  )
	return true;
      return false;
    }  

  // {{{ isValid
  /**
   * Return true if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function isValid($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ($values['type'] == 5 &&
	  $values['subtype'] == 300  )
	return true;
      return false;
    }
      
  // {{{ isPrint
  /**
   * Return true if the cod bar is correct
   *
   * @access public
   * @return void
   */
  function isPrint($barCod)
    {
      $values = $this->_getCodes($barCod);
      if ($values['type'] == 5 &&
	  $values['subtype'] == 500  )
	return true;
      return false;
    }  
    
    
  // {{{ getCodItem
  /**
   * Return the bar code of an item
   *
   * @access public
   * @return array   array of users
   */
  function getCodItem($itemId)
    {
      $eventId = utvars::getEventId();
      $fields = array('item_id', 'item_rubrikId');
      $tables[] = 'items';
      $where = "item_id = $itemId ".
	" AND item_eventId=$eventId";
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
	} 
      if (!$res->numRows()) return -1;

      $item = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return $this->_getBarCod(1, abs($item['item_rubrikId']), $itemId);
    }
  // }}}

  // {{{ getCodTeam
  /**
   * Return the bar code of a team
   *
   * @access public
   * @return array   array of users
   */
  function getCodTeam($teamId)
    {
      $eventId = utvars::getEventId();
      $fields = array('team_id', 'team_eventId', 'asso_type');
      $tables = array('assocs', 'a2t', 'teams');
      $where = "team_id = $teamId ".
	" AND team_id = a2t_teamId ".
	" AND asso_id = a2t_assoId ";
	" AND team_eventId=$eventId";
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
	} 
      if (!$res->numRows()) return -1;

      $team = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return $this->_getBarCod(2, $team['asso_type'], $teamId);
    }
  // }}}

  // {{{ getCodRegi
  /**
   * Return the bar code of a registered member
   *
   * @access public
   * @return array   array of users
   */
  function getCodRegi($regiId)
    {
      $eventId = utvars::getEventId();
      $fields = array('regi_id', 'regi_eventId', 'regi_type');
      $tables[] = 'registration';
      $where = "regi_id = $regiId ".
	" AND regi_eventId=$eventId";
      $res = $this->_select($tables, $fields, $where);
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
	  return $err;
	} 
      if (!$res->numRows()) return -1;

      $regi = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return $this->_getBarCod(3, $regi['regi_type'], $regiId);
    }
  // }}}


  // {{{ _getBarCod
  /**
   * Return the bar code 
   *
   * @access private
   * @return array   array of users
   */
  function _getBarCod($type, $subType, $id)
    {
//      $eventId = utvars::getEventId();
//      $fields = array('evnt_id');
//      $tables[] = 'events';
//      $where = "evnt_id = $eventId";
//      $res = $this->_select($tables, $fields, $where);
//      if (empty($res)) 
//	{
//	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
//	  return $err;
//	} 
//      if (!$res->numRows()) return -1;
//      $event = $res->fetchRow(DB_FETCHMODE_ASSOC);

//      $code = sprintf("%06d%02d%03d%010d", $event['evnt_id'], 
//		      $type, $subType, $id);
      $code = sprintf("%02d%03d%07d", $type, $subType, $id);
      return $code;
    }
  // }}}

  // {{{ _getCodes
  /**
   * Return the bar code 
   *
   * @access private
   * @return array   array of users
   */
  function _getCodes($barCod)
    {
//      list($eventId, $type, $subType, $id) = 
//	sscanf($barCod, "%06d%02d%03d%010d");
      list( $type, $subType, $id) = 
	sscanf($barCod, "%02d%03d%07d");

//      return array('eventId'=>$eventId, 
      return array( 
		   'type'=>$type, 
		   'subtype'=>$subType,
		   'id'=>$id);
    }

}
?>