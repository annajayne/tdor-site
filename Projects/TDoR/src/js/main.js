// Content warning popup
var seen_cwpopup = $.cookie('seen_cwpopup');
// seen_cwpopup = undefined; // TODO force on
if (seen_cwpopup === undefined) {
    // No cookie, so show the popup
    $(".cw_popup").show();
} else {
    // Cookie set so hide the popup
    $(".cw_popup").hide();
}

$(".cw_continue").click(function(){
    // Click to close so hide the popup and set the cookie
    $(".cw_popup").hide();
    $.cookie('seen_cwpopup', '1', { expires: 30 });

    // Return false to not continue with the navigation
    return false;
})

// Fireup the plugins
$(document).ready(function(){
	
	// initialise  slideshow
	 $('.flexslider').flexslider({
        animation: "slide",
        start: function(slider){
          $('body').removeClass('loading');
        }
      });

});
/**
 * Handles toggling the navigation menu for small screens.
 */
( function() {
	var button = document.getElementById( 'topnav' ).getElementsByTagName( 'div' )[0],
	    menu   = document.getElementById( 'topnav' ).getElementsByTagName( 'ul' )[0];

	if ( undefined === button )
		return false;

	// Hide button if menu is missing or empty.
	if ( undefined === menu || ! menu.childNodes.length ) {
		button.style.display = 'none';
		return false;
	}

	button.onclick = function() {
		if ( -1 == menu.className.indexOf( 'srt-menu' ) )
			menu.className = 'srt-menu';

		if ( -1 != button.className.indexOf( 'toggled-on' ) ) {
			button.className = button.className.replace( ' toggled-on', '' );
			menu.className = menu.className.replace( ' toggled-on', '' );
		} else {
			button.className += ' toggled-on';
			menu.className += ' toggled-on';
		}
	};
} )();
