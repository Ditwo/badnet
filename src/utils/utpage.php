<?php
/*****************************************************************************
 !   Module     : utils
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utpage.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.32 $
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
require_once "utils.php";
require_once "utdate.php";

/**
 * Cette classe permet de factoriser les propriétés et les méthodes
 * communes aux classes de construction des modèles de pages utilisés
 * dans BadNet. Elle ne doit pas être utilisée directement.
 * Les modèles de pages de base sont 
 * @li kPage_V pour la creation d'une page visiteur
 * @li kPage_A pour la creation d'une page administrateur
 *
 * @author Gerard CANTEGRIL
 *
 */
class utPage
{
	// {{{ properties
	/**
	 * Pointer on kPage object
	 *
	 * @private
	 */
	var $_page=null;

	/**
	 * Pointer on main kDiv object
	 *
	 * @protected
	 */
	var $_container=null;

	/**
	 * Pointer on kDiv object of left column
	 *
	 * @protected
	 */
	var $_leftColumn=null;

	/**
	 * Pointer on kDiv object of right column
	 *
	 * @protected
	 */
	var $_rightColumn=null;


	// }}}

	/**
	 * Pointer on kDiv object of content (central column)
	 *
	 * @private
	 */
	var $_content=null;

	// }}}

	// {{{ setReload()
	/**
	 *
	 */
	function setReload($delay)
	{
		$this->_page->setReload($delay);
	}
	// }}}

