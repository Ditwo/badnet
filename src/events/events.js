function loadComboPage(sessionId, elt, type, act)
{
  var url = location.pathname+"?"; 
  url += elt.value;
  window.location = url;
}

function confirmdel(sessionId, elt, type, act)
{
  valid = confirm("Voulez aller supprimer les tournois s�lectionn�s. Toutes les donn�es seront perdues, sans possibilit�s de les r�cup�rer.");

 if (valid)
   upload(sessionId, elt, 'events', act, '0');   
}

function confirmempty(sessionId, elt, type, act)
{
  valid = confirm("Voulez aller vider les tournois s�lectionn�s. Toutes les donn�es seront perdues, sans possibilit�s de les r�cup�rer.");

 if (valid)
   upload(sessionId, elt, 'events', act, '0');   
}

