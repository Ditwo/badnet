<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';

class Obill extends Object
{
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
		$date = date('d-m-Y');
		$pdf->Cell(0, 6, 'Date : ' . $date, 0, 1);
		$this->setVal('date', $date);
		$this->save();

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

	/**
	 * Commande pdf pour les commandes corespondantes a la facture
	 *
	 */
	public function pdfCommand()
	{
		// Numero de commande
		$dir = '../Commandes/';
		$numComd = ''; //@todo a voir si besoin avec commande existante
		if ( empty($numComd) )
		{
			$thisyear = date('Y');
			$num = 0;
			$files = scandir($dir);
			foreach($files as $file)
			{
				if ( is_dir($file) ) continue;
				
				$year  = substr($file, 1, 4);
				$token = substr($file, 5, 4);
				if ($year == $thisyear) $num = max($num, $token);
			}
			$num++;
			$numComd = 'C' . $thisyear .  sprintf('%04d', $num);
			$protect = rand(1000, 10000);
			$file = $numComd . '_' . $protect . '.pdf';
			$this->setVal('pdfcomd', $file);
			$this->save();
			$filename = $dir . $file;
		}
		
		$adr1 = $this->getVal('adr1');
		$adr2 = $this->getVal('adr2');
		$adr3 = $this->getVal('adr3');
		$adr4 = $this->getVal('adr4');

		//Recuperer commandes a mettre dans la facture
		$commandIds = Ocommand::getCommandBill($this->getVal('id'));

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
		$pdf->SetXY(110, 20);
		$pdf->Cell(0, 6, 'Commande n° ' . $numComd, 0, 1);

		// Date du jour
		$pdf->SetFont('helvetica', '', 12);
		$pdf->SetXY(110, 25);
		$pdf->Cell(0, 6, 'Date : ' . Bn::date($this->getVal('date'), 'd-m-Y'), 0, 1);

		// Destinataire
		$pdf->SetXY(120, 40);
		$pdf->setFillColor(210);
		$pdf->Cell(70, 6, $adr1, 0, 2, 'C', 1);
		$pdf->Cell(70, 6, $adr2, 0, 2, 'C', 1);
		$pdf->Cell(70, 5, $adr3, 0, 2, 'C', 1);
		$pdf->Cell(70, 5, $adr4, 0, 2, 'C', 1);
		$pdf->Cell(70, 5, $adr5, 0, 2, 'C', 1);

		// En tete des colonne
		$pdf->SetXY(10, 80);
		$pdf->setTextColor(255);
		$pdf->setFillColor(0);
		$pdf->setDrawColor(255);
		$pdf->SetFont('helvetica', 'I', 12);
		$pdf->Cell(135, 6, 'Libellé', 'R', 0, 'C', 1);
		$pdf->Cell(15, 6, 'Qté', 'R', 0, 'C', 1);
		$pdf->Cell(20, 6, 'P.U. H.T.', 0, 0, 'C', 1);
		$pdf->Cell(20, 6, 'Prix H.T.', 0, 1, 'C', 1);
		$pdf->setDrawColor(0);

		// nombre de lignes
		$pdf->setTextColor(0);
		$total = 0;
		$top = 86;
		$maxtop = $top+130;

		// Une ligne pour chaque commande
		foreach($commandIds as $commandId)
		{
			$oCommand = new Ocommand($commandId);
			$value = $oCommand->getVal('value', 0);
			$pdf->SetXY(10, $top);
			$pdf->multiCell(135, 8, $oCommand->getVal('name'), 'LR', 'L', 0, 1);
			$height = $pdf->getY() - $top;
			$pdf->SetXY(145, $top);
			$pdf->multiCell(15, $height, '1', 'LR', 'R', 0, 0);
			$pdf->multiCell(20, $height, sprintf('%.02f', $value) . ' €', 'LR', 'R', 0, 0);
			$pdf->multiCell(20, $height, sprintf('%.02f', $value) . ' €', 'LR', 'R', 0, 1);
			$total += $value;
			unset($oCommand);
			$top += $height;
		}
		$height = $maxtop - $top;
		$pdf->SetXY(10, $top);
		$pdf->multiCell(135, $height, '', 'LRB', 'R', 0, 0);
		$pdf->multiCell(15, $height, '', 'LRB', 'R', 0, 0);
		$pdf->multiCell(20, $height, '', 'LRB', 'R', 0, 0);
		$pdf->multiCell(20, $height, '', 'LRB', 'R', 0, 1);
		$top = $maxtop;

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


	/**
	 * Constructeur
	 * @param	integer	$aBillId	Identifiant
	 * @return OBill
	 */
	public function __construct($aBillId=-1)
	{
		if ($aBillId >0)
		{
			$where = "bill_id=" . $aBillId;
			$this->load('bill', $where, '_asso');
		}
	}

	public function save()
	{
		$billId = $this->getVal('id', -1);
		$where = 'bill_id=' . $billId;
		$id = $this->update('bill', 'bill_', $this->getValues(), $where, '_asso');
		return $id;
	}

	public function delete()
	{
		// Suppression de la commande
		$q = new BN_query('bill', '_asso');
		$q->deleteRow('bill_id=' . $this->getVal('id', -1));
	}
	
}
?>
