var savedValues = new Object();
var savedChecked = new Object();

function fillList(list, items)
{
  var cur = list.options[list.options.selectedIndex].value
  var select = -1;
  list.options.length = 0;
  for(var clef in items)
   {
     if (typeof(items[clef]) != "function")
     {
        disabled = 0;
	if (typeof(items[clef]) == "string")
          {
            valeur = items[clef];
          }
        else
          {
             var buf = items[clef];
	     for (var vals in buf)
               {
                 if (vals == 'value')                  
                  {
	             valeur = buf[vals]; 
                  }
                 if (vals == 'disable')
		   disabled = buf[vals];
               }
          } 
        o = new Option(valeur, clef);
	list.options[list.options.length] = o;
	if (disabled == 1)
	   list.options[list.options.length-1].disabled = 1;
	if (cur == clef && disabled == 0)
	  select = list.options.length-1
     }
   }
  if (select==-1)
    select = list.options.length-1;
  list.options.selectedIndex = select;
}

function getHttpObj(callback)
{
  var xmlhttp = false;
  /*@cc_on
  @if (@_jscript_version >= 5)
     try
      { xmlhttp = new ActiveXObject("Msxml2.XMLHTTP"); }
     catch (e)
      { try
          { xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); }
        catch (E)
          { xmlhttp = false; }
      }
  @else
    xmlhttp = false;
  @end @*/

  // Creation de l'objet si ce n'est pas deja fait
  if (!xmlhttp && typeof XMLHttpRequest != 'undefined')
    {
     try
      { xmlhttp = new XMLHttpRequest(); }
     catch (e)
      { xmlhttp = false; }
    }

  if (xmlhttp)
    {
       xmlhttp.onreadystatechange=function()
        {
	  //if(xmlhttp.readyState == 1) // Open
	  //if(xmlhttp.readyState == 2) // Send
	  //if(xmlhttp.readyState == 3) // Receipt
	  if(xmlhttp.readyState == 4) // Finish
           {
	     if(xmlhttp.status == 200) // Code HTTP OK
              { callback(xmlhttp.responseText); return; }
           }
        }
    }
  return xmlhttp;

}

function callAjaj(sessionId, elt, callBack, action, data)
{
  if (typeof callBack == "function")
     { var ajaj = getHttpObj(callBack); }
  else
     { var ajaj = getHttpObj(endSubmit); }

  if (!ajaj)
   alert("Erreur ajaj");

  var url= location.pathname;
  var arg = "&kpid=ajaj"+"&kaid="+action+"&kdata="+data+_getFields(document);
  ajaj.open("GET", url+"?"+arg, true);
  ajaj.send(null);

  //ajaj.open("POST", url, true);
  //var arg = "PHPSESSID="+sessionId+"&kpid="+module+"&kaid="+action+_getFields(document);
  //ajaj.setRequestHeader('Content_type', 'application/x-www-form-urlencoded');
  //ajaj.send(arg);
}

function endSubmit()
{
alert("end");
}

function saveData(formName)
{
 var form = document.forms[formName];
 var tags = form.getElementsByTagName('input');
 for (var i=0; i < tags.length; i++) {
   elt = tags[i];
   if (elt.type == 'text')
     savedValues[elt.id] = elt.value;
   else if(elt.type == 'checkbox' ||
           elt.type == 'radio' )
    savedChecked[elt.id] = elt.checked;
   }

 var tags = form.getElementsByTagName('textarea');
 for (var i=0; i < tags.length; i++) {
   elt = tags[i];
   savedValues[elt.id] = elt.value;
 }

 var tags = form.getElementsByTagName('select');
 for (var i=0; i < tags.length; i++) {
   elt = tags[i];
   savedValues[elt.id] = elt.value;
 }

}

