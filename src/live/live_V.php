<?php
/*****************************************************************************
 !   Module     : live
 !   File       : $Source: /cvsroot/aotb/badnet/src/live/live_V.php,v $
 !   Version    : $Name: HEAD $
 !   Revision   : $Revision: 1.17 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/06 07:59:33 $
 ******************************************************************************/

require_once dirname(__FILE__)."/base_V.php";
require_once dirname(__FILE__)."/live.inc";
require_once dirname(__FILE__)."/../utils/utteam.php";
require_once dirname(__FILE__)."/../utils/utround.php";

/**
 * Module de gestion du live : classe visiteurs
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 * @see to follow
 *
 */

class live_V
{

	// {{{ properties

	/**
	 * Utils objet
	 *
	 * @var     object
	 * @access  private
	 */
	var $_ut;

	/**
	 * Database access object
	 *
	 * @var     object
	 * @access  private
	 */
	var $_dt;

	// }}}

	// {{{ constructor
	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	function live_V()
	{
		$this->_ut = new utils();
		$this->_dt = new liveBase_V();
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
		switch ($page)
		{

			case WBS_ACT_TODAY:
			case KID_SELECT:
				$this->_displayListMatch();
				break;

			case WBS_ACT_LIVE:
				$this->_displayLive();
				break;

			case WBS_ACT_STREAM:
				$this->_displayStream();
				break;

			case LIVE_ALONE:
				$this->_displayCourtLive();
				break;

			case LIVE_LAST_RESULTS:
				$this->_displayLastResults();
				break;

			case LIVE_AJAX_COURT_LIVE:
				$this->_ajaxCourtLive();
				break;
			case LIVE_AJAX_LIST_COURT:
				$this->_ajaxListCourt();
				break;
			default:
				echo "live_V->start($page) non autorise <br>";
				exit;
		}
	}
	// }}}

	// {{{ _displayLastResults()
	/**
	 * Display a page with the list of the last ended matchs
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayLastResults()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utt =  new utteam();

		$content =& $this->_displayHead('itLast');
		$kdiv =& $content;

		$khead =& $content->addDiv('headCont3', 'headCont3');
		$kdiv =& $content->addDiv('corp3', 'corpCont3');

		$matchs = $dt->getLastResults();
		if (isset($matchs['errMsg']))
		$kdiv->addWng($matchs['errMsg']);
		else
		{
	  $krow =& $kdiv->addRows("rowsResults", $matchs);
	  $krow->setSort(0);
	  $krow->displaySelect(false);

	  $sizes[10] = '0+';
	  $krow->setSize($sizes);

	  $imgs = array(2 => 8,9,10);
	  $krow->setLogo($imgs);
		}
		$kdiv->addDiv('recentBreak', 'blkNewPage');
		// Reload the page every 15 mn
		$this->_utpage->setReload(15*60);
		$this->_utpage->display();
		exit;
	}
	// }}}


	/**
	 * Display a page with the live
	 */
	function _displayCourtLive()
	{
		// Create a new page
		$utpage = new utPage('live');

		$div =& $utpage->getContentDiv();
		$kdiv =& $div->addDiv("divLive", 'blkLive');

		$link = kform::getData();
		$object=array();
		$object['data'] = $link;
		$object['type'] = 'text/html';
		$object['class'] = 'liveObject';
		$kdiv->addObject("live", $object);
		//echo $link;
		//include $link;
		$utpage->display();
		exit;
	}

	// {{{ _displayStream()
	/**
	* Display a page with the live
	* Version flash pour les france2009
	* @return void
	*/
	function _displayStream()
	{
		$utev = new utEvent();

		// Create a new page
		$div =& $this->_displayHead('itStream');
		$khead =& $div->addDiv('headCont3', 'headCont3');
		$div =& $div->addDiv('corp3', 'corpCont3');

		$meta = $utev->getMetaEvent(utvars::getEventId());
		//if ($meta['evmt_urlStream'] != '')
		{
			$str ='
<OBJECT id="mediaPlayer" width="400" height="370" classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95"
codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701"
standby="Loading Microsoft Windows Media Player components..." type="application/x-oleobject">
<param name="src" value="mms://ne.wms.edgecastcdn.net/200CF4/badminton">
<param name="autoStart" value="true">
<param name="showControls" value="false">
<param name="loop" value="false">
<EMBED type="application/x-mplayer2" pluginspage="http://microsoft.com/windows/mediaplayer/en/download/"
id="mediaPlayer" name="mediaPlayer" showcontrols="false" width="400" height="370"
src="mms://ne.wms.edgecastcdn.net/200CF4/badminton"
autostart="true" loop="false"></EMBED>
</OBJECT>';
			$div->addText($str);
		}
		/*
		 else
		 {
			$div->addWng('msgNoStream');
			}
			*/
		$div->addDiv('videoBreak', 'blkNewPage');

		$this->_utpage->display();
		exit;
	}
	// }}}



