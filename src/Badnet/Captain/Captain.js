/**********************************************
 !   $Id$
 *********************************************/
 
function addPlayer(a)
{
  $("#gridPlayers").trigger("reloadGrid");
  if(a.act==-1)
  {
  	$("#divMsg").show();
  }
  else if(a.act==1)
  {
  	$("input[type='text']").val('');
  	$("#divMsg").hide();
  }
  else if (a.act==0)
  {
    $("#targetDlg").empty();
    $("#dlg").dialog('close');
  }
  return false;

} 


function selectPlayer(event, ui)
{
	$('#player').val(ui.item.famname + ' ' + ui.item.firstname);
	$('#license').val(ui.item.license);
	$('#famname').val(ui.item.famname);
	$('#firstname').val(ui.item.firstname);
	$('#rank').val(ui.item.rank);
	$('#ranking').val(ui.item.level);
	$('#born').val(ui.item.born);
	$('#gender').val(ui.item.gender);
	$('#labgender').val(ui.item.labgender);
	$('#labcatage').val(ui.item.labcatage);
  	$("#divMsg").hide();
	return false;
} 

function  delPlayer() 
{
    $("#targetDlg").empty();
    $("#dlg").dialog('close');
	$("#gridPlayers").trigger("reloadGrid");
	return false;
}


 function formatItemInstance(row, i, max, value) {
 return row[3] + ' - ' + row[2] + ' - ' + row[1];
 }

function  formatResultInstance(row, value) {
return row[1];
}

function selectInstance(aEvent, aRow, aValue)
{
$('#instanceId').val(aRow[0]);
return false;
} 
 
 function selectGridMember(aRow, aAction)
 {
    var sel = false;
    var sels = $('#gridPlayers').getGridParam("selarrrow");
    for (var i = 0; i < sels.length; i++) {
       if (sels[i] == aRow) sel = true;
 }
    
    if ( sel )
    {
        var str = "<input type='hidden' value='on' id='jqgb_" + aRow + "' name='jqgb_" + aRow + "'";
	    $('#frmplayers').prepend(str);
	}
	else
	    $('#jqgb_' + aRow).remove();
 }
  
 
function formatItem(row, i, max, value) {
 return row[0] + ' ' + row[1] + ' ' + row[2];
 }

function  formatResult(row, value) {
return row[0];
}

function selectMember(aEvent, aRow, aValue)
{
$('#firstname').val(aRow[1]);
$('#license').val(aRow[2]);
$('#memberId').val(aRow[3]);
$('#born').val(aRow[4]);
$('input[value="' + aRow[5] + '"]').attr('checked','checked');
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
