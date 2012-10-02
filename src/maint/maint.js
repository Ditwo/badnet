function initMergeAsso(sessionId, page, data)
{
  saveData('fMergeAsso');
  desactivate('fMergeAsso');
  desactivate('fSrcAsso');
  var elt = document.getElementById("destAssoList");
  elt.disabled = '';
  elt = document.getElementById("srcAssoList");
  elt.disabled = '';
  return false;
}

function activeAsso(sessionId, page, data)
{
  saveData('fMergeAsso');
  activate(sessionId, page, 'fMergeAsso', data);
  var elt = document.getElementById("destAssoList");
  elt.disabled = 'yes';
  elt = document.getElementById("assoId");
  elt.disabled = 'yes';
}

function cancelAsso(sessionId, page, data)
{
  restoreData('fMergeAsso');
  desactivate('fMergeAsso');
  var elt = document.getElementById("destAssoList");
  elt.disabled = '';
}

function displayDestAsso(data)
{
 var asso = eval('('+data+')');

 var elt = document.getElementById("assoName");
 elt.value= asso['asso_name'];
 var elt = document.getElementById("assoStamp");
 elt.value= asso['asso_stamp'];
 var elt = document.getElementById("assoType");
 elt.value= asso['asso_type'];
 var elt = document.getElementById("assoId");
 elt.value= asso['asso_id'];
 var elt = document.getElementById("assoFedeId");
 elt.value= asso['asso_fedeId'];
 var elt = document.getElementById("assoCmt");
 elt.value= asso['asso_cmt'];
 var elt = document.getElementById("assoUrl");
 elt.value= asso['asso_url'];
 var elt = document.getElementById("assoLogo");
 elt.value= asso['asso_logo'];
 var elt = document.getElementById("assoNumber");
 elt.value= asso['asso_dpt'];
 var elt = document.getElementById("assoDpt");
 elt.value= asso['asso_dpt'];
 var elt = document.getElementById("assoPseudo");
 elt.value= asso['asso_pseudo'];
 var elt = document.getElementById("assoNoc");
 elt.value= asso['asso_noc'];
 var elt = document.getElementById("assoLockId");
 elt.value= asso['asso_lockid'];
 elt = document.getElementById("assoDateCrea");
 elt.value= asso['asso_cre'];
 elt = document.getElementById("assoDateUpdt");
 elt.value= asso['asso_updt'];
 elt = document.getElementById("assoUniId");
 elt.value= asso['asso_uniId'];
 return true;
}

function displaySrcAsso(data)
{
 var asso = eval('('+data+')');

 var elt = document.getElementById("srcAssoName");
 elt.value= asso['asso_name'];
 elt = document.getElementById("srcAssoStamp");
 elt.value= asso['asso_stamp'];
 elt = document.getElementById("srcAssoType");
 elt.value= asso['asso_type'];
 elt = document.getElementById("srcAssoId");
 elt.value= asso['asso_id'];
 elt = document.getElementById("srcAssoFedeId");
 elt.value= asso['asso_fedeId'];
 elt = document.getElementById("srcAssoCmt");
 elt.value= asso['asso_cmt'];
 elt = document.getElementById("srcAssoUrl");
 elt.value= asso['asso_url'];
 elt = document.getElementById("srcAssoLogo");
 elt.value= asso['asso_logo'];
 elt = document.getElementById("srcAssoNumber");
 elt.value= asso['asso_dpt'];
 elt = document.getElementById("srcAssoDpt");
 elt.value= asso['asso_dpt'];
 elt = document.getElementById("srcAssoPseudo");
 elt.value= asso['asso_pseudo'];
 elt = document.getElementById("srcAssoNoc");
 elt.value= asso['asso_noc'];
 elt = document.getElementById("srcAssoLockId");
 elt.value= asso['asso_lockid'];
 return true;
}

function endSubmitAsso(result)
{
  desactivate('fMergeAsso');
  saveData('fMergeAsso');
  var elt = document.getElementById("destAssoList");
  elt.disabled = '';
}

function endMergeAsso(data)
{
  displaySrcAsso(data)
  var asso = eval('('+data+')');

  var src = asso['src'];
  var dest = asso['dest'];
  var list = document.getElementById("srcAssoList");
  for( var i=0; i<list.options.length;i++)
    {
      if (list.options[i].value == src)
         list.options[i].disabled = 1;
      if (list.options[i].value == dest)
         list.options.selectedIndex = i;
    }
  var list = document.getElementById("destAssoList");
  for( var i=0; i<list.options.length;i++)
    {
      if (list.options[i].value == dest)
         list.options[i].disabled = 1;
    }
}


