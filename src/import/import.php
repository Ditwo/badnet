<?php
/*****************************************************************************
!   Module     : Module pour importer des onnees issues de site WEB
!   File       : $Source: /cvsroot/aotb/badnet/src/import/import.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : G.CANTEGRIL/D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
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
require_once "utils/const.inc";


/**
* Classe de base pour la gestion de l'import depuis un site WEB
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @author Didier BEUVELOT
* @see to follow
*
*/

class Import
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
  function Import()
    {
    }

  // }}}

  // {{{ loadPlayer()
  /**
   * Charge la page avec les joueurs.
   *
   * @access public
   * @param  string  $text_in  Texte  parser
   * @return void
   */
  function loadPlayer($hide, $where=false)
    {
      // Classe vituelle : a coder dans chaque classe
      // $hide contient le champ cache a rajouter pour utilisation 
      // ulterieure
      // $where contient les criteres de recherche des donnees
      // dans la raquete de la page 
    }
  // }}}


  // {{{ parseAdresse()
  /**
   * Parse la page des adresses. Retourne un tableau associatif
   * avec une entre pour chaque adresse
   *
   * @access private
   * @param  integer $isDel  deleted flag
   * @param  integer $satus  publication flag
   * @return void
   */
  function parseAdress()
    {
      // Classe vituelle : a coder dans chaque classe
    }

  // }}}


  // {{{ parsePlayer()
  /**
   * Parse la page avec les joueurs. Retourne un tableau 
   * avec les joueurs
   *
   * @access public
   * @param  string  $text_in  Texte  parser
   * @return void
   */
  function parsePlayer()
    {
      // Classe vituelle : a coder dans chaque classe
    }
  // }}}


  // {{{ parseRank()
  /**
   * Parse la page avec le classement des joueurs. Retourne un tableau 
   * avec les joueurs
   *
   * @access public
   * @param  string  $text_in  Texte  parser
   * @return void
   */
  function parseRank()
    {
      // Classe vituelle : a coder dans chaque classe
    } 
  // }}}

  // {{{ _contactUrl()
  /**
   * Recupere la page du site web dans une balise de type textearea
   *
   * @access private
   * @return void
   */
  function _contactUrl($hides, $cible)
    {
      if (!isset($hides['kpid']))
      {
	$err['errMsg'] = "import.contactUrl:manque le champ kpid<br/>";
	return $err;
      }
      if (!isset($hides['kaid']))
      {
	$err['errMsg'] = "import.contactUrl: manque le champ kaid<br/>";
	return $err;
      }
	  
      // Page html pour lancer la recherche
      echo "<html><head>";
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
      echo "<textarea  name=\"resultUrl\" id=\"resultUrl\" cols=\"70\" rows=\"50\">\n";
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