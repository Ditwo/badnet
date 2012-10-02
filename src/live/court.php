<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>test tableau</title>
    <link rel="stylesheet" type="text/css" href="../skins/live.css" />
<script type="text/javascript">
<!--
      function initReload()
      {
      setInterval(reload, 10000);
      reload();
      return true;
      }
      function reload()
      {
      parent.frames["cmd"].location.reload();
      doc = document.forms['fCmd'];
      if ((doc.elements['matchEnd'].value == "1") && 
	   doc.elements['toDisplay'].value == "1")
	{
	  alert(doc.elements['msg'].value);
	  doc.elements['toDisplay'].value = "0";
	}
      return true;
      }
-->  
      </script>
  </head>

  <body onload="initReload();">
  <form id="fCmd" style="display:none;">
	<input id="matchEnd" type="hidden" value="0">
	<input id="toDisplay" type="hidden" value="1">
	<input id="msg" type="hidden" value="test">
  </form>
  <div id="court">
  <img id="jig" height="20" width="250" class="player" src="../img/icon/empty.png" />
  <img id="jpg" height="20" width="250" class="player" src="../img/icon/empty.png" />
  <img id="jpd" height="20" width="250" class="player" src="../img/icon/empty.png" />
  <img id="jid" height="20" width="250" class="player" src="../img/icon/empty.png" />

  <img id="sc1g" height="32" width="27" class="score" src="../img/icon/empty.png" />
  <img id="sc1d" height="42" width="27" class="score" src="../img/icon/empty.png" />
  <img id="sc2g" height="42" width="27" class="score" src="../img/icon/empty.png" />
  <img id="sc2d" height="42" width="27" class="score" src="../img/icon/empty.png" />
  <img id="sc3g" height="42" width="27" class="score" src="../img/icon/empty.png" />
  <img id="sc3d" height="42" width="27" class="score" src="../img/icon/empty.png" />

  <img id="spg" height="20" width="25" class="server" src="../img/icon/empty.png" />
  <img id="sig" height="20" width="25" class="server" src="../img/icon/empty.png" />
  <img id="spd" height="20" width="25" class="server" src="../img/icon/empty.png" />
  <img id="sid" height="20" width="25" class="server" src="../img/icon/empty.png" />

  <img id="fpg" height="20" width="25" class="flag" src="../img/icon/empty.png" />
  <img id="fig" height="20" width="25" class="flag" src="../img/icon/empty.png" />
  <img id="fpd" height="20" width="25" class="flag" src="../img/icon/empty.png" />
  <img id="fid" height="20" width="25" class="flag" src="../img/icon/empty.png" />

  <img id="pro1" height="9" width="25" class="prol" src="../img/icon/empty.png" />
  <img id="pro2" height="9" width="25" class="prol" src="../img/icon/empty.png" />
  <img id="pro3" height="9" width="25" class="prol" src="../img/icon/empty.png" />
  </div>
<!--  <img  id="powered" src="../img/logo/powered.jpg" /> -->
  <img  id="event" src="../img/logo/event_1.jpg" />
  <img  id="stage" src="../img/logo/stage_q.jpg" />
  <a href="http://www.badnet.org">
  <img  id="badnet" src="../img/logo/badnetlive.jpg" />
  </a>
  </body>
</html>