function refresh()
{
      var theDoc = window.opener.document;
      frm = theDoc.forms['kHistory']
      //if (kpid == "")
         kpid=frm.elements["kpid"].value;
      //if (kaid == "")
         kaid=frm.elements["kaid"].value;
      var url= theDoc.location.pathname+"?";
      url += "kpid="+kpid+"&kaid="+kaid;
      //if (kdata == "")
         kdata=frm.elements["kdata"].value;
      if (kdata != "")
        url += "&kdata="+kdata;
      url += _getFields(theDoc);
      window.opener.location = url;
};


function initForm(sessionId, type, formName)
{
  mask(sessionId, type, formName);	
  saveData(formName);
  desactivate(formName);
 return false;
}

function mask(sessionId, type, formName)
{
 var blk = document.getElementById(formName);
 var form = document.getElementById("btnPlus");
 if (blk.style.display == 'none')
   {
     blk.style.display = "block";
     form.style.display = "none";
   }
 else
   {
     blk.style.display = "none";
     form.style.display = "";
   }
 return false;
}


function restoreData(formName)
{
 var form = document.forms[formName];
 var tags = form.getElementsByTagName('input');
 for (var i=0; i < tags.length; i++) {
   elt = tags[i];
   if (elt.type == 'text')
     elt.value = savedValues[elt.id];
   else if(elt.type == 'checkbox' ||
           elt.type == 'radio' )
     elt.checked = savedChecked[elt.id];
   }

 var tags = form.getElementsByTagName('textarea');
 for (var i=0; i < tags.length; i++) {
   elt = tags[i];
   elt.value = savedValues[elt.id];
   }

 var tags = form.getElementsByTagName('select');
 for (var i=0; i < tags.length; i++) {
   elt = tags[i];
   elt.value = savedValues[elt.id];
   }
}

function cancel(sessionId, elt, formName, activeElt)
{
  restoreData(formName);
  desactivate(formName);
}

function activate(sessionId, elt, formName, activeElt)
{
 div=window.document.getElementById('divModif');
 div.style.display = "none";
 div=window.document.getElementById('divValid');
 div.style.display = "";

 var form = window.document.getElementById(formName);
 var tags = form.getElementsByTagName('input');
 for (var i=0; i < tags.length; i++) {
  elt = tags[i];
  if (elt.type == 'text' ||
      elt.type == 'checkbox' ||
      elt.type == 'radio'){
     elt.disabled = '';
     if (elt.type == 'text'){
       elt.style.border = 'inset 1px';
       if (elt.className == 'kOption')
          elt.style.background = '#eee';
       else
          elt.style.background = '#fff';
       }
     }
  }

  var tags = form.getElementsByTagName('select');
  for (var i=0; i < tags.length; i++) {
     elt = tags[i];
     elt.disabled = '';
     elt.style.border = 'inset 1px';
     if (elt.className == 'kOption')
        elt.style.background = '#eee';
     else
        elt.style.background = 'white'; //transparent';
     }

  var tags = form.getElementsByTagName('textarea');
  for (var i=0; i < tags.length; i++) {
     elt = tags[i];
     elt.disabled = '';
     elt.style.border = 'inset 1px';
     if (elt.className == 'kOption')
        elt.style.background = '#eee';
     else
        elt.style.background = 'white'; //transparent';
     }

  div=window.document.getElementById(activeElt);
  div.focus();
}

function desactivate(formName)
{
  div=window.document.getElementById('divModif');
  div.style.display = "";
  div=window.document.getElementById('divValid');
  div.style.display = "none";

  form=window.document.getElementById(formName);
  var tags = form.getElementsByTagName('input');
  for (var i=0; i < tags.length; i++) {
     elt = tags[i];
     if (elt.type == 'text' ||
         elt.type == 'checkbox' ||
         elt.type == 'radio')  {
        elt.style.background = 'transparent';
        elt.style.border = 'none';
        elt.disabled = 'yes';
     }
  }

  var tags = form.getElementsByTagName('select');
  for (var i=0; i < tags.length; i++) {
     elt = tags[i];
     elt.disabled = 'yes';
     elt.style.background = 'transparent';
     elt.style.border = 'solid 1px';
     }

  var tags = form.getElementsByTagName('textarea');
  for (var i=0; i < tags.length; i++) {
     elt = tags[i];
     elt.disabled = 'yes';
     elt.style.background = 'transparent';
     elt.style.border = 'solid 1px';
     }
}



