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

	case LIVE_SCORE:
	  $this->_liveScore();
	  break;

	case LIVE_LAST_RESULTS:
	  $this->_displayLastResults();
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


  // {{{ _liveScoreOld()
  /**
   * Connect to the facteur and the definition of the match
   *
   * @access private
   * @param  integer $sort  Column selected for sort
   * @return void
   */
  function _liveScoreOld()
    {
      $eventId = utvars::getEventId();
      $court   = kform::getData();

      
      echo "<html>
  <head>
    <title>Court $court</title>
  </head>
";
      $ut = new utils();
      $light = $ut->getParam('isLiveLight', '');
      if ($ut->getParam('isLiveLight', ''))
	echo '
  <frameset cols="*,0">
<frame name="court" frameborder="0" noresize="noresize" src="live/courtlight.php">
';
      else
	echo '
  <frameset cols="*,0">
<frame name="court" frameborder="0" noresize="noresize" src="live/court.php">
';

      echo '<frame name="cmd" frameborder="0" noresize="noresize" ';   
      $url = ' src="../live/'.$eventId;
      $url .= '/court'.$court.'.php">';
      echo "$url\n";
      echo '
  </frameset>

  <body>
  </body>
</html>';
      
      exit();
    }
  // }}}

    
  // {{{ _liveScore()
  /**
   * Connect to the facteur and the definition of the match
   *
   * @access private
   * @param  integer $sort  Column selected for sort
   * @return void
   */
  function _liveScore()
    {
      $eventId = utvars::getEventId();
      $court   = kform::getData();
echo 
'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Court' . $court .'</title>
    <link rel="stylesheet" type="text/css" href="skins/live.css" />
    
    <script type="text/javascript" src="live/jquery-pack.js"></script>
    <script type="text/javascript" src="live/jquery.timer.js"></script>
    <script type="text/javascript" src="live/jquery.backgroundPosition.js"></script>
    
<script type="text/javascript">
<!--
      $(document).ready(function (){
         $.timer(15000, reload);
         $(".scorespeed").css({backgroundPosition: "0% 0px"});
         reload();
      });

      function reload()
      {
      var url = "http://www.badnet.org/badnet/live/' . $eventId . '/court' . $court . '.html"
      $.get(url, {terrain:2}, display, "json");
      return false;
      }
      
      function display(aData, aStatus)
       {
         image = "../live/img/" + aData.jpg;
         if (image != $("#jpg").attr("src") )
         	$("#court").hide();
          if (aData.play == 0)
         	$("#court").fadeTo(3000, 0.33);
         else
         	$("#court").fadeTo(3000, 1);
       
         var score = { backgroundPosition: "(" + (aData.sc1g*100)/30 + "% 0%)"};
         $("#sc1g").animate(score);
         var score = { backgroundPosition: "(" + (aData.sc2g*100)/30 + "% 0%)"};
         $("#sc2g").animate(score);
         var score = { backgroundPosition: "(" + (aData.sc3g*100)/30 + "% 0%)"};
         $("#sc3g").animate(score);
         var score = { backgroundPosition: "(" + (aData.sc1d*100)/30 + "% 0%)"};
         $("#sc1d").animate(score);
         var score = { backgroundPosition: "(" + (aData.sc2d*100)/30 + "% 0%)"};
         $("#sc2d").animate(score);
         var score = { backgroundPosition: "(" + (aData.sc3d*100)/30 + "% 0%)"};
         $("#sc3d").animate(score);
         
         refreschImg($("#jpg"), "../live/img/", aData.jpg);
         refreschImg($("#jig"), "../live/img/", aData.jig);
         refreschImg($("#jpd"), "../live/img/", aData.jpd);
         refreschImg($("#jid"), "../live/img/", aData.jid);
         
         refreschImg($("#fpg"), "img/pub/", aData.fpg);
         refreschImg($("#fig"), "img/pub/", aData.fig);
         refreschImg($("#fpd"), "img/pub/", aData.fpd);
         refreschImg($("#fid"), "img/pub/", aData.fid);

         refreschImg($("#spg"), "img/icon/", aData.spg);
         refreschImg($("#sig"), "img/icon/", aData.sig);
         refreschImg($("#spd"), "img/icon/", aData.spd);
         refreschImg($("#sid"), "img/icon/", aData.sid);
         
         refreschImg($("#pro1"), "img/icon/", aData.pro1);
         refreschImg($("#pro2"), "img/icon/", aData.pro2);
         refreschImg($("#pro3"), "img/icon/", aData.pro3);
                  
         refreschImg($("#event"), "img/logo/", aData.event);
         refreschImg($("#stage"), "img/logo/", aData.stage);
         if (image != $("#jpg").attr("src") )
         	$("#court").show(3000);
         
    }
    function refreschImg(aBalise, aPath, aImage)
    {
         if (aImage != "")
         {
           image = aPath + aImage;
           if (image != aBalise.attr("src") )
            aBalise.attr("src", image);
            }
         else   
            aBalise.attr("src", "img/icon/empty.png");
    }
-->  
      </script>
  </head>

  <body>
  <form id="fCmd" style="display:none;">
	<input id="matchEnd" type="hidden" value="0">
	<input id="toDisplay" type="hidden" value="1">
	<input id="msg" type="hidden" value="test">
  </form>
  <div id="court">
  <img id="jig" height="20" width="250" class="player" src="" />
  <img id="jpg" height="20" width="250" class="player" src="" />
  <img id="jpd" height="20" width="250" class="player" src="" />
  <img id="jid" height="20" width="250" class="player" src="" />

  <div id="sc1g" class="scorespeed"></div>
  <div id="sc1d" class="scorespeed"></div>
  <div id="sc2g" class="scorespeed"></div>
  <div id="sc2d" class="scorespeed"></div>
  <div id="sc3g" class="scorespeed"></div>
  <div id="sc3d" class="scorespeed"></div>

  <img id="spg" height="20" width="25" class="server" src="" />
  <img id="sig" height="20" width="25" class="server" src="" />
  <img id="spd" height="20" width="25" class="server" src="" />
  <img id="sid" height="20" width="25" class="server" src="" />

  <img id="fpg" height="20" width="25" class="flag" src="" />
  <img id="fig" height="20" width="25" class="flag" src="" />
  <img id="fpd" height="20" width="25" class="flag" src="" />
  <img id="fid" height="20" width="25" class="flag" src="" />

  <img id="pro1" height="9" width="25" class="prol" src="" />
  <img id="pro2" height="9" width="25" class="prol" src="" />
  <img id="pro3" height="9" width="25" class="prol" src="" />
  </div>
  <img  id="event" src="" />
  <img  id="stage" src="" />
  <a href="http://www.badnet.org">
  <img  id="badnet" src="img/logo/badnetlive.jpg" />
  </a>
  </body>
</html>';
exit;
    }
  // }}}
    
    
    

  // {{{ _displayCourtLive()
  /**
   * Display a page with the live 
   *
   * @access private
   * @param  integer $sort  Column selected for sort
   * @return void
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
  // }}}

  // {{{ _displayStream()
  /**
   * Display a page with the live 
   *
   * @access private
   * @param  integer $sort  Column selected for sort
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
      //      $div->addWng("Pour des raisons d'importance du traffic, nous ne pouvons diffuser le direct live sur cette page. Utiliser Winamp avec l'adress ci-dessus.");
      //      $div->addWng("Du to the important traffic, we can't diffuse streaming on this page. Use Winamp with the link above.");
      //$info= get_browser();
      if ($meta['evmt_urlStream'] != '')
	{
          
	  $location1 = $meta['evmt_urlStream'];
	  $size['maxHeight'] = 50;
	  $size['maxWidth'] = 100;
	  $kdiv =& $div->addDiv('leftColunm');
	  if (ereg("MSIE", $_SERVER["HTTP_USER_AGENT"])) 
	    {
	      $kdiv->addMsg('msgOnlyIE');
	    }
	  else
	    {
	      $kdiv->addMsg('msgVLC');
	    }
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
  // }}}


  // {{{ _displayLive()
  /**
   * Display a page with the live 
   *
   * @access private
   * @param  integer $sort  Column selected for sort
   * @return void
   */
  function _displayLive()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      $utev = new utEvent();

      // Create a new page
      $div =& $this->_displayHead('itLive');
      $khead =& $div->addDiv('headCont3', 'headCont3');
      $div =& $div->addDiv('corp3', 'corpCont3');
      
      $eventId = utvars::geteventId();
      $nbCourt = 0;
      $meta = $utev->getMetaEvent(utvars::getEventId());
      $url = $meta['evmt_urlLiveScore'];
      
      for ($i = 1; $i<15; $i++)
	{
	  $file = dirname(__FILE__)."/../../live/$eventId/court$i.html";
	  //$file = $url."/live/$eventId/court$i.php";
	  //echo $file;
	  //if($handle = @fopen($file, 'r'))
	    {
	     // @fclose($handle);

	      $kdiv =& $div->addDiv("blkLive$i", 'classBlkLive');

	      $kdiv2 = & $kdiv->addDiv("blkInfo$i", 'classBlkInfo');
	      $kmsg =& $kdiv2->addMsg("court$i", $i, "classNumCourt");
	      $kmsg =& $kdiv2->addMsg("win$i", "open", "classOpen");
	      $act = array();
	      $act[] = array(KAF_NEWWIN, 'live',  LIVE_SCORE, 
			     $i, 400, 170);
	      $kmsg->setActions($act);

	      $link = "index.php?kpid=live&kaid=".LIVE_SCORE."&kdata=$i&event=$eventId";
	      $object=array();
	      $object['data'] = $link;
	      $object['type'] = 'text/html';
	      $object['class'] = 'classLiveObject';

	      $kdiv2 = & $kdiv->addDiv("blkCourt$i", 'classBlkCourt');
	      $kdiv2->addObject("live$i", $object);

	      $nbCourt++;
	    }
	}
      if (!$nbCourt)
	$div->addWng('msgNoLiveNow');
      $div->addDiv("break", "blkNewPage");
      $this->_utpage->display();
      exit; 
    }
  // }}}

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
      if (isset($divs['errMsg']))
	$kdiv->addWng($divs['errMsg']);
      else
	{
	  $divSel = kform::getData();
	  if ( ($divSel == '' || !isset($divs[$divSel])) && 
	       sizeof($divs))
	    {
	      $first = each($divs); 
	      $divSel = $first[0]; 
	    }

	  $kcombo=& $kdiv->addCombo('selectList', $divs, $divs[$divSel]);
	  $acts[1] = array(KAF_UPLOAD, 'live', KID_SELECT);
	  $kcombo->setActions($acts);
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