	// {{{ close()
	/**
	 * @brief
	 * @~english Clear the cache
	 * @~french  Vide le cache du tournoi courant
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
	function close($clearCache=true, $pageId='', $actId='', $data='')
	{
		if ($clearCache)
		{
	  //echo "appel clearcache implicite";
	  $this->clearCache();
		}
		$this->_page->close(true, $pageId, $actId, $data);
	}
	// }}}

	// {{{ refreshParent()
	/**
	 * @brief
	 * @~english Clear the cache
	 * @~french  Vide le cache du tournoi courant
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
	function refreshParent($pageId='', $actId='', $data='')
	{
		$this->_page->refreshParent($pageId, $actId, $data);
	}
	// }}}


	// {{{ clearCache()
	/**
	 * @brief
	 * @~english Clear the cache
	 * @~french  Vide le cache du tournoi courant
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
	function clearCache($pages='.*', $actions='.*', $datas='.*')
	{
		$eventId = utvars::getEventId();
		$langs = array('fra', 'eng', 'de');

		// Construction de la liste des masques
		$masks = array();
		if (is_array($pages))
		foreach($pages as $page)
		$tmp[] = "{$page}_.*";
		else
		{
	  if (is_array($actions))
	  foreach($actions as $action)
	  $tmp[] = "{$pages}_{$action}_?.*";
	  else
	  if (is_array($datas))
	  foreach($datas as $data)
	  $tmp[] = "{$pages}_{$actions}_{$data}";
	  else
	  $tmp[] = "{$pages}_{$actions}_?{$datas}";
		}

		// Ajout du tournoi et de la langue aux masques
		foreach($langs as $lang)
		{
	  foreach($tmp as $mask)
	  $masks[] = "{$lang}_{$eventId}_{$mask}";
		}

		// Recuperation des fichiers du cache et suppression
		// s'ils correspondent a un des masque
		$dirh = @opendir("../cache");
		if ($dirh === FALSE) return;
		while ($file = @readdir($dirh))
		{
	  foreach($masks as $mask)
	  {
	  	//echo "mask=$mask, file=$file<br>";
	  	if (preg_match("/$mask/", $file))
	  	@unlink("../cache/".$file);
	  }
		}
		closedir($dirh);
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
	function display($cache='')
	{
		if (isset($this->_container))
		{
			 
	  if ( utvars::getTheme() == WBS_THEME_EVENT)
	  {
	  	$url = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
	  	$url .= "?kpid=cnx&kaid=100&eventId=".utvars::getEventId();
	  	$url .= "&pageId=".kform::getPageId();
	  	$url .= "&actionId=".kform::getActId();
	  	$url .= "&langu=".utvars::getLanguage();
	  	if (($data=kform::getData()) != '')
	  	$url .= "&kdata=".kform::getData();
	  	 
	  	$this->_container->addInfo('badnetLink', $url);
	  }

	  $foot =& $this->_container->addDiv('badnetFoot');
	  $ut = new utils();
	  $version = '$Name:  $';
	  if(preg_match("/V[\d]+_[\d]+r[\d]+/",
			$ut->getParam('softVersion', $version),
			$curVersion))
			$version = ereg_replace('_', '.', $curVersion[0]);
			 
	  $version = $ut->getParam('softVersion', $version);
	  if ($version != '$Name:  $')
	  {
	  	$isSquash = $ut->getParam('issquash', false);
	  	if ($isSquash)
	  	{
	  		$kmsg =& $foot->addInfo('badnetVersion', "Squashnet $version");
	  	 $kmsg->setUrl("http://www.squashnet.fr");
	  	}
	  	else
	  	{
	  		$kmsg =& $foot->addInfo('badnetVersion', "BadNet $version");
	  	 $kmsg->setUrl("http://www.badnet.org");
	  	}
	  }
	  $div =& $foot->addDiv('divBadnetCache');
	  $div->addMsg('badnetCache', 'badnetCacheSince');

		}
		if ($cache != '' &&
		utvars::_getSessionVar('userAuth') == WBS_AUTH_VISITOR)
		{
	  $eventId = utvars::getEventId();
	  $lang = utvars::getLanguage();
	  $file = "../cache/{$lang}_{$eventId}_{$cache}";
		}
		else
		$file = '';
		$this->_page->display($file);
	}
	// }}}

	// {{{ getContentDiv()
	/**
	 * @brief
	 * @~english Return the pointer on central colum division
	 * @~french Renvoi le pointeur sur l'objet division de la colonne centrale
	 *
	 * @par Description:
	 * @~french Permet de récuperer le pointeur sur l'objet de type kDiv 
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
		return $this->_content;
	}
	// }}}

	// {{{ getPage()
	/**
	 * @brief
	 * @~english Return a pointer on the page
	 * @~french  Renvoi un  pointer sur la page
	 *
	 * @par Description:
	 * @~french Lorsqu'un utilisateur choisi un tournoi, l'identifiant
	 * du tournoi est mémorisé dans la session courante (evnt_id dans 
	 * la base). Cette méthode permet de récuperer cet identifiant. 
	 *
	 * @~english
	 * See french doc.
	 *
	 * @return @~french (pointer) Pointer sur la page
	 *         @~english (pointer) Pointer on page
	 *
	 */
	function &getPage()
	{
		return $this->_page;
	}
	// }}}


	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @private
	 * @return void
	 */
	function utPage($name, $type='A')
	{
		$this->_page =& utvars::getPage($name);
		$ut = new utils();
		$dt = new utBasePage();

		//      if ($name != 'msg' && $ut->getPref('msgValue') != '')
		//	{
		//	  $action = array(KAF_NEWWIN, 'msg',  KID_MSG, 0, 200, 150);
		// $this->_page->addAction('onload', $action);
		//	}

		// Determination de la langue a utiliser
		$file = "lang/".utvars::getLanguage()."/{$name}_{$type}.inc";
		if (!file_exists($file)) $file = "lang/".utvars::getLanguage()."/$name.inc";
		if (!file_exists($file)) $file = "lang/eng/$name.inc";
		if (file_exists($file)) $this->_page->setStringFile($file);

		// Fichier de style BadNet
		$file = "utils/badnet.css";
		$this->_page->addStyleFile($file);

		$file = "utils/badnet_P.css";
		$this->_page->addStyleFile($file, 'print');

		// Fichier de style du module
		if ($type == 'A')
		{
	  // Fichier de style BadNet
	  $file = "skins/badnet_A.css";
	  $this->_page->addStyleFile($file);


	  $file = "$name/{$name}_A.css";
	   
	  if (file_exists($file))
	  $this->_page->addStyleFile($file);
	   
	  $file = "$name/{$name}_AP.css";
	  if (file_exists($file))
	  $this->_page->addStyleFile($file, 'print');
		}
	}
	// }}}
}
?>
