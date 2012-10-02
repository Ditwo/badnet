<?php
/*****************************************************************************
!   Module     : Matches
!   File       : $Source: /cvsroot/aotb/badnet/src/matches/matches_V.php,v $
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
require_once "matches.inc";

/**
* Module de gestion du carnet d'adresse : classe visiteurs
*
* @author Didier BEUVELOT <didier.beuvelot@free.fr>
* @see to follow
*
*/

class matches_V
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
    function matches_V()
    {
      $this->_ut = new utils();
      $this->_dt = new MatchBase();
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
        //$ut = new Utils();
        $id = kform::getDataId();
        switch ($action)
        {
			case KID_EDIT:
				$this->_displayFormMatch($id);
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

      $form = $ut->newForm_V("matches", KID_DEFAULT, true); 
      
      $sort = $form->getSort("rowsMatchs");
      
      $rows = $dt->getMatches($sort);

      if (isset($rows['errMsg']))
	$form->addWng($rows['errMsg']);
      else
	{
	  $form->addRows("rowsMatchs", $rows);  
	  /*foreach( $rows as $row=>$values)
	    $userPerm[$values[0]] = KOD_NONE;
	  $form->setPerms("rowsMatchs", $userPerm);*/

	  $sizes = array(4=> '0', 6 =>'0', '0');
	  $form->setLength("rowsMatchs", $sizes);

	  $actions[1] = array(KAF_NEWWIN, 'matches',KID_EDIT, 0, 500,350);
	  $form->setActions("rowsMatchs", $actions);
	  $form->addBloc("blkMatchs", "rowsMatchs");
	}

      $form->display(false);
      exit; 
    }
  // }}}

  // {{{ _displayFormMatch($infos)
  /**
   * Display the page for creating a new Match
   *
   * @access private
   * @param array $Match  info of the Match
   * @return void
   */
  function _displayFormMatch($Match="")
    {
      $ut = $this->_ut;
      $dt = $this->_dt;
      
      $form = $ut->newForm_V("matches", KID_RELOAD); 
      $form->setTitle("MATCH");
      $form->setSubTitle("displayFormMatch");
     
      // Initialize the field
		if ($Match=="")
		{
			$infos = array('mtch_num'   =>kform::getInput("mtch_num"),
				 'mtch_status'        =>kform::getInput("mtch_status"),
				 'mtch_discipline'        =>kform::getInput("mtch_discipline"),
				 'mtch_id'        =>kform::getInput("mtch_id"),
				 'mtch_cmt'        =>kform::getInput("mtch_cmt"),
				 'pair_id_un'        =>"",
				 'pair_id_deux'        =>"",
				 'mtch_score'         =>kform::getInput("mtch_score")
				 );
			$rids = kform::getInput("rids");
		}
      else
		{
		  $id = $Match;
		  if ( is_array($Match))
			{
			  if (isset($Match['rids']))
				{
				  $rids = $Match['rids'];
				  $id = array_shift($rids);
				}
			  else
				{
				  $infos = $dt->getMatch($id);
				  $id = "";
				}
			}
		  if ($id != "") $infos = $dt->getMatch($id);
		}

      $form->addMsg("tEditMatch");
      
      // Display the error if any 
      if (isset($infos['errMsg']))
	$form->addWng($infos['errMsg']);
      
      // Initialize the field
      $form->addHide("mtch_id", $infos['mtch_id']);
      //$form->addHide("regi_memberId",     $infos['regi_memberId'],10);
      if (isset($rids) && $rids != "")
	{
	  for ($i=0; $i < count($rids); $i++)
	    $form->addHide("rids[".$i."]", $rids[$i]);
	}

      $form->addInfo("mtch_num",     $infos['mtch_num'],11);
      $form->addInfo("mtch_id",     $infos['mtch_id'],11);
      $form->addInfo("mtch_status",     $infos['mtch_status'],11);
      $form->addInfo("mtch_discipline",     $infos['mtch_discipline'],11);
      $form->addInfo("mtch_score",     $infos['mtch_score'],11);

      $form->addInfo("pair_id_un", $dt->ibfNumber2Name($infos['0']));
      $form->addInfo("pair_id_deux", $dt->ibfNumber2Name($infos['1']));

      $form->addInfo("mtch_cmt", $infos['mtch_cmt'], 35 );
      $elts = array("mtch_num", "mtch_id", "mtch_discipline", "mtch_status", "mtch_cmt", "pair_id_un", "pair_id_deux",
                    "mtch_score");
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