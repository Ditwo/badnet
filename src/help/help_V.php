<?php
/*****************************************************************************
!   Module     : help
!   File       : $Source: /cvsroot/aotb/badnet/src/help/help_A.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.4 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
!   Mailto     : cage@free.fr
******************************************************************************/

require_once dirname(__FILE__)."/../regi/regi.inc";


/**
* Module de gestion du l'aide : classe administrateur
*
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/

class help_V
{

  // {{{ properties
  
  /**
   * Utils objet
   *
   * @var     object
   * @access  private
   */
  var $_ut;
  
  // }}}

  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function help_V()
    {
      $this->_ut = new utils();
    }
  // }}}

  // {{{ start()
  /**
   * Start the connexion processus
   *
   * @access public
   * @param  integer $action  what to do
   * @return void
   */
  function start($page)
    {
      if ($page == WBS_ACT_PHPINFO)
	{
	  phpinfo();
	  exit;
	}
      $lang = utvars::getLanguage();
      $file = "{$page}_$lang.pdf";
      $path = dirname(__FILE__)."/$file";
      if (!file_exists($path))
	$file = "{$page}_fra.pdf";

      $path = dirname(__FILE__)."/$file";
      if (!file_exists($path))
	echo "Rubrique aide :$page ($path)";
      else
	{
	  $url = dirname($_SERVER['PHP_SELF'])."/help/$file";
	  header("Location: $url");
	}
      exit(); 
    }
  // }}}
}
?>