<?php
/*****************************************************************************
!   Module     : Teams
!   File       : $Source: /cvsroot/aotb/badnet/src/teams/teamsPdf.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.18 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/05/19 09:56:25 $
!   Mailto     : cage@free.fr
******************************************************************************/
require_once "base_A.php";
require_once "pdf/pdfbase.php";

/**
* Module de gestion des equipes : classe administrateur
*
* @author Gerard CANTEGRIL
* @see to follow
*
*/

class teamsPdf
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
  function teamsPdf()
    {
      $this->_dt = new teamsBase_A();
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
    	/*
    	echo $page;
    	echo TEAM_PDF_ASSOPLAYERS . "<br>\n";
    	echo TEAM_PDF_PLAYERS. "<br>\n";
    	echo TEAM_PDF_PAIRS. "<br>\n";
    	echo TEAM_PDF_ENTRIES. "<br>\n";
*/
    	switch ($page)
        {
	case TEAM_PDF_ASSOPLAYERS:
	  $this->_assoPdf();
	  break;
	case TEAM_PDF_PLAYERS:
	  $teamId = kform::getData();
	  $pdf = new pdfbase();
	  $top = $pdf->start('L', 'tPlayersClub');
	  $this->_playersPdf($teamId, $pdf, $top);
	  $pdf->end();
	  break;
	case TEAM_PDF_PAIRS:
	  $teamId = kform::getInput('teamId');
	  $pdf = new pdfbase();
	  $top = $pdf->start('L', 'tPairsClub');
	  $this->_pairsPdf($teamId, $pdf, $top);
	  $pdf->end();
	  break;
	case TEAM_PDF_ENTRIES:
	  $teamId = kform::getInput('teamId');
	  $pdf = new pdfbase();
	  $top = $pdf->start('L', 'tEntries');
	  $this->_entriesPdf($teamId, $pdf, $top);
	  $pdf->end();
	  break;
	case TEAM_PDF_ALLPLAYERS:
	  $teamIds = kform::getInput('rowsTeams', array());
	  $pdf = new pdfbase();
	  $top = $pdf->start('L', 'tPlayersClub');
	  foreach($teamIds as $teamId)
	    {
	      $top = $this->_playersPdf($teamId, $pdf, $top);
	      $pdf->addPage('L');
	    }
	  $pdf->end();
	  break;
	case TEAM_PDF_ALLPAIRS:
	  $teamIds = kform::getInput('rowsTeams', array());
	  $pdf = new pdfbase();
	  $top = $pdf->start('L', 'tPairsClub');
	  foreach($teamIds as $teamId)
	    {
	      $top = $this->_pairsPdf($teamId, $pdf, $top);
	      $pdf->addPage('L');
	    }
	  $pdf->end();
	  break;
	case TEAM_PDF_BADGES:
	  $this->_badges();
 	  break;
	case TEAM_PDF_PURCHASE:
	  $this->_purchase();
 	  break;
	case TEAM_SEND_CONVOC:
	  $this->_sendConvoc();
	  break;

	case TEAM_PDF_RESULTS:
	  $teamId = kform::getData();
	  $this->_results($teamId);
	  break;

	case TEAM_XLS_ENTRIES:
	  $teamIds = kform::getInput('rowsTeams', array());

	  $this->_xlsEntries($teamIds);
	  exit;
	  break;
	  
	default:
	  echo "page $page demand�e depuis teamsPdf<br>";
	}
      exit();
    }
  // }}}

  // {{{ _xlsEntries()
  /**
   * Affiche le nom du fichier a enregistrer
   *
   * @access private
   * @return void
   */
  function _xlsEntries($teamIds)
    {
      require_once 'Spreadsheet/Writer.php';
      
      if (empty($teamIds))
      {
      	$teamIds = $this->_dt->getTeams(2);
      }
      
      // Cr�ation d'un manuel de travail
      $wb = new Spreadsheet_Excel_Writer();

      // Envoi des en-t�tes HTTP
      $wb->send('entries.xls');

      $worksheet =& $wb->addWorksheet('entries');
      $wk2 =& $wb->addWorksheet('wo');
      $linewk=1;

      $worksheet->hideGridlines();
      $format_title =& $wb->addFormat(array('top' => 1, 'left' => 1, 'right' => 1,
					    'bold' => 1, 'align'=> 'center' ));
      $format_tlr   =& $wb->addFormat(array('top' => 1, 'left' => 1, 'right' => 1));
      $format_blr   =& $wb->addFormat(array('left' => 1, 'bottom' => 1, 'right' => 1));
      $format_t     =& $wb->addFormat(array('top' => 1));
      $format_name  =& $wb->addFormat(array('bold' => 1));

      // Nom du tableau
      $line = 1;
      $worksheet->setcolumn(0, 0, 5);
      $worksheet->write($line, 0, '#',        $format_title);
      $worksheet->setcolumn(1, 1, 40);
      $worksheet->write($line, 1, "Club",   $format_title);
      $worksheet->setcolumn(2, 2, 7);
      $worksheet->write($line, 2, "Genre",      $format_title);
      $worksheet->setcolumn(3, 3, 20);
      $worksheet->write($line, 3, "Nom", $format_title);
      $worksheet->setcolumn(4, 4, 20);
      $worksheet->write($line, 4, "Prenom",  $format_title);
      $worksheet->setcolumn(5, 5, 10);
      $worksheet->write($line, 5, "Licence",  $format_title);
      $worksheet->setcolumn(6, 6, 10);
      $worksheet->write($line, 6, "Clt",  $format_title);
      $worksheet->setcolumn(7, 7, 10);
      $worksheet->write($line, 7, "Rang",  $format_title);
      $worksheet->setcolumn(8, 8, 20);
      $worksheet->write($line, 8, "Tableaux",  $format_title);
      $worksheet->setcolumn(9, 9, 20);
      $worksheet->write($line, 9, "Premier match",  $format_title);
      $line++;

      $wk2->setcolumn(0, 0, 5);
      $wk2->write($linewk, 0, '#',      $format_title);
      $wk2->setcolumn(1, 1, 30);
      $wk2->write($linewk, 1, "Club",   $format_title);
      $wk2->setcolumn(2, 2, 7);
      $wk2->write($linewk, 2, "Genre",  $format_title);
      $wk2->setcolumn(3, 3, 20);
      $wk2->write($linewk, 3, "Nom",    $format_title);
      $wk2->setcolumn(4, 4, 20);
      $wk2->write($linewk, 4, "Prenom", $format_title);
      $wk2->setcolumn(5, 5, 30);
      $wk2->write($linewk, 5, "Date",   $format_title);
      $linewk++;

      // Traiter chaque equipe
      $num = 1;
      $numwk = 1;
      $utdat = new utdate();
      foreach($teamIds as $teamId)	
	{
		if(is_array($teamId)) $teamId = $teamId['team_id'];
	  $team = $this->_dt->getTeam($teamId);
	  $players = $this->_dt->getPlayers($teamId, 2);
	  if (!isset($players['errMsg']))
	    {
	      foreach($players as $player)
		{
		  $draws = $this->_dt->getDraws($player[0]);
		  $worksheet->write($line, 0, $num++, $format_tlr);
		  $worksheet->write($line, 1, $team['team_name'], $format_tlr);
		  $worksheet->write($line, 2, $player[2], $format_tlr); // Genre
		  $worksheet->write($line, 3, $player[17], $format_tlr); // Nom
		  $worksheet->write($line, 4, $player[16], $format_tlr); // Prenom
		  $worksheet->write($line, 5, $player[7], $format_tlr); // Licence
		  $level = str_replace(',', ';', $player[4]);
		  $worksheet->write($line, 6, $level, $format_tlr); // Classement
		  $worksheet->write($line, 7, $player[5], $format_tlr); // Rang
		  $worksheet->write($line, 8, $draws, $format_tlr);
		  $worksheet->write($line, 9, $player[9], $format_tlr); // Premier match
		  $line++;
		  if ($player[21] == WBS_YES)
		    {
      
		      $utdat->setIsoDate($player[22]);
		      $wk2->write($linewk, 0, $num-1, $format_tlr);
		      $wk2->write($linewk, 1, $team['team_name'], $format_tlr);
		      $wk2->write($linewk, 2, $player[2],  $format_tlr); // Genre
		      $wk2->write($linewk, 3, $player[17], $format_tlr); // Nom
		      $wk2->write($linewk, 4, $player[16], $format_tlr); // Prenom
		      $wk2->write($linewk, 5, $utdat->getDate(), $format_tlr); // Date
		      $linewk++;
		    }

		}
	    }
	}
      $worksheet->write($line, 0, '', $format_t);
      $worksheet->write($line, 1, '', $format_t);
      $worksheet->write($line, 2, '', $format_t);
      $worksheet->write($line, 3, '', $format_t);
      $worksheet->write($line, 4, '', $format_t);
      $worksheet->write($line, 5, '', $format_t);
      $worksheet->write($line, 6, '', $format_t);
      $worksheet->write($line, 7, '', $format_t);
      $worksheet->write($line, 8, '', $format_t);
      $worksheet->write($line, 9, '', $format_t);
      
      $wk2->write($linewk, 0, '', $format_t);
      $wk2->write($linewk, 1, '', $format_t);
      $wk2->write($linewk, 2, '', $format_t);
      $wk2->write($linewk, 3, '', $format_t);
      $wk2->write($linewk, 4, '', $format_t);
      $wk2->write($linewk, 5, '', $format_t);
    
      //$worksheet->write($line+1, 0, 'Document generated by BadNet (http://www.badnet.org)');
      //$wk2->write($linewk+1, 0, 'Document generated by BadNet (http://www.badnet.org)');

      // Envoi du fichier
      $wb->close();
      return; 
    }
  // }}}

  // {{{ _results()
  /**
   * Resultats des joueurs d'un club
   * @return void
   */
  function _results($assoId)
    {
      require_once "utils/objmatch.php";
      require_once "pdf/pdfbase.php";
      require_once "utils/objplayer.php";
      $ut = new utils();
      $pdf = new pdfbase();
      $titres = array ('cDraw', 'cStage','cRound', 'cResult', 'cOpponent', 'cScore', 'cLength');
      $tailles = array (50, 30, 20, 20, 95, 40, 15);
      $styles = array ('B','','', '', 'B', '');


      $assos = $this->_dt->getAssos();
      //$results['titre'] = $assos[$assoId];
      //$results['orientation'] = 'L';
      $bottom = $pdf->start('L', $assos[$assoId]);

      // Joueurs du club
      $players = $this->_dt->getAssoPlayers($assoId);

      // Pour chaque joueur
      foreach($players as $player)
	{
	  $results = array('titre' => "{$player['regi_longName']}",
			   'orientation' => 'L',
			   'top' => $bottom+5,
			   'newPage' => false);
	  $playerId = $player['regi_id'];
	  $matchs = $this->_dt->getMatchPlayer($playerId);
	  //print_r($matchs);
	  $prevDraw = '';
	  foreach($matchs as $match)
	    {	      
	      $fullmatch = new objMatch($match['mtch_id']);
	      $row = array();
	      $sameDraw = false;
	      if ($prevDraw == $fullmatch->getDrawName())
		$sameDraw = true;
	      if ($sameDraw)
		$draw = '';
	      else
		$draw = $prevDraw = $fullmatch->getDrawName();	      
	      $row[] = $draw;
	      $row[] = $fullmatch->getStageName();
	      $row[] = $fullmatch->getStepStamp();
	      $row[] = $ut->getLabel($match['p2m_result']);
	      if ($match['p2m_result'] < WBS_RES_LOOSE)
		$row[] = $fullmatch->getFirstLosName();
	      else
		$row[] = $fullmatch->getFirstWinName();
	      $row[] = $fullmatch->getScore();
	      $row[] = $fullmatch->getLength();
	      if ($fullmatch->isDouble())
		{
		  $row['border'] = "TLR";
		  $results[] = $row;
		  $row = array();
		  $row['border'] = "BLR";
		  if (!$sameDraw)
		    {
		      $player = new objplayer($playerId); 
		      $row[] = $player->getPartnerName($fullmatch->getDiscipline());
		      unset($player);
		    }
		  else
		    $row[] = '';
		  $row[] = '';
		  $row[] = ''; 
		  if ($match['p2m_result'] < WBS_RES_LOOSE)
		    $row[] = $fullmatch->getSecondLosName();
		  else
		    $row[] = $fullmatch->getSecondWinName();
		  $row[] = '';
		  $row[] = ''; 
		  $row[] = ''; 
		}
	      else
		{
		  $row['border'] = "TBLR";
		}
	      $results[] = $row;
	      unset($fullmatch);	  
	    }
	  $bottom = $pdf->genere_liste($titres, $tailles, $results, $styles);
	}    
      $pdf->end();      
    }
  // }}}

  // {{{ _sendConvoc()
  /**
   * Envoi des convocations
   * @return void
   */
  function _sendConvoc()
    {
      
      $ute = new utevent();
      $event = $ute->getEvent(utvars::getEventId());

      $infosUser = $this->_dt->getInfosUser(utvars::getUserId());

      require_once "utils/utmail.php";

      $buf = "Bonjour\n\n";
      $buf .= "veuillez trouver ci-joint les convocations pour les joueurs de votre club.\n";
      $buf .= "Elles sont �galement disponibles sur le site :\n ";
      $buf .= "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}\n\n";
      $buf .= "Ces informations sont donn�es � titre indicatif et peuvent �tre modifi�es.\n";
      $buf .= "Consulter le site la veille du premier jour de la comp�tition pour vous assurer de leur validit�.\n\n";
      $buf .= "Sportivement.\n\n";     
      $buf .= $infosUser['user_name'];

      $ids = kform::getInput('rowsTeams', array());
      $contacts = kform::getInput('contacts', array());
      //print_r($ids);
      //print_r($contacts); 
      //foreach($ids as $id)
      foreach($contacts as $id)
	{
	  // Preparation du fichier convocation pdf
	  list($teamId, $ctacId) = explode(';', $id);
	  if (!in_array($teamId, $ids) ) continue;
	  $team = $this->_dt->getTeam($teamId);
	  if ($ctacId != '' && $ctacId != -1)
	    {
	      $pdf = new pdfbase();
	      $pdf->start('L');
	      $this->_playersPdf($teamId, $pdf);
	      $filepdf = $pdf->end(false);
	      unset($pdf);

	      $file = dirname($filepdf).'/convoc.pdf';
	      if (!rename($filepdf, $file))
		$file = $filepdf;
	      
	      // Envoi du fichier
	      $contact = $this->_dt->getContact($ctacId);
	      $mailer = new utmail();	      
	      $mailer->subject("Convocation {$event['evnt_name']}");
	      //$from = "\"{$infosUser['user_name']}\"<{$infosUser['user_email']}>";
	      $from = $infosUser['user_email'];
	      $mailer->from($from);
	      $mailer->cc($from);
	      $mailer->receipt();
	      $mailer->body($buf);
	      $mailer->addPdf($file);

	      $res =  $mailer->send($contact['ctac_value']);
	      if (PEAR::isError($res))
		$ret[] = array($team['team_id'], $team['team_name'], $contact['ctac_contact'],
			       $contact['ctac_value'],
			       $res->getMessage());
	      else
		$ret[] = array($team['team_id'], $team['team_name'], $contact['ctac_contact'],
			       $contact['ctac_value'], "Ok");
		
	      @unlink($file);
	      unset($mailer);
	      unset($res);
	      unset($contact);
	    }
	  else
	    $ret[] = array($team['team_id'], $team['team_name'], '','', "Pas de contact");

	}      

      $utpage = new utPage('teams');
      $content =& $utpage->getPage();
      $form =& $content->addForm('fSendConvoc', 'teams', TEAM_PUBLISHED);
      $form->addRows('rowsSend', $ret);
      $form->addBtn('btnCancel');
      $elts = array('btnModify', 'btnCancel');
      $form->addBlock('blkBtn', $elts);
      $utpage->display();
      exit; 

    }
  // }}}


  // {{{ _purchase()
  /**
   * List the purchase
   * @return void
   */
  function _purchase()
    {
      
      require_once "baseAdm_A.php";
      $dt = new teamsAdmBase_A();
      $pdf = new pdfbase();
      $pdf->start('L');
      $sort = kform::getData();
      $teamId = kform::getInput('teamId');

      $resas = $dt->getPurchaseList($teamId, 3);

      //$rows['titre'] = $team['team_name'];

      $titres = array ('cDate', 'cIdentity', 'cPurchase', 'cCost', 
		       'cDiscount', 'cCost', 'cBalance');
      $tailles = array (25, 80, 80, 20, 20, 20, 20);
      $styles = array ('B','','','','','','');
      foreach($resas as $resa)
	{
	  $rows[] = array_slice($resa, 1, 7);
	}
      $rows['orientation'] = 'L';
      $pdf->genere_liste($titres, $tailles, $rows, $styles);
      $pdf->end();
      return;
    }
  // }}}

  // {{{ _badges
  /**
   * Select ids for badge
   *
   * @access private
   * @param array team  info of the team
   * @return void
   */
  function _badges()
    {
    require_once "pdf/badges.php";
    	
      $dt = $this->_dt;

      $pdf = new badgesPdf();
      $idsP  = kform::getInput('rowsAdmRegis', array());
      $idsO  = kform::getInput("rowsOffos", array());
      $ids = array_merge($idsP,$idsO);
if (!count($ids))
	{
	  $teamId = kform::getInput('teamId');
	  $players = $dt->getPlayers($teamId, 2);
	  if (!isset($players['errMsg']))
	    foreach($players as $player)
	      $ids[] = $player[0];

	  $officials = $dt->getOfficials($teamId, 2);
	  if (!isset($officials['errMsg']))
	    foreach($officials as $official)
	      $ids[] = $official['regi_id'];
	}
      $res = $pdf->badges($ids);
      if (isset($res['err_msg']))
	echo $res['err_msg'];

      exit;
    }
  // }}}

  // {{{ _assoPdf()
  /**
   * Save the photo of a team
   *
   * @access private
   * @return void
   */
  function _assoPdf()
    {
      $dt = $this->_dt;
      $assoId = kform::getData();
      $teams = $dt->getTeams(2, $assoId);
      $pdf = new pdfbase();
      $top = $pdf->start('L', 'tPlayersClub');
      foreach($teams as $team)
	{
	  $this->_playersPdf($team['team_id'], $pdf, $top);
	  //$this->_pairsPdf($team['team_id'], $pdf, $top);
	}
      $pdf->end();
      exit;
    }
  // }}}

  // {{{ _pairsPdf()
  /**
   * pairs of a club
   *
   * @access private
   * @return void
   */
  function _pairsPdf($teamId, &$pdf, $top)
    {
      $dt = $this->_dt;
      
      $team = $dt->getTeam($teamId);
      $rows['titre'] = $team['team_name'];

      $sort = kform::getSort("rowsPlayers_V", 3);
      $pairs = $dt->getPairs($teamId, $sort);
      $titres = array ('cPairs', 'cLevel', 'cDraw', 'cPoints', 'cIntRank', 
		       'cNatRank');
      $tailles = array (90, 15, 60, 50,15, 15);
      $styles = array ('B','','','','','');
      if (!isset($pairs['errMsg']))
	{
	  foreach($pairs as $pair)
	    {
	      $players = $pair['regi_longName'];
	      $levels  = $pair['rkdf_label'];
	      $newPair = true;
	      foreach($players as $player)
		{
		  $row=array();
		  $row[] = $player['value'];
		  $row[] = $player['level'];
		  if ($newPair==true)
		    {
		      $row['border'] = "TLR";
		      $draw = $pair['draw_name'];
		      $row[] = $draw[0]['value'];
		      $row[] = $pair['i2p_cppp'];
		      $row[] = $pair['pair_intRank'];
		      $row[] = $pair['pair_natRank'];
		    }
		  else
		    {
		      $row['border'] = "BLR";
		      $row[] = ' ';
		      $row[] = ' ';
		      $row[] = ' ';
		      $row[] = ' ';
		    }
		  $rows[] = $row;
		  $newPair = false;
		}
	    }
	  $row = array_pop($rows);
	  $row['border'] = "BLR";
	  $rows[] = $row;
	  $rows['orientation'] = 'L';
	}
      if (!is_null($top))
	$rows['top'] = $top;
      $pdf->genere_liste($titres, $tailles, $rows, $styles);
      return;
    }
  // }}}

  // {{{ _enriesPdf()
  /**
   * entries of a club
   *
   * @access private
   * @return void
   */
  function _entriesPdf($teamId, &$pdf, $top)
    {
      $dt = $this->_dt;
      
      $team = $dt->getTeam($teamId);
      $rows['titre'] = $team['team_name'];

      $entries = $dt->getEntries($teamId);
      $pdf->entries($team, $entries, $top);
      return;
    }
  // }}}


  // {{{ _playersPdf()
  /**
   * 
   *
   * @access private
   * @return void
   */
  function _playersPdf($teamId, &$pdf, $top=null)
    {
      $dt = $this->_dt;
      $ute = new utevent();
      $convocation = $ute->getConvoc();
      
      $titres = array ('cGender', 'cIdentity','cEntrie',
		       'cNatNum', 'cConvocation', 'cFirstMatch', 'cVenue');
      $tailles = array (10,65,50,30,35,35,50);
      $styles = array ('','B','','','','','');

      $team = $dt->getTeam($teamId);
      $rows['titre'] = $team['team_name'];
      $rows['msg'] = "{$convocation['evnt_textconvoc']}\n{$team['team_textconvoc']}";
      $sort = kform::getSort("rowsPlayers", 2);
      $players = $dt->getPlayers($teamId, $sort);
      if (!isset($players['errMsg']))
	{
	  foreach($players as $player)
	    {
	      $row = array();
	      $row[] = $player[2];
	      $row[] = $player[1];
	      $row[] = $player[6];
	      $row[] = $player[7];
	      $row[] = $player[10];
	      $row[] = $player[9];
	      $row[] = $player[11];
	      $rows[] = $row;
	    }
	}
      $rows['orientation'] = 'L';
      if (!is_null($top))
	$rows['top'] = $top;
      $pdf->genere_liste($titres, $tailles, $rows, $styles);
      return;
    }
  // }}}
}
?>
