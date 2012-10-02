/**********************************************
 !   $Id$
 *********************************************/
function delTeam(a)
 {
    if(a.err == 1)
    {
      	$("#divMsg").show();
    }
    else
    {
    	$("#targetDlg").empty();
    	$("#dlg").dialog('close');
    }
	$("#gridTeams").trigger("reloadGrid");
	return false;
 }
  
 
function addTeam(a)
{
  $("#gridTeams").trigger("reloadGrid");
  if(a.act==-1)
  {
  	$("#divMsg").show();
  }
  else if(a.act==1)
  {
  	$("#assoc").val('');
  	$("#teamname").val('');
  	$("#teamstamp").val('');
  	$("#fedeid").val(-1);
  	$("#divMsg").hide();
  }
  else if (a.act==0)
  {
    $("#targetDlg").empty();
    $("#dlg").dialog('close');
  }
  return false;

} 

function selectAsso(event, ui)
{
	$('#assoc').val(ui.item.name);
	$('#teamname').val(ui.item.name);
	$('#teamstamp').val(ui.item.stamp);
	$('#fedeid').val(ui.item.id);
	return false;
} 

function filterTeams()
 {
 	$('#gridTeams').setPostDataItem('search', $('#search').val());
   	$('#gridTeams').trigger('reloadGrid');
   	return false;
 }
 
 
