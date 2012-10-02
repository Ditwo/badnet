<?php

//!!!!!!!!!!!!!!!
function get_CPPP($db, $lic)
{
  $query  = " SELECT CPPPsimple, CPPPdouble, CPPPmixte FROM temp WHERE licence = '$lic' ";
  $req = $db->query($query);
  if (DB::isError($req)) 
    {
      echo "get_CPPP:".DB::errorMessage($req).":$query";exit();
    }
  $res = $req->fetchRow(DB_FETCHMODE_ASSOC);
  $CPPPs[]=$res['CPPPsimple'];
  $CPPPs[]=$res['CPPPdouble'];
  $CPPPs[]=$res['CPPPmixte'];
  return $CPPPs;
}
  

function get_rank($db, $lic)
{
  $query  = " SELECT clsimple, cldouble, clmixte ";
  $query .= " FROM temp WHERE licence = '$lic' ";
  $req = $db->query($query);
  if (DB::isError($req)) 
    {
      echo "get_rank:".DB::errorMessage($req).":$query";exit();
    }
  $res = $req->fetchRow(DB_FETCHMODE_ASSOC);
  $ranks[]=$res['clsimple'];
  $ranks[]=$res['cldouble'];
  $ranks[]=$res['clmixte'];
  return $ranks;
}

function insert_membres($db, $membres)
{
  $query  = "CREATE TEMPORARY TABLE temp (";
  $query .= " prenom CHAR(20), nom CHAR(20), ";
  $query .= " clsimple CHAR(4), cldouble CHAR(4), clmixte CHAR(4),";
  $query .= " CPPPsimple DECIMAL(7,2), CPPPdouble DECIMAL(7,2), CPPPmixte DECIMAL(7,2),";
  $query .= " dob DATE, licence CHAR(10))";
  $requete = $db->query($query);
  if (DB::isError($requete)) 
    {
      echo "insert_membres10:".DB::errorMessage($requete).":$query";exit();
    }
  foreach($membres as $m)
    {
      $query  = "INSERT INTO temp (prenom, nom, clsimple, cldouble,clmixte";
      $query .= ",CPPPsimple, CPPPdouble, CPPPmixte, dob, licence)";
      $query .= " VALUES (";
      $query .= "'".$m['prenom']."',";
      $query .= "'".$m['nom']."', ";
      $query .= "'{$m['simple']}', '{$m['double']}', '{$m['mixte']}', ";
      $query .= "'{$m['CPPPsimple']}', '{$m['CPPPdouble']}', '{$m['CPPPmixte']}', ";
      $query .= "'{$m['dob']}', '{$m['licence']}' )";
      $requete = $db->query($query);
      if (DB::isError($requete)) 
	{
	  echo "insert_membres20:".DB::errorMessage($requete).":$query";exit();
	}
    }
}