function resize(sessionId, elt, width, height)
{
  window.resizeTo(width,height);
  left = opener.screenX + (opener.outerWidth-width)/2;
  top  = opener.screenY + (opener.outerHeight-height)/2;
  if (isNaN(left)) left = (self.screen.width - width)/2;
  if (isNaN(top)) top = (self.screen.height - height) /2;
  window.moveTo(left,top);
  return false;
}

function upload(sessionId, elt, pageId, actId, data, width, height)
{
 var url= location.pathname+"?";

 if (upload.arguments.length>6 && 
	width>0 && height>0)
    window.resizeTo(width,height);

 if(sessionId != "noSid")
   url += "PHPSESSID="+sessionId+"&";
 url += "kpid="+pageId+"&kaid="+actId;
 if (data != null)
    url += "&kdata="+data;
 else if (elt.value != null)
    url += "&kdata="+elt.value;

 url+= _getFields(document);
 document.location = url;
 return false;
}

function newWin(sessionId, elt, pageId, actId, data, width, height)
{
  var top;
  var left;

  if (width==0) width=300;
  if (isNaN(width))width=300;
  if (height==0) height=300;
  if (isNaN(height))height=300;
  left = window.screenX + (window.innerWidth-width)/2;
  top = window.screenY + (window.innerHeight-height)/2;
  if (isNaN(left)) left = (self.screen.width - width)/2;
  if (isNaN(top)) top = (self.screen.height/2) - height;

  var param = "directories=no, menubar=no, toolbar=no,";
  param += "scrollbars=yes, resizable=yes";
  param += ",width="+width;
  param += ",height="+height;
  param += ",top="+top;
  param += ",left="+left;

  var url= location.pathname+"?";
 if(sessionId != "noSid")
   url += "PHPSESSID="+sessionId+"&";
  url += "kpid="+pageId+"&kaid="+actId;
  if (elt.value != null && elt.type != "button")
     url += "&kdata="+elt.value;
  else if (data != null)
     url += "&kdata="+data;
  url+= _getFields(document);

  var now = new Date();
  var name = "badnet_" + now.getTime();
  window.open(url, name, param);
  return false;
}

function newWinUrl(url, action, data, width, height)
{
  var top;
  var left;

  if (width==0) width=300;
  if (isNaN(width))width=300;
  if (height==0) height=300;
  if (isNaN(height))height=300;
  left = window.screenX + (window.innerWidth-width)/2;
  top = window.screenY + (window.innerHeight-height)/2;
  if (isNaN(left)) left = (self.screen.width - width)/2;
  if (isNaN(top)) top = (self.screen.height/2) - height;

  var param = "directories=no, menubar=no, toolbar=no,";
  param += "scrollbars=yes, resizable=yes";
  param += ",width="+width;
  param += ",height="+height;
  param += ",top="+top;
  param += ",left="+left;

  var now = new Date();
  var name = "badnet_" + now.getTime();
  window.open(url+data, name, param);
  return false;
}

function cancelClose()
{
  window.close();
  return false;
}


function uploadSort(elt, colId)
{
 var url = new String(document.location);
 var fmt = "((kSort"+elt+"=)(-?[0-9]+))";
 reg = new RegExp(fmt, "g");
 if (url.match(reg))
 {
  lst = url.split(reg);
  if (lst[3] == colId)
    lien = url.replace(reg, "$2-"+colId);
  else 
   lien = url.replace(reg, "$2"+colId);
 }
 else
  lien = url+"&kSort"+elt+"="+colId;

 var fmt = "((kSort=)(-?[0-9]+))";
 reg = new RegExp(fmt, "g");
 var url = new String(lien);
 if (url.match(reg))
 {
  lst = url.split(reg);
  if (lst[3] == colId)
    lien = url.replace(reg, "$2-"+colId);
  else 
   lien = url.replace(reg, "$2"+colId);
 }
 else
  lien = url+"&kSort="+colId;

 document.location=lien;
}

