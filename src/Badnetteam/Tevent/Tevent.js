/*******************************************************************************
 * ! $Id$
 ******************************************************************************/
function submitTie(aData) {
	$("#targetDlg").empty();
	$("#dlg").dialog('close');
	$("#targetBody").load('index.php', aData);
	return false;
}


function submitParam(aData) {
	$("#targetDlg").empty();
	$("#dlg").dialog('close');
	$("#targetBody").load('index.php', aData);
	return false;
}

function submitTeam() {
    var options = {
      	dataType: 'json',
      	success :  function(a) {
			$("#dlg").dialog('close');

			var data = $('#bteam-col-' + a.numCol).metadata();
			data.tieId = a.tieId;
			data.numCol = a.numCol;
			$('#bteam-col-' + a.numCol).empty();
			$('#bteam-col-' + a.numCol).load('index.php', data);
			}
		};
	$('#frmPlayers').ajaxSubmit(options);
	return false;
}

function pageTeam() {
	$('select').change(function() {
		var queryString = $('#frmPlayers').formSerialize();
		queryString += '&check=1';
		$.post('index.php', queryString, function(a) {
			for (key in a) {
				$('#msg_' + key).html(a[key]);
			}
		}, 'json');
	});
	return false;
}

function control321() {
	var canceled = $("input:checked");
	var elt = '';
	var sc = new Array(2);
	var nbwin = 0;
	var idx = 1;
	var nbWinH = 0;
	var nbWinV = 0;

	for ( var i = 0; i < 3; i++) {
		for ( var j = 0; j < 2; j++) {
			elt = 'sc_' + $('#matchId').val() + '_' + idx++;
			sc[j] = parseInt($('#' + elt).val());
			if (isNaN(sc[j])) {
				elt.value = '';
				sc[j] = 0;
			}
		}

		if (sc[0] == 0 && sc[1] == 0)
			continue;
		if (sc[0] == sc[1] && canceled.length == 0)
			return errorscore(i, 1);

		if (sc[0] > sc[1]) {
			nbWinH++;
		} else {
			nbWinV++;
			tmp = sc[0];
			sc[0] = sc[1];
			sc[1] = tmp;
		}
		if (sc[0] < 0 || sc[1] < 0)
			return errorscore(i, 1);
		if (sc[0] > 30)
			return errorscore(i, 1);
		if (sc[0] == 30 && sc[1] != 29 && sc[1] != 28 && canceled.length == 0)
			return errorscore(i, 2);
		if (sc[0] < 21 && canceled.length == 0)
			return errorscore(i, 1);
		if (sc[0] == 21 && sc[0] - sc[1] < 2 && canceled.length == 0)
			return errorscore(i, 2);
		if (sc[0] > 21 && sc[0] < 30 && sc[0] - sc[1] != 2
				&& canceled.length == 0)
			return errorscore(i, 1);
	}
	if (nbWinH != 2 && nbWinV != 2 && canceled.length == 0)
		return errorscore(1, 0);
	return true;
}

function errorscore(game, player) {
	elt = 'sc_' + $('#matchId').val() + '_' + ((2 * game) + player);
	$('#' + elt).focus();
	return false;
}

function submitScore() {
	var valid = true;
	if (control321() == false)
		valid = confirm("Vous allez enregistrer un score incorrect !");
	if (!valid)
		return false;
    var options = {
      dataType: 'json',
      success :  function(a) {
		$("#dlg").dialog('close');

		var md = $('#slcttie-' + a.numCol).metadata();
		md.tieId = a.tieId;
		$('#bteam-tie-data-' + md.numCol).empty();
		$('#bteam-tie-data-' + md.numCol).load('index.php', md);

		var data = $('#bteam-col-' + a.numCol).metadata();
		data.tieId = a.tieId;
		data.numCol = a.numCol;
		$('#bteam-col-' + md.numCol).empty();
		$('#bteam-col-' + md.numCol).load('index.php', data);

		if (a.status==34) $('#slot-'+a.numCourt).empty();
	}
};
	$('#frmPlayer').ajaxSubmit(options);
	return false;
}

function pageScore() {
	$("#begin").mask("99-99-9999 99:99");
	$("#end").mask("99-99-9999 99:99");
	$('#sc_' + $('#matchId').val() + '_1').focus();

}

