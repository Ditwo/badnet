<?php
/*****************************************************************************
!   Module     : utils
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utpage_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.21 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:21 $
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
require_once "utpage.php";
require_once "utevent.php";

/**
* Cette classe permet d'initialiser une page standard pour un
* visiteur. Elle est constitu�e de cinq parties :
* @li le titre
* @li la barre de navigation horizontale
* @li une colonne � gauche 
* @li une colonne � droite
* @li le corp de la page au centre
* Des m�thodes permettent d'obtenir les pointeurs sur les objets kDiv
* corespondant a chacune des parties. Il est ainsi facile d'y rajouter
* de nouveau objets
*
* @author Gerard CANTEGRIL
*
*/
class utPage_V extends utPage
{

  // {{{ properties
  
  /**
   * Pointer on kDiv object of the title
   *
   * @private
   */
  var $_title=null;
  
  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function utPage_V($name, $menu = false, $select=0)
    {

      utpage::utpage($name, 'V');
      $ut = new utils();
      $page =& $this->_page;

      // Page de connexion
      if ($name == 'cnx')
	{
	  $skin = $ut->getParam('default_skin', 'badnet');
	  $file = "../skins/$skin/home.css";
	  if (!is_file($file))
	    $file = "skins/home.css";
	  $page->addStyleFile($file);
	  $this->_trame_V();
	  $this->_disDispo = &$this->_content;
	  return;
	}

      // Autre page
      $dt = new utBasePage();
      $trame = $dt->getUserType(WBS_AUTH_VISITOR);
      // Fichier style de l'utilisateur
      if ($trame === WBS_AUTH_USER)
	{
	  $skin = $ut->getPref('skin', 'badnet', false);
	  $file = "../skins/$skin/badnet_U.css";
	  if (!is_file($file))
	    $file = "skins/badnet_U.css";
	  $this->_page->addStyleFile($file);
	}

      // Fichier style du tournoi
      $eventId = utvars::getEventId();
      if ($eventId != -1)
	{
	  $ute = new utEvent();
	  $meta = $ute->getMetaEvent($eventId);
	  $skin  = $meta['evmt_skin'];
	  $file = "../skins/$skin/event_{$trame}.css";
	  if (!is_file($file))
	    $file = "skins/event_{$trame}.css";
	  $this->_page->addStyleFile($file);
	}

      // Affichage de la trame de la page de l'utilisateur
      if ($trame === WBS_AUTH_USER)
	$this->_trame_U($select);
      else
	$this->_trame_V();
      
      // Affichage du tournoi
      $theme = utvars::getTheme();
      if ($theme == WBS_THEME_EVENT) 
	$this->_eventPage($this->_content, $select);
      else if ($theme == WBS_THEME_BOOK) 
	$this->_bookPage();
      else
	$this->_disDispo = &$this->_content;      
    }
  // }}}


  // {{{ _trame_V
  /**
   * Initialize an user page: left column
   *
   * @private
   * @return void
   */
  function _trame_V()
    {
      $ut = new utils();
      $page =& $this->_page;

      //Main element
      $this->_container =& $page->addDiv('container');
      $this->_head = &$this->_container->addDiv('head');
	  //$this->_head =& $this->_container;
      $img = utimg::getLogo('badnet.jpg');
      $kimg =& $this->_head->addImg("badnetLogo", $img);
      $url = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
      $kimg->setUrl($url);

      $div =& $this->_head->addDiv('divBadnetTitle');
      $theme = utvars::getTheme();
      if ($theme == WBS_THEME_EVENT) 
	{
	  $utev = new utEvent();      
	  $event = $utev->getEvent(utvars::getEventId());
	  $div->addMsg('badnetTitle', $event['evnt_name']);
	  $div =& $this->_head->addDiv('divBadnetSubTitle');
	  $div->addMsg('badnetSubTitle', $event['evnt_date']);
	}
      else
	{
	  $title = $ut->getParam('mainTitle', 'Installation');
	  $div->addMsg('badnetTitle', $title);
	  $title = $ut->getParam('subTitle');
	  if ($title=='')
	    $title = "&nbsp";
	  $div =& $this->_head->addDiv('divBadnetSubTitle');
	  $div->addMsg('badnetSubTitle', $title);
	}
      $this->_content =& $this->_container->addDiv('content');
    }
  //}}}