function valid(frm, pageId, actId, data)
{
  frm.kpid.value = pageId;
  frm.kaid.value = actId;
  frm.kdata.value = data;
  return _checkFields(frm);
}


function uploadClose(af, kpid, kaid, kdata)
{
  if (window.opener)  
    {
      var theDoc = window.opener.document;
      frm = theDoc.forms['kHistory']
      if (kpid == "")
         kpid=frm.elements["kpid"].value;
      if (kaid == "")
         kaid=frm.elements["kaid"].value;
      var url= theDoc.location.pathname+"?";
      url += "kpid="+kpid+"&kaid="+kaid;
      if (kdata == "")
         kdata=frm.elements["kdata"].value;
      if (kdata != "")
        url += "&kdata="+kdata;
      url += _getFields(theDoc);
      window.opener.location = url;
   }
   window.close();
}


function _checkFields(frm)
{
  var fields ='';
  nbe = frm.elements.length;
  for (j=0; j<nbe; j++)
    {
	if (frm.elements[j].type == 'radio')
          {
	      var theDoc = window.document;
	      var isSelect = false;
	      var selectTags = theDoc.getElementsByName(frm.elements[j].name);
              for (var i=0; i<selectTags.length; i++) 
	        {
		  if (selectTags[i].checked) isSelect = true;
                }
              if (isSelect == false)
	        {
                  alert(getMsg(frm.elements[j].name));
  	          frm.elements[j].focus();
                 return false;
                }
          }
         
	if(frm.elements[j].className == 'kMandatory')
          {
           if (frm.elements[j].value == '')
	     {
               alert(getMsg(frm.elements[j].name));
  	       frm.elements[j].focus();
               return false;
             }
          }	
     }
  return true;
}

function _getFieldsold(theDoc)
{
 var nbf= theDoc.forms.length;
 var lst='';
 var glue='&';
 for (i=0; i<nbf; i++)
  {
    frm = theDoc.forms[i]
alert (frm.name);
    nbe = frm.elements.length;
    for (j=0; j<nbe; j++)
      {
      if (  frm.elements[j].name != 'kpid' &&
            frm.elements[j].name != 'kaid' &&
	    frm.elements[j].name != 'kdata' &&
          (frm.elements[j].value != null) &&
	   ((frm.elements[j].type != 'checkbox' ||
            frm.elements[j].checked)) &&
	   ((frm.elements[j].type != 'radio' ||
            frm.elements[j].checked)))
         {
	   lst += glue + frm.elements[j].name + "=";
           lst += frm.elements[j].value;
	   glue = '&';
         }
      }
  }
 return lst;
}


function _getFields(theDoc)  
  {
    var nbf= theDoc.forms.length;
    var out='';
    for (f=0; f<nbf; f++)
    {
        form = theDoc.forms[f];
        var el, inpType, value, name, escName;
	var inputTags = form.getElementsByTagName('INPUT');
	var selectTags = form.getElementsByTagName('SELECT');
	var textareaTags = form.getElementsByTagName('TEXTAREA');
        var arrayRegex = /.+\[\]/;

        var validElement = function (element) {
            if (!element || !element.getAttribute) {
                return false;
            }
            el = element;
            name = el.getAttribute('name');
            if (!name || name == 'kpid' ||
		name == 'kaid' || name == 'kdata') {
                // no element name so skip
                return false;
            }
            if (element.disabled == 'true') {
                return false;
            }
            if (element.type == 'button') {
                return false;
            }

            escName = escape(name);
	    value = escape(el.value);		
            inpType = el.getAttribute('type');
	//alert(escName+":"+value);
            return true;
        }
        
        inputLoop:
        for (var i=0; i < inputTags.length; i++) {
            if (!validElement(inputTags[i])) {
                continue;
            }
            if (inpType == 'checkbox' || inpType == 'radio') {
                if (!el.checked) {
                    // unchecked radios/checkboxes don't get submitted
                    continue inputLoop;
                }
            }
            // add element to output array
	    out += '&' +  escName + '=' + value;

        } // end inputLoop

        selectLoop:
        for (var i=0; i<selectTags.length; i++) {
            if (!validElement(selectTags[i])) {
                continue selectLoop;
            }
            var options = el.options;
            var nbselect = 0;
            for (var z=0; z<options.length; z++){
                var option=options[z];
                if(option.selected){
                    nbselect++;
                }
            }
            for (var z=0; z<options.length; z++){
                var option=options[z];
                if(option.selected){
                    if (nbselect > 1)
                    	out += '&' + escName + '[]=' + escape(option.value);
                     else
                    	out += '&' + escName + '=' + escape(option.value);
                }
            }
        } // end selectLoop

        textareaLoop:
        for (var i=0; i<textareaTags.length; i++) {
            if (!validElement(textareaTags[i])) {
                continue;
            }
            // add element to output array
	    out += '&' + escName + '=' + value;
        } // end textareaLoop
     }
 return out;
}


