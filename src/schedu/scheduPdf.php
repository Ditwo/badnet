<?php
/*****************************************************************************
!   Module     : Schedu
!   File       : $Source: /cvsroot/aotb/badnet/src/schedu/scheduPdf.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.6 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/04/04 22:55:51 $
******************************************************************************/
require_once "base_A.php";
require_once "schedu.inc";
require_once "utils/utdate.php";

/**
* Module de gestion du calendrier : classe administrateur
*
*/

class scheduPdf
{

  // {{{ properties
  
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
  function scheduPdf()
    {
      $this->_dt = new scheduBase_A();
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
	case SCHEDU_PDF_INDIV:
	  require_once "pdf/pdfschedu.php";
 	  $pdf = new pdfschedu();
	  $pdf->start();
	  $place = kform::getData();
	  $this->_pdfIndiv($pdf, $place);
	  $pdf->end();
	  exit;
 	  break;

	case SCHEDU_PDF_ALLINDIV:
	  require_once "pdf/pdfschedu.php";
 	  $pdf = new pdfschedu();
	  $pdf->start();
	  $places = $this->_dt->getPlaces();
	  foreach($places as $place)
	    $this->_pdfIndiv($pdf, $place);
	  $pdf->end();
	  exit;
 	  break;

	case SCHEDU_PDF:
	  require_once "pdf/pdfschedu.php";
	  $divId = kform::getData();
 	  $pdf = new pdfschedu();
	  $pdf->start();
	  $pdf->displayProgramTeam($divId);
	  $pdf->end();
	  exit;
 	  break;

	case SCHEDU_PDF_PLAYER:
	  $this->_pdfPlayer();
	  exit;
 	  break;


	default:
	  echo "page $page demand�e depuis schedu_A<br>";
	  exit;
	}
    }
  // }}}


  // {{{ _pdfPlayer()
  /**
   * Affiche la liste des joueurs par salle au format pdf
   *
   * @access private
   * @return void
   */
  function _pdfPlayer()
    {
      require_once "pdf/pdfbase.php";
      $pdf = new pdfBase();
      $pdf->start('P', 'Gestion des salles');


      $venues = $this->_dt->getVenuePlayer();
      $venue = $venues['pbs'];
      unset($venues['pbs']);

      if (count($venue))
	{
	  $rows = array();
	  $titres = array ('Joueur','Match', 'Commentaire');
	  $tailles = array (50, 60, 70);
	  $styles = array ('');
	  $rows["titre"] = "Joueurs sur plusieurs salles";
	  $curent = '';
	  foreach($venue as $player=>$matchs)
	    {
	      //echo "$player<br>";
	      $line = array();
	      $line[] = $player;
	      $line['border'] = "TLR";
	      foreach($matchs as $match)
		{
		  //echo "$match<br>";
		  $line[] = $match;
		  $line[] = '';
		  $rows[] = $line;
		  $line = array();
		  $line['border'] = "LR";
		  $line[] = '';
		}
	    }
	  $line = array_pop($rows);
	  $line['border'] = "LRB";
	  $rows[] = $line;
	  $bottom = $pdf->genere_liste($titres, $tailles, $rows, $styles);
	}
      foreach($venues as $venue=>$players)
	{
	  //--- 
	  $rows = array();
	  $titres = array ('Joueur','Commentaire');
	  $tailles = array (70,110);
	  $styles = array ('');
	  $rows["titre"] = $venue;
	  $rows['newPage'] = true;
	  
	  foreach($players as $player)
	    {
	      $line = array();
	      $line[] = $player;
	      $line[] = '';
	      $rows[] = $line;
	    }
	  $bottom = $pdf->genere_liste($titres, $tailles, $rows, $styles);
	}
      $pdf->end();
    }
  // }}}

  // {{{ _pdfIndiv()
  /**
   * Display a page with the list of the ties
   * and the field to update the schedule
   * for an individual event
   *
   * @access private
   * @return void
   */
  function _pdfIndiv(&$pdf, $place)
    {
      /*
   *      $date = $dates[0]
   *      $date['date']  = "01-05-06"
   *      $times = $date['times']
   *      $time = $times[0]
   *      $time['time'] = "13:00"
   *
   *      $lines = $time['lines'][0];
   *      $lines['round'] = "Division A" | "Finale" | "Group A";
   *      $lines['num'] = "5";
   *      $lines['value'] = "VCT-USRB" | "FRA-DEN" | "Men's Doubles A";
   */
      // Display the schedule for each time
      $rows = array();
      $dates = $this->_dt->getDateTies('', $place);
      foreach ($dates as $date=>$fulldate)
	{
	  $rows = array();
	  $row['date'] = $date;
	  $row['fulldate'] = $fulldate;
	  $times = $this->_dt->getTiesIndivPdf($date, $place);	  
	  $row['times'] = $times;
	  $rows[] = $row;
	  $pdf->displayProgram($place, $rows);
	}

      return; 
    }
  // }}}
}

?>