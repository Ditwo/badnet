<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';
$locale = BN::getLocale();
require_once 'Locale/' . $locale . '/Oaccount.inc';


class Oadhe extends Object
{
	/**
	 * Constructeur
	 */
	public function __construct($aAccountId = -1)
	{
		if ($aAccountId > 0)
		{
			$q = new Bn_query('u2a, adherents', '_asso');
			$q->addField('adhe_civilite',  'civilite');
			$q->addField('adhe_name',      'name');
			$q->addField('adhe_firstname', 'firstname');
			$q->addField('adhe_rue',   'rue');
			$q->addField('adhe_lieu',  'lieu');
			$q->addField('adhe_appt',  'appt');
			$q->addField('adhe_code',  'code');
			$q->addField('adhe_ville', 'ville');
			$q->addField('adhe_cedex', 'cedex');
			$q->addField('adhe_email', 'email');
			$q->addField('adhe_emailtresor', 'emailtresor');
			$q->addField('adhe_emailsenior', 'emailsenior');
			$q->addField('adhe_emailyoung', 'emailyoung');
			$q->addField('adhe_fixe',   'fixe');
			$q->addField('adhe_mobile', 'mobile');
			$q->addField('adhe_ticknets', 'ticknets');
			$q->addField('adhe_parrain', 'parrain');
			$q->addField('adhe_directline', 'directline');
			$q->addField('adhe_id',   'id');
			$q->setWhere('u2a_userid=' . $aAccountId);
			$q->addWhere('u2a_adherentid=adhe_id');
			$adhe = $q->getRow();
			if ( !empty($adhe) )
			{
				$adhe['labcivilite'] = constant('LABEL_' . $adhe['civilite']);
			}
			$adhe['userid'] = $aAccountId;
			$this->setValues($adhe, true);
			unset($q);
		}
	}

	/**
	 * Destructeur
	 */
	public function __destruct()
	{
		parent::__destruct();
	}

