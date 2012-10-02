function pager(sessionId, elt, aPage, aForm)
{
  var page = document.getElementById("page");
  var form = document.getElementById("tSearchPlayer");
  page.value = aPage;
  valid(form, 'regi', '703');
  form.submit();
}



function updateClubList(clubs)
{
  var res = eval('('+clubs+')');

  if( typeof res['errMsg'] == "string")
  {
    alert(res['errMsg']);
    delete(res['errMsg']);
  }
  var list = document.getElementById("assoId");
  list.options.length = 0;
  for(var clef in res)
   {
     if (typeof(res[clef]) == "string")
     {
	o = new Option(res[clef], clef);
        list.options[list.options.length] = o;
     }
   }
  return false;
}

function updateDrawList(infos)
{
  var res = eval('('+infos+')');

  var list = document.getElementById("regiSurclasse");
  tmp=res['surclasse'];
  for(var i=0; i<list.options.length; i++)
   {
     list.options[i].disabled = !tmp[i];
   }
  if (list.options[list.options.selectedIndex].disabled)
    list.options.selectedIndex = 0;

  var list = document.getElementById("regiNumcatage");
  fillList(list, res['numcatage']);

  var list = document.getElementById("single");
  fillList(list, res['single']);
  
  list = document.getElementById("double");
  fillList(list, res['double']);

  list = document.getElementById("doublePair");
  fillList(list, res['doublePair']);

  var list = document.getElementById("mixed");
  fillList(list, res['mixed']);

  list = document.getElementById("mixedPair");
  fillList(list, res['mixedPair']);
}

