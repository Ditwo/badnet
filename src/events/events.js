function loadComboPage(sessionId, elt, type, act)
{
  var url = location.pathname+"?"; 
  url += elt.value;
  window.location = url;
}

function confirmdel(sessionId, elt, type, act)
{
  valid = confirm("Voulez aller supprimer les tournois sélectionnés. Toutes les données seront perdues, sans possibilités de les récupérer.");

 if (valid)
   upload(sessionId, elt, 'events', act, '0');   
}

function confirmempty(sessionId, elt, type, act)
{
  valid = confirm("Voulez aller vider les tournois sélectionnés. Toutes les données seront perdues, sans possibilités de les récupérer.");

 if (valid)
   upload(sessionId, elt, 'events', act, '0');   
}