	public function displayContact($aBalise)
	{
		$div = $aBalise->addDiv('adheDivContact', 'bn-div-criteria');
		$div->setAttribute('style', 'width:300px;');
		$oRib = $this->getRib();
		$div->addP('', "Contact", 'bn-title-1');
		$str = $this->getVal('labcivilite') . ' '
		 . $this->getVal('firstname') . ' '
		 . $this->getVal('name');
		 $div->addP('', $str, 'bn-title-4');
		 
		 $str = $this->getVal('rue');
		 $div->addP('', $str);

		 $str = $this->getVal('lieu');
		 $appt = $this->getVal('appt');
		 if (!empty($appt)) $str .= 'Appt ' . $appt;
		 if (!empty($str)) $div->addP('', $str);
		 
		 $str = $this->getVal('code') . ' '
		 . $this->getVal('ville') . ' '
		 . $this->getVal('cedex');
		 $div->addP('', $str);

		 $str = $this->getVal('fixe');
		 if (!empty($str)) $div->addP('', "Tel : " . $str);
		 $str = $this->getVal('mobile');
		 if (!empty($str)) $div->addP('', "Mob : " . $str);
		 $str = $this->getVal('email');
		 if (!empty($str)) $div->addP('', "Email : " . $str);
		 $str = $this->getVal('emailtresor');
		 if (!empty($str)) $div->addP('', "Trésorier : " . $str);
		 $str = $this->getVal('emailsenior');
		 if (!empty($str)) $div->addP('', "Resp. sénior : " . $str);
		 $str = $this->getVal('emailyoung');
		 if (!empty($str)) $div->addP('', "Resp. jeunes : " . $str);
		return;
	}
	
	
	public function displayRib($aBalise)
	{
		$div = $aBalise->addDiv('adheDivRiv', 'bn-div-criteria');
		$div->setAttribute('style', 'width:400px;');
		$oRib = $this->getRib();
		$div->addP('', "Relevé d'identité bancaire", 'bn-title-1');
		$div->addP('', $oRib->getVal('titulaire'), 'bn-title-4');
		$div->addP('', $oRib->getVal('banque'), 'bn-title-4');
		$t = $div->addBalise('table', 'tableDomi');
		$t->setAttribute('cellspacing', 0);
		$tr = $t->addBalise('tr');
		$td = $tr->addBalise('td', '', 'DOMICILIATION');
		$td->setAttribute('colspan', 4);
		$tr = $t->addBalise('tr');
		$tr->setAttribute('style', 'font-weight:bold;');
		$style = 'border:solid 1px #ccc;border-bottom:none;';
		$td = $tr->addBalise('td', '', $oRib->getVal('etablissement'));
		$td->setAttribute('style', $style);
		$td = $tr->addBalise('td', '', $oRib->getVal('guichet'));
		$td->setAttribute('style', $style);
		$td = $tr->addBalise('td', '', $oRib->getVal('compte'));
		$td->setAttribute('style', $style);
		$td = $tr->addBalise('td', '', $oRib->getVal('cle'));
		$td->setAttribute('style', $style);
		$tr = $t->addBalise('tr');
		$style = 'border:solid 1px #ccc;border-top:none;';
		$td = $tr->addBalise('td', '', 'Code établissement');
		$td->setAttribute('style', $style);
		$td = $tr->addBalise('td', '', 'Code guichet');
		$td->setAttribute('style', $style);
		$td = $tr->addBalise('td', '', 'Numéro de compte');
		$td->setAttribute('style', $style);
		$td = $tr->addBalise('td', '', 'Clé RIB');
		$td->setAttribute('style', $style);
		
		$t = $div->addBalise('table', 'tableIban');
		$t->setAttribute('cellspacing', 0);
		$tr = $t->addBalise('tr');
		$td = $tr->addBalise('td', '', 'IBAN (International Bank Account Number');
		$td->setAttribute('colspan', 7);
		$tr = $t->addBalise('tr');
		$iban = $oRib->getVal('iban');
		for($i=0; $i<7;$i++)
		{
			$str = substr($iban, $i*4, 4);
			$td = $tr->addBalise('td', '', $str);
			$td->setAttribute('style', 'border:solid 1px #ccc;');
		}

		$t = $div->addBalise('table', 'tableSwift');
		$t->setAttribute('cellspacing', 0);
		$tr = $t->addBalise('tr');
		$td = $tr->addBalise('td', '', 'code BIC (Bank Identification Code - code swift:');
		$tr = $t->addBalise('tr');
		$tr->setAttribute('style', 'font-weight:bold;');
		$tr->addBalise('td', '', $oRib->getVal('swift'));

		return;
	}

	public function getDu()
	{
		$q = new Bn_query('commands', '_asso');
		$fields  = array('sum(comd_value-comd_paid-comd_discount) as due');
		$q->setFields($fields);
		$q->addWhere("comd_adherentid = " . $this->getVal('id', -1));
		$q->addWhere("comd_season = " . Oseason::getCurrent());
		$du = $q->getFirst();
		return $du;
	}

	/**
	 * enregistre en bdd les donnees adherents
	 */
	public function save()
	{
		$adheId = $this->getVal('id', -1);
		// Enregistrer les donnees
		$where = 'adhe_id='.$adheId;
		$id = $this->update('adherents', 'adhe_', $this->getValues(), $where, '_asso');

		// Mise a jour de la relation adherent/users
		if ($adheId == -1)
		{
			$q = new Bn_query('u2a', '_asso');
			$q->setValue('u2a_userid', $this->getValue('userid', -1));
			$q->addValue('u2a_adherentid', $id);
			$q->setWhere('u2a_userid='.$this->getValue('userid', -1));
			$q->addRow();
			if ( $q->isError() )
			{
				$this->_error($q->getMsg());
			}
		}
		unset($q);
		return;
	}

	/**
	 * Supprime le lien avec l'association
	 */
	public function unlinkAssociation()
	{
		$q = new BN_query('rights');
		$q->addWhere('rght_theme =' .  THEME_ASSOS);
		$q->addWhere("rght_status ='" . AUTH_MANAGER . "'");
		$q->addWhere('rght_userid =' . $this->getValue('userid', -1));
		$q->deleteRow();
		unset($q);
		return true;
	}

