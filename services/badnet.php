<?php
/*****************************************************************************
!   Module     : services
!   File       : $Source: /cvsroot/aotb/badnet/services/badnet.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.8 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/05/19 09:56:24 $
******************************************************************************/
/**
* Serveur SOAP pour acceder aux donnees des tournois
*
* @author Gerard CANTEGRIL
*
*/

require_once "../src/utils/const.inc";
require_once 'nusoap/nusoap.php';

$s = new soap_server;
$s->register('select');
$s->register('updateMatchTeamResult');
$s->register('updateMatchResult');
$s->register('updateMatchStatus');
$s->register('updateDraw');
$s->register('getAdmEventList');
$s->register('getFacteurData');

$s->register('getMatch');


// {{{ getFacteurData
/**
 * Renvoi la liste des donnees de cnonexion pour le facteur
 *
 * @access public
 * @return array   array of data
 */
function getFacteurData($login, $pwd)
{
  require_once "dba.php";
  $db = new dba();

  // Recherche des droits de l'utilisateur
  $where = "user_login='$login'".
    " AND user_pass='$pwd'";
  $type = $db->selectFirst('users', 'user_type', $where);

  // Utilisateur inconnu ou non administrateur: on se casse
  if (is_null($type) || $type != WBS_AUTH_ADMIN)
    {
      $res['errMsg'] = "Utilisateur inconnu ou mot de passe erron�";
      return $res;
    }

  $param['sync_server'] = _getParam($db, 'synchroUrl'); 
  $param['sync_user']   = _getParam($db, 'synchroUser');
  $param['sync_pwd']    = md5(_getParam($db, 'synchroPwd'));
  $param['sync_event']  = _getParam($db, 'synchroEvent');
  $param['live_light']  = _getParam($db, 'isLiveLight', 0);
  $param['ftp_host']    = _getParam($db, 'ftpHost');
  $param['ftp_port']    = _getParam($db, 'ftpPort', 21);
  $param['ftp_user']    = _getParam($db, 'ftpUser');
  $param['ftp_pwd']     = _getParam($db, 'ftpPwd');
  $param['ftp_dir']     = _getParam($db, 'ftpDir');

  unset($db);
  // Recherche des tournois
  return $param;
}
// }}}

// {{{ getFacteurData
/**
 * Renvoi la liste des donnees de cnonexion pour le facteur
 *
 * @access public
 * @return array   array of data
 */
function _getParam($db, $name, $default=null)
{
  $where = "meta_name ='$name'";
  $res = $db->selectFirst('meta', 'meta_value', $where);
  if (is_null($res)) 
    return $default;
  else
    return $res;
}
//}}}

// {{{ getMatch
/**
 * Renvoi le contenu d'un match
 *
 * @access public
 * @return array   array of data
 */
function getMatch($match)
{
  require_once "badnetmatch.php";
  $obj = new badnetMatch();

  // Verifier les autorisations
   $auth = $obj->checkAuth($match['login'], $match['pwd'], 
			   $match['eventId'], $match['mtch_uniId']);
   if( isset($auth['errMsg']))
     return $auth;

   
  // Mise a jour du match
   $res = $obj->getMatch($match['mtch_uniId']); 
   unset($obj);
   return $res;
}
// }}}

// {{{ getEventList
/**
 * Renvoi la liste des tournois autorises pour l'utilisateur 
 *
 * @access public
 * @return array   array of data
 */
function getAdmEventList($login, $pwd, $filtre)
{
  require_once "event.php";
  $obj = new badnetevent();

  // Recherche des tournois
  $res = $obj->getAdmEvents($login, $pwd, $filtre); 
  unset($obj);
  return $res;
}
// }}}

// {{{ updateDraw
/**
 * Met a jour la partie KO d'un tableau
 *
 * @access public
 * @return array   array of data
 */
function updateDraw($draw)
{
  require_once "badnetdraw.php";
  $obj = new badnetdraw();

  // Verifier les autorisations
   $auth = $obj->checkAuth($draw['login'], $draw['pwd'], 
			   $draw['eventId']);
   if( isset($auth['errMsg']))
     return $auth;

  // Mise a jour du tableau
   $res = $obj->updateDraw($draw['eventId'], $draw['rund_uniId'], $draw['pairs']); 
   unset($obj);
   return $res;
}
// }}}


// {{{ updateMatchStatus
/**
 * Met a jour le resultat d'un match
 *
 * @access public
 * @return array   array of data
 */
function updateMatchStatus($match)
{
  require_once "badnetmatch.php";
  $obj = new badnetMatch();

  // Verifier les autorisations
   $auth = $obj->checkAuth($match['login'], $match['pwd'], 
			   $match['eventId'], $match['mtch_uniId']);
   if( isset($auth['errMsg']))
     return $auth;

   
  // Mise a jour du match
   $eventId    = $match['eventId'];
   unset($match['login']);
   unset($match['pwd']);
   unset($match['eventId']);
   $res = $obj->updateMatchStatus($eventId, $match); 
   unset($obj);
   return $res;
}
// }}}


