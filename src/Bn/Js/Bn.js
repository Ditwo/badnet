/**********************************************
 !   $Id$
 *********************************************/
 (function( $ ){
  $.fn.bnGoto = function() {
  
	 var data = this.metadata();
	 var url = 'index.php';
	 var glue = "?";
	 for(key in data) {
        url += glue + key + "=" + data[key];
        glue = "&";
      }
	 location = url;
	 return false;

  };
})( jQuery );


(function( $ ){


	var options = {
		active: 0,
		animated: "slide",
		event: "click"
	};

  var methods = {
    init : function( options ) {
    	var self = $(this);
        self.addClass( "bn-dynpref" );
		self.find('ul').addClass( "bn-dynpref-menu" );		
		self.find('div').addClass( "bn-dynpref-content" );		
        self.find('li')
          .addClass( "bn-dynpref-item" )
          .bind( "click", function(event) {
            $(this).parent().find('li').removeClass('bn-dynpref-item-active');
		    $(this).addClass('bn-dynpref-item-active');
		    var md = $(this).metadata();
			$(this).parent().parent().find('div').load('index.php', md);
		});
		
		
		var md = self.find('#bn-item-'+options.active).addClass('bn-dynpref-item-active').metadata();
		self.find('div').load('index.php', md);
		
      }
  };

  $.fn.bndynpref = function( method ) {
    
    // Method calling logic
    if ( methods[method] ) {
      return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return methods.init.apply( this, arguments );
    } else {
      $.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
    }    
  
  };

})( jQuery );

(function( $ ){

  var methods = {
    init : function( options ) {
    	$(".unload").livequery('click', nodeLoad);
	    $('.node').livequery('click', nodeToggle);
      },
    show : function( ) {   },
    hide : function( ) {  },
    update : function( content ) {  }
  };

	function nodeToggle() 
	{
		if ($(this).hasClass('collapsable-node')) 
		{
			$(this).parent().find(">ul").hide();
		} 
		else 
		{
			$(this).parent().find(">ul").show();
		}
		$(this).parent().toggleClass('expandable');
		$(this).parent().toggleClass('collapsable');
		$(this).toggleClass('expandable-node');
		$(this).toggleClass('collapsable-node');

		if ($(this).hasClass('lastExpandable-node') ||
			$(this).hasClass('lastCollapsable-node')) 
		{
			$(this).parent().toggleClass('lastExpandable');
			$(this).parent().toggleClass('lastCollapsable');
			$(this).toggleClass('lastExpandable-node');
			$(this).toggleClass('lastCollapsable-node');
		}
		return false;
	}

	function nodeLoad() 
	{
		$(this).removeClass('unload');
		$(this).unbind('click', nodeLoad);
		var md = $(this).metadata();
		var cible = $(this).parent().attr('id');
		var html = "<div id='formLoading'><img src='Bn/Img/loading.gif' alt='Loading...' title='Loading...' ></div>";
		$('#' + cible).append(html);
		$.post('index.php', md, function(aData) {
			$('#formLoading').remove();
			$('#' + cible).append(aData);
		});
	}

  $.fn.bndynlist = function( method ) {
    
    // Method calling logic
    if ( methods[method] ) {
      return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return methods.init.apply( this, arguments );
    } else {
      $.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
    }    
  
  };

})( jQuery );
 
$.fn.goto = function(){
	 var data = this.metadata();
	 var url = 'index.php';
	 var glue = "?";
	 for(key in data) {
        url += glue + key + "=" + data[key];
        glue = "&";
      }
	 location = url;
	 return false;
	 }