	/**
	 * Renvoi l'association de l'adherent
	 */
	public function getAssociation()
	{
		$q = new BN_query('rights');
		$q->addField('rght_themeid');
		$q->addWhere('rght_theme = ' . THEME_ASSOS);
		$q->addWhere("rght_status ='" . AUTH_MANAGER . "'");
		$q->addWhere('rght_userid =' . $this->getValue('userid', -1));
		$assoId = $q->getFirst();
		if ( $q->isError() )
		{
			$this->_error($q->getMsg());
		}
		if (empty($assoId)) $assoId = -1;
		$oAsso = new Oasso($assoId);
		// Verifier que l'association existe toujours
		// Si ce n'est pas la cas, supprimer les droits
		if ($oAsso->isError()) $q->deleteRow();
		unset($q);
		return $oAsso;
	}

	/**
	 * Renvoi le rib de l'adherent
	 */
	public function getRib()
	{
		$q = new BN_query('rib', '_asso');
		$q->addField('rib_id');
		$q->addWhere('rib_adherentid =' . $this->getVal('id', -1));
		$ribId = $q->getFirst();
		if ( $q->isError() )
		{
			$this->_error($q->getMsg());
		}
		if (empty($ribId)) $ribId = -1;
		$oRib = new Orib($ribId);
		unset($q);
		return $oRib;
	}

	/**
	 * Renvoi les adhesions pour la saison en cours
	 */
	public function getAdhesions($aSaison = null)
	{
		require_once 'Object/Ocommand.inc';
		if (is_null($aSaison) )
		{
			require_once "Object/Oseason.php";
			$season = Oseason::getCurrent();
		}
		else
		{
			$season = $aSaison;
		}

		$q = new Bn_query('commands, u2a', '_asso');
		$q->setFields('comd_id, comd_objectid, comd_name');
		$q->addField('comd_value-comd_paid-comd_discount');
		$q->addWhere('comd_type=' . OCOMMAND_TYPE_ADHESION);
		$q->addWhere('comd_adherentid=u2a_adherentid');
		$q->addWhere('u2a_userid='.Bn::getValue('user_id'));
		$q->addWhere('comd_season='.$season);
		$res = $q->getRows();
		if ( $q->isError() )
		{
			$this->_error($q->getMsg());
		}
		unset($q);
		return $res;
	}

	/**
	 * Ajout ou (enleve si negatif) des ticknet a un adherant (gestion des dons ou cadeau parrainage...)
	 * @param int $aNb
	 * @param boolean $aObject
	 */
	public function addTicknets($aNb, $aObject, $aRemboursable = false)
	{
		require_once('Object/Oticknet.inc');

		// Mise a jour du nombre de ticknet de l'adherent
		$nbTicknets = $this->getVal('ticknets', 0) + $aNb;
		$this->setVal('ticknets', $nbTicknets);

		// Mise a jour du nombre de ticknet parrainage (5%)
		if ($aNb > 0)
		{
			$parrain = $this->getVal('parrain', 0) + ($aNb*0.05);
			$this->setVal('parrain', $parrain);
		}

		// Mise a jour du nombre de ticknets remboursables
		$remboursable = $this->getVal('remboursable', 0);
		if ($aRemboursable) $remboursable +=  $aNb;
		$remboursable = min($remboursable, $nbTicknets);
		$this->setVal('remboursable', $remboursable);
		$this->save();

		// Ecriture des ticknets
		$oTicknet = new Oticknet();
		$oTicknet->setVal('nb',  $aNb);
		$oTicknet->setVal('date', date('Y-m-d'));
		$oTicknet->setVal('object', $aObject);
		$oTicknet->setVal('adherentid', $this->getVal('id'));
		$oTicknet->setVal('season', Oseason::getCurrent());
		$oTicknet->save();
		unset($oTicknet);

		return;
	}

	/**
	 * Renvoi les ecriture non paye
	 */
	public function getSolde()
	{
		$q = new Bn_query('commands, u2a', '_asso');
		$q->setFields('comd_id, comd_season, comd_name');
		$q->addField('comd_value-comd_paid-comd_discount', 'solde');
		$q->addWhere('comd_adherentid=u2a_adherentid');
		$q->addWhere('u2a_userid='.Bn::getValue('user_id'));
		//$q->addWhere('solde>0');
		$res = $q->getRows();
		if ( $q->isError() )
		{
			$this->_error($q->getMsg());
		}
		unset($q);
		return $res;
	}

