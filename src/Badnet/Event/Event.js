/**********************************************
 !   $Id$
 *********************************************/
  function updatePostits()
 {
     $("#targetDlg").empty();
    $("#dlg").dialog('close');
   	$('#gridPostits').trigger('reloadGrid');
 }
 
 
 function pageNewEvent()
 {
   $( "#rdoIndiv, #rdoIc").click(function(){
    var md = $('#nature').metadata();
    md.type = $(this).val();
   	$('#nature').removeOption(/./);
    $("#nature").ajaxAddOption('index.php', md, false);
   
   });
 }
 
 function selectFirstDay(aDate, aInst)
 {
 	date = $.datepicker.parseDate(
		aInst.settings.dateFormat ||
		$.datepicker._defaults.dateFormat,
		aDate, aInst.settings );
	$( '#lastdayDisp' ).datepicker( "option", 'minDate', date );
	return false;
 }
 function selectLastDay(aDate, aInst)
 {
 	date = $.datepicker.parseDate(
		aInst.settings.dateFormat ||
		$.datepicker._defaults.dateFormat,
		aDate, aInst.settings );
	$( '#firstdayDisp' ).datepicker( "option", 'maxDate', date );
	return false;
 }
 
function createEvent(a)
{
	var md = $("#divfrmNewEvent").metadata();
	md.eventId = a;
	$("#targetBody").load('index.php', md);
    $("#targetDlg").empty();
    $("#dlg").dialog('close');
    return false;
}

function pageEvents()
{
	$("#lstSeason").change(
	  function(){
	var param = {};
	param.lstSeason =  $("#lstSeason").val();
	$("#gridEvents").appendPostData(param);
	$("#gridEvents").trigger("reloadGrid");
	  });
	  
	$("#lstSeason").change();
}

function searchEvents() 
 { 
	var param = {};
	param.lstSeason =  $("#lstSeason").val();
	param.txtSearch =  $("#txtSearch").val();
	if ($("#allevent:checked").length) param.allevent=$("#allevent").val();
	else param.allevent=0;
      $("#gridEvents").appendPostData(param);
      $("#gridEvents").trigger("reloadGrid");
  return false;
 }

 
 function delCaptain()
 {
   	$('#gridTeams').trigger('reloadGrid');
 }
 
 
function pagePlayers()
{
  $('#btnFilter').click(_fillPlayers);
  $('#asso').keypress(function(){$('#instanceId').val(0);});
}

function _fillPlayers()
{
	var genders = $("#divGender input:checked");
	var glue = '';
	var gender = '';
	for (var i = 0; i < genders.length; i++) {
	   gender += glue + $(genders[i]).val();
	   glue = ':';
    }
	$('#gridPlayers').setPostDataItem('gender', gender);

	$('#gridPlayers').setPostDataItem('search', $('#search').val());
	$('#gridPlayers').setPostDataItem('instanceId', $('#instanceId').val());

   	$('#gridPlayers').trigger('reloadGrid');
 	$('#aSearch').removeClass('searchHover');
   	$('#aSearch').addClass('search');
   	
    return false;
}
