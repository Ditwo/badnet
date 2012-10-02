<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
require_once 'Object.php';


class Ogeo extends Object
{

	/**
	 * Renvoi la liste des régions avec l'id Poona
	 *
	 * return array idPoona=>Nom region
	 */
	public function getRegions($aValue=null, $aText =null)
	{
		if (!is_null($aValue))
		$reg[$aValue] = $aText;
		$reg['2'] = 'Alsace';
		$reg['3'] = 'Aquitaine';
		$reg['4'] = 'Auvergne';
		$reg['5'] = 'Basse Normandie';
		$reg['6'] = 'Bourgogne';
		$reg['7'] = 'Bretagne';
		$reg['8'] = 'Centre';
		$reg['9'] = 'Champagne Ardenne';
		$reg['10'] = 'Franche Comté';
		$reg['26'] = 'Guyane';
		$reg['11'] = 'Haute Normandie';
		$reg['12'] = 'Ile de France';
		$reg['15'] = 'Languedoc Roussillon';
		$reg['23'] = 'La Réunion';
		$reg['13'] = 'Limousin';
		$reg['14'] = 'Lorraine';
		$reg['24'] = 'Martinique';
		$reg['16'] = 'Midi Pyrénées';
		$reg['17'] = 'Nord Pas de Calais';
		$reg['25'] = 'Nouvelle Calédonie';
		$reg['21'] = 'Pays de la Loire';
		$reg['20'] = 'Picardie';
		$reg['19'] = 'Poitou Charentes';
		$reg['18'] = 'Provence Alpes Côte d\'Azur';
		$reg['22'] = 'Rhônes Alpes';
		return $reg;
	}

	/**
	 * Renvoi la tableau des codeps avec id pooona
	 *
	 * @return array idPoona => nom departement
	 */
	public function getDepts($aValue=null, $aText =null)
	{
		if ( Bn::getConfigValue('squash', 'params') ) return Ogeo::getDeptsSquash($aValue=null, $aText =null);
		
		if (!is_null($aValue)) $reg[$aValue] = $aText;
		
		$reg[107] = '01 Ain';
		$reg[99] = '02 Aisne';
		$reg[35] = '03 Allier';
		$reg[18] = '04 Alpes-de-Haute-Provences'; // Pas de codep 04, ligue Paca
		$reg[90] = '05 Hautes-Alpes';
		$reg[91] = '06 Alpes-Maritimes';
		$reg[108] = '07 Ardèche - 26 Drôme';
		$reg[54] = '08 Ardennes';
		$reg[82] = '09 Ariège';
		$reg[55] = '10 Aube';
		$reg[77] = '11 Aude';
		$reg[83] = '12 Aveyron';
		$reg[92] = '13 Bouche-du-Rhône';
		$reg[38] = '14 Calvados';
		$reg[36] = '15 Cantal';
		$reg[95] = '16 Charente';
		$reg[96] = '17 Charente-Maritime';
		$reg[48] = '18 Cher';
		$reg[70] = '19 Corrèze';
		//??=> '2A Corse-du-Sud,
		//??=> '2B Haute-Corse,
		$reg[41] = '21 Côtes-d\'Or';
		$reg[44] = '22 Côtes-d\'Armor';
		$reg[71] = '23 Creuse';
		$reg[30] = '24 Dordogne';
		$reg[57] = '25 Doubs';
		//108 => '26 Drôme', // Voir 07 Ardeche
		$reg[60] = '27 Eure';
		$reg[49] = '28 Eure-et-Loir';
		$reg[45] = '29 Finistère';
		$reg[78] = '30 Gard';
		$reg[84] = '31 Haute garonne';
		$reg[85] = '32 Gers - 65 Hautes-Pyrénées';
		$reg[31] = '33 Gironde';
		$reg[79] = '34 Hérault';
		$reg[46] = '35 Ile-et-Vilaine';
		$reg[50] = '36 Indre';
		$reg[51] = '37 Indre-et-Loire';
		$reg[110] = '38 Isère';
		$reg[58] = '39 Jura';
		$reg[32] = '40 Landes';
		$reg[52] = '41 Loir-et-Cher';
		$reg[111] = '42 Loire';
		$reg[2071] = '43 Haute-Loire';
		$reg[102] = '44 Loire-Atlantique';
		$reg[53] = '45 Loiret';
		$reg[86] = '46 Lot';
		$reg[33] = '47 Lot-et-Garonne';
		$reg[80] = '48 Lozère';
		$reg[103] = '49 Maine-et-Loire';
		$reg[39] = '50 Manche';
		$reg[56] = '51 Marne';
		//?? => '52 Haute-Marne';
		$reg[104] = '53 Mayenne';
		$reg[73] = '54 Meurthe-et-Moselle';
		$reg[74] = '55 Meuse';
		$reg[47] = '56 Morbihan';
		$reg[75] = '57 Moselle';
		//?? => '58 Nièvre';
		$reg[88] = '59 Nord';
		$reg[100] = '60 Oise';
		$reg[40] = '61 Orne';
		$reg[89] = '62 Pas-de-Calais';
		$reg[37] = '63 Puy-de-Dôme';
		$reg[34] = '64 Pyrénées-Atlantiques';
		//85 => '65 Hautes-Pyrénées'; // Voir 32 gers
		$reg[81] = '66 Pyrénées-Orientales';
		$reg[28] = '67 Bas-Rhin';
		$reg[29] = '68 Haut-Rhin';
		$reg[112] = '69 Rhônes';
		$reg[59] = '70 Haute-Saône';
		$reg[42] = '71 Saône-et-Loire';
		$reg[105] = '72 Sarthe';
		$reg[113] = '73 Savoie';
		$reg[114] = '74 Haute-Savoie';
		$reg[62] = '75 Paris';
		$reg[61] = '76 Seine-Maritine';
		$reg[63] = '77 Seine-et-Marne';
		$reg[64] = '78 Yvelines';
		$reg[97] = '79 Deux-Sèvres';
		$reg[101] = '80 Somme';
		$reg[27] = '81 Tarn';
		$reg[87] = '82 Tarn-et-Garonne';
		$reg[93] = '83 Var';
		$reg[94] = '84 Vaucluse';
		$reg[106] = '85 Vendée';
		$reg[98] = '86 Vienne';
		$reg[72] = '87 Haute-Vienne';
		$reg[76] = '88 Vosges';
		$reg[43] = '89 Yonne, 90 Territoire de Belfort';
		//43 => '90 Territoire de Belfort';
		$reg[65] = '91 Essone';
		$reg[66] = '92 Hauts-de-Seine';
		$reg[67] = '93 Seine-Saint-Denis';
		$reg[68] = '94 Val-de-Marne';
		$reg[69] = "95 Val-d'Oise";
		// ?? => '971 Guadeloupe';
		// ?? => '972 Martinique';
		// ?? => '973 Guyane';
		// ?? => '974 La Réunion';
		// ?? => '975 Saint-Pierre-et-Miquelon';
		// ?? => '976 Mayotte';
		// ?? => '984 Terres Australes et Antarctiques',
		// ?? => '986 Wallis et Futuna',
		// ?? => '987 Polynésie Française',
		// ?? => '988 Nouvelle-Calèdonie',
		return  $reg;
	}