	/**
	 * Achat pour l'adherent : verifie si le nombre de ticknet est suffisant
	 * et le decremente apres entegistrement de l'achat
	 *
	 * @param int $aNb
	 * @param boolean $aForce
	 */
	public function buy($aNb, $aObject, $aRef = null)
	{
		$nbTicknets = $this->getVal('ticknets');
		if ($nbTicknets < $aNb) return false;

		// Enregistrement de l'achat
		$oTicknet = new Oticknet();
		$oTicknet->setVal('nb',  -$aNb);
		$oTicknet->setVal('date', date('Y-m-d'));
		$oTicknet->setVal('object', $aObject);
		$oTicknet->setVal('adherentid', $this->getVal('id'));
		$oTicknet->setVal('ref', $aRef);
		$oTicknet->setVal('season', Oseason::getCurrent());
		$oTicknet->save();
		unset($oTicknet);

		// Mise a jour du nombre de ticknet de l'adherent
		$nbTicknets -= $aNb;
		$this->setVal('ticknets', $nbTicknets);
		$this->save();
		return;
	}

	/**
	 * Renvoi l'email du tresorier
	 *
	 */
	public function getEmailTresor()
	{
		$email = $this->getVal('emailtresor');
		if (empty($email) )	$email = $this->getVal('email');
		return $email;
	}