  // {{{ _trame_U
  /**
   * Initialize an user page: left column
   *
   * @private
   * @return void
   */
  function _trame_U($select=0)
    {

      $dt = new utBasePage();
      $ut = new utils();
      $page =& $this->_page;
      //Main element
      $this->_container =& $page->addDiv('container');

	  //$this->_head =& $this->_container;
      $this->_head = &$this->_container->addDiv('head');
      $img = utimg::getLogo('badnet.jpg');
      $kimg =& $this->_head->addImg("badnetLogo", $img);
      $url = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
      $kimg->setUrl($url);


      $div =& $this->_head->addDiv('divBadnetTitle');
      $theme = utvars::getTheme();
      if ($theme == WBS_THEME_EVENT) 
	{
	  $utev = new utEvent();      
	  $event = $utev->getEvent(utvars::getEventId());
	  $div->addMsg('badnetTitle', $event['evnt_name']);
	  $div =& $this->_head->addDiv('divBadnetSubTitle');
	  $div->addMsg('badnetSubTitle', $event['evnt_date']);
	}
      else
	{
	  $div->addMsg('badnetTitle', $ut->getParam('mainTitle'));
	  $title = $ut->getParam('subTitle');
	  if ($title=='')
	    $title = "&nbsp";
	  $div =& $this->_head->addDiv('divBadnetSubTitle');
	  $div->addMsg('badnetSubTitle', $title);
	}

      $this->_leftColumn =& $this->_container->addDiv('leftCol');
      $content =& $this->_container->addDiv('content');

     // Main menu 
      $items['itHome']    = array(KAF_UPLOAD, 'users', WBS_ACT_USERS); 
      $items['itEvents']  = array(KAF_UPLOAD, 'events', WBS_ACT_EVENTS); 
      //$items['itBooks'] = array(KAF_UPLOAD, 'books', WBS_ACT_BOOKS);
      //items['itSubs']    = array(KAF_UPLOAD, 'subscript', WBS_ACT_SUBS);
      //$items['itPref']    = array(KAF_UPLOAD, 'main', WBS_ACT_PREFERENCES);
      //$items['itMail']  = array(KAF_UPLOAD, 'email', WBS_ACT_EMAIL);
      $items['Help']    = array(KAF_UPLOAD, 'help', WBS_ACT_HELP);

      //$itemsR['itExit']   = array(KAF_UPLOAD, 'users',  KID_LOGOUT);
      $handle=opendir('./lang');
      while ($file = readdir($handle))
	{
	  if ($file != "." && $file != ".." && $file != "CVS")
	    $itemsR[$file] = array(KAF_NEWWIN, 'users', WBS_NEWLANG,
				   $file, 150, 150);
	}
      closedir($handle);      

      // Select item
      $eventId = utvars::getEventId();
      $selected = 'itEvents';
      if($eventId == -1)
	{
	  if (is_numeric($select) && $select > -1)
	    {
	      $keys = array_keys($items);
	      $selected = $keys[$select];
	    }
	  if (is_string($select))
	    {
	      $selected = $select;
	    }      
	}

      // Adding menu
      $div =& $content->addDiv('divMenu', 'onglet1');
      $div->addMenu("menuUser", $items,  $selected);
      $div->addMenu("menuLang", $itemsR, -1);
      $div->addDiv('breakV1', 'blkNewPage');

      $content->addDiv('menuUPad', 'pad');

      $this->_content =& $content->addDiv('divTrame', 'cont1');
    }
  // }}}