	static public function getDeptsSquash($aValue, $aText)
	{
		if (!is_null($aValue)) $reg[$aValue] = $aText;
		$reg = array( 1 => '01 Ain', '02 Aisne', '03 Allier', '04 Alpes-de-Haute-Provences',
		'05 Hautes-Alpes', '06 Alpes-Maritimes', '07 Ardèche', '08 Ardennes', '09 Ariège',
		'10 Aube', '11 Aude', '12 Aveyron', '13 Bouche-du-Rhône', '14 Calvados', '15 Cantal',
		'16 Charente', '17 Charente-Maritime', '18 Cher', '19 Corrèze', '20 Corse', '21 Côtes-d\'Or',
		'22 Côtes-d\'Armor', '23 Creuse', '24 Dordogne', '25 Doubs', '26 Drôme', '27 Eure',
		'28 Eure-et-Loir', '29 Finistère', '30 Gard', '31 Haute garonne', '32 Gers', '33 Gironde',
		'34 Hérault', '35 Ile-et-Vilaine', '36 Indre', '37 Indre-et-Loire', '38 Isère', '39 Jura',
		'40 Landes', '41 Loir-et-Cher', '42 Loire', '43 Haute-Loire', '44 Loire-Atlantique',
		'45 Loiret', '46 Lot', '47 Lot-et-Garonne', '48 Lozère', '49 Maine-et-Loire', '50 Manche',
		'51 Marne', '52 Haute-Marne', '53 Mayenne', '54 Meurthe-et-Moselle', '55 Meuse', '56 Morbihan',
		'57 Moselle', '58 Nièvre', '59 Nord', '60 Oise', '61 Orne', '62 Pas-de-Calais', '63 Puy-de-Dôme',
		'64 Pyrénées-Atlantiques', '65 Hautes-Pyrénées', '66 Pyrénées-Orientales', '67 Bas-Rhin',
		'68 Haut-Rhin', '69 Rhônes', '70 Haute-Saône', '71 Saône-et-Loire', '72 Sarthe', '73 Savoie',
		'74 Haute-Savoie', '75 Paris', '76 Seine-Maritine', '77 Seine-et-Marne', '78 Yvelines',
		'79 Deux-Sèvres', '80 Somme', '81 Tarn', '82 Tarn-et-Garonne', '83 Var', '84 Vaucluse',
		'85 Vendée', '86 Vienne', '87 Haute-Vienne', '88 Vosges', '89 Yonne', '90 Territoire de Belfort',
		'91 Essone', '92 Hauts-de-Seine', '93 Seine-Saint-Denis', '94 Val-de-Marne', "95 Val-d'Oise");
		return  $reg;
	}
}
?>
