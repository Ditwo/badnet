<?php
/*****************************************************************************
!   Module     : Utilitaires
!   File       : $Source: /cvsroot/aotb/badnet/src/utils/utfile.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.4 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/05/06 07:59:33 $
******************************************************************************/
require_once "Upload.php";

/**
* Classe de base pour la gestion de l'upload
* @author Gerard CANTEGRIL <cage@free.fr>
* @see to follow
*
*/
class Utfile
{
  // {{{ properties
  /**
   * upload object
   *
   * @var     object
   * @access  private
   */
  var $_upload;
  
  // {{{ constructor
  /**
   * Constructor. 
   *
   * @access public
   * @return void
   */
  function Utfile()
    {
      $this->_upload = new http_upload('fr');
    }
  // }}}
  

  // {{{ upload
  /**
   * upload un fichier sur le serveur
   *
   * @access public
   * @param  string $nom  chemin du fichier � uploader
   * @param  string $destination r�pertoire de destination du fichier
   * @return string chemin du fichier uploader
   */
  function upload($nom, $destination)
    {
      $res['file'] = '';
      $file = $this->_upload->getFiles($nom);

      if ($file->isError()) 
	$res['errMsg'] = $file->getMessage();

      if ($file->isValid()) 
	{
	  $dest_name = $file->moveTo($destination); 
	  if (PEAR::isError($dest_name)) 
	    $res['errMsg'] = $dest_name->getMessage();
	  else
	    {
	      $eventId = utvars::getEventId();
	      $oldName = "{$destination}/{$dest_name}";
	      $newName = "{$destination}/{$eventId}_{$dest_name}";
	      chmod($oldName, 0777);	      
	      rename($oldName, $newName);
	      $res['file'] = "{$eventId}_{$dest_name}";
	    }
	} 
      elseif ($file->isMissing()) 
	{
	  $res['errMsg'] = "No file selected";
    	} 
      elseif ($file->isError()) 
	{
	  $res['errMsg'] = $file->errorMsg();
    	}
      return $res;
    }		
  // }}}
  
}