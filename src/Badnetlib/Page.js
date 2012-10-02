
/*****************************************************************************
 !   $Id$
 ******************************************************************************/
function submitForm(aForm)
{
  var md = $(aForm).metadata();
  //md.async = true;
  //md.timeout = 5000;
  //md.error = fctErrorForm;
  //md.success = yes;
  $(aForm).ajaxSubmit(md);
  return false;
}

function submitConfirm(aForm)
{
  var option = {
     url : 'index.php',
     dataType : 'json',
     success : function(a){
	    alert(a.msg);
        if (a.error == false)
        {
            $("#dlgConfirm").dialog('close');
        }
     } 
  }
  $(aForm).ajaxSubmit(option);
}

function fctErrorForm(request, status, obj)
{
 alert('timeout form');
 alert(status);
 request.abort();
}

function fctError(object, request)
{
 alert('timeout div');
 request.abort();
}


function fillTrame(aCorner)
{
	//$(window).load(function(){ $.preloadCssImages(); });
	
	jQuery.validator.addMethod("dateFR", function(value, element) { 
  return this.optional(element) || /^\d\d?\-\d\d?\-\d\d\d?\d?$/.test(value); 
}, "Entrer une date valide (jj-mm-yyyy).");
	

	if (aCorner==1) bncorner('#divBody', '20px');
	
	var md = $("#targetBody").metadata();
	$("#targetBody").load('index.php', md);
	
	$(".lnkAjax").livequery(function(){
  	   var md = $(this).metadata();
  	   var data = {};
  	   data.params = '';
  	   for(key in md) {if (key != 'target') data.params += '&' + key + '=' + md[key];
  	   else data.target = '#' + md[key];  	   
  	   }
  	   data.loading_img = 'Bn/Img/loading.gif';
  	   data.onError = fctError;
  	   data.target = '#targetBody';
	   $(this).ajaxify(data);
	});

	$(".divAjax").livequery(function(){
  	   var md = $(this).metadata();
  	   var data = {};
  	   data.link = 'index.php';
  	   data.event = false; //'load';
  	   data.target = '#' + $(this).attr('id');
  	   data.params = '';
	   //data.timeout = 10000;
  	   data.loading_img = 'Bn/Img/loading.gif';
  	   data.onError = fctError;
  	   jQuery.ajaxSetup({async:true});
  	   for(key in md) {if (key != 'target') data.params += '&' + key + '=' + md[key];}
	   $(this).ajaxify(data);
	});

	$(".btnAjax").livequery(function(){
  	   var md = $(this).metadata();
  	   var data = {};
  	   data.link = 'index.php';
  	   data.target = md.target;
  	   data.params = 'ajax=true';
	   //data.timeout = 10000;
  	   data.loading_img = 'Bn/Img/loading.gif';
 	   data.onError = fctError;
  	   jQuery.ajaxSetup({async:true});
  	   for(key in md) {if (key != 'target') data.params += '&' + key + '=' + md[key];}
	   $(this).ajaxify(data);
	});

	$(".btnGoto").livequery('click', function(){
  	   $(this).bnGoto();
	});

	$(".selectAjax").livequery('change', function(){
  	   var md = $(this).metadata();
  	   var data = {};
  	   data.link = 'index.php';
  	   data.target = md.target;
  	   data.params = 'ajax=true';
	   //data.timeout = 10000;
  	   data.loading_img = 'Bn/Img/loading.gif';
 	   data.onError = fctError;
  	   jQuery.ajaxSetup({async:true});
  	   for(key in md) {if (key != 'target') data.params += '&' + key + '=' + md[key];}
  	   data.params += '&' + $(this).attr('id') + '=' + $(this).val();
	   $(this).ajaxify(data);
	});

    $('.bnPoints').livequery(function(){$(this).mask('99'); });

   $('.bnPoints').livequery(function(){
      $(this).keyup(function(){
	  var point = $(this).val();
	  var stroke = point.split('_'); 
	  if (stroke[0].length >= 2)
	  {
	  	var name = $(this).attr('id');
	  	var stroke =name.split('_'); 
	  	var num = parseInt(stroke[2]) + 1;
	  	if ( parseInt($(this).val()) < 20 && (num%2 == 0) )
	  	{
	  		$('#' + stroke[0] + '_' + stroke[1] + '_' + num).val(21);
	  		num++;
	  	} 
	  	$('#' + stroke[0] + '_' + stroke[1] + '_' + num).focus();
	  }
	  return false;	    
	});
	},
	function(){$(this).unbind('keyup');});
	
  $(".btnClose").livequery('click', function (aEvent) {
   	  $("#dlg").dialog('option', 'height', 380);
   	  $("#dlg").dialog('option', 'width', 500);
      if ($("#dlg").dialog('isOpen') ) {$("#targetDlg").empty();$("#dlg").dialog('close');}
	  return false;
	  });

   $('.bn-delete').livequery('click', function(aEvent){
      var md = $(this).metadata();
      var width=300;
      var height=150;
      var title='Suppression';
      if (md.width!=undefined) width=md.width;
      if (md.height!=undefined) height=md.height;
      if (md.title!=undefined) title=md.title;
      $('#dlg').dialog('option', 'title', title);
      $('#dlg').dialog('option', 'width', width);
      $('#dlg').dialog('option', 'height', height);
   	  $("#dlg").dialog('open');
	  return false;	    
	});

	  
   $('.bn-dlg').livequery('click', function(aEvent){
      var md = $(this).metadata();
      var width=500;
      var height=380;
      var title='';
      if (md.width!=undefined) width=md.width;
      if (md.height!=undefined) height=md.height;
      if (md.title!=undefined) title=md.title;
      $('#dlg').dialog('option', 'title', title);
      $('#dlg').dialog('option', 'width',   width);
      $('#dlg').dialog('option', 'height', height);
   	  $("#dlg").dialog('open');
	  return false;	    
	});

   $('.bn-popup').livequery('click', function(){
      var width=400;
      var height=300;
      var top=10;
      var left=10;
      var title='Suppression';
   	  var md = $(this).metadata();
      if (md.top!=undefined) top=md.top;
      if (md.left!=undefined) left=md.left;
      if (md.width!=undefined) width=md.width;
      if (md.height!=undefined) height=md.height;
      if (md.title!=undefined) title=md.title;
   	  var url = $(location).attr('href') + '?bnAction=' + md.bnAction;
   	  for(key in md) {if (key != 'bnAction') url += '&' + key + '=' + md[key];}
   	  var now = new Date();
   	  var name = "bn_" + now.getTime();
   	  var option = "width=" + width + ",height=" + height + ",top=" + top + ",left=" + left + ",location=no,menubar=no,toolbar=no,scrollbars=no,resizable=yes,status=no"
   	  window.open(url, name, option);
	  return false;	    
	});
	  
	$(".badnetBlk1").livequery(function(){
	    $(this).corner('10px');
	});

	$(".badnetPlayer").livequery(function(){
	    $(this).corner('10px');
	});
}

function bnConfirm(aMsg, aOption)
{
	$("#dlgConfirm").dialog('open');
	return false;
}

function bncorner(aElt, aOption)
{
  if ( !$.browser.msie || $.browser.version >= 7 )
  {
  	$(aElt).corner(aOption);
  }
}

