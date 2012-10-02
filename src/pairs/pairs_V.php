<?php
/*****************************************************************************
!   Module     : Pairs
!   File       : $Source: /cvsroot/aotb/badnet/src/pairs/pairs_V.php,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.2 $
!   Author     : D.BEUVELOT
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
!   Mailto     : didier.beuvelot@free.fr
******************************************************************************
!   License   : Licensed under GPL [http://www.gnu.org/copyleft/gpl.html]
!      This program is free software; you can redistribute it and/or
!      modify it under the terms of the GNU General Public License
!      as published by the Free Software Foundation; either version 2
!      of the License, or (at your option) any later version.
!
!      This program is distributed in the hope that it will be useful,
!      but WITHOUT ANY WARRANTY; without even the implied warranty of
!      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
!      GNU General Public License for more details.
!
!      You should have received a copy of the GNU General Public License
!      along with this program; if not, write to the Free Software
!      Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, 
!      USA.
******************************************************************************/

require_once "base.php";
require_once "pairs.inc";

/**
* Module de gestion du carnet d'adresse : classe visiteurs
*
* @author Didier BEUVELOT <didier.beuvelot@free.fr>
* @see to follow
*
*/

class pairs_V
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
  function pairs_V()
    {
      $this->_ut = new utils();
      $this->_dt = new pairBase();
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
  function start($action)
    {
      $id = kform::getDataId();
      switch ($action)
        {
	case WBS_ACT_PAIRS:
	  $this->_displayFormPair($id);
	  break;
	  
	default:
	  $this->_displayFormList();
	}
    }
  // }}}

  
  // {{{ _displayFormList()
  /**
   * Display a page with the list of the Matchs
   *
   * @access private
   * @param  integer $sort  Column selected for sort
   * @return void
   */
  function _displayFormList()
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      $form = $ut->newForm_V("pairs", WBS_ACT_PAIRS, true); 
      
      $sort = $form->getSort("rowsPairs");
      $rows = $dt->getPairs($sort, $first, $step);
      
      if (isset($rows['errMsg']))
	$form->addWng($rows['errMsg']);
      else
	{
	  $form->addRows("rowsPairs", $rows);  
	  /*foreach( $rows as $row=>$values)
	    $userPerm[$values[0]] = KOD_NONE;
	  
	  $form->setPerms("rowsPairs", $userPerm);*/
	  
	  $sizes = array(2=> '0', 4=> '0', 6 =>'0', '0');
	  $form->setLength("rowsPairs", $sizes);
	  
	  $actions = array( 1 => array(KAF_NEWWIN, 'pairs',KID_EDIT, 0, 500,350),
			    3 => array(KAF_NEWWIN, 'regi',KID_EDIT, 4, 500,350),
			    5 => array(KAF_NEWWIN, 'regi',KID_EDIT, 6, 500,350),
			    );
	  $form->setActions("rowsPairs", $actions);
	  $form->addBloc("blkMatchs", "rowsPairs");
	}
      
      $form->display(false);
      exit; 
    }
  // }}}

  
  // {{{ _displayFormPair($infos)
  /**
   * Display the page for a Pair
   *
   * @access private
   * @param array $Match  info of the Pair
   * @return void
   */
  function _displayFormPair($Pair="")
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      $form = $ut->newForm_V("pairs", KID_RELOAD); 
      $form->setTitle("PAIR");
      $form->setSubTitle("");
      
      // Initialize the field
      if ($Pair=="")
	{
	  $infos = array('pair_ibfNum'   =>kform::getInput("pair_ibfNum"),
			 'pair_natRank'        =>kform::getInput("pair_natRank"),
			 'pair_intRank'        =>kform::getInput("pair_intRank"),
			 'pair_id'        =>kform::getInput("pair_id"),
			 'pair_cmt'        =>kform::getInput("pair_cmt"),
			 'pair_status'         =>kform::getInput("pair_status")
			 );
	  $rids = kform::getInput("rids");
	}
      else
	{
	  $id = $Pair;
	  if ( is_array($Pair))
	    {
	      if (isset($Pair['rids']))
		{
		  $rids = $Pair['rids'];
		  $id = array_shift($rids);
		}
	      else
		{
		  $infos = $dt->getPair($id);
		  $id = "";
		}
	    }
	  if ($id != "") $infos = $dt->getPair($id);
	}
      //print_r($infos);
      $form->addMsg("tEditPair");
      
      // Display the error if any 
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);
      
      //print_r($infos);
      
      // Initialize the field
      $form->addHide("pair_id", $infos['pair_id']);
      if (isset($rids) && $rids != "")
	{
	  for ($i=0; $i < count($rids); $i++)
	    $form->addHide("rids[".$i."]", $rids[$i]);
	}
      
      $form->addInfo("pair_id",     $infos['pair_id'],11);
      $form->addInfo("pair_ibfNum",     $infos['pair_ibfNum'],11);
      $form->addInfo("pair_natRank",     $infos['pair_natRank'],11);
      $form->addInfo("pair_intRank",     $infos['pair_intRank'],11);
      $form->addInfo("pair_status",     $infos['pair_status'],11);
      
      $form->addInfo("pair_cmt", $infos['pair_cmt'], 35 );
      
      $values = $dt->getFreeRegistrated();
      if(isset($infos['0']))
	$form->addInfo("regi_id_un", $infos['0']);
      else
	$form->addInfo("regi_id_un");
      
      if(isset($infos['1']))
	$form->addInfo("regi_id_deux", $infos['1']);
      else
	$form->addInfo("regi_id_deux");
      
      $elts = array("pair_id", "pair_ibfNum", "pair_natRank", "pair_intRank", "pair_status",
		    "regi_id_un", "regi_id_deux", "pair_cmt");
      $form->addBloc("blkAdmin", $elts);
      
      
      $form->addBtn("btnCancel");
      $elts = array("btnCancel");
      $form->addBloc("blkBtn", $elts);
      
      //Display the form
      $form->display(false);
      
      exit;
    }
  // }}}
}

?>