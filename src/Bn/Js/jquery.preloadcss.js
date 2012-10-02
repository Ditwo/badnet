/**
 * A jQuery plugin for preloading images from CSS
 *
 * @about:
http://marcarea.com/weblog/?post/2008/07/12/Plug-in-jQuery-pour-pre-charger-les-images-CSS
 * @permalink: http://marcarea.com/code/javascript/jquery.preloadcssimages.js
 *
 * Inspired by:
 *
http://www.filamentgroup.com/lab/update_automatically_preload_images_from_css_with_jquery/
 *
 * @author: Kemar (http://marcarea.com/)
 * @version: 1.0 (12.07.2008)
 * @requires jQuery v1.2.6 or later
 *
 * (c) CopyWHAT???
 * Hax0rs, do WTF you want with this script, free means free  :) 
 *
 * Key features:
 *  - process only @media screen styleSheets
 *  - recurse through @imported styleSheets (both W3C and IE ways)
 *  - stock CSSRules in an array for faster processing
 *
 * Usage:
 *  - $(window).load(function(){ $.preloadCssImages(); });
 *  - Be sure to use with $(window).load
 *  - alternatively you can link CSS files before JavaScript in HTML <head>
 *
 * TODO
 *  - detection of url seems kind of weak, can't figure out a better way to do
it?
 *  - caching the resulting images?
 *
 * References & further reading:
 * http://developer.mozilla.org/en/docs/DOM:stylesheet
 * http://www.w3cdom.org/#cssRule
 * http://www.hunlock.com/blogs/Totally_Pwn_CSS_with_Javascript
 *
http://blog.stchur.com/2008/04/09/programmatically-accessing-the-css-rules-in-your-pages-stylesheets/
 * http://www.howtocreate.co.uk/tutorials/javascript/domstylesheets
 * http://www.howtocreate.co.uk/tutorials/javascript/domstructure
 *
 */

jQuery.preloadCssImages = function() {

    var sheets = document.styleSheets,
        styles = [],    //array of CSS rules
        imgs   = [],    //array of images path as they appear in CSS
        hrefs  = [],    //array of styleSheets href
        url    = '/';   //full path to imgs

    if (!sheets) return;

    jQuery(sheets).each(function(){
        var mediaText = typeof this.media.mediaText !== 'undefined' ?
this.media.mediaText : this.media;
        if ( mediaText.indexOf('screen') == -1 ) return 0;
        feedStylesArray( this );
    });

    if (hrefs.length) {
        hrefs = removeDuplicates(hrefs);
        url = hrefs[0].split('/');
        url.pop();
        url = url.join('/') || window.location.href;
        if ( url.charAt(url.length - 1) !== '/' ) url += '/';//FireFox need this
    }

    if (styles.length) {
        imgs = jQuery.grep( styles, function (rule) { return
rule.match(/(gif|jpg|jpeg|png)/g); });
        imgs = imgs.join(',');
        imgs = imgs.match(/[^\(("|')?]+\.(gif|jpg|jpeg|png)/g);
        imgs = jQuery.makeArray(imgs);
        // can't use "unique" on non DOM elements
http://dev.jquery.com/ticket/1747
        imgs = removeDuplicates(imgs);
    }

    if (imgs.length) {
        createImgObjects(imgs);
    }

    return;

    function feedStylesArray( styleSheet ) {

        if ( styleSheet.href ) hrefs.push( styleSheet.href );

        if ( typeof styleSheet.cssRules !== 'undefined' ) {
            // do it the W3 way
            jQuery.each( styleSheet.cssRules,
                function() {
                    if (this.type === 1) {
                        styles.push( this.cssText );
                    }
                    else if (this.type === 3) {
                        // recurse through @import rules
                        feedStylesArray( this.styleSheet );
                    }
                }
            )
        }
        else {
            // do it the IE way
            jQuery(styleSheet.rules).each(function(){
                if (this.style)
                    styles.push( this.style.cssText.toLowerCase() );
            });
            // recurse through @import rules
            if(styleSheet.imports) {
                jQuery(styleSheet.imports).each(function(){
                    feedStylesArray(this);
                });
            }
        }
    }

    function removeDuplicates( array ) {
        var ret = [],
        done = {};
        try {
            for (var i = 0, length = array.length; i < length; i++) {
                var id = array[i];
                if (!done[id]) {
                    done[id] = true;
                    ret.push(array[i]);
                }
            }
        } catch(e) {
            ret = array;
        }
        return ret;
    }

    function createImgObjects( array ) {
        jQuery(array).each(function(){
            var img = new Image();
            $(img).attr('src', (this=='/' || !!this.match('http://')) ? this :
url+this );
        });
    }

};