<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
include_once 'Object/Opair.inc';

// Status des paires 
define('LABEL_' . OPAIR_STATUS_DNS,       'Simple');
define('LABEL_' . OPAIR_STATUS_MAINDRAW,  'Tableau final');
define('LABEL_' . OPAIR_STATUS_QUALIF,    'Qualification');
define('LABEL_' . OPAIR_STATUS_RESERVE,   'Liste d\'attente');
define('LABEL_' . OPAIR_STATUS_PTQ,       'Promu dans les qualifs');
define('LABEL_' . OPAIR_STATUS_PFR,       'Promu de la liste d\'attente');
define('LABEL_' . OPAIR_STATUS_PTM,       'Promu dans le tableau final');
define('LABEL_' . OPAIR_STATUS_PFQ,       'Promu des qualifs');
define('LABEL_' . OPAIR_STATUS_WDN,   	  'Forfait');
define('LABEL_' . OPAIR_STATUS_INJ,       'Blessé');
define('LABEL_' . OPAIR_STATUS_NONE,      'Aucun');

define('SMA_LABEL_' . OPAIR_STATUS_DNS,       'DNS');
define('SMA_LABEL_' . OPAIR_STATUS_MAINDRAW,  'M');
define('SMA_LABEL_' . OPAIR_STATUS_QUALIF,    'Q');
define('SMA_LABEL_' . OPAIR_STATUS_RESERVE,   'R');
define('SMA_LABEL_' . OPAIR_STATUS_PTQ,       'PTQ');
define('SMA_LABEL_' . OPAIR_STATUS_PFR,       'PFR');
define('SMA_LABEL_' . OPAIR_STATUS_PTM,       'PTM');
define('SMA_LABEL_' . OPAIR_STATUS_PFQ,       'PFQ');
define('SMA_LABEL_' . OPAIR_STATUS_WDN,   	  'WND');
define('SMA_LABEL_' . OPAIR_STATUS_INJ,       'INJ');
define('SMA_LABEL_' . OPAIR_STATUS_NONE,      'None');

// Etat des paires 
define('LABEL_' . OPAIR_STATE_NOK,  'Incomplète');
define('LABEL_' . OPAIR_STATE_REG,  'Inscrite');
define('LABEL_' . OPAIR_STATE_COM,  'Composée');

?>
