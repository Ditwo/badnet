<?php
/*****************************************************************************
!   Module     : tools
!   File       : $Source: /cvsroot/aotb/badnet/src/tools/tools_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.1 $
!   Author     : G.CANTEGRIL
!   Mailto     : cage@free.fr
!   Revised by : $Author: cage $
!   Date       : $Date: 2005/09/07 20:51:00 $
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
require_once "base_A.php";
require_once "tools.inc";
require_once "utils/utpage_A.php";



/**
* Module de outils
*
* @author Gerard CANTEGRIL <cage@aotb.org>
* @see to follow
*
*/

class Tools_A
{
  
  // {{{ properties
  
  // }}}
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @private
   */
  function Tools_A()
    {
      $this->_dt = new toolsBase_A();
    }
  // }}}
  
  // {{{ start()
  /**
   * Start the connexion processus
   *
   * @public
   * @return void
   */
  function start($action)
    {
      $ut = new Utils();

      switch ($action)
        {
	case TOOLS_INIT_DATABASE:
	  $this->_initDatabase();
	  break;
	case TOOLS_ADD_LANG:
	  $this->_displayFormLang();
	  break;
	case TOOLS_UPDATE_LANG:
	default :
	  $this->_displayFormMain();
	  exit;
	  break;
	}
      exit; 
    }
  // }}}
  
  // {{{ _displayFormMain()
  /**
   * Create the form to display
   *
   * @access private
   * @return void
   */
  function _displayFormMain()
    {
      
      $utPage = new utPage_A('tools', false, 0);
      $content =& $utPage->getContentDiv();

      // Add the menu 
      $items['itInitDatabase']  = array(KAF_UPLOAD, 'tools',  
					TOOLS_INIT_DATABASE);
      $items['itAddLang']  = array(KAF_UPLOAD, 'tools',  
					TOOLS_ADD_LANG);
      $content->addMenu("menuMain", $items, -1);

      $utPage->display();
      exit; 
    }
  // }}}



  // {{{ _displayFormLang()
  /**
   * Display a form to modify the parameters
   *
   * @access public
   * @param integer $userId  Id of the user to modify.
   * @return void
   */
  function _displayFormLang()
    {
      $utpage = new utPage('tools');
      $content =& $utpage->getPage();
      $kform =& $content->addForm('fTools', 'tools', TOOLS_UPDATE_LANG);
      $kform->addMsg('tNewLang');

      // Display informations for parameters congiguration
      $infos = $dt->getParamInfos();
      $kedit =& $kform->addEdit('lang', '',5);
      $kedit->setMaxLength(5);
      $kform->addBlock('blkLang', 'lang');

      $kform->addBtn('btnRegister', KAF_SUBMIT);
      $kform->addBtn('btnCancel');
      $elts = array('btnRegister', 'btnCancel');
      $kform->addBlock('blkBtn', $elts);

      //Display the form
      $utpage->display();
      exit; 
    }
  // }}}

  // {{{ _initDatabase()
  /**
   * Creat table and fill then with files lang content
   *
   * @access private
   * @return void
   */
  function _initDatabase()
    {
      $dt = $this->_dt;

      // Creation des tables
      $dt->createTransTable();

      // Liste des langues
      $handle=opendir('./lang');
      while ($file = readdir($handle))
        {
          if ($file != '.' && $file != '..' && 
	      $file != 'CVS' && $file != 'eng')
	    $res = $dt->addLang($file);
        }
      closedir($handle);

      // Liste des fichiers (a partir de la langue francaise)
      $handle=opendir('./lang/fra');
      while ($file = readdir($handle))
        {
          if ($file != '.' && $file != '..' && 
	      $file != 'CVS')
	    {
	      // Read the file
	      echo "$file<br>";
	      $vars = array();
	      $fd = @fopen("./lang/fra/$file", "r");
	      if ($fd)
		{
		  while (!feof($fd)) 
		    {
		      $buf = fgets($fd, 4096);
		      if ($buf{0} == '$')
			{
			  $var= explode('=', $buf);
			  $vars[] = trim($var[0]);
			}
		    }
		  fclose($fd); 
		}
	      $res = $dt->updateFile($file, $vars);
	      print_r($res);
	    }
	}
      closedir($handle);

      
      $this->_displayFormMain();

    }
  // }}}
}

?>