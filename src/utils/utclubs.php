<?php
/*****************************************************************************
!   Module     : Utilitaires pour les traiter les donnees issues du site FFBA
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utclubs.php,v $
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
require_once "utils/utpage_A.php";


/**
* Classe de base pour la gestion des images
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @author Didier BEUVELOT
* @see to follow
*
*/

class Utclubs
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
  function Utclubs()
    {
      $this->_ut = new utils();
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
      $ut = $this->_ut;

      $cpt2libelle = array( '0' =>"assoStamp",
                            '1' =>"assoDept",
                            '2' =>"assoPseudo",
                            '3' =>"assoName",
                            '4' => "web");
    
      $infos = array();
      $resultats   = array();
      $nb = preg_match_all("[<td>(.*)<\/td>]", $text_in, $res);
//		print_r($res);
		$clubs = $res[0];
    $cpt_clubs = 1 ;
		$cpt = 0;
    for ($i=0; $i < $nb; $i++)
	{
	    $libelle = $cpt2libelle[$cpt++];
      $label_tmp = ereg_replace("<td>","",$clubs[$i]);
      $club[$libelle] = ereg_replace("</td>","",$label_tmp);
//     echo $cpt." ".$club[$cpt]."<br>"    ;
      if($cpt>4){
        $cpt=0;
        
        $club_sortie['idx'] = $cpt_clubs++;
        $club_sortie['assoName'] = $club['assoName'];
        $club_sortie['assoPseudo'] = $club['assoPseudo'];
        $nbstamp = preg_match_all("[<a href=\'annuclub.php3\?req\[id\]=(.*)\'>(.*)<\/a>]", $club['assoStamp'], $stamp);
        $club_sortie['assoStamp'] = $stamp[2][0];
//        $club_sortie['type'] = $ut->getLabel(WBS_CLUB);
        $club_sortie['type'] = WBS_CLUB;
        $resultats[] = $club_sortie;
        
      }
   }
      
//     print_r($resultats);
  return $resultats;
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
      $cible = "http://www.ffba.net/annuclub.php3?";
  
      if (isset($where['sigleville']))
	$cible .= "&req[sigleville]={$where['sigleville']}";


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
  function _contactUrlFede($hides,$where)
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
//      echo "cible=$cible<br/>";

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
      echo "<img alt=\"badnet\" src=\"img/logo/badnet.png\" ";
      echo "width=\"100\" height=\"45\" />\n";
      echo "</a></p>\n";
      echo "Traitement en cours...";
      echo "</body></html>";
      exit();
    }
  // }}}


}
?>