// {{{ updateMatchResult
/**
 * Met a jour le resultat d'un match
 *
 * @access public
 * @return array   array of data
 */
function updateMatchResult($match)
{
  require_once "badnetmatch.php";
  $obj = new badnetMatch();

  // Verifier les autorisations
   $auth = $obj->checkAuth($match['login'], $match['pwd'], 
			   $match['eventId'], $match['mtch_uniId']);
   if( isset($auth['errMsg']))
     return $auth;
   
  // Mise a jour du match
   $winPairId  = $match['winUniId'];
   $loosPairId = $match['loosUniId'];
   $eventId    = $match['eventId'];
   unset($match['login']);
   unset($match['pwd']);
   unset($match['winUniId']);
   unset($match['loosUniId']);
   unset($match['eventId']);
   $res = $obj->updateMatchResult($eventId, $match, $winPairId, $loosPairId); 
   unset($obj);
   return $res;
}
// }}}

// {{{ updateMatchTeamResult
/**
 * Met a jour le resultat d'un match (tournoi par equipe)
 *
 * @access public
 * @return array   array of data
 */
function updateMatchTeamResult($match)
{
  require_once "badnetmatch.php";
  $obj = new badnetMatch();

  // Verifier les autorisations
   $auth = $obj->checkAuth($match['login'], $match['pwd'], 
			   $match['eventId'], $match['mtch_uniId']);
   if( isset($auth['errMsg']))
     return $auth;
   
  // Mise a jour du match
   $eventId    = $match['eventId'];
   unset($match['login']);
   unset($match['pwd']);
   unset($match['winUniId']);
   unset($match['loosUniId']);
   unset($match['eventId']);
   $res = $obj->updateMatchTeamResult($eventId, $match); 
   unset($obj);
   return $res;
}
// }}}


// {{{ select
/**
 * Select data in the database
 *
 * @access public
 * @return array   array of data
 */
function select($tables, $fields, $where=false, $order=false, 
		$publish=null)
{
  // Connexion a la base de donnees
  require_once 'dba.php';
  $db = new dba();
  if ($db->isError())
    {
       $err = $db->getErrorObj();
       return new soap_fault("Serveur", "select", $err->getMessage(), $err->getUserInfo());
    }
  $prefix = $db->getPrefix();
  if (is_array($tables))
    $tablesList = $tables;
  else
    $tablesList[] = $tables;

  // Liste des tables
  foreach($tablesList as $table)
    {
      $ta = preg_replace("/join ([.]*)/i", 
			 "JOIN $prefix$1", $table);
      $i=0;
      while($ta{$i} == '(') $i++;
      if ($i)
	$tableNames[] = substr($ta, 0, $i)."{$prefix}".substr($ta, $i);
      else	      
	$tableNames[] = "{$prefix}{$ta}";
    }
  $tableString = implode(',', $tableNames); 

  // Liste des champs
  if (is_array($fields))
    $fieldsList = $fields; 
  else
    $fieldsList[] = $fields; 

  $fieldString = implode(',', $fieldsList); 

  // Preparation de la requete
  $sql = "SELECT $fieldString FROM $tableString";

  // Limitation des enregistrements en fonction
  // des autorisations
  $limit = false;
  if (!is_null($publish))
    {
      $mask = utvars::getMask();
      if (!is_array($publiTable))
	$filtres[] = $publiTable;
      else
	$filtres = $publiTable;
      foreach($filtres as $filtre)
	$limits[] = "{$filtre}_pbl & {$mask} > 0";
      $limit = implode(' AND ', $limits);
    }
  // Seul les enregistrements publies sont renvoyes
  else
    {
      $exp = "/([a-z]{2,4}_)/";
      $nb = preg_match_all($exp, $fieldString, $lines);
      $limit = implode('pbl = 2 AND ', array_unique($lines[1]));
      if (strlen($limit))
	$limit .= 'pbl = 2';
    }

  // Clause GROUP BY
  $groupby = false;
  if ($where)
    {
      $groupby = stristr($where, 'group by');
      if ($groupby)
	$where = substr($where, 0, strlen($where) - strlen($groupby));	  
    }
  
  // Clause where
  if ($where && $limit)
    $sql .= " WHERE $where AND $limit";
  else if ($where)
    $sql .= " WHERE $where";
  else if ($limit)
    $sql .= " WHERE $limit";

  if ($groupby)
    $sql .= " $groupby ";

  // Classement
  if ($order)
    $sql .= " ORDER BY $order";
  
  // Go
  $res = $db->query($sql);
  if ($db->isError())
    {
      $err = $db->getErrorObj();
      return new soap_fault("Serveur", "select", $err->getMessage(), $err->getUserInfo()."$sql");
    }

  // Traduire les champs 
  $datas = array();
  if($db->numRows($res))
    {
      while ($data = $res->fetch(PDO::FETCH_ASSOC))
	//$datas[] = $this->_getTranslate($data);
	$datas[] = $data;
    }

  $res->free();    
  $db->disconnect();
  return $datas;
}
// }}}

$s->service($HTTP_RAW_POST_DATA);

//$res = getFacteurData('cage', md5('ddv8r986'));
//print_r($res);
?>