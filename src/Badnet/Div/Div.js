/**********************************************
 !   $Id$
 *********************************************/
function pageCompo()
{
		// let the gallery items be draggable
		$( ".gr-team" ).draggable({
			cancel: "a.ui-icon", // clicking an icon won't initiate dragging
			revert: "invalid", // when not dropped, the item will revert back to its initial position
			containment: $( "#demo-frame" ).length ? "#demo-frame" : "document", // stick to demo-frame if present
			helper: "clone",
			cursor: "move"
		});

		// Emplacement vide accepte les elements du tournoi
		$( '.gr-drop' ).droppable({
			accept: ".gr-team",
			activeClass: "ui-state-highlight",
			drop: function( event, ui ) {
			    // L'entree vient d'un autre slot; inverser avec le contenu
			    if (ui.draggable.parent('.gr-drop').length)
			    {
			    	$(this).children('.gr-team').appendTo(ui.draggable.parent('.gr-drop'));
			    	if (ui.draggable.parent('.gr-drop').children('p').length ==2)
					  ui.draggable.parent('.gr-drop').addClass('gr-empty');
			    } 
			    // L'entree vient de la liste, enlever l'ancienne
			    else 
			    {
			    	$(this).children('.gr-team').appendTo(ui.draggable.parent());
				}
				$(this).removeClass('gr-empty');
				addEntrie( ui.draggable, $(this) );
			}
		});
		
		// Liste du tournoi accepte les elements du groupe
		$( '#eventteams' ).droppable({
			accept: ".gr-drop .gr-team",
			activeClass: "ui-state-highlight",
			drop: function( event, ui ) {
				ui.draggable.parent().addClass('gr-empty');
				deleteEntrie( ui.draggable, $(this) );
			}
		});

		// fonction de suppression d'un inscrit du groupe
		function deleteEntrie( $item ) {
				$item.appendTo('#eventteams');
		}

		// fonction de suppression d'un inscrit du groupe
		function addEntrie( $item, $cible ) {
				$item.appendTo($cible);
		}


	
	$('#btnReg').click(function(){
	var str = [];
	var teams = $('#gr-teams .gr-slot'	);
	$(teams).each( function(){
	  var res = ($(this).children('p').attr('id') || '').match((/(.+)[-=_](.+)/));
	  if (res) str.push('teamIds[]=' + res[2]);
	  else str.push('teamIds[]=0');
	});  
	data = str.join('&');
	var md= $(this).metadata();
	for(key in md) data += '&' + key + '=' + md[key];
	$.post('index.php', data, function(a){updated(a);}, 'json');
	});
	return false;
}

function teamReceived()
{
 var nb = $('#groupteams li').length;
 var max = $('#teamsize').val();
 if (nb > max)
 {
    $('#eventteams').sortable('cancel');
 }
 return true;
} 


function pageGroupDef()
{
	return false;
}

function addGroup(aData)
{
     $("#targetDlg").empty();
     $("#dlg").dialog('close');
     $("#targetBody").load('index.php', aData);
     return false;
}

function pageGroups()
{
	$("#drawId").change(searchGroups);
}

function searchGroups() 
 { 
	var param = {};
	param.drawId =  $("#drawId").val();
	param.search =  $("#search").val();
    $("#gridDivs").appendPostData(param);
    $("#gridDivs").trigger("reloadGrid");
    return false;
 }
 
 function updated(a)
{
    $('#dlg').dialog('option', 'title', a.title);
    $('#dlg').dialog('option', 'width', 200);
    $('#dlg').dialog('option', 'height', 150);
    $("#dlg").dialog('open');
    $("#targetDlg").html(a.content);
    return false;
}
