<?php
/*****************************************************************************
!   Module     : Utilitaires pour les traiter les donnees issues du site FFBA
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utffba.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : G.CANTEGRIL/D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:21 $
!   Mailto     : didier.beuvelot@free.fr/cage@free.fr
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
require_once "const.inc";


/**
* Classe de base pour la gestion des images
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @author Didier BEUVELOT
* @see to follow
*
*/

class Utffba
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
  function Utffba()
    {
    }
  // }}}
  
  // {{{ parse()
  /**
   * Parse le resultat de la requete FFBA
   *
   * @access public
   * @param  string  $text_in  Texte  parser
   * @return void
   */
  function parse($text_in)
    {
      $utd=new utdate();
      $debut = utdate::getMicrotime();
      // Extraction de la date
      $nb = preg_match_all("/<h1>.*(\d+\/\d+\/\d+).*<\/h1>/", $text_in, $date);

      $infos = array();
      $infos[' trsh_ibfNumber'] = 0;
      $infos['trsh_memberId'] = -1;
      $infos['trsh_isFedeS'] = 1;
      $infos['trsh_isFedeD'] = 1;
      $infos['trsh_isFedeM'] = 1;
      if ($nb) $utd->setFrDate($date[1][0], '/');
      else return -1;
      $infos['trsh_dateFedeS'] = $utd->getIsoDateTime();
      $infos['trsh_dateFedeD'] = $infos['trsh_dateFedeS'];
      $infos['trsh_dateFedeM'] = $infos['trsh_dateFedeS'];	 

      // extraction des joueurs
      $nb = preg_match_all("/<tr>(.*)<\/tr>/", $text_in, $players);
      if (!$nb) return 0;
      if ($nb > 500) return -2;
      $res =array();
      for ($i=0; $i < $nb; $i++)
	{
	  $player = $players[1][$i];
	  $nbFields = preg_match_all("/<td[^>]*>(<a[^>]*>)*([^<]*)(<\/a>)*<\/td>/", 
				     $player, $fields);
	  if ($nbFields>=10)
	    {
	      $infos['trsh_sexe']       = $fields[2][0]=='M' ? WBS_MALE:WBS_FEMALE;
	      $infos['trsh_secondName'] = $fields[2][1];
	      $infos['trsh_firstName']  = $fields[2][2];
	      $infos['trsh_licence']    = $fields[2][3];
	      $utd->setFrDate($fields[2][4], '/');
	      $infos['trsh_born']     = $utd->getIsoDateTime();
	      if (preg_match("/[ABCDEFN]{1}.*/", $fields[2][5], $classe))
		$infos['trsh_clSimple'] = $classe[0];
	      else
		$infos['trsh_clSimple'] = '--';
	      
	      if (preg_match("/[ABCDEFN]{1}.*/", $fields[2][6], $classe))
		$infos['trsh_clDouble'] = $classe[0];
	      else
		$infos['trsh_clDouble'] = '--';
	      if (preg_match("/[ABCDEFN]{1}.*/", $fields[2][7], $classe))
		$infos['trsh_clMixed']  = $classe[0];
	      else
		$infos['trsh_clMixed']  = '--';
	      $infos['trsh_averageSimple'] = strtr($fields[2][8],',','.');
	      $infos['trsh_averageDouble'] = strtr($fields[2][9],',','.');
	      $infos['trsh_averageMixed']  = strtr($fields[2][10],',','.');
	      $infos['trsh_club']   = $fields[2][11];
	      $infos['trsh_depart'] = $fields[2][12];
	      $res[] = $infos;
	    }
	}
      $fin = utdate::getMicrotime();
      //echo "Duree:".($fin-$debut)."<br>";
      if (!count($res)) return 0;
      else return ($res);
    }
  
  // }}}

  
  // {{{ _getReq()
  /**
   * Prepare la requete pour la recherche d'un licencie
   *
   * @access public
   * @param  array   $where Crteres de recherche
   * @return void
   */
  function _getReq($where)
    {
      $cible = "http://www.ffba.net/cppp_clt.php3?";
      if (!isset($where['table']))
	$cible .= "table=cppp";
      else
	if ($where['table'] == 1)
	  $cible .= "table=cppp0109";
	else
	  $cible .= "table=cppp";
      
      if (isset($where['isLicencie']) &&
	  $where['isLicencie'] == 1)
	$cible .= "&req[LICN-val]=O";
      
      if (isset($where['name']))
	$cible .= "&req[N-val]={$where['name']}";
      
      if (isset($where['club']))
	{
	  $cible .= "&req[CLUB-val]={$where['club']}";
	  $cible .= "&req[CLUB-wh]=CLUB+like+%27%25s%25%25%27";
	}
      if (isset($where['dept']))
	  $cible .= "&req[DEPT-val]={$where['dept']}";

      if (isset($where['licence']))
	{
	  $cible .= "&req[NU-val]={$where['licence']}";
	  $cible .= "&req[NU-wh]=NU+in+({$where['licence']})";
	}

      if (isset($where['sexe']))
	{
	  if ($where['sexe'] == WBS_MALE)
	    $cible .= "&req[SEXE-val]=M";
	  else if ($where['sexe'] == WBS_FEMALE)
	    $cible .= "&req[SEXE-val]=F";
	}
      $cible .= "&req[SEXE-aff]=1";
      $cible .= "&req[N-aff]=2";
      $cible .= "&req[P-aff]=3";
      $cible .= "&req[NU-aff]=4";
      $cible .= "&req[DNAIS-aff]=5";
      $cible .= "&req[affS-aff]=6";
      $cible .= "&req[affD-aff]=7";
      $cible .= "&req[affM-aff]=8";
      $cible .= "&req[SMN-aff]=9";
      $cible .= "&req[DMN-aff]=10";
      $cible .= "&req[MMN-aff]=11";
      $cible .= "&req[CLUB-aff]=12";
      $cible .= "&req[DEPT-aff]=13";
      $cible .= "&req[N-wh]=N+like+%27%25s%25%25%27";
      $cible .= "&forcer=1";
      return $cible ;
    }
  // }}}

  // {{{ _contactUrlFede()
  /**
   * Search a player in the fede site
   *
   * @access private
   * @return void
   */
  function _contactUrlFede($hides, $where)
    {
      if (!isset($hides['kpid']))
      {
	$err['errMsg'] = "utffba->_contactUrlFede:manque le champ kpid<br/>";
	return $err;
      }
      if (!isset($hides['kaid']))
      {
	$err['errMsg'] = "utffba->_contactUrlFede: manque le champ kaid<br/>";
	return $err;
      }
	  
      $cible = $this->_getReq($where);
      //echo "cible=$cible<br/>";

      // Page html pour lancer la recherche
      echo "<html><head>";
      echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"regi/regi_A.css\" />";
      echo "</head><body  onload=\"document.forms['Url'].submit();\">\n";
      //echo "</head><body>";

      echo "<form id=\"Url\" method=\"post\" style=\"{display:none;}\"";
      if (isset($GLOBALS['PHP_SELF']))
	echo "action=\"$GLOBALS[PHP_SELF]\">\n";
      else
	echo "action=\"".$_SERVER['PHP_SELF']."\">\n";

      foreach($hides as $hide=>$value)
	{
	  echo "<input type=\"hidden\" name=\"$hide\" ";
	  echo "id=\"$hide\" value=\"$value\"  />\n";
	}
      echo '<textarea name="fedeUrl"  id="fedeUrl" >';
      //echo "<textarea  id=\"fedeUrlvis\" cols=\"100\" rows=\"50\">\n";
      include $cible;

      echo "</textarea></form>\n";
      echo "<p><a href=\"http://www.badnet.org\" class=\"kUrl\" >\n";
      echo "<img alt=\"badnet\" src=\"img/logo/badnet.jpg\" ";
      echo "width=\"100\" height=\"45\" />\n";
      echo "</a></p>\n";
      echo "Traitement en cours...";
      echo "</body></html>";
      exit();
    }
  // }}}


}
?>