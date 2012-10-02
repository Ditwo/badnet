<?php
require_once 'Bn/Balise/Image.php';
require_once 'Bn/Balise/Edit.php';
require_once 'Bn/Balise/Select.php';

class Bn_Table
{
	var $_rows = array(); // Lignes du tableau
	var $_editLine  = null;   // Ligne a modifier
	var $_editCols  = array();   // Colonnes de la ligne a modifier
	var $_allowEdit = false;  // Autorisation de modifier une ligne
	var $_allowDel  = false;  // Autorisation de supprimer une ligne
	var $_allowAdd  = false;  // Autorisation d'ajouter une ligne
	var $_cancelAction  = null;   // Action pour l'abandon d'edition d'une ligne
	var $_pager  = null;          // Pager pour naviguer de page en page
	var $_nbRow  = 1;             // Numero de la premiere ligne
	var $_actions = array();      // Action a ajouter en fin de ligne en consultation
	var $_editActions = array();      // Action a ajouter en fin de ligne en modification
	
	//---------------
	// Constructeur
	function __construct($name)
	{
		$attributes = array('id' => $name);
		HTML_Table::HTML_Table($attributes);
	}

	//---------------
	// Fonction addAction
	// Ajoute une action sur chaque ligne
	// $p_i_action : action a effectuer
	// $p_s_image  : image a afficher
	function addAction($p_i_action, $p_s_image)
	{
		$this->_actions[] = array('dzAction' => $p_i_action,
								  'img'      => $p_s_image,
		);
		$this->_editActions[] = array('dzAction' => $p_i_action,
								  'img'      => $p_s_image,
		);
	}
	
	//---------------
	// Fonction setPager
	// Rajoute la pagination
	// $p_i_nbRows : nombre total de lignes
	// $p_i_limit  : nombre de ligne par page
	// $p_i_numPage : page courante
	function setPager($p_i_nbRows, $p_i_limit, $p_i_numPage)
	{
		$tableName = $this->getAttribute('id');
		$this->_pager = new Pager('pager'.$tableName, $p_i_nbRows, $p_i_limit, $p_i_numPage);
		if ($p_i_limit && ($p_i_nbRows  > $p_i_limit) )
		{
			$this->_nbRow = ($p_i_numPage - 1) * $p_i_limit;
			$this->_nbRow++;
		}
		return $this->_pager;
	}

	//---------------
	// Fonction setTitleCols
	// Produit une chaine Html pour l'affichage de la table
	function setTitleCols($titles, $attr=null)
	{
		$this->addRow($titles, $attr, 'th');
	}

	//---------------
	// Fonction setRows
	// Ajout les lignes au tableau
	function setRows($rows, $attr=null)
	{
		if (is_array($rows))
		{
			$this->_rows = $rows;
		}
		else
		{
			$this->_rows[] = $rows;
		}
	}


	//---------------
	// Fonction setConsultAction
	// Fixe l'action pour la consultation d'une ligne
	function setConsultAction($p_i_action)
	{
		$this->_actions[] = array('url' => 'index.php?dzAction=' . $p_i_action,
								  'img' => 'Bn/Img/magnifier.png');
	}

	//---------------
	// Fonction setEditAction
	// Fixe l'action pour la modification d'une ligne
	function setEditAction($p_i_editAction, $p_i_validAction = null, $p_i_cancelAction = null)
	{
		$this->_actions[] = array('url' => 'index.php?dzAction=' . $p_i_editAction,
		 						  'img' => 'Bn/Img/edit.png');

		$this->_editActions[] = array('url'      => 'javascript:submitForm(this,' . $p_i_validAction . ',1);',
									  'img'      => 'Bn/Img/accept.png',
									  'isScript' => true);
		
		$this->_editActions[] = array('url' => 'index.php?dzAction=' . $p_i_cancelAction,
									  'img' => 'Bn/Img/cancel.png');
	}

