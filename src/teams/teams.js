function endSubmit(result)
{
  var list = document.getElementById("selectList");
  var name = document.getElementById("teamName");
  var stamp= document.getElementById("teamStamp");
  list.options[list.options.selectedIndex].text = name.value + "(" + stamp.value +")";
  desactivate('formTeam');
  saveData('formTeam');
  return false;
}

  