	// {{{ _displayStream()
	/**
	 * Display a page with the live
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayStreamBadnet()
	{
		$utev = new utEvent();

		// Create a new page
		$div =& $this->_displayHead('itStream');
		$khead =& $div->addDiv('headCont3', 'headCont3');
		$div =& $div->addDiv('corp3', 'corpCont3');

		$meta = $utev->getMetaEvent(utvars::getEventId());
		//      $div->addWng("Pour des raisons d'importance du traffic, nous ne pouvons diffuser le direct live sur cette page. Utiliser Winamp avec l'adress ci-dessus.");
		//      $div->addWng("Du to the important traffic, we can't diffuse streaming on this page. Use Winamp with the link above.");
		//$info= get_browser();
		if ($meta['evmt_urlStream'] != '')
		{
	  $location1 = $meta['evmt_urlStream'];
	  $size['maxHeight'] = 50;
	  $size['maxWidth'] = 100;
	  $kdiv =& $div->addDiv('leftColunm');
	  $img = utimg::getLogo("vlc.png");
	  $kimg =& $kdiv->addImg("vlc", $img, $size);
	  $kimg->setUrl("http://www.videolan.org");
	  $kdiv->addMsg('msgLink');
	  $kdiv->addMsg($location1);

	  $location2="http://193.84.73.155:8000/flux1.ogg";
	  $kdiv->addMsg($location2);

	  $location =  kform::getInput('urlvideo', $location1);

	  if ($location == $location1)
	  {
	  	$kdiv->addMsg("Camera 1", '', 'titre');
	  	$act[] = array(KAF_UPLOAD, 'live', WBS_ACT_STREAM,
			     "5&urlvideo=$location2");
	  	$msg =& $kdiv->addMsg('Voir camera 2');

	  }
	  else
	  {
	  	$kdiv->addMsg("Camera 2", '', 'titre');
	  	$act[] = array(KAF_UPLOAD, 'live', WBS_ACT_STREAM,
			     "5&urlvideo=$location1");
	  	$msg =& $kdiv->addMsg('Voir camera 1');
	  }
	  $msg->setActions($act);

	  $object=array();
	  $object['width'] = '391'; //'326';
	  $object['height'] = '289'; //'241';
	  $object['align'] = 'absmiddle';
	  $content[] = "<embed type=\"application/x-vlc-plugin\" width=\"400\" height=\"300\" name=\"test\" target=\"$location\"></embed>";
	  $div->addObject("nsvplayx", $object, $content);
	  $div->addDiv("break", "blkNewPage");

	  $location2="http://193.84.73.155:8000/flux1.ogg";
	  $content2[] = "<embed type=\"application/x-vlc-plugin\" width=\"400\" height=\"300\" name=\"test\" target=\"$location2\"></embed>";

	  //$div->addObject("nsvplayx2", $object, $content2);
	  $div->addDiv("break2", "blkNewPage");

		}
		else
		$div->addWng('msgNoStream');
		$div->addDiv('videoBreak', 'blkNewPage');

		$this->_utpage->display();
		exit;
	}

	/**
	 * Affichage d'un terrain ajax
	 */
	function _ajaxCourtLive()
	{
		define ('PROJECT_BADNETRES', 0x040000);
		include_once 'Badnetres/Live/Live.inc';
		$court = $_POST['court'];
		$eventId =  utvars::getEventId();
		$str = '<iframe class="classLiveObject" frameborder="0" src="';

		$link = 'index.php?ajax=1&bnAction='. LIVE_PAGE_SCORE . '&court='. $court . '&eventId=' . $eventId;
		$str .= $link;
		$str .= '"></iframe>';
		echo $str;
		exit;
	}


	/**
	 * Liste des terrains disponibles
	 */
	function _ajaxListCourt()
	{
		require_once dirname(__FILE__)."/../utils/json.php";
		$court = array();
		$eventId = utvars::getEventId();
		$court[0] =  utvars::getEventId();
		for ($i = 1; $i<15; $i++)
		{
			$file = dirname(__FILE__)."/../../Live/$eventId/court$i.html";
			if( file_exists($file) ) $court[$i] = 1;
			else $court[$i] =  0;
		}
		$json = new Services_JSON();
		echo $json->encode($court);
		exit;
	}