function initMergeMember(sessionId, page, data)
{
  saveData('fMergeMember');
  desactivate('fMergeMember');
  desactivate('fSrcMember');
  var elt = document.getElementById("destMemberList");
  elt.disabled = '';
  elt = document.getElementById("srcMemberList");
  elt.disabled = '';
  elt = document.getElementById("srcAlphaList");
  elt.disabled = '';
  elt = document.getElementById("destAlphaList");
  elt.disabled = '';
  return false;
}

function activeMember(sessionId, page, data)
{
  saveData('fMergeMember');
  activate(sessionId, page, 'fMergeMember', data);
  var elt = document.getElementById("destMemberList");
  elt.disabled = 'yes';
  elt = document.getElementById("mberId");
  elt.disabled = 'yes';
}

function cancelMember(sessionId, page, data)
{
  restoreData('fMergeMember');
  desactivate('fMergeMember');
  var elt = document.getElementById("destMemberList");
  elt.disabled = '';
  elt = document.getElementById("destAlphaList");
  elt.disabled = '';
}

function displayDestMember(data)
{
 var mber = eval('('+data+')');

 var elt = document.getElementById("mberSecondName");
 elt.value= mber['mber_secondname'];
 var elt = document.getElementById("mberFirstName");
 elt.value= mber['mber_firstname'];
 var elt = document.getElementById("mberSexe");
 elt.value= mber['mber_sexe'];
 var elt = document.getElementById("mberCountryId");
 elt.value= mber['mber_countryId'];
 var elt = document.getElementById("mberBorn");
 elt.value= mber['mber_born'];
 var elt = document.getElementById("mberIbfNumber");
 elt.value= mber['mber_ibfnumber'];
 var elt = document.getElementById("mberLicence");
 elt.value= mber['mber_licence'];
 var elt = document.getElementById("mberFedeId");
 elt.value= mber['mber_fedeId'];
 var elt = document.getElementById("mberUrlPhoto");
 elt.value= mber['mber_urlphoto'];
 var elt = document.getElementById("mberLockId");
 elt.value= mber['mber_lockid'];
 var elt = document.getElementById("mberId");
 elt.value= mber['mber_id'];
 return true;
}

function displaySrcMember(data)
{
 var mber = eval('('+data+')');

 var elt = document.getElementById("srcMberSecondName");
 elt.value= mber['mber_secondname'];
 var elt = document.getElementById("srcMberFirstName");
 elt.value= mber['mber_firstname'];
 var elt = document.getElementById("srcMberSexe");
 elt.value= mber['mber_sexe'];
 var elt = document.getElementById("srcMberCountryId");
 elt.value= mber['mber_countryId'];
 var elt = document.getElementById("srcMberBorn");
 elt.value= mber['mber_born'];
 var elt = document.getElementById("srcMberIbfNumber");
 elt.value= mber['mber_ibfnumber'];
 var elt = document.getElementById("srcMberLicence");
 elt.value= mber['mber_licence'];
 var elt = document.getElementById("srcMberFedeId");
 elt.value= mber['mber_fedeId'];
 var elt = document.getElementById("srcMberUrlPhoto");
 elt.value= mber['mber_urlphoto'];
 var elt = document.getElementById("srcMberLockId");
 elt.value= mber['mber_lockid'];
 var elt = document.getElementById("srcMberId");
 elt.value= mber['mber_id'];
 return true;
}

function endSubmitMember(result)
{
  desactivate('fMergeMember');
  saveData('fMergeMember');
  var elt = document.getElementById("destMemberList");
  elt.disabled = '';
  elt = document.getElementById("destAlphaList");
  elt.disabled = '';
}

function endMergeMember(data)
{
  displaySrcMember(data)
  var mber = eval('('+data+')');

  var src = mber['src'];
  var dest = mber['dest'];
  var list = document.getElementById("srcMemberList");
  for( var i=0; i<list.options.length;i++)
    {
      if (list.options[i].value == src)
         list.options[i].disabled = 1;
      if (list.options[i].value == dest)
         list.options.selectedIndex = i;
    }
  var list = document.getElementById("destMemberList");
  for( var i=0; i<list.options.length;i++)
    {
      if (list.options[i].value == dest)
         list.options[i].disabled = 1;
    }
}
  
function fillSrcMemberList(data)
{
  var items = eval('('+data+')');
  var list = document.getElementById("srcMemberList");
  fillList(list, items);
}
function fillDestMemberList(data)
{
  var items = eval('('+data+')');
  var list = document.getElementById("destMemberList");
  fillList(list, items);
}