<?php
/*****************************************************************************
!   Module     : Utilitaires pour les traiter les donnees issues du site IBF
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utibf.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.1 $
!   Author     : G.CANTEGRIL/D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2005/09/07 20:51:00 $
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
* Classe de base pour la gestion de l'import depuis le site Ibf
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @author Didier BEUVELOT
* @see to follow
*
*/

class Utibf
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
  function Utibf()
    {
    }
  // }}}


  // {{{ parseAdresse()
  /**
   * Parse la page des adresse des fede. Retourne un tableau associatif
   * avec une entre pour chaque fede
   *
   * @access private
   * @param  integer $isDel  deleted flag
   * @param  integer $satus  publication flag
   * @return void
   */
  function parseAdress($text)
    {
      // Extraction des blocs fede
      //$nb = preg_match_all("/<p align=\"left\"><b>(.*)<\/b>/", $text, $fedes);
      //$nb = preg_match_all("/(.*)<b>(.*)<\/b>(.*)/", $text, $fedes);
      //$text="<b>voile le texte</b>dkjjksldjl";
      //$nb = preg_match_all("|<b>(.*)</b>|", $text, $fedes);
      $masque = "|<p align=\"left\"><b>(.*)</b>(.*)<br>Tel:(.*)<br>Fax:(.*)<br>E-[Mm]ail:(.*)</p>|";
      $nb = preg_match_all($masque, $text, $fedes);
      echo "nb=$nb<br>";
      $res =array();
      for ($i=0; $i < $nb; $i++)       
	{
	  $fede  =array();
	  $fede['Nom'] = $fedes[1][$i];
	  $fede['Adress'] = $fedes[2][$i];
	  $fede['Tel'] = $fedes[3][$i];
	  $fede['Fax'] = $fedes[4][$i];
	  if (isset($fedes[5][$i]))
	      $fede['Email'] = $fedes[5][$i];
	  if (isset($fedes[6][$i]))
	      $fede['www'] = $fedes[6][$i];
	  $feds[]=$fede;
	}
      print_r($feds);
      return $feds;
    }

  // }}}


  // {{{ parse()
  /**
   * Parse le resultat de la requete IBF
   *
   * @access public
   * @param  string  $text_in  Texte  parser
   * @return void
   */
  function parse($text_in)
    {
      $utd=new utdate();
      $debut = utdate::getMicrotime();
      // Extraction du genre
      $nb = preg_match("/<b>Gender=<\/b>\"([MF])\"/", $text_in, $res);
      $gender = $res[1];

      $infos = array();
      $infos['trsh_ibfNumber'] = 0;
      $infos['trsh_memberId'] = -1;
      $infos['trsh_isFedeS'] = 1;
      $infos['trsh_isFedeD'] = 1;
      $infos['trsh_isFedeM'] = 1;
      $infos['trsh_dateFedeS'] = $utd->getIsoDateTime();
      $infos['trsh_dateFedeD'] = $infos['trsh_dateFedeS'];
      $infos['trsh_dateFedeM'] = $infos['trsh_dateFedeS'];	 
      $infos['trsh_licence']    = "";
      $infos['trsh_born']     = "";
      $infos['trsh_clSimple'] = '--';	      
      $infos['trsh_clDouble'] = '--';
      $infos['trsh_clMixed']  = '--';
      $infos['trsh_averageSimple'] = 0;
      $infos['trsh_averageDouble'] = 0;
      $infos['trsh_averageMixed']  = 0;
      $infos['trsh_club']   = "";
      $infos['trsh_depart'] = "";
      $infos['trsh_sexe']       = $gender=='M' ? WBS_MALE:WBS_FEMALE;

      // extraction des joueurs
      $nb = preg_match_all("/<td valign=\"top\">.*<b>(.*)<\/b>.*\n.*\n.*\n.*<b>(.*)<\/b>/", 
			   $text_in, $players);

      if (!$nb) return 0;
      if ($nb > 500) return -2;
      $res =array();
      for ($i=0; $i < $nb; $i++)
	{
	  preg_match("/([A-Z '\-]*).*([A-Z]['a-z\-]*)/", $players[1][$i], $tmp);
	  $infos['trsh_secondName'] = $tmp[1];
	  $infos['trsh_firstName']  = $tmp[2];
	  $infos['trsh_ibfNumber'] = $players[2][$i];
	  $res[] = $infos;	  
	}
      // Extraction de la page suivante
      //      $nb = preg_match("/\?firstrec=(.*)&(amp|#180);searchna.*\n.*<b>next/", $text_in, $tmp);
      $nb = preg_match("/\?firstrec=([0-9]*).*\n.*next/", $text_in, $tmp);
      if (isset($tmp[1]))
	{
	  $res['next'] = $tmp[1];
	  $first = $res['next'] -20;
	  $buff = "Traitement des joueurs IBF $first � ".$res['next'];
	  $bufe = "IBF players from $first to ".$res['next'];
	  preg_match("/\?firstrec=([0-9]*).*\n.*last/", $text_in, $tmp);
	  if (isset($tmp[1]))
	    {
	      $buff .= ". Nombre de joueurs � traiter : ".$tmp[1];
	      $bufe .= ". Total players number : ".$tmp[1];
	    }
	  $buff .= "</br>";
	  echo $buff;
	  echo $bufe;
	}
      else
	$res['next'] = 0;

      //      $res['next'] = 0;
      //return $res;
      $fin = utdate::getMicrotime();
      if (!count($res)) return 0;
      else return $res;
    }
  
  // }}}

  // {{{ _getReq()
  /**
   * Prepare la requete pour la recherche d'un licencie
   *
   * @access public
   * @param  array   $where Criteres de recherche
   * @return void
   */
  function _getReq($where)
    {
      $cible = "http://www.ffba.net/cppp_clt.php3?";
      $cible = "http://www.intbadfed.org/Portal/desktopmodules/searchplayer_all.asp?firstrec=";

      $cible .= $where['first'];
      if ($where['ibfNum']!='')
	$cible .= "&searchplayernumber=".$where['ibfNum'];

      if ($where['name']!='')
	$cible .= "&searchlastname=".$where['name'];

      if ($where['noc']!='')
	$cible .= "&searchnation=".$where['noc'];

      $cible .= "&searchmorf=";
      if ($where['sexe'] == WBS_MALE)
	$cible .= "M";
      else
	$cible .= "F";      

      return $cible ;
    }
  // }}}


  // {{{ _contactUrl()
  /**
   * Search a player in the ibf site
   *
   * @access private
   * @return void
   */
  function _contactUrl($hides, $where)
    {
      if (!isset($hides['kpid']))
      {
	$err['errMsg'] = "utibf->_contactUrl:manque le champ kpid<br/>";
	return $err;
      }
      if (!isset($hides['kaid']))
      {
	$err['errMsg'] = "utibf->_contactUrl: manque le champ kaid<br/>";
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
      //echo "<form id=\"Url\" method=\"post\" \"";
      if (isset($GLOBALS['PHP_SELF']))
	echo "action=\"$GLOBALS[PHP_SELF]\">\n";
      else
	echo "action=\"".$_SERVER['PHP_SELF']."\">\n";

      foreach($hides as $hide=>$value)
	{
	  echo "<input type=\"hidden\" name=\"$hide\" ";
	  echo "id=\"$hide\" value=\"$value\"  />\n";
	}
      //echo '<textarea name="ibfUrl"  id="ibfUrl" >';
      echo "<textarea  name=\"ibfUrl\" id=\"ibfUrl\" cols=\"70\" rows=\"50\">\n";
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