function pageTies(a) {
    $(window).unload(function(){
	    $('.bteam-court').stopTime();
	});

	$('#div-courts').sortable();

	// les match en cours sont deplacables
	$(".bteam-match").draggable({
		revert : "invalid",
		containment : $(".Tevent"),
		helper : "clone",
		handle : ".bteam-match-move",
		cursor : "move"
	});

	// Emplacement accepte les matchs
	$('.bteam-slot').droppable({
		accept : ".bteam-match",
		activeClass : "ui-state-highlight",

		drop : function(event, ui) {

			var md = $(this).metadata();
			var matchData = ui.draggable.metadata();
			md.matchId = matchData.matchId;
			var status = $('#status-' + md.matchId).val();
			if(status > 34) return false;

			// Y a t il un match,
			var tmp = $(this).children('.bteam-match');
			if (tmp.length) {
				var meta = $(tmp).metadata();
				md.liveMatchId = meta.matchId;
			} else
				md.liveMatchId = -1;
			// Mise a jour etats des matchs
			$.post('index.php', md, function(a) {
				$('#start-' + a.matchId).val(a.begin);
				$('#status-' + a.matchId).val(a.status);
			}, 'json');
			// Enlever le match existant
			$(this).children('.bteam-match').prependTo(ui.draggable.parent());
			// Ajouter le nouveau match
			ui.draggable.find('.bteam-match-rank').removeClass().addClass('bteam-match-rank bteam-match-status-34');
			ui.draggable.appendTo($(this));

			// Recharger la liste des matchs pour mise a jour etat des joueurs
			$.post('index.php', md, function(a) {
				var donnee = $('#bteam-col-' + matchData.numCol).metadata();
				donnee.tieId = matchData.tieId;
				donnee.numCol = matchData.numCol;
				$('#bteam-col-' + matchData.numCol).empty();
				$('#bteam-col-' + matchData.numCol).load('index.php', donnee);
			}, 'json');

		}
	});

	$('.bteam-court').stopTime();
	$('.bteam-court').everyTime(1000, 'chrono', _chrono);

	// Selection d'une rencontre
	$('select').change(function() {
		var tieId = $(this).val();
		var courts = $('.bteam-tie');
		var stop = false;
		// Si la renocntre est deja affiche, l'enlever
		$(courts).each(function() {
			var tmp = $(this).find('.bteam-tie-order');
			if (tmp.length)
			{
				md = tmp.metadata();
				if (md.tieId == tieId) {
					$('#bteam-tie-data-' + md.numCol).empty();
					$('#bteam-col-' + md.numCol).empty();
					$('#slcttie-' + md.numCol).val(-1);
					return false;
				}
			}
		});

		var md = $(this).metadata();
		md.tieId = tieId;
		$('#bteam-tie-data-' + md.numCol).empty();
		$('#bteam-tie-data-' + md.numCol).load('index.php', md);

		var data = $('#bteam-col-' + md.numCol).metadata();
		data.tieId = tieId;
		data.numCol = md.numCol;
		$('#bteam-col-' + md.numCol).empty();
		$('#bteam-col-' + md.numCol).load('index.php', data);
		return false;
	});

	// Saisie des resultats
   $('.bteam-court .bteam-match-edit').click(function(){
      var md = $(this).metadata();
      $('#dlg').dialog('option', 'title', md.title);
      $('#dlg').dialog('option', 'width',   md.width);
      $('#dlg').dialog('option', 'height', md.height);
   	  $("#dlg").dialog('open');
   	  $("#targetDlg").empty();
   	  $("#targetDlg").load('index.php', md);
	  return false;
	});

    // Initialiser les rencontres selectionnees
	$('select').each(function(){$(this).change();});


	// Afficher la fenetre de chargement si besoin
	if(a == 1)
    {
    	var md = $("#btnLoad").metadata();
        $('#dlg').dialog('option', 'title', md.title);
        $('#dlg').dialog('option', 'width',   md.width);
        $('#dlg').dialog('option', 'height', md.height);
   	 	$("#dlg").dialog('open');
   	 	md.ajax = true;
		$("#targetDlg").load('index.php', md);
    }

	return false;
}

