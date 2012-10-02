<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

require_once 'Bn/Balise.php';


class Body extends Bn_balise
{
	private $_module;

	/**
	 * Constructeur
	 * @param	string	$aModule	Nom du module
	 * @return Bn_balise
	 */
	public function __construct()
	{
		$project = Bn::getProject();
		$module  = Bn::getModule();

		// Fichier de langue
		$locale = Bn::getLocale();
		$fileLocale = "$project/Locale/$locale/" . $project . '.inc';
		if ( file_exists($fileLocale) )	require_once $fileLocale;

		$fileLocale = "$project/Locale/$locale/" . $module . '.inc';
		if ( file_exists($fileLocale) )	require_once $fileLocale;

		$fileLocale = "$project/Locale/$locale/" . 'Commun.inc';
		if ( file_exists($fileLocale) )	require_once $fileLocale;

		$fileLocale = "Badnetlib/Locale/$locale/Commun.inc";
		if ( file_exists($fileLocale) )	require_once $fileLocale;
		
		// Construction de la balise
		parent::__construct('div');
		$this->setAttribute('class', $module);
		$msg = Bn::getUserMsg();
		if ( !empty($msg) ) $p = $this->addP('', $msg, 'bn-error');
	}

	/**
	 * Fonction de fermeture d'un dialoque
	 * @param 	string		$aDialog	nom du dialog
	 * @return void
	 */
	public function close($aDialog = 'dlg')
	{
		$this->addJQReady('$("#' . $aDialog . '").dialog("close");');
		$this->display();
		return false;
	}

	/**
	 * Fonction d'affichage de la page
	 * @param 	integer		$aItem	Item de menu selectionne
	 * @return void
	 */
	public function display($aMenu=false, $aItem=0)
	{
		$project = Bn::getProject();
		$module  = Bn::getModule();

		Bn::displayTrace($this);

		$txt2 = '';
		$debug = '';
		$txt = '';

		// Ajout du java script : Fichier du module
		$fileName = "$project/$project.js";
		if ( file_exists($fileName) )
		{
			$debug .= $fileName . '<br>';
			$txt .= '<script type="text/javascript" src="';
			$txt .= $fileName;
			$txt .= '"></script>';
		}
		$fileName = "$project/$module/$module.js";
		if ( file_exists($fileName) )
		{
			$debug .= $fileName . '<br>';
			$txt .= '<script type="text/javascript" src="';
			$txt .= $fileName;
			$txt .= '"></script>';
		}

		// Ajout du javascript
		$txt .= '<script type="text/javascript">';
		$txt .= '$(document).ready(function(){';
		// Rechargement du menu
		if ( BN::getValue('needMenu', 'true') == true && $aMenu)
		{
			if( $aMenu != BADNETLIB_NO_MENU)
			{
			$txt .= 'var md = $("#targetMenu").metadata();
			if (md.bnAction !=' . $aMenu .'){
		    md.bnAction =' . $aMenu . ';
		    md.item =' . $aItem . ';
		    md.ajax=true;
	 		$("#targetMenu").load("index.php", md, function(){
	 			$("#targetMenu li").removeClass("active");
	 			$("#targetMenu li:eq('. intval($aItem-1) .')").addClass("active");
			});
	 		}
	 		else{
	 		$("#targetMenu li").removeClass("active");
	 		$("#targetMenu li:eq('. intval($aItem-1) .')").addClass("active");
	 		};
	 		$("#targetMenu").show();';
			}
			else $txt .= '$("#targetMenu").hide();';
		}

		//Creation du script de la fonction Ready de JQuery
		$functions = array();
		$this->getJQueryFunctions($functions);
		if (count($this->_jQueryFunction))
		{
			foreach($this->_jQueryFunction as $jq){
				$function[] = $jq;
			}
		}

		if ( count($functions) )
		{
			$ctrlFct = '';
			foreach($functions as $function)
			{
				$ctrlFct .= $function." \n";
			}
			$txt .= $ctrlFct;
		}

		// Fin du script
		$txt .='});</script>';
		//$txt .='</script>';
		// Contenu de la page
		$txt .= $this->toHtml();
		//$txt .= $debug;
		header('Content-type: text/html; charset=utf-8');
		echo $txt;
	}

	/**
	 * Affichage de la legende de publication
	 *
	 * @param object $aDiv    Balise recevant  la legende
	 * @param string $aName   nom de la legende
	 * @return unknown
	 */
	public function addLgdPubli(Bn_balise $aDiv, $aName = 'lgdPubli')
	{
		$theme = Bn::getValue('theme', 'Badnet');
		$lgd = $aDiv->addBalise('ul', $aName);
		$lgd->setAttribute('class', 'bn-lgd');
		$items[] = array(LOC_LGD_CONFIDENT, '8.png');
		$items[] = array(LOC_LGD_PRIVATE, '4.png');
		$items[] = array(LOC_LGD_PUBLIC, '2.png');
		foreach($items as $item)
		{
			$li = $lgd->addBalise('li');
			$li->addImage('', '../Themes/' . $theme . '/Img/' . $item[1], $item[0]);
			$li->addBalise('span', '', $item[0]);
		}
		return true;
	}

	/**
	 * Affichage de la legende des droits sur tournois
	 *
	 * @param Bn_balise $aDiv	Balise recevant la legende
	 * @param sting $aName Nom de la legende
	 * @return void
	 */
	public function addLgdRight(Bn_balise $aDiv, $aName = 'lgdRight')
	{
		$theme = Bn::getValue('theme', 'Badnet');
		$lgd = $aDiv->addBalise('ul', $aName);
		$lgd->setAttribute('class', 'bn-lgd');
		$items[] = array(LOC_LGD_M, 'm.png');
		$items[] = array(LOC_LGD_S, 's.png');
		$items[] = array(LOC_LGD_T, 't.png');
		$items[] = array(LOC_LGD_F, 'f.png');
		$items[] = array(LOC_LGD_G, 'g.png');
		//$items[] = array(LOC_LGD_V, 'v.png');
		foreach($items as $item)
		{
			$li = $lgd->addBalise('li');
			$li->addImage('', '../Themes/' . $theme . '/Img/' . $item[1], $item[0]);
			$li->addBalise('span', '', $item[0]);
		}
		return $lgd;
	}

	/**
	 * Affichage de la legende d'etape
	 *
	 * @param object $aDiv      Balise recevant la legende
	 * @param integer $aNn      nombre d'etape
	 * @param integer $aActive  etape active
	 * 	 * @return unknown
	 */
	public function addLgdStep(Bn_balise $aDiv, $aNb, $aActive, $aTitle)
	{
		$theme = Bn::getValue('theme', 'Badnet');
		$lgd = $aDiv->addBalise('ul');
		$lgd->setAttribute('class', 'bn-step');
		for($i=1; $i<=$aNb; $i++)
		{
			$li = $lgd->addBalise('li', 'step' . $i);
			//$li->addImage('', '../Themes/' . $theme . '/Img/' . $item[1], $item[0]);
			$li->addBalise('span', '', $i);
			if ($i == $aActive) $li->completeAttribute('class', 'active');
		}
		$p = $aDiv->addP('title');
		$p->setAttribute('class', 'bn-step-title');
		$sp = $p->addBalise('span', 'num', $aActive);
		$sp->setAttribute('class', 'bn-step-num');
		$sp = $p->addBalise('span', 'text', $aTitle);
		$sp->setAttribute('class', 'bn-step-text');
		return true;
	}

}
?>