	/**
	 * Display a page with the live
	 */
	function _displayLive()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;

		// Create a new page
		$div =& $this->_displayHead('itLive');

		$page = $this->_utpage->getPage();
		$khead =& $div->addDiv('headCont3', 'headCont3');
		$corp3 =& $div->addDiv('corp3', 'corpCont3');
		$div =& $corp3->addDiv('divLive');
		$eventId = utvars::getEventId();
		for ($i=1; $i<6;$i++)
		{
			$link = "http://badnet.org/badnet/src/live/apeLive.php?event=$eventId&court=$i";
			$iframe=array();
			$iframe['src'] = $link;
			$iframe['class'] = 'classBlkLive';
			$iframe['frameborder'] = '0';
			$iframe['scrolling'] = 'no';
			$div->addIframe("blkLive$i", $iframe);
		}
		$this->_utpage->display();
		exit;
	}

	// {{{ _displayListMatch()
	/**
	 * Display a page with the list of the ties
	 * and the field to update the schedule
	 *
	 * @access private
	 * @return void
	 */
	function _displayListMatch()
	{
		$utev = new utEvent();

		$eventId = utvars::getEventId();
		if ($utev->getEventType($eventId)== WBS_EVENT_INDIVIDUAL)
		$this->_displayListMatchIndiv();
		else
		$this->_displayListMatchTeam();
	}
	//}}}


	// {{{ _displayListMatchIndiv()
	/**
	 * Display a page with the list of the Matchs of the day  for individual event
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayListMatchIndiv()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utt =  new utteam();

		$content =& $this->_displayHead('itList');
		$khead =& $content->addDiv('headCont3', 'headCont3');
		$kdiv =& $content->addDiv('corp3', 'corpCont3');
		$matches = $dt->getMatches();
		if (isset($matches['errMsg']))
		$kdiv->addWng($matches['errMsg']);
		else
		{
	  $kform =& $kdiv->addForm('fMatches');

	  $utd = new utdate();
	  $date = '';
	  $place = '';
	  $rows = array();
	  foreach ($matches as $match)
	  {
	  	if ($date != $match['tie_schedule'] ||
	  	$place != $match['tie_place'])
	  	{
	  		$utd->setIsoDateTime($match['tie_schedule']);
	  		$title = $utd->getTime() . ' ' . $match['tie_place'];
	  		$date = $match['tie_schedule'];
	  		$place = $match['tie_place'];
	  		$rows[] = array(KOD_BREAK, "title", $title);
	  	}
	  	$rows[] = $match;
	  }
	  $krow =& $kform->addRows("rowsMatchesV", $rows);
	  $krow->setSort(0);
	  $krow->displayNumber(false);
	  $krow->displaySelect(false);

	  $sizes[8] = '0+';
	  $krow->setSize($sizes);

		}

		// Reload the page every 15 mn
		$kdiv->addDiv('recentBreak', 'blkNewPage');
		$this->_utpage->setReload(15*60);
		$this->_utpage->display();
		exit;
	}
	// }}}


	// {{{ _displayListMatchTeam()
	/**
	 * Display a page with the list of the Matchs of the day
	 *
	 * @access private
	 * @param  integer $sort  Column selected for sort
	 * @return void
	 */
	function _displayListMatchTeam()
	{
		$ut = $this->_ut;
		$dt = $this->_dt;
		$utt =  new utteam();

		$content =& $this->_displayHead('itList');
		$khead =& $content->addDiv('headCont3', 'headCont3');
		$kdiv =& $content->addDiv('corp3', 'corpCont3');

		// List of divisions
		$divs = $utt->getDivs();
		if (isset($divs['errMsg'])) $kdiv->addWng($divs['errMsg']);
		else
		{
			$divSel = kform::getData();
			if ( ($divSel == '' || !isset($divs[$divSel])) && sizeof($divs))
			{
				$first = each($divs);
				$divSel = $first[0];
			}
				
			if(sizeof($divs) > 1)
			{
				$kcombo=& $kdiv->addCombo('selectList', $divs, $divs[$divSel]);
				$acts[1] = array(KAF_UPLOAD, 'live', KID_SELECT);
				$kcombo->setActions($acts);
			}
		}
		$kdiv =& $content->addDiv('contenu');

		$div =& $kdiv->addDiv('lgnd', 'blkLegende');
		$div->addImg('lgdIncomplete', utimg::getIcon(WBS_MATCH_INCOMPLETE));
		//$div->addImg('lgdBusy',       utimg::getIcon(WBS_MATCH_BUSY));
		//$div->addImg('lgdRest',       utimg::getIcon(WBS_MATCH_REST));
		$div->addImg('lgdReady',      utimg::getIcon(WBS_MATCH_READY));
		$div->addImg('lgdLive',       utimg::getIcon(WBS_MATCH_LIVE));
		$div->addImg('lgdClosed',     utimg::getIcon(WBS_MATCH_CLOSED));


		$kdiv =& $kdiv->addDiv('blkLive');
		$ties = $dt->getTies($divSel);
		if (isset($ties['errMsg']))
		$kdiv->addWng($ties['errMsg']);
		else
		{
	  $numTie=0;
	  $titles = array();
	  for ($k=1;$k<12; $k++)
	  $titles[$k] = "rowsMatchs$k";
	  $utd = new utdate();
	  foreach ($ties as $tie)
	  {
	  	$utd->setIsoDateTime($tie['tie_schedule']);
	  	$titles[1] = $tie['rund_name'];
	  	$titles[2] = $tie['team_name'];
	  	$titles[3] = $tie['team_name2'];

	  	/*
	  	 $size['maxHeight'] = 30;
	  	 $kdiv->addWng("tie$numTie", $tie['rund_name']);

	  	 $kdiv2 =& $kdiv->addDiv("divD$numTie", 'titre');
	  	 $kimg =& $kdiv2->addImg("logoD$numTie", $tie['asso_logo'], $size);
	  	 $kimg->setText($utd->getTime().'&nbsp;'.$tie['team_name'].
	  	 '&nbsp;:'.$tie['t2t_scoreW']);

	  	 $kdiv2 =& $kdiv->addDiv("divG$numTie", 'titre');
	  	 $kimg =& $kdiv2->addImg("logoG$numTie", $tie['asso_logo2'], $size);
	  	 $kimg->setText($tie['team_name2'].'&nbsp;:'.$tie['t2t_scoreW2']);
	  	 */
	  	$matchs = $dt->getMatchsTie($tie['tie_id'], $tie['team_id'], $tie['team_id2']);
	  	if (!isset($rows['errMsg']))
	  	{
	  		$title = $utd->getTime(). '&nbsp;'.
	  		$tie['team_name'].'-'.
	  		$tie['team_name2'];
	  		$title .= '&nbsp;&nbsp;'.
	  		$tie['t2t_scoreW'].'-'.
	  		$tie['t2t_scoreW2'];
	  		$break = array(KOD_BREAK, "title", $title);
	  		$rows = array_unshift($matchs, $break);
	  		$krow =& $kdiv->addRows("rowsMatches$numTie", $matchs);
	  		$krow->setSort(0);
	  		$krow->displaySelect(false);
	  		$krow->setTitles($titles);

	  		$sizes[10] = '0+';
	  		$krow->setSize($sizes);

	  		$imgs = array(2 => 11,12,10);
	  		$krow->setLogo($imgs);
	  	}
	  	$numTie++;
	  }
		}
		$kdiv->addDiv("break", "blkNewPage");
		// Reload the page every 15 mn
		$this->_utpage->setReload(15*60);
		$this->_utpage->display();
		exit;
	}
	// }}}

	// {{{ _displayHead()
	/**
	 * Display header of the page
	 *
	 * @access private
	 * @param array team  info of the team
	 * @return void
	 */
	function & _displayHead($select)
	{
		$dt = $this->_dt;

		// Create a new page
		$this->_utpage = new utPage_V('live', true, 'itToday');
		$content =& $this->_utpage->getContentDiv();

		// Add a menu for different action
		$kdiv =& $content->addDiv('choix', 'onglet3');
		$items['itList']   = array(KAF_UPLOAD, 'live', WBS_ACT_TODAY);
		$items['itLive']   = array(KAF_UPLOAD, 'live', WBS_ACT_LIVE);
		$items['itStream'] = array(KAF_UPLOAD, 'live', WBS_ACT_STREAM);
		$items['itLast']   = array(KAF_UPLOAD, 'live', LIVE_LAST_RESULTS);
		$kdiv->addMenu("menuType", $items, $select);
		$kdiv =& $content->addDiv('register', 'cont3');
		return $kdiv;
	}
	// }}}


}
?>
