/**********************************************
 !   $Id$
 *********************************************/
function submitFile(aData)
 {
     $("#targetDlg").empty();
     $("#dlg").dialog('close');
     $("#targetBody").load('index.php', aData);
   return false;
 }
 