	//---------------
	// Fonction setDeleteAction
	// Fixe l'action pour la suppression d'une ligne
	function setDeleteAction($p_i_action)
	{
		$this->_actions[] = array('url' => 'index.php?dzAction=' . $p_i_action,
								  'img' => 'Bn/Img/delete.png',
								  'confirm' => true);
	}

	//---------------
	// Fonction setEditLine
	// Fixe la ligne a modifier
	function setEditLine($line, $cols)
	{
		$this->_editLine = $line;
		$this->_editCols = $cols;
	}

	//---------------
	// Fonction allowEdit
	// Autorise la modification de ligne
	function allowEdit()
	{
		$this->_allowEdit = true;
	}

	//---------------
	// Fonction allowDelete
	// Autorise la suppression de ligne
	function allowDelete()
	{
		$this->_allowDel = true;
	}

	//---------------
	// Fonction allowAdd
	// Autorise l'ajout de ligne
	function allowAdd()
	{
		$this->_allowAdd = true;
	}

	//---------------
	// Fonction toHtml
	// Construit la chaine HTML du tableau
	function toHtml()
	{

		// En debut de chaque ligne, ajouter le numero de ligne
		// En fin de chaque ligne, ajouter les boutons d'edition
		// et de suppression si necessaire

		// Initialisation du numero de ligne
		$nbRow = $this->_nbRow;
		$nbSpan = 0;
		// Nom HTML de la table
		$tableName = $this->getAttribute('id');
		
		// Preparation des actions en fin de ligne
		if ( !empty($this->_deleteAction) )
		{
			$this->_actions[] = array('dzAction' => $this->_deleteAction,
									  'img' => 'Bn/Img/delete.png',
				                      'url' =>  $this->_deleteUrl,
									  'confirm' => true);
		}
		
		// Boucle sur les lignes
		foreach($this->_rows as $row)
		{
			// La ligne n'est pas en mode modification
			if($row[0] != $this->_editLine)
			{
				// Ajouter les icones des actions en fin de ligne
				foreach($this->_actions as $l_as_action)
				{
					if ( isset($l_as_action['isScript']) )
					{
						$l_s_url = $l_as_action['url'];
					}
					else					
					{
						$l_s_url = $l_as_action['url'] . '&' . $row[0];
					}
					$l_o_img  = new Image('img_consult', $l_as_action['img']);
					$l_o_link = new Bn_Balise('a', null, $l_o_img);
					if ( !isset($l_as_action['confirm']) )
					{
						$l_o_link->setAttribute('href', $l_s_url);
					}
					else
					{
						$l_s_java = "javascript:if (confirm('Confirmer la suppression de la ligne')) document.location='$l_s_url';";
						$l_o_link->setAttribute('href', $l_s_java);
					}
					$row[] = $l_o_link;
				}				
			}
			// La ligne est en mode modification
			else
			{
				// Traiter chaque colonne en mode modfication
				$nbSpan = 2;
				$l_i_col = 0;
				foreach($this->_editCols as $numCol=>$col)
				{
					$l_i_col++;
					// Nom du champ : soit il fait parti des donnees (name)
					// soit on lui donne un nom generique col+[numero de colonne]
					$colName = isset($col['name']) ? $col['name'] :"col{$numCol}";
					// Liste de valeurs
					if (isset($col['values']))
					{
						// -- Valeurs selectionnees
						$select = isset($col['selected']) ? $col['selected']:$row[$numCol];
						$select = Bn::getValue($colName, $select);
						// -- Valeurs gris�es
						$disable = isset($col['disabled']) ? $col['disabled']:null;
						// -- Creation d'un objet select dans la cellule
						$l_i_action   = isset($col['action']) ? $col['action']:null;
		    			$l_s_callback = isset($col['callback']) ? $col['callback']:null;						
						$row[$numCol] = new Select($colName, NULL, $l_i_action, $l_s_callback);
						
						// -- chargement des valeurs dans l'objet
						$row[$numCol]->addLov($col['values']);
						$row[$numCol]->setSelectedTexts($select);
					}
					// Champ de saisie
					elseif (empty($col['date']))
					{
						// -- La valeur : soit elle fait partie des donnees (value)
						//    soit on utilise la valeur de la ligne
						$value = isset($col['value'])?$col['value']:$row[$numCol];
						$value = stripslashes(Bn::getValue($colName, $value));
						// -- Creation d'un objet texte dans la cellule
						$row[$numCol] = new Edit($colName, null, $value,  $col['maxsize']);
					}
					// Champ de saisie de type date
					else
					{
						// definition de la boite de saisie de la date de changement d �tat
						$dzDate = new DesirDate($colName, '');
						$date = isset($col['value'])?$col['value']:$row[$numCol];
						$date = stripslashes(Bn::getValue($colName, $date));
						if ( !empty($date) )
						{
							list($dt_day, $dt_month, $dt_year) = explode('-', $date);
						}
						else
						{
							$dt_day = 0;
							$dt_month = 0;
							$dt_year = 0;
						}
						$dzDate->setDate($dt_year, $dt_month, $dt_day);
						$row[$numCol] = $dzDate;
					}
				}
				// Ajout de l'icone de validation en fin de ligne
				// -- Initialisation de la chaine Html
				$strHtml = '';

				// -- Ajout des champs caches pour identification de la ligne :
				//    ils sont extraits de la premiere colonne de la ligne
				$args = explode('&', $row[0]);
				foreach($args as $arg)
				{
					$trv = explode('=', $arg);
					$strHtml .= "<input type=\"hidden\" name=\"{$trv[0]}\" value=\"{$trv[1]}\">\n";
				}				
				
				// -- Ajout de la cellule en fin de ligne
				$row[] = $strHtml;

					
			} // Fin mode modification de la ligne

			// Le premiere cellule de la ligne contient le numero de ligne
			$row[0] = $nbRow++;

			// Ajout de la ligne a la table
			$this->addRow($row);
		}

		// Nombre de colonne da la table
		$nbCol = $this->getColCount();

		// S'il ya des actions
		$nbSpan = count($this->_actions);		
		if ($nbSpan)
		{
			// Ajout du titre pour les actions
			$attribs = array('colspan' => $nbSpan);

			$this->setCellContents(0, $nbCol-$nbSpan, 'Actions', 'th');
			$this->setCellAttributes(0, $nbCol-$nbSpan, $attribs);

			// Centrer les icones des actions
			$l_as_attribs = array('style' => 'text-align:center;width:25px');
			$this->updateColAttributes(0, $l_as_attribs);
			for ($i=0; $i < $nbSpan; $i++)
			{
				$this->updateColAttributes($nbCol-($i+1),$l_as_attribs);
			}
		}

		// Pager
		$l_s_html = '';
		if ( !empty($this->_pager) )
		{
			$l_s_html = $this->_pager->toHtml();
		}

		// Construire la chaine
		$l_s_html .= html_table::toHtml();
		return $l_s_html;
	}

