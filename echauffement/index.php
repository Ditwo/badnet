<?php print('<?xml version="1.0" encoding="UTF-8"?>'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    
  <head>
    <title>Suivi des matchs en cours</title>
    <style type="text/css">
    <!--
		body {
			font-family: Arial,Helvetica;
		}

		div#options {
			position: 1px solid blue;
		}

		div#matchs table, div#matchs table td {
			border: 1px solid black;
			text-align: center;
		}

		div#matchs table {
			float: left;
			border-collapse: collapse;
			margin: 10px;
		}

		div#matchs table td.debut {
			width: 3em;
		}

		div#matchs table td.court {
			font-size: 250%;
			padding: 10px;
        }

		div#matchs table td.matchnum {
			font-size: 250%;
			padding: 5px;
		}

		div#matchs table.terrainvide td.court {
			background-color: lime;
		}
        
        div#matchs td.drapeau {
            background-color: red;
            height: 80px;
            display: none;
        }   
            

    //-->
    </style>
    <script type="text/javascript" src="jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="soundmanager2.js"></script>
    <script type="text/javascript">    
    // <![CDATA[

        var mySound;
        soundManager.setup({
            url: '.',
            useHTML5Audio: true,
            onready: function() {
                mySound = soundManager.createSound({
                    id: 'aSound',
                    url: 'alarm.mp3'
                });
                mySound.play();
            },
            ontimeout: function() {
                // Hrmm, SM2 could not start. Missing SWF? Flash blocked? Show an error, etc.?
            }
        });

        function biip() {
            mySound.play();
        }

		function toggleOptions() {
			$('div#options').toggle();
		}

		function tick() {
			$('table.terrainoccupe td.duree').each(function(index, elt){
				d = new Date(new Date().getTime().toString()-$(elt).attr('begin'));
				h = d.getUTCHours();
				m = d.getUTCMinutes();
				s = d.getUTCSeconds();
				s0 = (d.getUTCSeconds() < 10) ? '0' + d.getUTCSeconds() : d.getUTCSeconds();
				$(elt).html(h * 60 + m + ':' + s0);

				e = $('#echauff').val();
				td = $(elt).parent().parent().find('td.court');
				tddrapeau = $(elt).parent().parent().find('td.drapeau');

				if (60*m + s < e) {
					td.css('background-color', 'yellow');
                } else {
					if ((60*m + s < 1*e + 5) && !(tddrapeau.hasClass('averti'))) {
                        if ($('input#flagechauff').attr('checked')) {
                            tddrapeau.slideDown();
                            tddrapeau.addClass('averti');
                        }
                        if ($('input#bipechauff').attr('checked')) {
                            biip();
                        }
                    }
    				td.css('background-color', 'red');
                }
			});
		}

		jQuery.fn.sort = function() {
			return this.pushStack( [].sort.apply( this, arguments ), []);
		};

		function update() {
			$.getJSON("echauffement.ajax.php", function (data){

                if (data != null) {
                    // ajout des nouveaux matchs
                    for (d in data) {
                        if ($('div#matchs table#match' + d ).length == 0) {
                            t = $('table#template').clone();
                            t.attr('id', 'match' + d);
                            t.addClass('court' + data[d].court);
                            t.addClass('terrainoccupe');
                            $('div#matchs').append(t);
                            $('td.court', t).html(data[d].court);
                            $('td.matchnum', t).html(d);
                            $('td.debut', t).html(new Date(data[d].begin*1000).toTimeString().substring(0,5));
                            $('td.duree', t).attr('begin', new Date().getTime().toString() - data[d].duration*1000);
                            $('td.drapeau', t).click(function(){$(this).hide();});
                            t.show();
                        }
                    }

                    // suppression des matchs terminés et des terrains vide qui n'on plus lieu d'être
                    $('div#matchs table').each(function(){
                        if (!($('td.matchnum', this).html() in data)) {
                            $(this).remove();
                        }
                    });
                }

				// On recrée les match vides
				for (i = 1; i <= $('#nbcourt').val(); i++) {
					if ($('div#matchs table.court' + i ).length == 0) {
						t = $('table#template').clone();
						t.attr('id', '');
						t.addClass('court' + i);
						t.addClass('terrainvide');
						$('div#matchs').append(t);
						$('td.court', t).html(i);
						t.show();
					}
				}

				// ensuite on trie
				function sortNumMatchs(a, b) {
                    return $(a).html()*1 > $(b).html()*1;
				}
				// ensuite on trie
				function sortMatchs(a, b) {
					if ($('td.court', a).html() == $('td.court', b).html()) {
						return $('td.duree', a).attr('begin') > $('td.duree', b).attr('begin') ? -1 : 1;
					} else {
						return 1*$('td.court', a).html() < 1*$('td.court', b).html() ? -1 : 1;
					}
				}
				$('div#matchs table').sort(sortMatchs).appendTo($('div#matchs'));

				// on redécale les doublons à la fin
				$('div#matchs table').each(function(){
					if ($('td.court', this).html() == $('td.court', $(this).prev()).html()) {
						$(this).appendTo($('div#temp'));
					}
				});

				$('div#matchs').append($('div#temp table'));

                // on met le prochain match
                $('span#next').html($('div#matchs table.terrainoccupe td.matchnum').sort(sortNumMatchs).last().html().trim()*1+1);

			});
		}

		$(document).ready(function() {
			$('#nbcourt').change(update);
            $('#nummatchsize').change(function(){
                $('span#next').css('font-size', $('#nummatchsize').val());
            });
            

			update();

			setInterval(tick, 1000);
			setInterval(update, 20000);

		});
    //]>
    </script>

  </head>
  <body>
	<div style="float:right;"><a href="javascript:toggleOptions()"><img alt="O" src="settings.png" /></a></div>
	<div id="options" style="display:none;">
        <label for="nbcourt">Nombre de courts : </label>
        <input type="text" name="nbcourt" id="nbcourt" value="6" size="2" /><br />
        <label for="echauff">Temps d'échauffement (en secondes) : </label>
        <input type="text" name="echauff" id="echauff" value="180" size="5" /><br />
        <label for="flagechauff">Affichage fin d'échauffement : </label>
        <input type="checkbox" name="flagechauff" id="flagechauff" checked="checked" /><br />
        <label for="bipechauff">Bip en fin d'échauffement : </label>
        <input type="checkbox" name="bipechauff" id="bipechauff" checked="checked" /><br />
        <label for="nummatchsize">Taille de la police du numéro de match : </label>
        <input type="text" name="nummatchsize" id="nummatchsize" value="100%" size="7" />

		<table id="template" style="display:none;">
			<tr><td rowspan="2" class="court"></td><td class="matchnum">&nbsp;</td></tr>
			<tr><td class="debut">&nbsp;</td></tr>
			<tr><td colspan="2" class="duree" begin="">&nbsp;</td></tr>
			<tr><td colspan="2" class="drapeau">&nbsp;</td></tr>
		</table>
	</div>
    <div style="text-align: center; font-size: 350%;">Prochain Match :<br /><span id="next" style="font-size: 100%"></span></div>
  	<div id="matchs"></div>
    <div id="temp"></div>
<a href="javascript:biip()">bip</a>
  </body>
</html>
