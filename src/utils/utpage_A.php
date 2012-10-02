<?php
/*****************************************************************************
!   Module     : utils
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utpage_A.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.15 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/10/10 11:38:16 $
!   Mailto     : cage@free.fr
******************************************************************************/
require_once "utpage.php";
require_once "utimg.php";

/**
* Classe de base pour la creation de menu
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class utPage_A extends utPage
{

  // {{{ properties
  
  /**
   * Pointer on kPage object
   *
   * @private
   */
  //  var $_kPage;
  
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function utPage_A($name, $isMenu = false, $select=0)
    {
      $dt = new utBasePage_A();

      utpage::utpage($name, 'A', false, $select);
      $page =& $this->_page;

      // Fichier de style du module
      $file = "$name/{$name}_A.css";

      if (file_exists($file)) $this->_page->addStyleFile($file);
      
      $file = "$name/{$name}_AP.css";
      if (file_exists($file)) $this->_page->addStyleFile($file, 'print');

      // Fichier style de l'administrateur
      $ut = new utils();
      $skin = $ut->getPref('skin', 'base');
      $file = "../skins/$skin/badnet_A.css";
      if (!is_file($file)) $file = "skins/badnet_A.css";
      $this->_page->addStyleFile($file);

      // Fichier style du tournoi
      $eventId = utvars::getEventId();
      if ($eventId != -1) 
	{
	  $ute = new utEvent();
	  $meta = $ute->getMetaEvent($eventId);
	  $skin  = $meta['evmt_skin'];
	  $file = "../skins/$skin/event_A.css";
	  if (!is_file($file))
	    $file = "skins/event_A.css";
	  $this->_page->addStyleFile($file);
	}

      // Entete de la page
      $this->_container =& $page->addDiv('container');
      $this->_head = &$this->_container->addDiv('head');
      $img = utimg::getLogo('badnet.jpg');
      $kimg =& $this->_head->addImg("badnetLogo", $img);
      $url = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
      $kimg->setUrl($url);
      $theme = utvars::getTheme();
      $div =& $this->_head->addDiv('divBadnetTitle');
      if ($theme == WBS_THEME_EVENT) 
	{
	  $utev = new utEvent();      
	  $event = $utev->getEvent(utvars::getEventId());
	  $logo = utimg::getPubliIcon($event['evnt_del'], 
				      $event['evnt_pbl']);
	  $kimg =& $div->addImg('badnetTitle', $logo);
	  $kimg->setText($event['evnt_name']);
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
      $content =& $this->_container->addDiv('content');

      // Affichage de la trame de la page de l'administrateur
      $dt = new utBasePage();
      $trame = $dt->getUserType(WBS_AUTH_VISITOR);
      if ($trame === WBS_AUTH_USER)
	$this->_trame_U($content, $select);
      else
	$this->_trame_A($content, $select);

      if ($eventId != -1) 
	$this->_pageEvent($this->_content, $eventId, $isMenu, $select);
      //else if (utvars::getBookId() != -1) 
      //$this->_pageBook($this->_content, $eventId, $isMenu, $select);     
      else
	$this->_disDispo = &$this->_content;	   	   
    }
  // }}}

  // {{{ _trame_A
  /**
   * Initialize an administrator page: no left and righ cols
   *
   * @private
   * @return void
   */
  function _trame_A(&$content, $select=0)
    {

      $dt = new utBasePage();
      $ut = new utils();
      $page =& $this->_page;

     // Main menu 
      $items['itEvents'] = array(KAF_UPLOAD, 'events', WBS_ACT_EVENTS); 
      $items['itBooks'] = array(KAF_UPLOAD, 'books', WBS_ACT_BOOKS);
      $items['itUsers'] = array(KAF_UPLOAD, 'users',
				 WBS_ACT_USERS);
      $items['itAdmi'] = array(KAF_UPLOAD, 'main', WBS_ACT_PARAMETERS);
      $items['itPref'] = array(KAF_UPLOAD, 'main', WBS_ACT_PREFERENCES);
      //$items['itMail'] = array(KAF_UPLOAD, 'email', WBS_ACT_EMAIL);
      //$items['itSubs'] = array(KAF_UPLOAD, 'subscript', WBS_ACT_SUBS);
      $items['itMaint'] = array(KAF_UPLOAD, 'maint', WBS_ACT_MAINT);
      $items['itPhpInfo'] = array(KAF_NEWWIN, 'help', WBS_ACT_PHPINFO, 0, 500, 400);
      $items['itHelp'] = array(KAF_NEWWIN, 'help', WBS_ACT_HELP, 0, 500, 400);


      //$itemsR['itExit'] = array(KAF_UPLOAD, 'users',  KID_LOGOUT);
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
      $div->addDiv('breakA1', 'blkNewPage');
      $content->addDiv('menuAPad', 'pad');
      $this->_content =& $content->addDiv('divTrame', 'cont1');
    }
  // }}}

  // {{{ _trame_U
  /**
   * Initialize an user page: left column
   *
   * @private
   * @return void
   */
  function _trame_U(&$content, $select=0)
    {

      $dt = new utBasePage();
      $ut = new utils();
      $page =& $this->_page;

     // Main menu 
      $items['itHome']    = array(KAF_UPLOAD, 'users', WBS_ACT_USERS); 
      $items['itEvents']  = array(KAF_UPLOAD, 'events', WBS_ACT_EVENTS); 
      //$items['itBooks'] = array(KAF_UPLOAD, 'books', WBS_ACT_BOOKS);
      //items['itSubs']    = array(KAF_UPLOAD, 'subscript', WBS_ACT_SUBS);
      //$items['itPref']    = array(KAF_UPLOAD, 'main', WBS_ACT_PREFERENCES);
      //$items['itMail']  = array(KAF_UPLOAD, 'email', WBS_ACT_EMAIL);
      //$items['itHelp']    = array(KAF_UPLOAD, 'help', WBS_ACT_HELP);

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

  // {{{ _pageEvent
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function _pageEvent(&$div, $eventId, $isMenu, $select)
    {
      $utev = new utEvent();
      $ut = new utils();
      $event = $utev->getEvent($eventId);
      $extra = $utev->getExtraEvent($eventId);
      if ($isMenu)
	{

	  // Add menu for event 
	  $items = array();

	  $section = utvars::getSectorId();
	  if ($section == WBS_SECTOR_SPORT)
	    {
	      $items['itAttribs']  = array(KAF_UPLOAD, 'events', KID_SELECT);
	      if ($utev->getEventType($eventId)== WBS_EVENT_INDIVIDUAL)
		{
		  $items['itRegister']  = array(KAF_UPLOAD, 'regi', WBS_ACT_REGI); //WBS_ACT_TEAMS);
		  $items['itDraws']    = array(KAF_UPLOAD, 'draws', WBS_ACT_DRAWS);
		  $items['itCalendar']  = array(KAF_UPLOAD, 'schedu', WBS_ACT_SCHEDU);
		  if( !$ut->getParam('limitedUsage') || $extra['evxt_liveupdate'] == WBS_YES)
		  $items['itManage']    = array(KAF_UPLOAD, 'live',  WBS_ACT_LIVE);
		}
	      else
		{
		  $items['itDivisions'] = array(KAF_UPLOAD, 'divs', WBS_ACT_DIVS);
		  $items['itRegister']  = array(KAF_UPLOAD, 'regi', WBS_ACT_REGI);//WBS_ACT_TEAMS);
		  $items['itTies']      = array(KAF_UPLOAD, 'ties', WBS_ACT_TIES);
		  $items['itCalendar']  = array(KAF_UPLOAD, 'schedu', WBS_ACT_SCHEDU);
			
		  if( !$ut->getParam('limitedUsage') || $extra['evxt_liveupdate'] == WBS_YES)
		  $items['itManage']    = array(KAF_UPLOAD, 'live', WBS_ACT_LIVE);
		}	      
	      $items['itTransfert']      = array(KAF_UPLOAD, 'export', WBS_ACT_EXPORT);
	      
	      $itemsR['itAdmin'] = array(KAF_UPLOAD, 'items', WBS_SECTOR_ADMIN);
	    }
	  else
	    {
	      $items['itItems']  = array(KAF_UPLOAD, 'items', WBS_ACT_ITEMS);
	      $items['itTeams']  = array(KAF_UPLOAD, 'teams', WBS_ACT_TEAMS);
	      $items['itEntries']  = array(KAF_UPLOAD, 'regi',  WBS_ACT_REGI);
	      $items['itAccomodation'] = array(KAF_UPLOAD, 'items', WBS_ACT_ACCOMODATION);	      
	      $items['itTransport'] = array(KAF_UPLOAD, 'regi', WBS_ACT_TRANSPORT);	      
	      $items['itAccounts'] = array(KAF_UPLOAD, 'account', WBS_ACT_ACCOUNT);	      
	      $items['itPurchases'] = array(KAF_UPLOAD, 'purchase', WBS_ACT_PURCHASE);	      
	      $items['itBadges'] = array(KAF_UPLOAD, 'badges', WBS_ACT_BADGES);	      
	      $items['itHelp'] = array(KAF_NEWWIN, 'help', WBS_SECTOR_ADMIN, 0, 500, 400);

	      $itemsR['itSport'] = array(KAF_UPLOAD, 'events', WBS_SECTOR_SPORT);
	    }
	  if (is_numeric($select))
	    {
	      $keys = array_keys($items);
	      $sel = $keys[$select];
	    }
	  else
	    $sel = $select;
	  $kdiv =& $div->addDiv('divMenuEvent', 'onglet2');
	  $kdiv->addMenu("menuEvent", $items, $sel);
	  $kdiv->addMenu("menuAdmin", $itemsR, -1);
	  $kdiv->addDiv('breakAE2', 'blkNewPage');
	  $kdiv->addDiv('menuEventPad', 'pad');
	  $this->_disDispo =& $div->addDiv('eventDispo', 'cont2');
	  $this->_divNiv1 =& $div;

	}// End if (menu)
    }
  // }}}


  // {{{ _pageBook
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function _pageBook($page, $bookId, $isMenu)
    {
      $head =&$this->_container->addDiv('head');

      $head->addMsg('leftTitle', '$this->getBookName($themeId)');
      $head->addMsg('rightTitle', 'demo.badnet.org');

      if ($isMenu)
	{
	  // Add standard menu only for administrator. Because user
	  // can be a user with manager right on the event 
	  if (utvars::_getSessionVar('userAuth') == WBS_AUTH_ADMIN)
	    {
	      //'Reload'      => array(KAF_UPDATE, 'main'),
	      $items['itPreference']  = array(KAF_UPLOAD, 'prefs',  
					      WBS_ACT_PREFERENCES);
	      $items['itUsers']       = array(KAF_UPLOAD, 'users',  
					      WBS_ACT_USERS);
	      $items['itEvents']      = array(KAF_UPLOAD, 'events', 
					      WBS_ACT_EVENTS);
	      $items['itAddressBook'] = array(KAF_UPLOAD, 'books',  
					      WBS_ACT_BOOKS);
	      $items['itMaintenance'] = array(KAF_UPLOAD, 'maint',  
					      WBS_ACT_MAINT);
	      $select = 2;
	    }
	  $keys = array_keys($items);
	  $kmenu =& $this->_container->addMenu("menuUser", $items, 
					       $keys[$select]);
	  $itemsR['Home']   = array(KAF_UPLOAD, 'main', KID_HOME);
	  $itemsR[ 'Exit']  = array(KAF_UPLOAD, 'users',  KID_LOGOUT);
	  $this->_container->addMsg("&nbsp;");
	  //$this->_container->addMenu("menuOption", $itemsR, -1);
	  
	}// End if (menu)
      // Content
      $this->_content =& $this->_container->addDiv('content_A');
      
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
       if (isset($this->_divNiv1))
	{
	  // Legend
	  //$kdiv =& $this->_divNiv1->addDiv('blkLegende');
	  //$kdiv->addImg('lgdConfident', utimg::getIcon(WBS_DATA_CONFIDENT));
	  //$kdiv->addImg('lgdPrivate', utimg::getIcon(WBS_DATA_PRIVATE));
	  //$kdiv->addImg('lgdPublic', utimg::getIcon(WBS_DATA_PUBLIC));
	  //$kdiv->addImg('lgdDelete', utimg::getIcon(WBS_DATA_DELETE));
	}
      utpage::display($file);
    }
  // }}}


}

class utBasePage_A extends utbase
{

  // {{{ properties
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
      if (empty($res)) 
	{
	  $err['errMsg'] = __FILE__.' ('.__LINE__.'):base inaccessible';
 	   return $err;
        } 
      $entry = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return $entry;
    }
   // }}}


}
?>