	//---------------
	// Fonction getMandatoryFields
	// Renvoi les champs obligatoires pour le contorle de la saisie
	function getMandatoryFields(&$p_as_fields)
	{
		foreach($this->_editCols as $numCol=>$col)
		{
			//if (empty($col['date']))
			$colName = isset($col['name']) ? $col['name'] :"col{$numCol}";
			$p_as_fields[$colName] = $this->getCellContents(0, $numCol);
		}
	}
}

class Pager  extends Bn_Balise
{
	var $_nbRows  = 0;   // Nombre de ligne total
	var $_limite  = 0;   // Nombre de ligne par page
	var $_numPage = 0;   // Numero de page affiche
	var $_name = '';     // Nom du pager

	//---------------
	// Constructeur
	function Pager($p_s_name, $p_i_nbRows, $p_i_limit, $p_i_numPage)
	{
		parent::Bn_Balise('div', 'dvi'.$p_s_name);
		$this->setAttribute('class', 'dzpager');

		$this->_nbRows  = $p_i_nbRows;
		$this->_limit   = $p_i_limit;
		$this->_numPage = $p_i_numPage;
		$this->_name    = $p_s_name;
	}

	//---------------
	// Fonction toHtml
	// Construit la chaine HTML du tableau
	function toHtml()
	{
		$l_s_html = '';
		// S'il n'y a qu'une page, ne rien afficher
		if ( !$this->_limit )
		{
			return $l_s_html;
		}


		if ( !empty($this->_formName) )
		{
			$l_s_js = 'var l_o_form = document.forms['. $this->_formName . '];';
		}
		else
		{
			$l_s_js = 'var l_o_form = document.forms[0];';
		}


		// Pour aller a la premiere page
		if ( $this->_numPage > 1)
		{
			$l_s_jsP = 'javascript:' . $l_s_js . 'l_o_form.elements[\''. $this->_name .'\'].value=1;';
			$l_s_jsP .= 'l_o_form.submit();';

			$l_o_lnk =& $this->addLink('', $l_s_jsP);
			$l_o_lnk->addImage('prev'.$this->_name, 'Bn/Img/first.png');
		}
		else
		{
			$this->addImage('prev'.$this->_name, 'Bn/Img/first.png');
		}

		// Pour aller a la page precedente
		if ( $this->_numPage > 1)
		{
			$l_i_num = $this->_numPage -1;
			$l_s_jsP = 'javascript:' . $l_s_js . 'l_o_form.elements[\''. $this->_name .'\'].value=' . $l_i_num .';';
			$l_s_jsP .= 'l_o_form.submit();';

			$l_o_lnk =& $this->addLink('', $l_s_jsP);
			$l_o_lnk->addImage('prev'.$this->_name, 'Bn/Img/previous.png');
		}
		else
		{
			$this->addImage('prev'.$this->_name, 'Bn/Img/previous.png');
		}

		// Nombre total de ligne
		$l_i_firstRow = (($this->_numPage -1) * $this->_limit)+1;
		$l_i_lastRow = min($this->_numPage * $this->_limit, $this->_nbRows);
		$l_s_text = $l_i_firstRow . '-' . $l_i_lastRow . '/' . $this->_nbRows;
		$this->addBalise('span', null, $l_s_text);


		// Liste deroulante avec les numeros de page
		$l_i_nbPage = intval($this->_nbRows / $this->_limit) + 1;
		for ($l_i_i=1; $l_i_i <= $l_i_nbPage ;$l_i_i++)
		{
			$l_i_lov[$l_i_i] = $l_i_i;
		}
		$l_o_obj =& $this->addSelect($this->_name);
		$l_o_obj->addOptions($l_i_lov, $this->_numPage);
		$l_o_select =& $l_o_obj->getSelect();
		$l_o_select->setAttribute('onchange', $l_s_js . 'l_o_form.submit();');

		// Pour aller a la page suivante
		if ( $this->_numPage < $l_i_nbPage)
		{
			$l_i_num = $this->_numPage + 1;
			$l_s_jsN = 'javascript:' . $l_s_js . 'l_o_form.elements[\''. $this->_name .'\'].value=' . $l_i_num .';';
			$l_s_jsN .= 'l_o_form.submit();';
			$l_o_lnk =& $this->addLink('', $l_s_jsN);
			$l_o_lnk->addImage('prev'.$this->_name, 'Bn/Img/next.png');
		}
		else
		{
			$this->addImage('prev'.$this->_name, 'Bn/Img/next.png');
		}

		// Pour aller a la derniere page
		if ( $this->_numPage < $l_i_nbPage)
		{
			$l_s_jsN = 'javascript:' . $l_s_js . 'l_o_form.elements[\''. $this->_name .'\'].value=' . $l_i_nbPage .';';
			$l_s_jsN .= 'l_o_form.submit();';
			$l_o_lnk =& $this->addLink('', $l_s_jsN);
			$l_o_lnk->addImage('prev'.$this->_name, 'Bn/Img/last.png');
		}
		else
		{
			$this->addImage('prev'.$this->_name, 'Bn/Img/last.png');
		}

		return parent::toHtml();
	}
}

