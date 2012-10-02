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

function raz(sessionId, type, nbPairs, list)
{

  for (i=1; i<=nbPairs;i++)
   {
      var check = document.getElementById(list+i);
      check.checked = false;
      var tds   =  document.getElementById("pairTds"+i);
      tds.options.selectedIndex = 0;
      var pos   =  document.getElementById("pairPos"+i);
      pos.value = '';
   }
	
};

function razDraw(sessionId, type, nbPairs)
{

  for (i=1; i<=nbPairs;i++)
   {
      var check = document.getElementById("pairList2ko"+i);
      check.checked = false;
      var tds   =  document.getElementById("pairTds"+i);
      tds.options.selectedIndex = 0;
      var pos   =  document.getElementById("pairPos"+i);
      pos.value = '';
   }
	
};


function seedKo(sessionId, type, action, roundId)
{
	var form = document.forms['tSelectKoPair'];
    form.kpid.value = 'pairs';
    form.kaid.value = action;
    form.kdata;value = roundId;
    form.submit();
};

function seedGroup(sessionId, type, action, roundId)
{
	var form = document.forms['tSelectGroupPair'];
    form.kpid.value = 'pairs';
    form.kaid.value = action;
    form.kdata;value = roundId;
    form.submit();
};