/******** BEGIN LFA ********/
/**
* permet de cocher ou décocher la sélection d'éléments elts_name en fonction 
* de la valeur d'une checkbox the_checkbox d'un formulaire the_form
*
* @param   object   l'objet de la case à cocher qui asservit les autres
* @param   string   le nom du formulaire 
* @param   string   le nom des elements
*
* @return  boolean  false si aucun élément elts_name n'existe, true sinon 
*
* @example <input type=checkbox onClick="setCheckboxes (this, 'fregi', 'rowsRegis[]');" title="Cocher/Décocher tous les cases"> 
*/ 

function setCheckboxes(the_checkbox, the_form, elts_name)
{
 //var elts      = (typeof(document.forms[the_form].elements[elts_name]) != 'undefined')
	//              ? document.forms[the_form].elements[elts_name];

 if (typeof(document.forms[the_form].elements[elts_name]) == 'undefined') 
	return false;
 var elts  = document.forms[the_form].elements[elts_name];
 var elts_cnt  = (typeof(elts.length) != 'undefined')
 	                 ? elts.length : 0;

 if (elts_cnt) 
  {
    for (var i = 0; i < elts_cnt; i++) 
      {
        elts[i].checked = the_checkbox.checked;
      } // end for
  } 
  else 
  {
       elts.checked        = the_checkbox.checked;
  } // end if... else

  return true;
} // end of the 'setCheckboxes()' function

/********* END LFA *********/


//--------------------------------
// tout au dessus est bon
// en dessous c'est experimental
//--------------------------------
function openNew(elt, pageId, actId, data, width, height)
{
  var top;
  var left;

  if (width==0) width=100;
  if (height==0) height=300;
  left = window.screenX + (window.innerWidth-width)/2;
  top = window.screenY + (window.innerHeight-height)/2;
  if (isNaN(left)) left = (self.screen.width - width)/2;
  if (isNaN(top)) top = (self.screen.height/2) - height;

  var param = "directories=no, menubar=no, toolbar=no,";
  param += "scrollbars=yes, resizable=yes";
  param += ",width="+width;
  param += ",height="+height;
  param += ",top="+top;
  param += ",left="+left;

  var url= location.pathname+"?";
  url += "kpid="+pageId+"&kaid="+actId;
  if (elt.value != null && elt.type != "button")
     url += "&kdata="+elt.value;
  else if (data != null)
     url += "&kdata="+data;
  url+= _getFields(document);

//alert(elt.type);
//alert(url);
  var now = new Date();
  var name = "badnet_" + now.getTime();
  window.open(url, name, param);
  return false;
}







function _getData(dataId)
{
  var nbf= document.forms.length;
  for (i=0; i<nbf; i++)
  {
    frm = document.forms[i]
    nbe = frm.elements.length;
    for (j=0; j<nbe; j++)
    {
      if (frm.elements[i].name == dataId)
      {
        return  frm.elements[i].value;
      }
    }
  }
  return dataId;
}

