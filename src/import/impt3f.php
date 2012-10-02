<?php
/*****************************************************************************
!   Module     : Utilitaires pour les traiter les donnees issues du site t3f
!   File       : $Source: /cvsroot/aotb/badnet/src/import/impt3f.php,v $
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
require_once "import.php";

/**
* Classe pour la recuperation de donnes depuis le site t3f
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @author Didier BEUVELOT
* @see to follow
*
*/

define("PLAYERID",   0);
define("LICENSE",    1);
define("FAMNAME",    2);
define("FIRSTNAME",  3);
define("GENDER",     4);
define("LEVELS",     5);
define("LEVELD",     6);
define("LEVELM",     7);
define("DRAWS",      8);
define("DRAWD",      9);
define("DRAWM",      10);
define("IDD",        11);
define("IDM",        12);
define("NAMED",      13);
define("NAMEM",      14);
define("ASSOID",     15);
define("ASSOSTAMP",  16);
define("ASSOPSEUDO", 17);
define("ASSONOC",    18);

define("CTACT_CLUB_ID",      0);
define("CTACT_CLUB_CODE",    1);
define("CTACT_CLUB_CITY",    2);
define("CTACT_CLUB_COUNTRY", 3);
define("CTACT_NAME",         4);
define("CTACT_PHONE1",       5);
define("CTACT_PHONE2",       6);
define("CTACT_EMAIL",        7);


class Impt3f  extends import
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
  function Impt3f()
    {
    }
  // }}}

  // {{{ parseContacts()
  /**
   * Parse le resultat de la requete t3f pour recuperer les joueurs
   *
   * @access public
   * @param  string  $text_in  Texte  parser
   * @return void
   */
  function parseContacts()
    {
      $text_in =  kform::getInputHtml("resultUrl");

      //echo "text_in=$text_in;";
      $utd=new utdate();
      $debut = utdate::getMicrotime();

      $exp = '/.*\n/';
      $nb = preg_match_all($exp, $text_in, $vals);
      $lines = $vals[0];
      unset($lines[0]);
      $contacts=array();
      foreach($lines as $line)
	{
	  $fields = explode(';', $line);
	  // fields[0] = <club_id>;
	  // fields[1] = <club_code>;
	  // fields[2] = <club_city>;
	  // fields[3] = <club_country>;
	  // fields[4] = <name>;
	  // fields[5] = <phone_1>;
	  // fields[6] = <phone_2>;
	  // fields[7] = <email>;
	  $contacts[$fields[0]] = $fields;
	}
      return ($contacts);
    }
  
  // }}}

  // {{{ parsePlayer()
  /**
   * Parse le resultat de la requete t3f pour recuperer les joueurs
   *
   * @access public
   * @param  string  $text_in  Texte  parser
   * @return void
   */
  function parsePlayer()
    {
      $text_in =  kform::getInputHtml("resultUrl");

      //echo "text_in=$text_in;";
      $utd=new utdate();
      $debut = utdate::getMicrotime();

      $exp = '/.*\n/';
      $nb = preg_match_all($exp, $text_in, $vals);
      $lines = $vals[0];
      unset($lines[0]);
      $players=array();
      foreach($lines as $line)
	{
	  $fields = explode(';', $line);
	  // fields[0] = <player_id>;
	  // fields[1] = <fr_license>;
	  // fields[2] = <family_name>;
	  // fields[3] = <first_name>;
	  // fields[4] = <sex>;
	  // fields[5] = <level_single>;
	  // fields[6] = <level_double>;
	  // fields[7] = <level_mixed>;
	  // fields[8] = <serie_single>;
	  // fields[9] = <serie_double>;
	  // fields[10] = <serie_mixed>;
	  // fields[11] = <partner_double>;
	  // fields[12] = <partner_mixed>;
	  // fields[13] = <partner_double_name>;
	  // fields[14] = <partner_mixed_name>;
	  // fields[15] = <club_id>;
	  // fields[16] = <club_code>;
	  // fields[17] = <club_city>;
	  // fields[18] = <club_country>
	  $players[$fields[PLAYERID]] = $fields;
	}
      return ($players);
    }
  
  // }}}

  
  // {{{ loadFile()
  /**
   * Prepare la requete pour la recherche d'un licencie
   *
   * @access public
   * @param  array   $where Crteres de recherche
   * @return void
   */
  function loadFile($hides, $where=false)
    {
      if ($where==false)
	$cible = "http://www.v3f-badminton.org/template/tournois/csv.php";
      else
	$cible=$where;
      $this->_contactUrl($hides, $cible);
    }
  // }}}

}
?>