	/**
	 * Facture pdf pour achat de ticknets
	 * @param Ocommand $aCommand Commande a l'origine de la facture
	 *
	 */
	public function pdfBill($aCommand)
	{
		$oAsso = $this->getAssociation();

		$adr1 = $oAsso->getVal('name');
		$adr2 = $this->getVal('firstname') . ' ' . $this->getVal('name');
		$adr3 = $this->getVal('rue');
		$lieu = $this->getVal('lieu');
		$appt = $this->getVal('appt');
		if ( !empty($lieu) || !empty($appt) )
		{
			$adr4 = $lieu . ' ' . $appt;
			$adr5 = $this->getVal('code') . ' ' . $this->getVal('ville') . ' ' . $this->getVal('cedex');
		}
		else
		{
			$adr4 = $this->getVal('code') . ' ' . $this->getVal('ville') . ' ' . $this->getVal('cedex');
			$adr5 = '';
		}

		// Numero de facture
		$year = date('Y');
		$dir = '../Factures/';
		$num = 0;
		if ($dh = opendir($dir))
		{
			while (($file = readdir($dh)) !== false)
			{
				$token = preg_split('/[ _.]/', $file);
				if ($token[1] == $year) $num = max($num, $token[2]);
			}
			closedir($dh);
		}
		$num++;
		$numFacture = Bn::getConfigValue('facture', 'params') . '_' . $year . '_' . sprintf('%04d', $num);
		$protect = rand(1000, 10000);
		$file = $numFacture . '_' . $protect . '.pdf';
		while (file_exists($file))
		{
			$protect = rand(1000, 10000);
			$file = $numFacture . '_' . $protect . '.pdf';
		}
		$filename = $dir.$file;
			
		//Recuperer items a mettre dans la facture
		require_once 'Bn/Bnpdf.php';

		$pdf = new Bnpdf();
		$pdf->setAutoPageBreak(false);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->start();
		$pdf->addPage('P');

		$pdf->SetFont('helvetica', '', 12);
		$pdf->image('../Themes/Badnet/Img/Badnet.png', 8, 8, 0, 10);
		$pdf->SetXY(8, 18);
		$pdf->Cell(0, 6, 'Badminton Netware', 0, 2);
		$pdf->Cell(0, 6, 'N° siren 514 165 935', 0, 2);
		$pdf->Cell(0, 6, '53 rue du Caillou Gris', 0, 2);
		$pdf->Cell(0, 6, '31200 Toulouse', 0, 2);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->Cell(0, 6, "Dispensé d'immatriculation au registre du commerce et", 0, 2);
		$pdf->Cell(0, 6, "des sociétés (RCS) et au répertoire des métiers (RM)", 0, 1);

		$pdf->SetXY(102, 8);
		$pdf->setTextColor(255);
		$pdf->setFillColor(0);
		$pdf->SetFont('helvetica', 'BI', 12);
		$pdf->Cell(100, 6, 'La promotion du sport par le net', 0, 1, 'C', 1);

		// Numéro de facture
		$pdf->SetFont('helvetica', 'B', 12);
		$pdf->setTextColor(0);
		$pdf->SetXY(140, 20);
		$pdf->Cell(0, 6, 'Facture n° ' . $numFacture, 0, 1);

		// Date du jour
		$pdf->SetFont('helvetica', '', 12);
		$pdf->SetXY(140, 25);
		$pdf->Cell(0, 6, 'Date : ' . date('d-m-Y'), 0, 1);

		// Destinataire
		$pdf->SetXY(132, 40);
		$pdf->setFillColor(210);
		$pdf->Cell(70, 6, $adr1, 0, 2, 'C', 1);
		$pdf->Cell(70, 6, $adr2, 0, 2, 'C', 1);
		$pdf->Cell(70, 5, $adr3, 0, 2, 'C', 1);
		$pdf->Cell(70, 5, $adr4, 0, 2, 'C', 1);
		$pdf->Cell(70, 5, $adr5, 0, 2, 'C', 1);

		// En tete des colonne
		$pdf->SetXY(20, 80);
		$pdf->setTextColor(255);
		$pdf->setFillColor(0);
		$pdf->setDrawColor(255);
		$pdf->SetFont('helvetica', 'I', 12);
		$pdf->Cell(75, 6, 'Désignation', 'R', 0, 'C', 1);
		$pdf->Cell(15, 6, 'Qté', 'R', 0, 'C', 1);
		$pdf->Cell(40, 6, 'Prix unitaire HT', 0, 0, 'C', 1);
		$pdf->Cell(40, 6, 'Total HT', 0, 1, 'C', 1);
		$pdf->setDrawColor(0);

		// nombre de lignes
		$nbMaxLine = 20;
		$numLine = 0;
		$total = 0;
		$pdf->setTextColor(0);
		$top = 86;

		// Une seule ligne
		$value = $aCommand->getVal('value');
		$pdf->SetXY(20, $top);
		$pdf->multiCell(75, 120, $aCommand->getVal('name'), 'LRB', 'L', 0, 0);
		$pdf->multiCell(15, 120, '1', 'LRB', 'R', 0, 0);
		$pdf->multiCell(40, 120, sprintf('%.02f', $value) . ' €', 'LRB', 'R', 0, 0);
		$pdf->multiCell(40, 120, sprintf('%.02f', $value) . ' €', 'LRB', 'R', 0, 1);
		$top += 120;
		$total += $value;

		// Total
		$pdf->SetFont('helvetica', 'B', 12);
		$top += 2;
		$pdf->SetXY(20, $top);
		$pdf->Cell(136, 6, 'Total HT', '', 0, 'R');
		$pdf->Cell(34, 6, sprintf('%.02f', $total) . ' €', 1, 0, 'R');

		$pdf->SetFont('helvetica', '', 10);
		$top += 6;
		$pdf->SetXY(20, $top);
		$pdf->Cell(170, 6, 'TVA non applicable, art. 293 B du CGI', '', 0, 'R');

		// Réglement
		$top = 250;
		$pdf->SetXY(10, $top);
		$str = 'Par chèque à l\'ordre de Badminton Netware. Envoyer à :';
		$str .= 'Badminton Netware 53 rue du Caillou Gris 31200 Toulouse France';
		$pdf->Cell(0, 6, $str, '', 2, 'L');

		$str = 'Par virement :';
		$pdf->Cell(0, 6, $str, '', 2, 'L');
		$str = 'Banque Crédit Agricole Toulouse Minimes ';
		$str .= 'Code banque 13106 Code guichet 00500 Compte 20006205063 Rib 38';
		$pdf->Cell(0, 6, $str, '', 2, 'L');
		$pdf->Cell(0, 6, 'IBAN FR76 1310 6005 0020 0062 0506 338', '', 0, 'L');

		$pdf->end($filename, false);
		return $filename;
	}

}
?>
