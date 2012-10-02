<?php
/*****************************************************************************
 !   Module     : Utilitaires
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/utimg.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.9 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/
require_once "const.inc";


/**
 * Classe de base pour la gestion des images
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class Utimg
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
	function Utimg()
	{
	}
	// }}}

	// {{{ getIcon()
	/**
	 * return the correct path for an icon
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function getIcon($iconId)
	{
		switch ($iconId)
		{
			case WBS_DATA_DELETE :
				$img = "img/icon/data-delete.png";
				break;
			case WBS_DATA_PUBLIC :
				$img = "img/icon/data-visible.png";
				break;
			case WBS_DATA_PRIVATE :
				$img = "img/icon/data-hidden.png";
				break;
			case WBS_DATA_CONFIDENT :
				$img = "img/icon/data-private.png";
				break;
			default :
				$img = 'img/icon/'.strtolower($iconId).'.png';
				if (! file_exists($img))
				$img = 'img/icon/'.strtolower($iconId).'.gif';
				if (! file_exists($img))
				$img = '';
				break;
		}
		return $img;
	}
	// }}}

	// {{{ getPubliIcon()
	/**
	 * return the icon path  for the publication statut
	 *
	 * @access public
	 * @param  integer $isDel  deleted flag
	 * @param  integer $satus  publication flag
	 * @return void
	 */
	function getPubliIcon($isDel, $status)
	{
		if ($isDel == WBS_DATA_DELETE)
		$img = utimg::getIcon($isDel);
		else
		$img = utimg::getIcon($status);
		return $img;
	}
	// }}}

	// {{{ getLogo()
	/**
	 * return the correct path for a logo
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function getLogo($logo)
	{
		$img = "img/logo/$logo";
		return $img;
	}
	// }}}

	// {{{ getPathPoster()
	/**
	 * return the correct path for a poster
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function getPathPoster($img='')
	{
		if(substr($img, 0, 7) == "http://")
		$path = $img;
		else
		{
	  $path = "../img/poster";
	  if ($img != '')
	  {
	  	$path .= "/$img";
	  	if (!@getImagesize($path))
	  	{
	  		@unlink($path);
	  		return false;
	  	}
	  }
		}
		return $path;
	}
	// }}}

	// {{{ getPathFlag()
	/**
	 * return the correct path for the logo of an association
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function getPathFlag($img='')
	{
		$path = "../img/logo_asso";
		if ($img != '')
		{
			if (substr($img, 0,5) != 'http:')
			{
				$path = "img/pub/$img";
				if (! @getImagesize($path))
				{
					@unlink($path);
					$path = "../img/logo_asso/$img";
				}
				if (! @getImagesize($path))
				{
					@unlink($path);
					$path = false;
				}
			}
			else $path = $img;
		}
		return $path;
	}
	// }}}

	// {{{ getPathPhoto()
	/**
	 * return the correct path for a member's picture
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function getPathPhoto($photo='')
	{
		if(substr($photo, 0, 7) == "http://") $path = $photo;
		else
		{
	  		$path = "../img/photo_mber";
	  		if ($photo != '')
	  		{
	  			$path .= "/$photo";
	  			if (! @getImagesize($path))
	  			{
	  				@unlink($path);
	  				$path = false;
	  			}
	  		}
		}
		return $path;
	}
	// }}}

	// {{{ getPathTeamPhoto()
	/**
	 * return the correct path for a member's picture
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function getPathTeamPhoto($photo='')
	{
		if(substr($photo, 0, 7) == "http://") $path = $photo;
		else
		{
	  		$path = "../img/photo";
	  		if ($photo != '')
	  		{
	  			$path .= "/$photo";
	  			if (! @getImagesize($path))
	  			{
	  				@unlink($path);
	  				$path = false;
	  			}
	  		}
		}
		return $path;
	}
	// }}}

	// {{{ getPathTeamLogo()
	/**
	 * return the correct path for a team's logo
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function getPathTeamLogo($logo='')
	{
		$path = "../img/logo";
		if ($logo != '')
		{
			if (substr($logo, 0,5) == 'http:')
			{
				$path = $logo;
			}
			else
			{
				$path = "img/pub/$logo";
				if (! @getImagesize($path))
				{
					@unlink($path);
					$path = "../img/logo/$logo";
				}
				if (! @getImagesize($path))
				{
					@unlink($path);
					$path = false;
				}
			}
		}
		return $path;
	}
	// }}}

	// {{{ selectAssoLogo()
	/**
	 * Display a page with the avaible images in img/logo
	 * and src/img/pub
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function selectAssoLogo($page, $action, $hides=array())
	{
		$path['dir'] = "../img/logo_asso";
		$path['pref'] = "";
		$listDir[] = $path;

		$path['dir'] = "img/pub";
		$path['pref'] = "";
		$listDir[] = $path;
		utimg::_selectImg($listDir, $page, $action, $hides);
	}
	// }}}

	// {{{ selectTeamLogo()
	/**
	 * Display a page with the avaible images in img/logo
	 * and img/<eventId>/logo
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function selectTeamLogo($page, $action, $hides=array())
	{
		$path['dir'] = "../img/logo";
		$path['pref'] = utvars::getEventId()."_";
		$listDir[] = $path;

		$path['dir'] = "../img/asso";
		$path['pref'] = "";
		$listDir[] = $path;

		$path['dir'] = "img/pub";
		$path['pref'] = "";
		$listDir[] = $path;

		utimg::_selectImg($listDir, $page, $action, $hides);
	}
	// }}}

	// {{{ selectTeamPhoto()
	/**
	 * Display a page with the avaible images in
	 * and img/<eventId>/photo
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function selectTeamPhoto($page, $action, $hides=array())
	{
		$path['dir'] = "../img/photo";
		$path['pref'] = utvars::getEventId()."_";
		$listDir[] = $path;

		utimg::_selectImg($listDir, $page, $action, $hides);
	}
	// }}}

	// {{{ selectMberPhoto()
	/**
	 * Display a page with the avaible images in
	 * and img/photo
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function selectMberPhoto($page, $action, $hides=array())
	{
		$path['dir'] = "../img/photo_mber";
		$path['pref'] = "";
		$listDir[] = $path;

		utimg::_selectImg($listDir, $page, $action, $hides);
	}
	// }}}


	// {{{ selectPoster()
	/**
	 * select an image
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function selectPoster($page, $action, $hides=array())
	{
		$path['dir'] = "../img/poster";
		$path['pref'] = utvars::getEventId()."_";
		$listDir[] = $path;

		utimg::_selectImg($listDir, $page, $action, $hides);
	}
	// }}}

	// {{{ _selectImg()
	/**
	 * select an image
	 *
	 * @access public
	 * @param  integer $iconId  Id of the icon
	 * @return void
	 */
	function _selectImg($listDir, $page, $action, $hides=array())
	{

		$utpage = new utPage($page);
		$content =& $utpage->getPage();

		$div =& $content->addDiv('main');
		$form =& $div->addForm('fImage', $page, $action);
		foreach($hides as $hide=>$value)
		$form->addHide($hide, $value);

		$kedit =& $form->addFile('image', '', 30);
		$kedit->setMaxLength(200);
		$kedit->noMandatory();

		$form->addBtn('btnRegister', KAF_SUBMIT);
		$form->addBtn('btnCancel');
		$elts = array('btnRegister', 'btnCancel');
		$form->addBlock('blkBtn', $elts);
		$form->addMsg("msgUseImage");
		$form->addWng("msgLimitedImage");

		$i=0;
		$j=0;
		while (count($listDir))
		{
	  $dir = array_shift($listDir);
	  $start = strlen($dir['dir'])+1;
	  $subList = array();
	  $subList[] = $dir['dir'];
	  $prefixe = $dir['pref'];
	  //echo "start=$start<br>";
	  //print_r($subList);
	  // Boucle sur les dossiers
	  while (count($subList))
	  {
	  	$dir = array_shift($subList);
	  	$handle=@opendir($dir);
	  	if ($handle)
	  	{
	  		$files = array();
	  		// Lecture des entree du sossier
	  		while ($file = readdir($handle))
	  		{
	  			// Stockage des fichiers images
	  			if (@getImagesize("$dir/$file") &&
	  			substr($file, 0, strlen($prefixe)) == $prefixe)
	  			$files[]=$file;
	  			// Stockage des dossiers
	  			else if ($file!="." && $file != ".." &&
	  			$file != "CVS" && is_dir("$dir/$file"))
	  			$subList[] = "$dir/$file";
	  		}
	  		closedir($handle);
	  		// S'il y a des fichiers, les classer et
	  		// afficher le nom du dossier
	  		if (count($files))
	  		{
	  			$form->addDiv("page$j", 'blkNewPage');
	  			$div =& $form->addDiv("divDir$j", 'blkListImg');
	  			$titre = substr($dir, $start);
	  			if ($titre == '')
	  			$titre="autres";
	  			$div->addMsg("tDivDir$j", $titre, 'titre');
	  			sort($files);
	  		}
	  		// Afficher les images
	  		foreach($files as $file)
	  		{
	  			$kdiv =& $div->addDiv("divImg$i", 'blkImg');
	  			$img = "$dir/$file";
	  			$size['maxHeight'] = 50;
	  			$size['maxWidth'] = 100;
	  			$kimg =& $kdiv->addMsg("nameImg$i", $file);
	  			$kimg =& $kdiv->addImg("img$i", $img, $size);
	  			$kimg->inForm();
	  			$path = substr("$dir/$file", $start);
	  			$act[0] = array(KAF_UPLOAD, $page, $action, $path);
	  			$kimg->setActions($act);
	  			$i++;
	  		}
	  		$j++;
	  	}
	  }
		}
		$utpage->display();
		exit;
	}
	// }}}
}
?>