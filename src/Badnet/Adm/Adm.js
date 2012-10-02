/**********************************************
 !   $Id$
 *********************************************/
 function deleteP2m()
{
	$("#gridP2m").trigger("reloadGrid");
    return false;
}
 
function pageRegistration()
{
      $('#btnSearch').click(function(){
		var param = {};
		param.txtSearch =  $("#txtSearch").val();
	      $("#gridMembers").appendPostData(param);
	      $("#gridMembers").trigger("reloadGrid");
      	  return false;
      });
}

function pageRegistrations()
{
      $('#btnSearch').click(function(){
		var param = {};
		param.txtSearch =  $("#txtSearch").val();
		if ($("input:checked").length) param.scan = 1;
	      $("#gridRegis").appendPostData(param);
	      $("#gridRegis").trigger("reloadGrid");
      	  return false;
      });
}
 
 
function pageChoice()
{
      $('#lnkDiscipline').click(function(){
        var data = $(this).metadata();
        data.ajax=1;
      	$.post('index.php', data, function(){
	      alert('Traitement Ok');
	      return false;
	      });
      	return false;
      });
      $('#lnkI2p').click(function(){
        var data = $(this).metadata();
        data.ajax=1;
      	$.post('index.php', data, function(a){
	      alert('Traitement fini');
	      return false;
	      });
      	return false;
      });
}


function pageWo()
{
      $('#btnUpdt').click(function(){
        var data = $(this).metadata();
      	$.post('index.php', data, function(){
	      $("#gridPlayers").trigger("reloadGrid");
	      return false;
	      });
      	return false;
      });
}

function deletePair()
{
	$("#gridPairs").trigger("reloadGrid");
    return false;
}
  
function pageRanking()
{
      $('#btnSearch').click(function(){
		var param = {};
		param.txtSearch =  $("#txtSearch").val();
	      $("#gridPlayers").appendPostData(param);
	      $("#gridPlayers").trigger("reloadGrid");
      	  return false;
      });
      
      $('#btnRaz').click(function(){
        var data = $(this).metadata();
      	$.post('index.php', data, function(){
	      $("#gridPlayers").trigger("reloadGrid");
	      return false;
	      });
      	return false;
      });
 
       $('#btnMaj').click(function(){
        var data = $(this).metadata();
      	$.post('index.php', data, function(){
	      $("#gridPlayers").trigger("reloadGrid");
	      return false;
	      });
      	return false;
      });
      
}
 