// Remplissage de la rencontre apres choix d'une rencontre
function fillTie(aNum, aTie){
	// Ordre automatique des matchs
	$('#tie-'+aNum+' .bteam-tie-order').click(function(){
		var md = $(this).metadata();
		$.get('index.php', md, function(a){
			var data = $('#bteam-col-' + md.numCol).metadata();
			data.tieId = md.tieId;
			$('#bteam-col-' + md.numCol).empty();
			$('#bteam-col-' + md.numCol).load('index.php', data);
			return false;
		});
		return false;
	});

	// Documents pdf de la rencontre
	$('#tie-'+aNum+' .bteam-tie-print').click(function(){
      $('#tie-'+aNum+' .print-menu').show();

	});

    $('#tie-'+aNum+' .bteam-item-print').click(function(){
      $('#tie-'+aNum+' .print-menu').hide();
   	  var md = $(this).metadata();
   	  var url = 'index.php?bnAction=' + md.bnAction;
   	  for(key in md) {if (key != 'bnAction') url += '&' + key + '=' + md[key];}
   	  var now = new Date();
   	  var name = "bn_" + now.getTime();
   	  var option = "width=100, height=10, top=10, left=100, location=no, menubar=no, toolbar=no, scrollbars=no, resizable=yes, status=no"
   	  window.open(url, name, option);
	  return false;
	});

    $('#tie-'+aNum+' .print-menu').hover(null, function(){
      $(this).hide();
    });

   // Modification rencontre et compo des equipes
   $('#tie-'+aNum+' .bteam-tie-edit, #tie-'+aNum+' .bteam-team-compo').click(function(){
      var md = $(this).metadata();
      $('#dlg').dialog('option', 'title', md.title);
      $('#dlg').dialog('option', 'width',   md.width);
      $('#dlg').dialog('option', 'height', md.height);
   	  $("#dlg").dialog('open');
   	  $("#targetDlg").empty();
   	  $("#targetDlg").load('index.php', md);
	  return false;
	});

	// Aficher/masquer les match termines
	$('#tie-'+aNum+' .bteam-match-hide').click(function(){
	     var isHide = 1 - $('#isHide-' + aNum).val();
	     if (isHide == 1 )$('#bteam-col-'+aNum+' .bteam-status-36').hide();
	     else $('#bteam-col-'+aNum+' .bteam-status-36').show();
	     $('#isHide-' + aNum).val(isHide);
	});

	$('#tie-'+aNum+' .bteam-match-hide').click();
}

// Remplissage des matchs apres choix d'une rencontre
function fillMatchs(aNum, aTie){

	// les match sont deplacables
	$("#bteam-col-" + aNum + " .bteam-match").draggable({
		revert : "invalid",
		containment : $(".Tevent"),
		helper : "clone",
		handle : ".bteam-match-move",
		cursor : "move"
	});

	// La liste terrain accepte les matches de la rencontre
	$('#bteam-col-' + aNum).droppable("destroy");
	$('#bteam-col-' + aNum).droppable({
		accept : ".bteam-match-" + aTie,
		activeClass : "ui-state-highlight",
		drop : function(event, ui) {
			ui.draggable.appendTo($(this));
			var md = $('#divCols').metadata();
			var matchData = ui.draggable.metadata();
			md.matchId = matchData.matchId;
			$.post('index.php', md, function(a) {
					var data = $('#bteam-col-' + matchData.numCol).metadata();
					data.tieId = matchData.tieId;
					data.numCol = matchData.numCol;
					$('#bteam-col-' + matchData.numCol).empty();
					$('#bteam-col-' + matchData.numCol).load('index.php', data);
			}, 'json');
			return false;

		}
	});

	// Saisie des resultats
   $('#bteam-col-'+aNum+' .bteam-match-edit').click(function(){
      var md = $(this).metadata();
      $('#dlg').dialog('option', 'title', md.title);
      $('#dlg').dialog('option', 'width',   md.width);
      $('#dlg').dialog('option', 'height', md.height);
   	  $("#dlg").dialog('open');
   	  $("#targetDlg").empty();
   	  $("#targetDlg").load('index.php', md);
	  return false;
	});

	// monter un match
   $('#bteam-col-'+aNum+' .bteam-match-up').click(function(){
      var md = $(this).metadata();
      // Match concerne
      var matchSrc = $(this).parents('.bteam-match');
      var matchDest = null;
      var textSrc = matchSrc.find('.bteam-match-rank').text();
      var matchDataSrc = matchSrc.metadata();
      var rankSrc = textSrc.split(' ')[1];
      var textDest = 'N° ' + (rankSrc - 1);
      $('.bteam-match').each(function(){
      			var data = $(this).metadata();
      			if( data.tieId == matchDataSrc.tieId && $(this).find('.bteam-match-rank').text() == textDest)
      			{
        		   $(this).find('.bteam-match-rank').text(textSrc);
      			   matchDataDest = data;
      			   matchDest = $(this);
      			   return false;
      			}
      	});

     if (matchDest != null)
     {
     	if( $('#status-' + matchDataSrc.matchId).val() != 34 &&
     	    $('#status-' + matchDataDest.matchId).val() != 34)
     	{
			matchDest.insertAfter(matchSrc);
		}
		matchSrc.find('.bteam-match-rank').text(textDest);
      	md.matchBottomId = matchDataSrc.matchId;
      	md.matchTopId = matchDataDest.matchId;
      	$.post('index.php', md);
      }
	  return false;
	});

	// descendre un match
   $('#bteam-col-'+aNum+' .bteam-match-down').click(function(){
      var md = $(this).metadata();
      // Match concerne
      var matchSrc = $(this).parents('.bteam-match');
      var matchDest = null;
      var textSrc = matchSrc.find('.bteam-match-rank').text();
      var matchDataSrc = matchSrc.metadata();
      var rankSrc = textSrc.split(' ')[1];
      var textDest = 'N° ' + (parseInt(rankSrc) + 1);
      $('.bteam-match').each(function(){
      			var data = $(this).metadata();
      			if( data.tieId == matchDataSrc.tieId && $(this).find('.bteam-match-rank').text() == textDest)
      			{
        		   $(this).find('.bteam-match-rank').text(textSrc);
      			   matchDataDest = data;
      			   matchDest = $(this);
      			   return false;
      			}
      	});
     if (matchDest != null)
     {
     	if( $('#status-' + matchDataSrc.matchId).val() != 34 &&
     	    $('#status-' + matchDataDest.matchId).val() != 34)
     	{
			matchDest.insertBefore(matchSrc);
		}
		matchSrc.find('.bteam-match-rank').text(textDest);
      	md.matchTopId = matchDataSrc.matchId;
      	md.matchBottomId = matchDataDest.matchId;
      	$.post('index.php', md);
      }
	  return false;
	});

	// Masquer les matchs termines
    var isHide = $('#isHide-' + aNum).val();
	if (isHide == 1 )$('#bteam-col-'+aNum+' .bteam-status-36').hide();

	$('.bteam-match-player').hover(function(){
	    var md=$(this).metadata();
	    $('.player-'+md.playerId).addClass('bteam-hover');
	    },
	    function(){
	    if (!$(this).hasClass('bteam-fix')){
	    	var md=$(this).metadata();
	    	$('.player-'+md.playerId).removeClass('bteam-hover');
	    }
	});
	$('.bteam-match-player').click(function(){
	    var md=$(this).metadata();
	    $('.player-'+md.playerId).toggleClass('bteam-fix');
	});


}


