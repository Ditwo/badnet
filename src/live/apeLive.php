<!DOCTYPE html PUBLIC "-//W4C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" dir="ltr" lang="en">
<head>
	<title>Live scoring</title>
    <link rel="stylesheet" type="text/css" href="apeLive.css" />	
   
        
	<script type="text/javaScript" src="http://ape.badnet.org/Ape/Clients/mootools-core.js"></script>
	<script type="text/javaScript" src="http://ape.badnet.org/Ape/Clients/MooTools.js"></script>
	<script type="text/javaScript" src="http://ape.badnet.org/Ape/Demos/config.js"></script>
	<script type="text/javaScript" src="apeLive.js"></script>
	<script type="text/javaScript">
		window.addEvent('domready', function(){
			var client = new APE.Livescore({
				container: $('apeControllerDemo'),
				event : <?php echo $_GET['event']; ?>,
				court : <?php echo $_GET['court']; ?>
			});

			client.load({
				identifier: 'action',
				channel: 'court_' + <?php echo "'" . $_GET['event'] . '_' . $_GET['court'] . "'"; ?> 
			});
		});
	</script>
</head>
<body>
<div>
  <form id="fCmd" style="display:none;"> 
	<input id="matchEnd" type="hidden" value="0">
	<input id="toDisplay" type="hidden" value="1">
	<input id="msg" type="hidden" value="test">
	<input id="match" type="hidden" value="0">
  </form>
  <div id="wait">
  <h4>Terrain <?php echo $_GET['court']; ?> </h4>
  <p>Le match apparaitra au prochain changement de score</p>
  </div>
  <div id="court" class="court">

  <img id="playerg" class="player" src="empty.png" />
  <img id="playerd" class="player" src="empty.png" />

  <div id="sc1g" class="score top" style="background-position: 10px 20px"></div>
  <div id="sc1d" class="score bottom" style="background-position: 10px 20px"></div>
  <div id="sc2g" class="score top" style="background-position: 10px 20px"></div>
  <div id="sc2d" class="score bottom" style="background-position: 10px 20px"></div>
  <div id="sc3g" class="score top" style="background-position: 10px 20px"></div>
  <div id="sc3d" class="score bottom" style="background-position: 10px 20px"></div>

  <img id="flagg" class="flag" src="" />
  <img id="flagd" class="flag" src="" />

  <div id="service" class="server"  style="background-position: 0px 20px"></div>

  <p id="num" ></p>
  <p id="event"></p>
  <p id="stage"></p>
  <p id="time"></p>
  <p id="umpire"></p>
  <p id="users"></p>
  <p id="popup"></p>
  </div>

</div>
</body>
</html>