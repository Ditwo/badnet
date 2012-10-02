<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>test tableau</title>
    <link rel="stylesheet" type="text/css" href="../skins/live.css" />
<script type="text/javascript">
<!--
      function initReload()
      {
      setInterval(reload, 15000);
      reload();
      return true;
      }
      function reload()
      {
      parent.frames["cmd"].location.reload();
      if (document.forms['fCmd'].elements['matchEnd'].value == "1")
	{
	  alert(document.forms['fCmd'].elements['msg'].value);
	  document.forms['fCmd'].elements['matchEnd'].value = "0";
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
  <form id="court">
  <input id="jig" type="text" class="player" value="jig" size="25"/>
  <input id="jpg" type="text" class="player" value="jpg" size="25"/>

  <input id="jpd" type="text" class="player" value="jpd" size="25"/>
  <input id="jid" type="text" class="player" value="jid" size="25"/>

  <input id="sc1g" type="text" class="score" value="sc1g" size="2"/>
  <input id="sc1d" type="text" class="score" value="sc1d" size="2"/>
  <input id="sc2g" type="text" class="score" value="sc2g" size="2"/>
  <input id="sc2d" type="text" class="score" value="sc2d" size="2"/>
  <input id="sc3g" type="text" class="score" value="sc3g" size="2"/>
  <input id="sc3d" type="text" class="score" value="sc3d" size="2"/>

  <input id="spg" type="text" class="server" value="spg" size="2"/>
  <input id="sig" type="text" class="server" value="sig" size="2"/>
  <input id="spd" type="text" class="server" value="spd" size="2"/>
  <input id="sid" type="text" class="server" value="sid" size="2"/>

  <input id="fpg" type="text" class="flag" value="fpg" size="3"/>
  <input id="fig" type="text" class="flag" value="fig" size="3"/>
  <input id="fpd" type="text" class="flag" value="fpd" size="3"/>
  <input id="fid" type="text" class="flag" value="fid" size="3"/>

  <input id="pro1" type="text" class="prol" value="pro1" size="3"/>
  <input id="pro2" type="text" class="prol" value="pro2" size="3"/>
  <input id="pro3" type="text" class="prol" value="pro3" size="3"/>

  </form>
  </body>
</html>