function _chrono() {
	var match = $(this).find('.bteam-match');
	var training = $('#warming').val();

   	if (!match.length) {
   	    $(this).find('.bteam-court-chrono').css('background', "green");
		$(this).find('.bteam-court-start').text('--:--');
		$(this).find('.bteam-court-chrono').text("00:00");

		return true;
	}
	var md = $(match).metadata();
	var start = $('#start-' + md.matchId).val();
	$(this).find('.bteam-court-start').text(start.substr(11, 5));
	debut = new Date();
	now = debut.getTime();
	debut.setHours(start.substr(11, 2));
	debut.setMinutes(start.substr(14, 2));
	debut.setSeconds(start.substr(17, 2));
	duree = (now - debut.getTime()) / 1000;
	if (duree <= 0) $(this).find('.bteam-court-chrono').css('background', "green");
	else if (duree < training) $(this).find('.bteam-court-chrono').css('background', "yellow");
	else $(this).find('.bteam-court-chrono').css('background', "red");
	m = Math.floor(duree / 60);
	s = duree - m * 60;
	if (m < 10)
		m = "0" + m;
	if (s < 10)
		s = "0" + s;
	$(this).find('.bteam-court-chrono').text(m + ":" + s);
	return true;
}

function pageCheck() {

	$('#btnCheck').click(function() {
		$('#check').val(1);
		$('#frmPlayers').ajaxSubmit(_fillPlayers);
		return false;
	});
	$('#btnUncheck').click(function() {
		$('#check').val(0);
		$('#frmPlayers').ajaxSubmit(_fillPlayers);
		return false;
	});

	$("input[type='checkbox']").click(function() {
		$('#btnFilter').addClass('badnetBtnHover');
	});
	$("#search").keypress(function() {
		$('#btnFilter').addClass('badnetBtnHover');
	});
	$("#teamId").change(function() {
		$('#btnFilter').addClass('badnetBtnHover');
	});
	$("#btnFilter").click(function() {
		$('#btnFilter').removeClass('badnetBtnHover');
		_fillPlayers();
	});

}

function _fillPlayers() {
	var genders = $("#divGender input:checked");
	var glue = '';
	var gender = '';
	for ( var i = 0; i < genders.length; i++) {
		gender += glue + $(genders[i]).val();
		glue = ':';
	}
	$('#gridPlayers').setPostDataItem('gender', gender);

	$('#gridPlayers').setPostDataItem('search', $('#search').val());
	$('#gridPlayers').setPostDataItem('teamId', $('#teamId').val());

	$('#divHidden').empty();
	$('#gridPlayers').trigger('reloadGrid');
	return false;
}
