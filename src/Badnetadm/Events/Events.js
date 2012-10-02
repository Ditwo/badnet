/**********************************************
 !   $Id$
 *********************************************/
 function deleteEvent()
 {
 	var param = {};
	param.lstSeason =  $("#lstSeason").val();
	$("#gridEvents").appendPostData(param);
	$("#gridEvents").trigger("reloadGrid");
	return false;
 }
 
function changeDeadLine(aDate)
{
var cd = $("#txtDeadline").val();
var d = getDate(cd);
var day = d.getDay();
if (day < 2 )
{
	$("#txtDatedrawDisp").datepicker('setDate', d);
	$("#txtFirstdayDisp").datepicker('setDate', addJour(cd, 6-day));
	$("#txtLastdayDisp").datepicker('setDate', addJour(cd, 7-day));
}
else
{
	$("#txtDatedrawDisp").datepicker('setDate', addJour(cd, 7-day));
	$("#txtFirstdayDisp").datepicker('setDate', addJour(cd, 13-day));
	$("#txtLastdayDisp").datepicker('setDate', addJour(cd, 14-day));
}
}
function changeDateDraw(aDate)
{
var cd = $("#txtDatedraw").val();
var d = getDate(cd);
var day = d.getDay();
if (day < 2 )
{
	$("#txtFirstdayDisp").datepicker('setDate', addJour(cd, 6-day));
	$("#txtLastdayDisp").datepicker('setDate', addJour(cd, 7-day));
}
else
{
	$("#txtFirstdayDisp").datepicker('setDate', addJour(cd, 13-day));
	$("#txtLastdayDisp").datepicker('setDate', addJour(cd, 14-day));
}
}

function changeFirstDay(aDate)
{
var cd = $("#txtFirstday").val();
var d = getDate(cd);
var day = d.getDay();
if (day )
{
	$("#txtLastdayDisp").datepicker('setDate', addJour(cd, 7-day));
}
else
{
	$("#txtLastdayDisp").datepicker('setDate', d);
}
}

function getDate(aDate)
{
var a = aDate.substr(0,4);
var m = aDate.substr(5,2);
var j = aDate.substr(8,2);
var d = new Date(a, m-1, j);
return d
}

function addJour(aDate, aJour)
{
var d = getDate(aDate);
var time = d.getTime();
time += 24*60*60*1000*aJour;
d.setTime(time);
var nd = d.getDate() + '-' + (d.getMonth()+1) + '-' + d.getFullYear()
//return nd;
return d;
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


function displayEvents()
{
	$("#lstSeason").change(
	  function(){
	var param = {};
	param.lstSeason =  $("#lstSeason").val();
	$("#gridOwner").appendPostData(param);
	$("#gridOwner").trigger("reloadGrid");
	$("#gridAuth").appendPostData(param);
	$("#gridAuth").trigger("reloadGrid");
	  });
	  
	$("#lstSeason").change();
}



