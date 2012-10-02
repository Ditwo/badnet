var scg1 = -1;
var scg2 = -1;
var scg3 = -1;
var scd1 = -1;
var scd2 = -1;
var scd3 = -1;

APE.Livescore = new Class({
	
	Extends: APE.Client,
	
	Implements: Options,
	
	options: {
		container: null,
		event : 0,
		court : 0
	},
	
	initialize: function(options){
		this.setOptions(options);
		this.container = $(this.options.container) || document.body;
		this.event = options.event;
		this.court = options.court;

		this.onRaw('postmsg', this.onMsg);
		this.addEvent('load',this.start);
		$('court').setStyle('display', 'none');
         $("sc1g").set('morph', {duration:'long', transition: 'bounce:out'});
         $("sc2g").set('morph', {duration:'long', transition: 'bounce:out'});
         $("sc3g").set('morph', {duration:'long', transition: 'bounce:out'});
         $("sc1d").set('morph', {duration:'long', transition: 'bounce:out'});
         $("sc2d").set('morph', {duration:'long', transition: 'bounce:out'});
         $("sc3d").set('morph', {duration:'long', transition: 'bounce:out'});
         $("service").set('morph', {duration:'long', transition: 'bounce:out'});
         
         $("popup").addEvent('click', function(){
           var url = "http://ape.badnet.org/badnet/src/live/apeLive.php?event=" + options.event +"&court=" + options.court;
           window.open(url, "live'.$court.'", "menubar=no, status=no,location=no,width=360,height=180");
	       });
         
	},
	
	start: function(core){
		this.core.start({'name': $time().toString()});
	},
	
	onMsg: function(raw){
		$('court').setStyle('display', '');
		$('wait').setStyle('display', 'none');
	    var imagePath = "http://ape.badnet.org/badnet/Live/" + this.event + "/Img_site/";

        if (raw.data.match != $("match").get('value') )
        {
        	$("match").set('value', raw.data.match);	  
        	$("num").set('text', this.court);	  
        	$("time").set('text', "0 mn");	  
        	$("umpire").set('text', raw.data.umpire);
        	$("event").set('text', raw.data.event);	  
        	if (raw.data.isteam == 1)
        	{
        	     $("stage").set('text', raw.data.tstg + ':' + raw.data.tptg + ' ' + raw.data.tstd + ':' + raw.data.tptd);
        	}
        	else
        	{
        		$("stage").set('text', raw.data.stage);	  
        	}  
			//if (aData.flagg == "") $("#flagg").hide();
			//else  $("#flagg").show();
			//if (aData.flagd == "") $("#flagd").hide();
			//else  $("#flagd").show();
	     }
        	$("users").set('text', raw.data.liveuser + ' cnx');
	 var fulllargeur = 984;
	 var numlargeur = 24;
     var position = {};
     var score = 0;
     var sco = parseInt(raw.data.sc1g, 10);
     var newpos;
	 if(scg1 != sco)
	 	 {
         score = fulllargeur-(sco+1)*numlargeur;
         newpos =  score + "px 0%";
         $("sc1g").morph({'background-position': newpos});  
                
         scg1 = sco;
         }
     sco = parseInt(raw.data.sc2g, 10);
	 if(scg2 != sco)
	 	 {
         score = fulllargeur-(sco+1)*numlargeur;
         newpos =  score + "px 0%";
         $("sc2g").morph({'background-position': newpos});  
         scg2 = sco;
         }
     sco = parseInt(raw.data.sc3g, 10);
	 if(scg3 != sco)
	 	 {
         score = fulllargeur-(sco+1)*numlargeur;
         newpos =  score + "px 0%";
         $("sc3g").morph({'background-position': newpos});  
         scg3 = sco;
         } 
     sco = parseInt(raw.data.sc1d, 10);
	 if(scd1 != sco)
	 	 {
         score = fulllargeur-(sco+1)*numlargeur;
         newpos =  score + "px 0%";
         $("sc1d").morph({'background-position': newpos});  
         scd1 = sco;
         }
     sco = parseInt(raw.data.sc2d, 10);
	 if(scd2 != sco)
	 	 {
         score = fulllargeur-(sco+1)*numlargeur;
         newpos =  score + "px 0%";
         $("sc2d").morph({'background-position': newpos});  
         scd2 = sco;
         }
     sco = parseInt(raw.data.sc3d, 10);
	 if(scd3 != sco)
	 	 {
         score = fulllargeur-(sco+1)*numlargeur;
         newpos =  score + "px 0%";
         $("sc3d").morph({'background-position': newpos});  
         scd3 = sco;
         }
        
     refreschImg($("playerg"), imagePath, raw.data.playerg);
     refreschImg($("playerd"), imagePath, raw.data.playerd);
     /* 
     //refreschImg($("#flagg"), "img/pub/", aData.flagg);
     //refreschImg($("#flagd"), "img/pub/", aData.flagd);
 	 */   
     var service = 0;
     if(raw.data.sig) service = 25;
     else if(raw.data.spd) if (raw.data.disci<3) service = 96; else service = 88;
	 else if(raw.data.sid)service = 107;
	 else if(raw.data.disci<3) service = 15; else service = 5;
     newpos = "0% " + service + "px";
     $("service").morph({'background-position': newpos});  
     $("time").set('text', raw.data.length + " mn");	  
	}
	
});

function refreschImg(aBalise, aPath, aImage)
{
	if (aImage != "")
    {
        image = aPath + aImage;
        if (image != aBalise.get("src") ) aBalise.set("src", image);
    }
    else aBalise.set("src", aPath + "empty.png");
}