function update_membres($db, $eventId, $assocId)
{
  $query  = " SELECT mber_id, mber_secondname, mber_firstname, mber_born";
  $query  .= " ,mber_licence, mber_ibfnumber,  regi_id";
  $query  .= "  FROM aotb_members, aotb_registration, aotb_teams, aotb_a2t  ";
  $query  .= "  WHERE mber_id=regi_memberId";   
  $query  .= "  AND regi_eventId=$eventId";
  $query  .= "  AND regi_teamId=team_id";
  $query  .= "  AND a2t_teamId=team_id";
  $query  .= "  AND a2t_assoId=$assocId";
  $query  .= "  AND mber_Id >= 0";
  $req = $db->query($query);

  if (DB::isError($req)) 
    {
      echo "update_membres10:".DB::errorMessage($req).":$query";exit();
    }

  $errs = array();
  while($enr = $req->fetchRow(DB_FETCHMODE_ASSOC))
    {
      if ($enr['mber_licence']=='')
	{
	  $enr['error'] = "Joueur non traite: numero de licence non renseigne";
	  $errs[] = $enr;
	  continue;
	}

      // Mise � jour de la table des membres
      $query_temp  = " SELECT dob, nom, prenom, licence FROM temp";
      $query_temp .= " WHERE licence = {$enr['mber_licence']} ";     
      $req_temp = $db->query($query_temp) ;
      if (DB::isError($req_temp)) 
	{
	  echo "update_membres20:".DB::errorMessage($req_temp).":$query_temp";exit();
	}
      if (!$req_temp->numRows())
	{
	  $enr['error'] = "Joueur non trouve dans Fede: numero de licence inconnu";
	  $errs[] = $enr;
	  continue;
	}

      $licence = "00000000";
      while($enr_temp = $req_temp->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $licence=$enr_temp['licence'];
	  if ($enr_temp['nom'] == $enr['mber_secondname'] &&
	      $enr_temp['prenom'] == $enr['mber_firstname'])
	    {
	      $query_update  = " UPDATE aotb_members SET ";
	      $query_update .= " mber_born = '{$enr_temp['dob']}'" ;
	      $query_update .= " ,mber_licence = '{$enr_temp['licence']}'" ;
	      $query_update .= " WHERE mber_licence = {$enr['mber_licence']}" ;
	      $req_update = $db->query($query_update) ;
	    }
	  else
	    {
	      $enr['error'] =  "Incoherence : {$enr_temp['nom']},";
	      $enr['error'] .= "{$enr_temp['prenom']},({$enr_temp['licence']})";
	      $enr['error'] .=  "**** Incoherence--> Fede/local **** ";
	      $errs[] = $enr;
	    }
	}
      
      // Mise � jour de la table i2p
      // On r�cup�re le regi_id pour l'event 
      $regiId = $enr['regi_id'];
      
      // On r�cup�re les CPPP pour ce joueur
      $CPPPs = get_CPPP($db, $licence);
			 
// 	// on r�cup�re la liste des pairs_id
// 	$query_temp  = "SELECT pair_id, pair_disci ";
// 	$query_temp .= " FROM aotb_pairs as ap, aotb_i2p as ai, aotb_registration as ar ";
// 	$query_temp .= " WHERE ar.regi_memberId = $enr[1] AND ai.i2p_regiId = ar.regi_Id";
// 	$query_temp .= " AND ap.pair_id = ai.i2p_pairId ";
      
// 	$req_temp = $db->query($query_temp) ;
// 	if (DB::isError($req_temp)) 
// 	  {
// 	    echo "update_membres30:".DB::errorMessage($req_temp).":$query_temp";exit();
// 	  }
// 	while($enr_temp = $req_temp->fetchRow(DB_FETCHMODE_ASSOC))
// 	  {
// 	    // Pour chaque couple (regi_id, pair_id), on met � jour i2p	
// 	    $query_update  = " UPDATE aotb_i2p SET CPPP=";
	    
// 	    if(discipline_def($enr_temp[1]) == "simple")
// 	      $query_update .= $CPPP[0];
// 	    elseif(discipline_def($enr_temp[1]) == "double")
// 	      $query_update .= $CPPP[1];
// 	    else // mixte
// 	      $query_update .= $CPPP[2];
	    
// 	    $query_update .= " WHERE i2p_regiId=$regiId AND i2p_pairId = $enr_temp[0] ";
	    
// 	    echo $query_update."<br>";
// 	    $req_update = $db->query($query_update) ;
	    
// 	  }
      
      // On r�cup�re les classements pour ce joueur
      $ranks = get_rank($db, $licence);

      // on r�cup�re la liste des disciplines pour un regiId
      $query_temp  = " SELECT rank_id, rank_disci FROM aotb_ranks ";
      $query_temp .= " WHERE rank_regiId = {$enr['regi_id']} ";    
      $req_temp = $db->query($query_temp) ;
      if (DB::isError($req_temp)) 
	{
	  echo "update_membres40:".DB::errorMessage($req_temp).":$query_temp";exit();
	}

      // Pour chaque discipline, on met � jour ranks (classement et moyenne)
      while($enr_temp = $req_temp->fetchRow(DB_FETCHMODE_ASSOC))
	{
	  $query_update  = " UPDATE aotb_ranks SET rank_rankdefId='";
	  switch ($enr_temp['rank_disci'])
	    {
	    case WBS_MS:
	    case WBS_LS:
	      $query_update .= $ranks[0];
	      $query_update .= "', rank_average='{$CPPPs[0]}'";
	      break;
	    case WBS_MD:
	    case WBS_LD:
	      $query_update .= $ranks[1];
	      $query_update .= "', rank_average='{$CPPPs[1]}'";
	      break;
	    case WBS_MX:
	      $query_update .= $ranks[2];
		$query_update .= "', rank_average='{$CPPPs[2]}'";
		break;
	    }
	  $query_update .= " WHERE rank_id={$enr_temp['rank_id']}";
	  
	  //echo $query_update."<br>";
	  $req_update = $db->query($query_update);
	  if (DB::isError($req_update)) 
	    {
	      echo "update_membres50:".DB::errorMessage($req_update).":$query_update";
	      exit();
	    }	    
	}
    } // Fin boucle sur les membres
  return $errs;
} //Fin fonction update_membres


function parse($text_in, $eventId, $assocId)
{
  $ut = new Utils();
  $dsn = $ut->getDsn();
  
  $db = DB::Connect($dsn);
  if (DB::isError($db)) 
    {
      echo "dns=".$dsn.";<br>";
      $infos['errMsg'] = DB::errorMessage($db);
      return $infos;
    }
  
  require_once "utils/utffba.php";
  $ffba= new utffba();
  $res = $ffba->parse($text_in);

  if($res == -1)
    {
      $err[] = KAF_NONE;
      $err[] = "Aucun joueur dans la base f�d� correspondant � vos crit�res.";
      $errs[]=$err;
      return $errs;
    }
  insert_membres($db, $res);
  //	return $res;
  $res = update_membres($db, $eventId, $assocId);
  
  /* Delete tempory table */
  $query  = " DROP TABLE temp ";
  $req = $db->query($query) ; 
  return $res;

}









