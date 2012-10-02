/* French initialisation for the jQuery UI date picker plugin. */
/* Written by Keith Wood (kbwood@iprimus.com.au). */
jQuery(function($){
	$.datepicker.regional['fr'] = {
	    clearText: 'Effacer', 
	    clearStatus: '',
		closeText: 'Fermer', 
		closeStatus: '',
		prevText: '&lt;Pr&eacute;c', 
		prevStatus: '',
		nextText: 'Suiv&gt;', 
		nextStatus: '',
		currentText: 'Ce jour', 
		currentStatus: '',
		monthNames: ['Janvier','F&eacute;vrier','Mars','Avril','Mai','Juin',
		'Juillet','Ao&ucirc;t','Septembre','Octobre','Novembre','D&eacute;cembre'],
		monthNamesShort: ['Jan','F&eacute;v','Mar','Avr','Mai','Jun','Jul','Ao&ucirc;','Sep','Oct','Nov','D&eacute;c'],
		monthStatus: '', 
		yearStatus: '',
		weekHeader: 'Sm', 
		weekStatus: '',
		dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
		dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
		dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
		dayStatus: 'DD', 
		dateStatus: 'D, M d',
		dateFormat: 'dd/mm/yy', 
		firstDay: 1, 
		initStatus: '', 
		isRTL: false
		};
	$.datepicker.setDefaults($.datepicker.regional['fr']);
});
