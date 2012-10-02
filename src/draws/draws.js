function changeCatage()
{
  var list = document.getElementById("drawCatage");
  var max = 2;
  if (list.value == 301) max = 0; // Poussin
  if (list.value == 306) max = 0; // Senior
  if (list.value == 307) max = 5; // Veteran
  var numcatage = {};

  for (var i=0; i<=max; i++)
  {
     numcatage[i]  = {'value':i};
  } 
  var list = document.getElementById("drawNumcatage");
  fillList(list, numcatage);
}

function changeDrawType()
{
  var drawType =  document.getElementById("drawType");
  var blkGroup =  document.getElementById("blkGroup");
  var blkKo    =  document.getElementById("blkKo");
  var consol   =  document.getElementById("kdrawNbcons");
  var qualif   =  document.getElementById("kdrawNbq");
  var place    =  document.getElementById("kdrawNbplq");
  var third    =  document.getElementById("blkThird");
  if(drawType.value == 10) // Poule
    {
      third.style.display = "block";
      blkGroup.style.display = "block";
      blkKo.style.display = "none";
      height = 430;
      var nb = parseInt(document.getElementById("drawNbp3").value) +
      parseInt(document.getElementById("drawNbp4").value) +
      parseInt(document.getElementById("drawNbp5").value);
      var nbpairs = parseInt(document.getElementById("drawNbPairs").value);
      if ( isNaN(nbpairs) ) nbpairs = 0;
 	  if (nbpairs == 5 && !nb)
		document.getElementById("drawNbp5").value = 1;
	  else if (nbpairs%3 == 0 && !nb)
		document.getElementById("drawNbp3").value = nbpairs/3;
	  else if (nbpairs%4 == 0  && !nb)
		document.getElementById("drawNbp4").value = nbpairs/4;
	  else if (!nb && nbpairs>2)
	  {
		 var nbpairs4 = nbpairs%3;
		 var nbpairs3 = Math.floor(nbpairs/3)- nbpairs4;
		 document.getElementById("drawNbp3").value = nbpairs3;
		 document.getElementById("drawNbp4").value = nbpairs4;
	  }
    }
  if(drawType.value == 11) // Qualif
    {
      third.style.display = "block";
      blkGroup.style.display = "none";
      blkKo.style.display    = "block";
      qualif.style.display   = "block";
      place.style.display    = "block";
      height = 350;
    }
  if(drawType.value == 12) // KO
    {
       third.style.display = "block";
      blkGroup.style.display = "none";
      blkKo.style.display    = "block";
      qualif.style.display   = "none";
      place.style.display    = "none";
      height = 320;
    }
  if(drawType.value == 18) // Consolante
    {
      third.style.display = "none";
      blkGroup.style.display = "none";
      blkKo.style.display    = "block";
      qualif.style.display   = "none";
      place.style.display    = "none";
      height = 320;
    }
  window.resizeTo(450,height);
}

function endSubmit(result)
{

  var roundId =  eval('('+result+')');
  var list = document.getElementById("roundList");
  var name = document.getElementById("roundName");
  list.options[list.options.selectedIndex].text = name.value;
  desactivate('formRoundDef');
  saveData('formRoundDef');

  var nb    = document.getElementById("roundEntries");
  var oldnb = document.getElementById("oldEntries");
  if (nb.value != oldnb.value)
  {
     var theDoc = window.document;
     frm = theDoc.forms['kHistory']
     kaid=frm.elements["kaid"].value;
     var url= theDoc.location.pathname+"?";
     url += "kpid=draws";
     url += "&kaid="+kaid;
     url += "&kdata="+roundId;
     window.location = url;
  }
}

function newRound(sessionId, type, formName)
{
 var blk = document.getElementById(formName);
 var form = document.getElementById("btnPlus");

 blk.style.display = "block";
 form.style.display = "none";
 activate(sessionId, type, formName, 'roundName');  

 var elt = document.getElementById("roundId");
 elt.value = -1;
 var elt = document.getElementById("roundName");
 elt.value = '';
 var elt = document.getElementById("roundStamp");
 elt.value = '';
 var elt = document.getElementById("roundEntries");
 elt.value = '';
}


  
