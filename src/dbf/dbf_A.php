<?php
/*****************************************************************************
 !   Module     : Export dbf
 !   File       : $Source: /cvsroot/aotb/badnet/src/dbf/dbf_A.php,v $
 !   Version    : $Name: HEAD $
 !   Revision   : $Revision: 1.27 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/03/28 21:43:16 $
 ******************************************************************************/
require_once "dbf.inc";
require_once "base_A.php";
//require_once "matches/matches.inc";
require_once "utils/utpage_A.php";
require_once "pdf/pdfbase.php";
require_once "export/export.inc";

/**
 * Module d'export des resultats au format dbf
 *
 */
define("RESULT_FILENAME",    "results");

define("ARCHIVE_PATH",    realpath('../archive/').'/');
define("EXPORT_PATH",     	realpath('../export/').'/');

class dbf_A
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
   function dbf_A()
   {
      $this->_ut = new Utils();
      $this->_dt = new dbfBase();
      $this->_files = array();
   }
   // }}}

   function stripAccent($aStr)
	{
		return strtr($aStr,
 "\xe1\xc1\xe0\xc0\xe2\xc2\xe4\xc4\xe3\xc3\xe5\xc5".
 "\xaa\xe7\xc7\xe9\xc9\xe8\xc8\xea\xca\xeb\xcb\xed".
 "\xcd\xec\xcc\xee\xce\xef\xcf\xf1\xd1\xf3\xd3\xf2".
 "\xd2\xf4\xd4\xf6\xd6\xf5\xd5\x8\xd8\xba\xf0\xfa\xda".
 "\xf9\xd9\xfb\xdb\xfc\xdc\xfd\xdd\xff\xe6\xc6\xdf\xf8\xb0\x27\x2F\x26?",
 "aAaAaAaAaAaAacCeEeEeEeEiIiIiIiInNoOoOoOoOoOoOoouUuUuUuUyYyaAso  - -");
		//return strtr($aStr,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
//'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
		
	}
   
   
   // {{{ start()
   /**
    * Start the connexion processus
    *
    * @access public
    * @return void
    */
   function start($page)
   {
      switch ($page)
      {
         case WBS_ACT_DBF:
            $this->display();
            break;

         case DBF_WRITE:
            $this->prepareFile();
            break;

         case DBF_SEND:
            $this->_sendFile();
            break;

         case DBF_ARCHIVES:
            $this->displayArchive();
            break;

         case DBF_ARCHIVE_FILE:
            $this->_archiveFile('msgFileArchived');
            break;

         default :
            echo "dbf_A : page $page not allowed";
            break;
      }
      exit;
   }
   // }}}

   // {{{ display()
   /**
    * display the form to prepare the generation of the file
    *
    * @access public
    * @param string $err Error message
    * @return void
    */
   function display($err="")
   {
      $dt = $this->_dt;
      $date = new utdate();

      // Get event data
      $event = $dt->getEventDef();

      // For a team event, we need the geographic zone
      if ($event['evnt_type'] == "EQUIP")
      $this->displaySelectTies($event);
      else
      $this->prepareFile($event);
   }
   // }}}

   // {{{ prepareFile()
   /**
    * Prepare the file to be send
    *
    * @access public
    * @param string $err Error message
    * @return void
    */
   function prepareFile($event=NULL)
   {
      // Get event data
      if (is_null($event))
      $event = $this->_dt->getEventDef();

      // Construct zip file name
      $fileDir = EXPORT_PATH;
      $i=1;
      $zipFileName =
      $fileDir."{$event["evnt_id"]}_{$event["evnt_type"]}_$i.zip";
      while (file_exists($zipFileName))
      {
         $i++;
         $zipFileName =
         $fileDir."{$event["evnt_id"]}_{$event["evnt_type"]}_$i.zip";
      }

      $userId = utvars::getUserId();

      // Write the definition of the ties
      if ($event['evnt_type']== "EQUIP")
      {
         $zone = kform::getInput("evnt_zone");
         if ($zone == "")
         $zone = $event['evnt_zone'];
         if ($zone == "")
         $this->display("msgevnt_zone");
         $this->_dt->setEventZone($zone);
         $event['evnt_zone'] = $zone;
      }

      // Write the results of the match in the file
      $errs = $this->_writeResults($event);
      if( $errs['is_error'] )
      {
         unset($errs['files']);
         unset($errs['is_error']);
         unset($errs['firstday']);
         $this->displayErrors($errs);
      }
      // Generate Zip file
      require_once "pclzip/pclzip.lib.php";
      $zip = new pclZip($zipFileName);
      $userId = utvars::getUserId();
      $zip->create($this->_files, PCLZIP_OPT_REMOVE_ALL_PATH);

      // Display page for email
      //if ( count($this->_files) > 1 )
      //$this->displayExport($event, $zipFileName, $errs['firstday']);
      //else
      //$this->displayExport($event, $this->_files[0], $errs['firstday']);
      $this->displayExport($event, $this->_files, $errs['firstday']);
   }
   // }}}

   // {{{ displaySelectTies
   /**
    * display the form to prepare the generation of the file
    * for a team event
    *
    * @access public
    * @param string $err Error message
    * @return void
    */
   function displaySelectTies(&$event)
   {
      $dt = $this->_dt;
      $date = new utdate();

      // Create the page
      $content =& $this->_displayHead('itExport');

      $kform =& $content->addForm('fdbf', 'dbf', DBF_WRITE);
      $kform->addMsg("tExportFede");

      // We need the geographic zone
      $kform->addMsg("msgDescZone");
      if ($event['evnt_level']=== 'DEP')
      {
         $lst = utvars::getDepartements();
         if (isset($lst[$event['evnt_zone']]))
         $slt = $lst[$event['evnt_zone']];
         else
         $slt = each($lst);
         $kform->addCombo("evnt_zone", $lst, $slt);
      }
      else if ($event['evnt_level']=== 'REG')
      {
         $lst = utvars::getRegions();
         if (isset($lst[$event['evnt_zone']]))
         $slt =$lst[$event['evnt_zone']];
         else
         $slt = each($lst);
         $kform->addCombo("evnt_zone", $lst, $slt);
      }
      else
      {
         $kedit =& $kform->addEdit("evnt_zone",   $event['evnt_zone']);
         $kedit->setLength(10);
      }
      $kform->addBlock("blkevnt_zone","evnt_zone");

      $ties = $dt->getScheduledTies();
      // Begin and end date for transfert
      $begin = kform::getInput("beginDate");
      if ($begin == "")
      {
         foreach($ties as $tie)
         {
            if ($tie['tie_schedule'] != "")
            {
               $begin = $tie['tie_schedule'];
               break;
            }
         }
      }
      $end = kform::getInput("endDate");
      if ($end == "")
      $end = $date->getDate();
      $kedit =& $kform->addEdit("beginDate", $begin, 10);
      $kedit->setMaxLength(10);
      $kform->addBlock("blkbeginDate", "beginDate");

      $kedit =& $kform->addEdit("endDate", $end, 10);
      $kedit->setMaxLength(10);
      $kform->addBlock("blkendDate", "endDate");

      $kform->addCheck("exportAll", kform::getInput("exportAll"));
      $kform->addBlock("blkexportAll", "exportAll");

      // Add the button
      $kform->addBtn("btnWrite", KAF_SUBMIT);

      $elts=array('msgDescZone', 'blkevnt_zone', 'blkbeginDate',
		  'blkendDate', 'btnWrite', 'blkexportAll');
      $kform->addBlock('blkInfo', $elts);

      $krow =& $kform->addRows("listTies", $ties);
      $krow->setSort(0);

      $actions = array( 1 => array(KAF_UPLOAD, 'ties',
      KID_SELECT));
      $krow->setActions($actions);

      $this->_utpage->display();
      exit;
   }
   // }}}

   // {{{ displayErrors()
   /**
    * display the form with the results of the match export
    *
    * @access public
    * @param  string $err error message
    * @return void
    */
   function displayErrors($errs)
   {
      $dt = $this->_dt;

      // Create the page
      $content =& $this->_displayHead('itExport');
      $kdiv =& $content->addDiv('info');
      $kdiv->addMsg("tExportFede");

      $kform =& $content->addForm('fdbf', 'dbf', DBF_SEND);
      $kform->addHide("beginDate", kform::getInput("beginDate"));
      $kform->addHide("endDate", kform::getInput("endDate"));
      $kform->addHide("exportAll", kform::getInput("exportAll"));
      //      foreach($ties as $tie)
      //	$kform->addHide("listTies[]", $tie);

      $kpage = $this->_utpage->getPage();
      //@unlink($this->_fileName);
      $kform->addWng("errBadMatch");
      foreach($errs as $id => $err)
      {
         $err[4] = $kpage->getLabel($err[4]);
         $errs[$id] = $err;
      }
      $krow =& $kform->addRows("errs", $errs);
      $actions[1] = array(KAF_NEWWIN, 'matches', KID_EDIT,
      0, 550,350);
      $krow->setActions($actions);
      $krow->setSort(0);
      $krow->displaySelect(false);
      $kform->addBtn("btnSelect", KAF_UPLOAD,'dbf', WBS_ACT_DBF);
      $this->_utpage->display();
      exit;
   }
   // }}}

   // {{{ displayExport()
   /**
    * display the form to mail the file
    *
    * @access public
    * @param  string $err error message
    * @return void
    */
   function displayExport(&$event, $aFiles, $firstday)
   {
      $dt = $this->_dt;

      // Create the page
      $content =& $this->_displayHead('itExport');
      $kdiv =& $content->addDiv('info');
      $kdiv->addMsg("tExportFede");

      $kform =& $content->addForm('fdbf', 'dbf', DBF_SEND);
      $kform->addHide('beginDate', kform::getInput('beginDate'), -1);
      $kform->addHide('endDate', kform::getInput('endDate'), -1);
      $ties = kform::getInput('listTies', array());
      foreach($ties as $tie)
      $kform->addHide("listTies[]", $tie);

      $infosUser = $dt->getInfosUser();

      // Display fields for email
      $kpage = $this->_utpage->getPage();
      $kform->addMsg('msgListFile');
    if ( is_array($aFiles) )
      	$files = $aFiles;
    else
      	$files[] = $aFiles;
      	$i=1;
	foreach($files as $file)
	{      
      $kedit =& $kform->addMsg("file$i",    basename($file));
      $url = dirname($_SERVER['PHP_SELF']).'/../export/'.
      basename($file);
      $kedit->setUrl($url);
      $i++;
	}

	if (count($ties) )
	{
      $kform->addMsg('msgArchive');
	  $kform->addBtn('btnArchiveFile', KAF_UPLOAD,'dbf', DBF_ARCHIVE_FILE);
      $elts = array('btnMail', 'btnArchiveFile');
      $kform->addBlock('blkBtn', $elts);
	}
      $this->_utpage->display();
      exit;
   }
   // }}}

   // {{{ displayArchive()
   /**
    * display the form with the list of sended files
    *
    * @access public
    * @param string $err Error message
    * @return void
    */
   function displayArchive($err='')
   {
      $date = new utdate();

      // Create the page
      $content =& $this->_displayHead('itExport');

      $div =& $content->addDiv('divFile', 'cartouche');
      $div->addMsg('tListFile', '', 'kTitre');

      if($err!='')
      $div->addWng($err);

      $eventId = utvars::getEventId();
      $handle=@opendir(ARCHIVE_PATH);
      if (!$handle)
      $div->addErr('errArchiveNotAccessible');
      else
      {
         $masque = "/(.*)-{$eventId}_(.*).zip/";
         while ($file = readdir($handle))
         {
            if (preg_match($masque, $file))
            {
               $itms[$file] = array(KAF_UPLOAD, 'cnx',
               CNX_NEWLANG, $file);
               $kedit =& $div->addMsg($file);
               $url = dirname($_SERVER['PHP_SELF'])."/../archive/$file";
               $kedit->setUrl($url);
            }
         }
         $event = $this->_dt->getEventDef();
         $masque = "/(.*)IC{$event["evnt_level"][0]}_{$event["evnt_zone"]}_(.*).dbf/";
         rewinddir($handle);
         while ($file = readdir($handle))
         {
            if (preg_match($masque, $file))
            {
               $itms[$file] = array(KAF_UPLOAD, 'cnx',
               CNX_NEWLANG, $file);
               $kedit =& $div->addMsg($file);
               $url = dirname($_SERVER['PHP_SELF'])."/../archive/$file";
               $kedit->setUrl($url);
            }
         }
         
         closedir($handle);

         $handle=@opendir(ARCHIVE_PATH."$eventId");
         if ($handle)
         {
            while ($file = readdir($handle))
            {
               if ($file != '.' && $file != '..' && $file != 'CVS')
               {
                  $itms[$file] = array(KAF_UPLOAD, 'cnx',
                  CNX_NEWLANG, $file);
                  $kedit =& $div->addMsg($file);
                  $url = dirname($_SERVER['PHP_SELF'])."/../archive/$eventId/$file";
                  $kedit->setUrl($url);
               }
            }
            closedir($handle);
         }
         if (!isset($itms))
         $div->addWng('msgNoArchive');
      }
      $content->addDiv('breakc', 'blkNewPage');
      $this->_utpage->display();
      exit;
   }
   //}}}

   // {{{ _sendFile
   /**
    * Send the file
    *
    * @access private
    * @return void
    */
   function _sendFile()
   {
      $dt = $this->_dt;
      $ut = $this->_ut;

      // Get event data
      $event = $dt->getEventDef();
      require_once "utils/utmail.php";

      $zipFileName = kform::getInput('fileName');

      // Prepare mailer
      $infosUser = $dt->getInfosUser();
      $mailer = new utmail();

      $mailer->subject(kform::getInput('subject'));
      $from = "\"{$infosUser['user_name']}\"<{$infosUser['user_email']}>";
      $mailer->from($from);
      $mailer->cc(kform::getInput('cc'));
      $mailer->body(kform::getInput('message'));
      $mailer->addZip($zipFileName);

      // Send the message
      $res =  $mailer->send($ut->getParam("ffba_email"));
      if (PEAR::isError($res))
      {
         $this->displayExport($res->getMessage());
      }

      // Archive the file
      $this->_archiveFile('msgexportEnded');
   }
   // }}}

   // {{{ _archiveFile
   /**
    * Archive the file
    *
    * @access private
    * @return void
    */
   function _archiveFile($msg='', $aFile = null, $tieId = null)
   {
      $dt = $this->_dt;
      $ut = $this->_ut;

      $zipFileName = kform::getInput('fileName', $aFile);

      // Archive the file
      $utd = new utdate();
      $archFileName = ARCHIVE_PATH;
      $date = $utd->getIsoDate();
      $time =$utd->getTime('-');
      $archFileName .= "{$date}-{$time}-".basename($zipFileName);
      if (@copy($zipFileName, $archFileName))
      @unlink($zipFileName);

      // Update the status of send matches
      $txtDate = kform::getInput("beginDate", -1);
      if ( $txtDate != -1)
      {
         $utd->setFrDate($txtDate);
         $begin = $utd->getIsoDateTime();
      }
      else
      $begin = -1;
       
       
      $txtDate = kform::getInput("endDate", -1);
      if ( $txtDate != -1)
      {
         $utd->setFrDate(kform::getInput("endDate"));
         $end = $utd->getIsoDateTime($txtDate);
      }
      else
      $end = -1;
      $ties = kform::getInput("listTies", array($tieId));
      $dt->updateStatusMatchs($begin, $end, $ties);

      // Display the archive page
      //if (is_null($aFile))
      //$this->displayArchive($msg);
     $this->display();
      
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
      // Create a new page
      $this->_utpage = new utPage_A('dbf', true, 'itTransfert');
      $content =& $this->_utpage->getContentDiv();

      // Add a menu for different action
      $kdiv =& $content->addDiv('choix', 'onglet3');
      $items['itExport'] = array(KAF_UPLOAD, 'export', WBS_ACT_EXPORT);
      $items['itImport'] = array(KAF_UPLOAD, 'export', WBS_ACT_IMPORT);
      $items['itHelp']   = array(KAF_NEWWIN, 'help',   WBS_ACT_EXPORT, 0, 500, 400);
      $kdiv->addMenu("menuType", $items, $select);
      $kdiv =& $content->addDiv('register', 'cont3');
      return $kdiv;
   }
   // }}}

   // {{{ _writeResults()
   /**
    * write the results in files
    */
   function _writeResults(&$event, $tie=array(), $aAll=false)
   {
      if ( is_null($event) )
      $event = $this->_dt->getEventDef();
      $dt =& $this->_dt;
      $matchs = array();
      $firstday = '';
      
      // Get dates
      $err['is_error'] = false;
      if ($event['evnt_type']== "EQUIP")
      {
         $date = new utdate();
         $dateTmp = kform::getInput("beginDate",-1);
         if ($dateTmp != -1)
         {
            $date->setFrDate($dateTmp);
            $begin = $date->getIsoDateTime();
         }
         else
         $begin = -1;

         $dateTmp = kform::getInput("endDate",-1);
         if ($dateTmp != -1)
         {
            $date->setFrDate(kform::getInput("endDate", -1));
            $end = $date->getIsoDateTime();
         }
         else
         $end = -1;
         $ties = kform::getInput("listTies", $tie);
         $exportAll = kform::getInput("exportAll", $aAll);
         $dates = $this->_dt->getDateTies($begin, $end, $ties, $exportAll);
         if (!count($dates))
         {
            $err[] = array(KOD_NONE,'','','', "errNoMatch");
            $err['is_error'] = true;
            return $err;
         }
         $num=1;
         foreach($dates as $date)
         {
            $fileName = reset($date['tie_id']) . '_' . $date['date'];
            $fileName .= "_IC{$event["evnt_level"][0]}_{$event["evnt_zone"]}_";
            $fileName .= "{$date['draw_stamp']}_{$date['rund_stamp']}_{$date['tie_step']}.dbf";
            
            $fullName = EXPORT_PATH . $this->stripAccent($fileName);
            $firstday = $firstday =='' ? $date['date'] : $firstday;
            $begin = "{$date['date']} 00:00:00";
            $end = "{$date['date']} 23:59:59";
            $tieId = $date['tie_id'];
            $matchs = $dt->getMatchs($begin, $end, $tieId, $exportAll, $date);
            if (count($matchs))
            {
               $err[] = $this->_writeMatchs($event, $fullName, $matchs);
               $this->_files[] = $fullName;
               $num++;
            }
         }
      }
      else
      {
         $numauto = $this->stripAccent($event['evnt_numauto']);
         $fileName = EXPORT_PATH."{$numauto}.dbf";
         $matchs = $dt->getMatchs(-1, -1, null, true);
         if (!count($matchs))
         {
            $err[] = array(KOD_NONE,'','','', "errNoMatch");
            $err['is_error'] = true;
            return $err;
         }
         $res = $this->_writeMatchs($event, $fileName, $matchs);
         if (is_array($res) )
         {
            $res['is_error'] = true;
            return $res;         	
         }
         $this->_files[] = $fileName;
      }
      $err['files'] = $this->_files;
      $err['firstday'] = $firstday;
      return $err;
   }
   //}}}

   // {{{ _writeMatchs()
   /**
    * write the match in the file
    */
   function _writeMatchs(&$event, $fileName, &$matchs)
   {
      // Write the matches in the file
      unset($this->_row);
      $this->_rows = array();
      $this->_initHeadMatch();
      $matchId = -1;
      $pairId = -1;
      $numPair=2;
      $numPlayer=2;
      $nbMatchWrite = 0;
      $emptyMatch = array("mber_licence"    => "0000000",
			  "mber_secondname" => "",
			  "mber_firstname"  => "",
			  "mber_sexe"       => "",
			  "mber_born"       => "",
			  "rank_average"    => "",
			  "rkdf_label"      => "",
			  "asso_stamp"      => "",
			  "asso_dpt"        => "");
      //      foreach( $matchs as $match)
      while($match = array_shift($matchs))
      {
         if ($matchId != $match["mtch_id"])
         {
            if ($numPair == 2)
            {
               if ($numPlayer ==1)
               {
                  $this->_writePlayer($emptyMatch);
                  $this->_row[] = $matchMem["mtch_score"];
                  if ($matchMem["discipline"]>=WBS_MD)
                  {
                     $err[] = array($matchMem["mtch_id"],
                     $matchMem['draw_stamp'],$matchMem["rund_name"],
                     $matchMem["idMatch"], "errPlayerMissing");
                  }
               }
            }
            else
            {
               if ($numPlayer ==1)
               $this->_writePlayer($emptyMatch);
               if ($matchMem["discipline"]>=WBS_MD)
               {
                  $err[] = array($matchMem["mtch_id"],
                  $matchMem['draw_stamp'],$matchMem["rund_name"],
                  $matchMem["idMatch"], "errPlayerMissing");
               }
               $this->_writePlayer($emptyMatch);
               $this->_writePlayer($emptyMatch);
               $this->_row[] = $matchMem["mtch_score"];
               $err[] = array($matchMem["mtch_id"],
               $matchMem['draw_stamp'],$matchMem["rund_name"],
               $matchMem["idMatch"], "errPairMissing");
            }
            $matchId = $match["mtch_id"];
            $pairId  = $match["p2m_pairId"];
            $numPair = 1;
            $numPlayer = 1;
            $nbMatchWrite++;
            $this->_writeMatchDef($event, $match);
            $this->_writePlayer($match);
            $matchMem = $match;
            continue;
         }

         // Meme match, meme paire
         if ($pairId == $match["p2m_pairId"])
         {
            $numPlayer++;
            $this->_writePlayer($match);
            if ($numPair==2)
            {
               $this->_row[] = $matchMem["mtch_score"];
            }
            $matchMem = $match;
            continue;
         }

         // Meme match, deuxieme paire
         if ($numPlayer == 1)
         {
            $this->_writePlayer($emptyMatch);
         }
         $numPair++;
         $numPlayer=1;
         $pairId = $match["p2m_pairId"];
         $this->_writePlayer($match);
         $matchMem = $match;
      }

      // Control the last match
      if ($numPair == 2)
      {
         if ($numPlayer ==1)
         {
            $this->_writePlayer($emptyMatch);
            $this->_row[] = $matchMem["mtch_score"];
            if ($matchMem["discipline"]>=WBS_MD)
            {
               $err[] = array($matchMem["mtch_id"],
               $matchMem['draw_stamp'],$matchMem["rund_name"],
               $matchMem["idMatch"], "errPlayerMissing");
            }
         }
      }
      else
      {
         if ($numPlayer ==1)
         $this->_writePlayer($emptyMatch);
         if ($matchMem["discipline"]>=WBS_MD)
         {
            $err[] = array($matchMem["mtch_id"],
            $matchMem['draw_stamp'],$matchMem["rund_name"],
            $matchMem["idMatch"], "errPlayerMissing");
         }
         $this->_writePlayer($emptyMatch);
         $this->_writePlayer($emptyMatch);
         $this->_row[] = $matchMem["mtch_score"];
         $err[] = array($matchMem["mtch_id"],
         $matchMem['draw_stamp'],$matchMem["rund_name"],
         $matchMem["idMatch"], "errPairMissing");
      }
      if (isset($this->_row))
      {
         $this->_rows[] = $this->_row;
         end($this->_row);
         $score = current($this->_row);
         $this->_head['RES'] = max($this->_head['RES'],
         strlen($score));
      }

      if (!$nbMatchWrite)
      {
         $err[] = array(KOD_NONE,'','','', "errNoMatch");
         return $err;
      }
      if (isset($err))
      return $err;

      return $this->_writeFile($fileName);
   }
   // }}}

   // {{{ _writeMatchDef()
   /**
    * write the defintoin of the match in the file
    *
    * @access private
    * @param array r $match match
    * @return void
    */
   function _writeMatchDef($event, $match)
   {
      if (isset($this->_row))
      {
         end($this->_row);
         $score = current($this->_row);
         $this->_head['RES'] = max($this->_head['RES'],
         strlen($score));

         $this->_rows[] = $this->_row;
      }
      $this->_row = array();
      $this->_row[] = $match['tie_schedule'];
      $this->_head['DATE'] = max($this->_head['DATE'],
      strlen($match['tie_schedule']));

      $this->_row[] = $event['evnt_type'];
      $this->_head['NATURE'] = max($this->_head['NATURE'],
      strlen($event['evnt_type']));

      $this->_row[] = $event['evnt_level'];
      $this->_head['NIVTOURN'] = max($this->_head['NIVTOURN'],
      strlen($event['evnt_level']));

      $this->_row[] = $event["evnt_zone"];
      $this->_head['ZONE'] = max($this->_head['ZONE'],
      strlen($event['evnt_zone']));

      if ($event['evnt_type']== "EQUIP")
      {
         $this->_row[] = $match['draw_stamp'];
         $this->_head['DIVISION'] = max($this->_head['DIVISION'],
         strlen($match['draw_stamp']));
         $this->_row[] = $match['rund_name'];
         $this->_head['POULE'] = max($this->_head['POULE'],
         strlen($match['rund_name']));

         $name = "IC{$event['evnt_level'][0]} {$event['evnt_zone']} ";
         $name .= "{$match['draw_name']} {$match['rund_name']} {$match['tie_step']}";
         $this->_row[] = substr($name, 0, 50);
         $this->_head['TOURNOI'] = max($this->_head['TOURNOI'],
         strlen($name));
         $this->_row[] = $match["tie_id"];
         $this->_head['IDRENC'] = max($this->_head['IDRENC'],
         strlen($match['tie_id']));
         $this->_row[] = $match["idMatch"];
         $this->_head['IDMATCH'] = max($this->_head['IDMATCH'],
         strlen($match['idMatch']));
         $this->_row[] = '';
         $this->_head['STADE'] = max($this->_head['STADE'], 1);
      }
      else
      {
         $this->_row[] = '';
         $this->_head['DIVISION'] = max($this->_head['DIVISION'], 1);
         $this->_row[] = '';
         $this->_head['POULE'] = max($this->_head['DIVISION'], 1);
         $this->_row[] = substr($event['evnt_name'],0,50);
         $this->_head['TOURNOI'] = max($this->_head['TOURNOI'],
         strlen($event['evnt_name']));
         $this->_row[] = '';
         $this->_head['IDRENC'] = max($this->_head['IDRENC'], 1);
         $this->_row[] = $match['tie_posRound'];
         $this->_head['IDMATCH'] = max($this->_head['IDMATCH'],
         strlen($match['tie_posRound']));
         $this->_row[] = $match['tie_stade'];
         $this->_head['STADE'] = max($this->_head['STADE'],
         strlen($match['tie_stade']));
      }

      $this->_row[] = $match['discipline'];
      $this->_head['DISCIPLI'] = max($this->_head['DISCIPLI'],
      strlen($match['discipline']));

      $this->_row[] = $match['draw_rankDefId'];
      $this->_head['NIVEAU'] = max($this->_head['NIVEAU'],
      strlen($match['draw_rankDefId']));

      $this->_row[] = $match['draw_catage'];
      $this->_head['CAT'] = max($this->_head['CAT'],
      strlen($match['draw_catage']));
      return true;
   }
   // }}}

   // {{{ _writeplayer()
   /**
    * write player information into the file
    *
    * @access private
    * @param integer $fd    file descriptor
    * @param array r $match match
    * @return void
    */
   function _writePlayer($match)
   {
      $this->_row[] = $match["mber_licence"];
      $this->_head['NUG'] = max($this->_head['NUG'],
      strlen($match["mber_licence"]));
      $this->_head['NUPG'] = $this->_head['NUG'];
      $this->_head['NUP'] = $this->_head['NUG'];
      $this->_head['NUPP'] = $this->_head['NUG'];

      $this->_row[] = "";
      $this->_head['VG'] = max($this->_head['VG'],
      strlen(' '));
      $this->_head['VPG'] = $this->_head['VG'];
      $this->_head['VP'] = $this->_head['VG'];
      $this->_head['VPP'] = $this->_head['VG'];

      $this->_row[] = $match["mber_secondname"];
      $this->_head['NG'] = max($this->_head['NG'],
      strlen($match["mber_secondname"]));
      $this->_head['NPG'] = $this->_head['NG'];
      $this->_head['NP'] = $this->_head['NG'];
      $this->_head['NPP'] = $this->_head['NG'];

      $this->_row[] = $match["mber_firstname"];
      $this->_head['PG'] = max($this->_head['PG'],
      strlen($match["mber_firstname"]));
      $this->_head['PPG'] = $this->_head['PG'];
      $this->_head['PP'] = $this->_head['PG'];
      $this->_head['PPP'] = $this->_head['PG'];

      $this->_row[] = $match["mber_sexe"];
      $this->_head['SG'] = max($this->_head['SG'],
      strlen($match["mber_sexe"]));
      $this->_head['SPG'] = $this->_head['SG'];
      $this->_head['SP'] = $this->_head['SG'];
      $this->_head['SPP'] = $this->_head['SG'];

      $this->_row[] = $match["mber_born"];
      $this->_head['ANG'] = max($this->_head['ANG'],
      strlen($match["mber_born"]));
      $this->_head['ANPG'] = $this->_head['ANG'];
      $this->_head['ANP'] = $this->_head['ANG'];
      $this->_head['ANPP'] = $this->_head['ANG'];

      $this->_row[] = $match["rank_average"];
      $this->_head['CPPPG'] = max($this->_head['CPPPG'],
      strlen($match["rank_average"]));
      $this->_head['CPPPPG'] = $this->_head['CPPPG'];
      $this->_head['CPPPP'] = $this->_head['CPPPG'];
      $this->_head['CPPPPP'] = $this->_head['CPPPG'];

      $this->_row[] = "";
      $this->_head['ECLAG'] = max($this->_head['ECLAG'],
      strlen(" "));
      $this->_head['ECLAPG'] = $this->_head['ECLAG'];
      $this->_head['ECLAP'] = $this->_head['ECLAG'];
      $this->_head['ECLAPP'] = $this->_head['ECLAG'];

      $this->_row[] = $match["asso_stamp"];
      $this->_head['CLUG'] = max($this->_head['CLUG'],
      strlen($match["asso_stamp"]));
      $this->_head['CLUPG'] = $this->_head['CLUG'];
      $this->_head['CLUP'] = $this->_head['CLUG'];
      $this->_head['CLUPP'] = $this->_head['CLUG'];

      $this->_row[] = $match["asso_dpt"];
      $this->_head['DEPG'] = max($this->_head['DEPG'],
      strlen($match["asso_dpt"]));
      $this->_head['DEPPG'] = $this->_head['DEPG'];
      $this->_head['DEPP'] = $this->_head['DEPG'];
      $this->_head['DEPPP'] = $this->_head['DEPG'];

      $this->_row[] = $match["rkdf_label"];
      $this->_head['CLAG'] = max($this->_head['CLAG'],
      strlen($match["rkdf_label"]));
      $this->_head['CLAPG'] = $this->_head['CLAG'];
      $this->_head['CLAP'] = $this->_head['CLAG'];
      $this->_head['CLAPP'] = $this->_head['CLAG'];

      return true;
   }
   // }}}

   // {{{ _writeFile()
   /**
    * write the dbf file
    *
    * @access private
    * @param integer $fd file descriptor
    * @param integer $nb  number of record
    * @return void
    */
   function _writeFile($fileName)
   {
      if (!isset($this->_rows))
      {
         $err[] = array(KOD_NONE,'','','', "errNoMatch");
         return $err;
      }
      $nb = count($this->_rows);

      // Open the file
      $fd = fopen($fileName, "wb");
      if (!$fd)
      {
         $err[] = array("","",$fileName,"", "errOpenFile");
         return $err;
      }

      // Version number : 1 bytes
      fwrite($fd, "\x03", 1);

      // Current Date: 3 bytes
      $now = getdate();
      fwrite($fd, chr($now['year']-2000), 1);
      fwrite($fd, chr($now['mon']), 1);
      fwrite($fd, chr($now['mday']), 1);

      // Number of record: 4 bytes
      fwrite($fd, chr($nb & 0x000000ff), 1);
      fwrite($fd, chr(($nb & 0x0000ff00)>>8), 1);
      fwrite($fd, chr(($nb & 0x00ff0000)>>16), 1);
      fwrite($fd, chr(($nb & 0xff000000)>>24), 1);

      $nbFields = count($this->_head);

      // Length of header structure: 2 bytes
      // 58 fields, 32 octet for each field
      // 32 octet for main header, 1 octet for
      // header terminator (13)
      $lg = (($nbFields + 1)*32) + 1;
      fwrite($fd, chr($lg & 0x000000ff), 1);
      fwrite($fd, chr(($lg & 0x0000ff00)>>8), 1);

      $lg = array_sum($this->_head) + 1;
      fwrite($fd, chr($lg & 0x000000ff), 1);
      fwrite($fd, chr(($lg & 0x0000ff00)>>8), 1);

      // Reserved  bytes
      for ($i=0; $i<17; $i++)
      fwrite($fd, "\x0", 1);
      // Code type : 1 bytes Windows
      fwrite($fd, "\x03", 1);
      fwrite($fd, "\x0", 1);
      fwrite($fd, "\x0", 1);

      // The fields
      $i = 0;
      $lg = array();
      foreach($this->_head as $head => $ln)
      {
         $lg[$i++] = $ln;
         $this->_fieldDesc($fd, $head, $ln, 'C');
      }

      fwrite($fd, "\xd", 1);
      foreach($this->_rows as $row)
      {
         // Validity of record  : 1 bytes
         fwrite($fd, "\x20", 1);
         $i = 0;
         foreach($row as $field)
         {
            $this->_writefield($fd, $field, $lg[$i++]);
         }
      }

      // Write end of the file
      fwrite($fd, "\x1a", 1);

      // Close the file
      fclose($fd);
      return true;
   }
   // }}}

   // {{{ _writeField()
   /**
    * write a field
    *
    * @access private
    * @param integer $fd file descriptor
    * @param string  $name name of field
    * @param integer $nb   length of field
    * @return void
    */
   function _writefield($fd, $data, $lg)
   {
      $len=strlen($data);
      fwrite($fd, $data,  $lg);
      for ($i=$len; $i<$lg; $i++)
      fwrite($fd, "\x20", 1);
      return true;
   }
   // }}}



   // {{{ _fieldDesc()
   /**
    * write a field descriptor
    *
    * @access private
    * @param integer $fd file descriptor
    * @param string  $name name of field
    * @param integer $nb   length of field
    * @param integer $type type of field
    * @return void
    */
   function _fieldDesc($fd, $name, $lg, $type)
   {
      $len=strlen($name);
      if ($len > 10) $len = 10;
      fwrite($fd, $name, $len);
      for ($i=$len; $i<11; $i++)
      fwrite($fd, "\x0", 1);

      fwrite($fd, $type, 1);

      for ($i=0; $i<4; $i++)
      fwrite($fd, "\x0", 1);

      fwrite($fd, chr($lg & 0x000000ff), 1);

      for ($i=0; $i<15; $i++)
      fwrite($fd, "\x0", 1);
      return true;
   }
   // }}}


   // {{{ _initHeadMatch
   /**
    * Initialise l'en tete de fichier des resultats
    *
    * @access private
    * @return void
    */
   function _initHeadMatch()
   {
      // The fields are :
      //               ----- Tournoi ---------
      //  1 DATE          date YYMMJJ
      //  2 NATURE        nature du tournoi EQUIP,
      //  3 NIVTOURN      niveau du tournoi DEP,REG,NAT,INT
      //  4 ZONE          zone geographique: numero departement,
      //  5 DIVISION
      //  6 POULE
      //  7 TOURNOI       nom du tournoi
      //  8 IDRENC        identifiant de la rencontre (tournoi par equipe)
      //  9 IDMATCH       identifiant du match SH1,...SD1,...DH1,...DD1,...DMx1...
      // 10 STADE         stade du match fi, po,
      // 11 DISCIPLI      discipline SH,SD,DH,DD,DM
      // 12 NIVEAU        niveau du match NC,E2,E1,D2,D1...
      // 13 CAT           categorie d'age S,J,C,M,B,P
      //               ----- Vainqueur ---------
      // 14 NUG           licence
      // 15 VG             ???
      // 16 NG            nom
      // 17 PG            prenom
      // 18 SG            sexe F,M
      // 19 ANG           annee de naissance
      // 20 CPPPG         cppp
      // 21 ECLAG         ???
      // 22 CLUG          sigle du club
      // 23 DEPG          departement
      // 24 CLAG          classement
      //               ----- Partenaire vainqueur ---------
      // 25 NUPG          licence
      // 26 VPG            ???
      // 27 NPG           nom
      // 28 PPG           prenom
      // 29 SPG           sexe F,M
      // 30 ANPG          annee de naissance
      // 31 CPPPPG        cppp
      // 32 ECLAPG        ???
      // 33 CLUPG         sigle du club
      // 34 DEPPG         departement
      // 35 CLAPG         classement
      //               ----- Perdant ---------
      // 36 NUP           licence
      // 37 VP             ???
      // 38 NP            nom
      // 39 PP            prenom
      // 40 SP            sexe F,M
      // 41 ANP           annee de naissance
      // 42 CPPPP         cppp
      // 43 ECLAP         ???
      // 44 CLUP          sigle du club
      // 45 DEPP          departement
      // 46 CLAP          classement
      //               ----- Partenaire perdant ---------
      // 47 NUPP          licence partenaire perdant
      // 48 VPP            ???
      // 49 NPP           nom
      // 50 PPP           prenom
      // 51 SPP           sexe F,M
      // 52 ANPP          annee de naissance
      // 53 CPPPPP        cppp
      // 54 ECLAPP        ???
      // 55 CLUPP         sigle du club
      // 56 DEPPP         departement
      // 57 CLAPP         classement
      // 58 RES           score
      $this->_head = array();
      $this->_head['DATE'] = 1;
      $this->_head['NATURE'] = 1;
      $this->_head['NIVTOURN'] = 1;
      $this->_head['ZONE'] = 1;
      $this->_head['DIVISION'] = 1;
      $this->_head['POULE'] = 1;
      $this->_head['TOURNOI'] = 1;
      $this->_head['IDRENC'] = 1;
      $this->_head['IDMATCH'] = 1;

      $this->_head['STADE'] = 1;
      $this->_head['DISCIPLI'] = 1;
      $this->_head['NIVEAU'] = 1;
      $this->_head['CAT'] = 1;
      $this->_head['NUG'] = 1;
      $this->_head['VG'] = 1;
      $this->_head['NG'] = 1;
      $this->_head['PG'] = 1;
      $this->_head['SG'] = 1;
      $this->_head['ANG'] = 1;
      $this->_head['CPPPG'] = 1;
      $this->_head['ECLAG'] = 1;
      $this->_head['CLUG'] = 1;
      $this->_head['DEPG'] = 1;
      $this->_head['CLAG'] = 1;

      $this->_head['NUPG'] = 1;
      $this->_head['VPG'] = 1;
      $this->_head['NPG'] = 1;
      $this->_head['PPG'] = 1;
      $this->_head['SPG'] = 1;
      $this->_head['ANPG'] = 1;
      $this->_head['CPPPPG'] = 1;
      $this->_head['ECLAPG'] = 1;
      $this->_head['CLUPG'] = 1;
      $this->_head['DEPPG'] = 1;
      $this->_head['CLAPG'] = 1;

      $this->_head['NUP'] = 1;
      $this->_head['VP'] = 1;
      $this->_head['NP'] = 1;
      $this->_head['PP'] = 1;
      $this->_head['SP'] = 1;
      $this->_head['ANP'] = 1;
      $this->_head['CPPPP'] = 1;
      $this->_head['ECLAP'] = 1;
      $this->_head['CLUP'] = 1;
      $this->_head['DEPP'] = 1;
      $this->_head['CLAP'] = 1;

      $this->_head['NUPP'] = 1;
      $this->_head['VPP'] = 1;
      $this->_head['NPP'] = 1;
      $this->_head['PPP'] = 1;
      $this->_head['SPP'] = 1;
      $this->_head['ANPP'] = 1;
      $this->_head['CPPPPP'] = 1;
      $this->_head['ECLAPP'] = 1;
      $this->_head['CLUPP'] = 1;
      $this->_head['DEPPP'] = 1;
      $this->_head['CLAPP'] = 1;
      $this->_head['RES'] = 1;
   }
   // }}}

}
?>