  // {{{ _eventPage()
  /**
   * Add an element to the left column
   *
   * @access public
   * @param object $form   pointer to the form 
   * @param string $elt    element to add to the form
   * @return string name of the form
   */
  function _eventPage(& $mainDiv, $select)
    {
      $eventId = utvars::getEventId();     
      $items['itGeneral']  = array(KAF_UPLOAD, 'events', 
				   KID_SELECT, $eventId);
      $utev = new utEvent();
      if ($utev->getEventType($eventId)== WBS_EVENT_INDIVIDUAL)
	{
	  $items['itRegister'] = array(KAF_UPLOAD, 'regi', 
					WBS_ACT_REGI);
 	  $items['itDraws']    = array(KAF_UPLOAD, 'draws',  
 				       WBS_ACT_DRAWS);
 	  //$items['itSchedule'] = array(KAF_UPLOAD, 'notyet');
 	  $items['itCalendar']  = array(KAF_UPLOAD, 'schedu',
 					WBS_ACT_SCHEDU);
 	  $items['itToday']     = array(KAF_UPLOAD, 'live',
 					WBS_ACT_TODAY);
	}
      else
	{
	  $items['itRegister']  = array(KAF_UPLOAD, 'regi', 
					WBS_ACT_REGI);
 	  $items['itCalendar']  = array(KAF_UPLOAD, 'schedu',
 					WBS_ACT_SCHEDU);
 	  $items['itResults']   = array(KAF_UPLOAD, 'ties',  
 					WBS_ACT_TIES);
 	  $items['itToday']      = array(KAF_UPLOAD, 'live',  
 					WBS_ACT_TODAY);
	}
      $selected = -1;
      if (is_numeric($select) && $select > -1)
	{
	  $keys = array_keys($items);
	  $selected = $keys[$select];
	}
      if (is_string($select))
	{
	  $selected = $select;
	}      

      $divMenu =& $mainDiv->addDiv('divMenuEvent', 'onglet2');
      $divMenu->addMenu('menuEvent', $items,  $selected);
      $itemsR['itEvent']  = array(KAF_UPLOAD, 'main', KID_LOGOUT);
      $divMenu->addMenu('menuAdmin', $itemsR, -1);
      $divMenu->addDiv('break3', 'blkNewPage');
      $mainDiv->addDiv('menuEventPad', 'pad');
      $divFrame =& $mainDiv->addDiv('divEvent', 'cont2');
      $this->_disDispo =& $divFrame;
    }
  // }}}



  // {{{ getContentDiv()
  /**
   * @brief 
   * @~english Return the pointer on central colum division
   * @~french Renvoi le pointeur sur l'objet division de la colonne centrale
   *
   * @par Description:
   * @~french Permet de r�cuperer le pointeur sur l'objet de type kDiv 
   * qui contient le corps de la page.
   *
   * @~english
   * See french doc.
   *
   * @return @~french (pointer) Pointer sur un objet kDiv
   *         @~english kDiv object pointer
   *
   * @~
   * @see kDiv
   */
  function & getContentDiv()
    {
      return $this->_disDispo;
    }
  // }}}


  // {{{ display()
  /**
   * @brief 
   * @~english Display the page
   * @~french  Affiche la page
   *
   * @par Description:
   * @~french Affiche la page. C'est clair non?
   *
   * @~english
   * See french doc.
   *
   * @return @~french Aucun
   *         @~english None
   *
   */
  function display($file='')
    {
      utpage::display($file);
    }
}


class utBasePage extends utbase
{

  // {{{ properties
  // }}}


  // {{{ getUserType
  /**
   * Return general informations about the connected
   *
   * @return array   array of informations
   *
   * @private
   */
  function getUserType($default)
    {
      $id = utvars::getUserId();

      $fields = array('user_type', 'user_login');
      $tables = array('users');
      $where = "user_id= '$id'";
      $res = $this->_select($tables, $fields, $where);
      if (is_null($res) || !$res->numRows())
	return $default;

      $entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return $entry['user_type'];
    }
   // }}}

  // {{{ getUser
  /**
   * Return general informations about the connected
   *
   * @return array   array of informations
   *
   * @private
   */
  function getUser($id)
    {
      $fields = array('user_name', 'user_type');
      $tables = array('users');
      $where = "user_id= $id";
      $res = $this->_select($tables, $fields, $where);
      $entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return $entry;
    }
   // }}}

}

?>