      function display(aData, aStatus)
       {
    	 var image = "../../live/img/" + aData.jpg;
         //if (image != $("#jpg").attr("src") )
         //	$("#court").hide();
         if (aData.play == 0)
         	$("#court").fadeTo(3000, 0.33);
         else
         	$("#court").fadeTo(3000, 1);
        
         var score = { backgroundPosition: "(" + (aData.sc1g*100)/30 + "% 0%)"};
         $("#sc1g").animate(score);
         var score = { backgroundPosition: "(" + (aData.sc2g*100)/30 + "% 0%)"};
         $("#sc2g").animate(score);
         var score = { backgroundPosition: "(" + (aData.sc3g*100)/30 + "% 0%)"};
         $("#sc3g").animate(score);
         var score = { backgroundPosition: "(" + (aData.sc1d*100)/30 + "% 0%)"};
         $("#sc1d").animate(score);
         var score = { backgroundPosition: "(" + (aData.sc2d*100)/30 + "% 0%)"};
         $("#sc2d").animate(score);
         var score = { backgroundPosition: "(" + (aData.sc3d*100)/30 + "% 0%)"};
         $("#sc3d").animate(score);
         
         refreschImg($("#jpg"), "../../live/img/", aData.jpg);
         refreschImg($("#jig"), "../../live/img/", aData.jig);
         refreschImg($("#jpd"), "../../live/img/", aData.jpd);
         refreschImg($("#jid"), "../../live/img/", aData.jid);
         
         refreschImg($("#fpg"), "../img/pub/", aData.fpg);
         refreschImg($("#fig"), "../img/pub/", aData.fig);
         refreschImg($("#fpd"), "../img/pub/", aData.fpd);
         refreschImg($("#fid"), "../img/pub/", aData.fid);

         refreschImg($("#spg"), "../img/icon/", aData.spg);
         refreschImg($("#sig"), "../img/icon/", aData.sig);
         refreschImg($("#spd"), "../img/icon/", aData.spd);
         refreschImg($("#sid"), "../img/icon/", aData.sid);
         
         refreschImg($("#pro1"), "../img/icon/", aData.pro1);
         refreschImg($("#pro2"), "../img/icon/", aData.pro2);
         refreschImg($("#pro3"), "../img/icon/", aData.pro3);
                  
         refreschImg($("#event"), "../img/logo/", aData.event);
         refreschImg($("#stage"), "../img/logo/", aData.stage);
    }
    function refreschImg(aBalise, aPath, aImage)
    {
         if (aImage != "")
         {
           image = aPath + aImage;
           if (image != aBalise.attr("src") )
            aBalise.attr("src", image);
            }
         else   
            aBalise.attr("src", "../img/icon/empty.png");
    }
