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


function startChrono(sessionId, elt, nbCourt)
{
 setInterval(nextSecond, 1000);
 return false;
}

function nextSecond()
{
 frm = document.forms["formLive"]
 nbCourt = 1 + eval(frm.elements["nbCourt"].value);
 training = eval(frm.elements["training"].value)*60;
 for(i=1; i<nbCourt;i++)
  {
     obj = frm.elements["startHide"+i];
     //obj= document.getElementById("start"+i);
     start = obj.value;
     if (start != "--")
     {
	debut = new Date();
	now = debut.getTime();
	debut.setHours(start.substr(0,2));
	debut.setMinutes(start.substr(3,2));
	debut.setSeconds(start.substr(6,2));
	duree = (now - debut.getTime())/1000;
	obj= document.getElementById("court"+i);
	if (duree <= 0)
          obj.style.background = "green";
	else if (duree < training)
          obj.style.background = "yellow";
	else
          obj.style.background = "red";
	m = Math.floor(duree/60);
	s = duree - m*60;
	if (m<10) m = "0"+m;
	if (s<10) s = "0"+s;
        frm.elements["chrono"+i].value = m+":"+s;
     }
  }
 return true;
}

