function raz(sessionId, type, nbMatch, nbSet)
{

 var elt = document.getElementById('penaltiesL');
 elt.value = '0';
 elt = document.getElementById('penaltiesR');
 elt.value = '0';
 var elt = document.getElementById('woL');
 elt.checked = '';
 var elt = document.getElementById('woR');
 elt.checked = '';
 for (i=0; i<nbMatch; i++)
   {
      for (j=0; j<nbSet; j++)
       {
          elt = document.getElementById("mtchSc0"+j+i);
          elt.value = '';
          elt = document.getElementById("mtchSc1"+j+i);
          elt.value = '';
  	  list = document.getElementById("mtchPair0"+i);
          list.options.selectedIndex = '----';
  	  list = document.getElementById("mtchPair1"+i);
          list.options.selectedIndex = '----';
	
       }
   }
 return;
}


  
