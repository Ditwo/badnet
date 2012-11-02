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
 // DBBN - Debut
 // Se place sur l'ancre du premier match de la liste qui est lancé
 window.location="#ancre";
 // DBBN - Fin
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
	else if (duree < training + 20) {
//    else if (duree < 500) {
          $("#blkTimer"+i +" div").slideDown();
          obj.style.background = "red";
    }
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

// DBBN - Debut
$(document).ready(function () {

    // Rejout d'une checkbox pour cocher par tour
    $('.kRowBreak').prepend('<input type="checkbox" /> ').find('input').change(function(){
        tr=$(this).parent().parent().next();
        console.log($(this).attr('checked'));
        while(tr.hasClass('kRow1') || tr.hasClass('kRow0')){
            $('input', tr).attr('checked', $(this).is(':checked'));
            tr=tr.next();
        }
    });

//    // ajout du'n div caché qui sert d'avertissement visuel pour la fin des échauffements
//    $('.classTimer').append('<div style="height:50px;background-color:red;display:none;">&nbsp;</div>');
//    $('.classTimer div').click(function(){$(this).hide();});
//
//    // ajout d'un champ caché avec la date
//    heureDebut = new Date().getTime();
//    //$('body').append('<input type="hidden" id="heureDebut" value="' + heureFin + '" />');
//    $('body').prepend('<div id="timeline" style="width:0%;height:2px;background-color:blue">&nbsp</div>');
//    setInterval(function(){
//        p = (new Date().getTime() - heureDebut) / (parseInt($('meta[http-equiv=refresh]').attr('content'))*10)
//        $('#timeline').css('width', p+'%');
//    }, 1000);
});
// DBBN - Fin