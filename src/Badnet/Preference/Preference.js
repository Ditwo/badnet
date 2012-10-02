/**********************************************
 !   $Id$
 *********************************************/
function pageSportive()
{
	$('#teamweight').change(function(){
	    var md = $('#divWeight').metadata();
	    md.weight = $('#teamweight').val();  
		$('#divWeight').load('index.php', md);
		return false;
	});
	$('#teamweight').change();
	return false;
} 

function selectUser(event, ui)
{
	$('#search').val(ui.item.label);
	$('#userId').val(ui.item.id);
	return false;
} 

function refreshPrivilege(a)
{
    $("#targetDlg").empty();
    $("#dlg").dialog('close');
	$("#gridUsers").trigger("reloadGrid");
	return false;
}
 
function pagePrint()
{
  $('#').click();
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
 
function pageCaptain()
 {
 
 	$('#captain').change(function(){
    if($('#captain:checked').length == 0)
    {
     	$('#divCaptain2 radio').attr('disabled', 'disabled');
     	$('#divCaptain2 input').attr('disabled', 'disabled');
     	$('#divCaptain2 checkbox').attr('disabled', 'disabled');
     	$('#divCaptain2 select').attr('disabled', 'disabled');
     	$('#divCaptain2').fadeTo('fast', 0.5);
    }
    else
    {
     	$('#divCaptain2 radio').removeAttr('disabled');
     	$('#divCaptain2 input').removeAttr('disabled');
     	$('#divCaptain2 checkbox').removeAttr('disabled');
     	$('#divCaptain2 select').removeAttr('disabled');
     	$('#divCaptain2').fadeTo('fast', 1);
    }
    });
    
 	$('#captain').change();
    return false;
 }

function pageInlineic()
 {
 
 	$('#reginline').change(function(){
    if($('#reginline:checked').length == 0)
    {
     	$('#divFees radio').attr('disabled', 'disabled');
     	$('#divFees input').attr('disabled', 'disabled');
     	$('#divFees').fadeTo('fast', 0.5);
    }
    else
    {
     	$('#divFees radio').removeAttr('disabled');
     	$('#divFees input').removeAttr('disabled');
     	$('#divFees').fadeTo('fast', 1);
    }
    });
    
 	$('#reginline').change();
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
