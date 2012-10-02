<?php
/*****************************************************************************
!   Module     : Utilitaires pour les traiter les donnees issues du site FFBA
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utdebug.php,v $
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

class Utdebug
{

  // {{{ properties
  // }}}
  
  var $_nb;
  var $_debug;
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function Utdebug($debug)
    {
      $this->_ut = new utils();
      $this->_nb = 0;
      // 1 => affiche les traces
      // 0 => n'affiche pas les traces
      $this->_debug = $debug;
    }
  // }}}
  
  function change_mode($debug)
    {
      $this->_debug = $debug;
    }

  // {{{ parse()
  /**
   * Parse le resultat de la requete FFBA
   *
   * @access public
   * @param  string  $text_in  Texte  parser
   * @return void
   */
  function debug_show($param,$nom)
    {
      $ut = $this->_ut;
      $nb = $this->_nb;
      $db = $this->_debug;
      
      if($db){
        echo " $nb => $nom : ";
        if(is_array($param)){
          $elt = current($param);
          if(is_array($elt)){
      echo "<br>";
      $this->_nb += 1;
            foreach($param as $idx=>$elt)
              $this->debug_show($elt,$nom.$idx);
          }
          else{
            print_r($param);
            $this->_nb += 1;
            echo "<br>";
          }
        }
        else{
          echo $param;
          $this->_nb += 1;
          echo "<br>";
        }
      }

    }
  // }}}


}
?>