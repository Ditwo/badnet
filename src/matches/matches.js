function controlscore(sessionId, elt, type, winner)
{
  var eltwo = document.getElementById("mtchWo");
  var eltab = document.getElementById("mtchAbort");

 if (eltwo.checked == true || eltab.checked == true)
    valid = true;
 else if (type == 331)
   valid = control315();
 else if (type == 332)
   valid = control57();
 else if (type == 333)
   valid = control321();
else if (type == 334)
   valid = control15();
else if (type == 335)
   valid = control311();
else if (type == 336)
   valid = control511();
 else 
   valid = true;
 
 if (!valid)
   valid = confirm("Score incorrect. Voulez-vous le conserver ou l'annuler ?");

 if (valid)
   upload(sessionId, elt, 'matches', winner,'0');
}


function control315(score)
{
  return true;
}

function control57(score)
{
  return true;
}

function control321(score)
{
  var sc = new Array(2);
  var nbwin = 0;
  for (var i=0; i<3; i++)
  {
     for (var j=0; j<2; j++)
     {
       var elt = document.getElementById("mtchSc"+j+i);
       sc[j] = parseInt(elt.value);
       if(isNaN(sc[j]))
         {
	  elt.value = '';
          sc[j] = 0;
         }
     }

     if (sc[0] == 0 && sc[1] == 0)continue;
     if (sc[0] == sc[1]) return errorscore(i, 0);

     if (sc[0] < sc[1])
     {
        if (i==2)return errorscore(i, 0);
        tmp  = sc[0];
        sc[0] = sc[1];
        sc[1] = tmp;
     }
     else
       nbwin++;  
     if (sc[0] < 0 || sc[1] < 0) return errorscore(i, 0);
     if (sc[0] > 30) return errorscore(i, 0);
     if (sc[0] == 30 && sc[1] != 29 && sc[1] != 28) return errorscore(i, 1);
     if (sc[0] < 21) return errorscore(i, 0);
     if (sc[0] == 21 && sc[0]-sc[1]<2 ) return errorscore(i, 1);
     if (sc[0] > 21 && sc[0] < 30 && sc[0]-sc[1] != 2) return errorscore(i, 0);
  }
  if (nbwin != 2)
     return errorscore(0, 0);
  return true;
}

function control311(score)
{
  var sc = new Array(2);
  var nbwin = 0;
  for (var i=0; i<3; i++)
  {
     for (var j=0; j<2; j++)
     {
       var elt = document.getElementById("mtchSc"+j+i);
       sc[j] = parseInt(elt.value);
       if(isNaN(sc[j]))
         {
	  elt.value = '';
          sc[j] = 0;
         }
     }

     if (sc[0] == 0 && sc[1] == 0)continue;
     if (sc[0] == sc[1]) return errorscore(i, 0);

     if (sc[0] < sc[1])
     {
        if (i==2)return errorscore(i, 0);
        tmp  = sc[0];
        sc[0] = sc[1];
        sc[1] = tmp;
     }
     else
       nbwin++;  
     if (sc[0] < 0 || sc[1] < 0) return errorscore(i, 0);
     if (sc[0] < 11) return errorscore(i, 0);
     if (sc[0] == 11 && sc[1] > 9){return errorscore(i, 0);}
     if (sc[0] > 11 && sc[0]-sc[1] != 2) return errorscore(i, 0);
  }
  if (nbwin != 2)
     return errorscore(0, 0);
  return true;
}

function control511(score)
{
  var sc = new Array(2);
  var nbwin = 0;
  for (var i=0; i<5; i++)
  {
     for (var j=0; j<2; j++)
     {
       var elt = document.getElementById("mtchSc"+j+i);
       sc[j] = parseInt(elt.value);
       if(isNaN(sc[j]))
         {
	  elt.value = '';
          sc[j] = 0;
         }
     }
     if (sc[0] == 0 && sc[1] == 0)continue;
     if (sc[0] == sc[1]) return errorscore(i, 0);

     if (sc[0] < sc[1])
     {
        if (i==4)return errorscore(i, 0);
        tmp  = sc[0];
        sc[0] = sc[1];
        sc[1] = tmp;
     }
     else nbwin++;  
     if (sc[0] < 0 || sc[1] < 0) return errorscore(i, 0);
     if (sc[0] < 11) return errorscore(i, 0);
     if (sc[0] == 11 && sc[1] > 9){return errorscore(i, 0);}
     if (sc[0] > 11 && (sc[0]-sc[1]) != 2){ return errorscore(i, 0);}
  }
  if (nbwin != 3) return errorscore(0, 0);
  return true;
}

function control15(score)
{
  var sc = new Array(2);
  var elt = document.getElementById("mtchSc00");
  sc[0] = parseInt(elt.value);
  if(isNaN(sc[0]))
  {
	  elt.value = '';
      sc[0] = 0;
  }
  var elt = document.getElementById("mtchSc10");
  sc[1] = parseInt(elt.value);
  if(isNaN(sc[1]))
  {
	  elt.value = '';
      sc[1] = 0;
  }

   if (sc[0] == 0 && sc[1] == 0)return true;
   if (sc[0] != 3) return errorscore(0, 0);
   if (sc[1] < 0 || sc[1] > 2) return errorscore(0, 0);
   return true;
}

function errorscore(game, player)
{
 var elt = document.getElementById("mtchSc"+player+game);
 elt.focus();
 return false;
}  
