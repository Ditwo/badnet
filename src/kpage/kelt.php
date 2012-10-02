<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kelt.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.6 $
!   Mailto     : cage@free.fr
!   Revised by : $Author: cage $
******************************************************************************/
require_once "kbase.php";

/**
* Classe de base pour les elements d'une kform
*
* @author Gerard CANTEGRIL <cage-at-aotb.org>
* @see to follow
*
*/

class kElt extends kBase
{

  // {{{ properties
  
  /**
   * Permissions associate with the element
   *
   * @private
   */
  var $_perms;

  /**
   * Is the element in a form
   *
   * @private
   */
  var $_isInForm = false;
  
  /**
   * Text to display 
   *
   * @private
   */
  var $_text='';

  /**
   * Tabulation index
   *
   * @private
   */
  var $_index=null;
  // }}}

  // {{{ constructor
  /**
   * Constructor. 
   *
   * @param string $module Module a traduire 
   * @param string $language   Langue de traduction
   * @access public
   * @return void
   */
  
  function kelt()
    {
      $this->_type = "none";
      $this->_isActive = false;
      $this->_isInForm = false;
    }
  // }}}

  // {{{ inForm()
  /**
   * The element is in a form
   *
   * @access public
   * @return void
   */
  function inForm()
    { 	
      $this->_isInForm = true;
    }
  // }}}

  // {{{ setPerms()
  /**
   * Set the permissions of the element
   *
   * @access public
   * @param array   $perms  Arrays of permission
   * @return void
   */
  function setPerms($perms)
    { 	
      $this->_perms = $perms;
    }
  // }}}
  
  // {{{ setActions()
  /**
   * Set the actions and the fonction of a button or a rows
   *
   * @access public
   * @param array   $actions  Arrays of the actions
   * @return void
   */
  function setActions($actions)
    { 
      $this->_actions = $actions;
    }
  // }}}
  
  
  // {{{ isMandatory()
  /**
   * Return true if the element is mandatory
   *
   * @access public
   * @return void
   */
  function isMandatory()
    {
      return false;
    }
  // }}}
  
  
  // {{{ getControl()
  /**
   * Return the string for controlling the field
   *
   * @access public
   * @param  string $path Path for the label file
   * @return void
   */
  function getControl($path)
    {
      $this->_path = $path;
      $control="";
      if ($this->isMandatory())
	{
	  $msg = "msg".$this->_name;
	  $msg = $this->_getLabel($msg);
	  $control = "msg['".$this->_name."']='".addslashes($msg)."';\n";
	}
      return $control;
    }
  // }}}

  // {{{ setUrl()
  /**
   * @~english Set the extern url link
   * @~french Permet d'attribuer un lien vers une url externe
   *
   * @par Description:
   * @~french Chaque composant ou �l�ment pr�sent dans la page peut avoir
   * un lien vers une page externe. Cette methode permet de m�moriser 
   * ce lien. Il sera mis en place lors de l'affichage de la page.
   *
   * @~english
   * See french doc.
   *
   * @param  $url (string) @~french Adresse du lien externe 
   * (http://www.domaine.ext/url)
   *         @~english Url link
   * @return @~french Aucun
   *         @~english None
   *
   * @~
   * @see kMsg, kWng....
   * @public
   */
  function setUrl($url)
    {
      $this->_urls = $url;
    }
  // }}}
  
  // {{{ setTabIndex()
  /**
   * set the tabulation index
   *
   * @access public
   * @param string  $class class
   * @return void
   */
  function setTabIndex($index)
    { 	
      $this->_index  = $index;
    }
  // }}}

  // {{{ _getLink()
  /**
   * return the link for the element. 
   *
   * @access public
   * @param array   $actions  Arrays of the actions
   * @return void
   */
  function _getLink($index='', $title='', $class = '')
    { 
      if ($index == '')
	{
	  reset ($this->_actions);
	  $action = each($this->_actions);
	  $index = $action[0];
	}

      if (is_array($index)) $action = $index;
      else if (isset($this->_actions[$index])) $action = $this->_actions[$index];

      if ($action)
	{
	  $fct = reset($action);
	  if ($this->_isInForm || $fct==KAF_NEWWIN ||
	      $fct==KAF_NEWWINURL)
	  //if ($fct==KAF_NEWWIN)
	    {
	      $fct = $this->_getAction($action);
	      $link =  "<a href=\"#\"";
	      if ($class != '')
		$link .=  " class=\"$class\"";
	      if ($title != '')
		$link .=  " title=\"$title\"";
	      $link  .= " onclick=\"return($fct);\" >";
	    }
	  else
	    {
              $link = "<a href=\"{$_SERVER['PHP_SELF']}?";
              if (SID != '')
                $link .= SID.'&amp;';
	      //$val = @array_shift($action);
	      $val = next($action);
	      $link .= "kpid=$val";
	      //$val = @array_shift($action);
	      $val = next($action);
	      $link .= "&amp;kaid=$val";
	      //$val = @array_shift($action);
	      $val = next($action);
	      if ($val != '')
		$link .= "&amp;kdata=$val";
	      $link .= "\"";
	      if ($class != '')
		$link .=  " class=\"$class\"";
	      if ($title != '')
		$link .=  " title=\"$title\"";
	      $link .= ">";
	    }
	  return $link;
	}

      if ($this->_urls != '')
	{
	  $link =  "<a href=\"{$this->_urls}\" class=\"kExtUrl\"";
	  if ($title != '')
	    $link .=  " title=\"$title\"";
	  $link  .= ">";
	  return $link;
	}

      return '';
    }
  // }}}

  // {{{ _getEndLink()
  /**
   * return the endde marker link for the element. 
   *
   * @access public
   * @param array   $actions  Arrays of the actions
   * @return void
   */
  function _getEndLink($index = '')
    {
      $link = '';
      $action = false;
      if ($index == '')
	{
	  $index = reset ($this->_actions);
	  $action = each($this->_actions);
	  $index = $action[0];
	}

      if (is_array($index))
	$action = $index;
      else if (isset($this->_actions[$index]))
	$action = $this->_actions[$index];

      if ($this->_urls != '' || $action)
	$link = "</a>\n";
      return $link;	
    }

  // {{{ _getAction()
  /**
   * return the action for the element. 
   *
   * @access public
   * @return void
   */
  function _getAction($index='')
    { 
      $fct = '';
      $action = false;
      if ($index == '')
	{
	  $index = reset ($this->_actions);
	  $action = each($this->_actions);
	  $index = $action[0];
	}
      if (is_array($index))
	  $action = $index;
      else if (isset($this->_actions[$index]))
	$action = $this->_actions[$index];

      if ($action)
	{
	  // First element of an action array is the name
	  // of the fonction
	  $fct = @array_shift($action);
	  if ($fct != KAF_NONE)
	    {	  
	      // then args are treated 
	      $arg = '';
	      if ($fct == KAF_AJAJ)
		$arg = @array_shift($action).",";
	      $arg .= "'".@implode("','", $action)."'";
	      if ($fct == KAF_NEWWINURL)
		$fct .= "($arg)";     
	      else
		if (defined("SID") && (SID != ''))
		  $fct .= "('" . session_id() . "', this, $arg)";        
		else
		  $fct .= "('noSid',this, $arg)";     
	    }
	  else $fct = '';
	}
      return $fct;
    }
  // }}}

  // {{{ setText()
  /**
   * set the text of the check box
   *
   * @access public
   * @param string  $msg Message
   * @return void
   */
  function setText($msg)
    { 	
      $this->_text  = $msg;
    }
  // }}}

}

?>