(function ( window, document ) {
	'use strict';

	/**
	 * Binds a click on a dismiss button DOM node.
	 * @param {Object} dismissButton - The DOM node to attach to.
	 */
	function bindClick( dismissButton ) {
		dismissButton.addEventListener( 'click', handleDismiss );
	}

	/**
	 * A function to handle a dismiss click event.
	 * @param {Object} event - The click event from when the dismiss button was triggered.
	 */
	function handleDismiss( event ) {
		var notice = event.target.parentNode;
		var message = notice.getAttribute('data-message');
		var nonce = notice.getAttribute('data-nonce');
		var type = notice.getAttribute('data-type');
		var xhr = new XMLHttpRequest();
		xhr.open('POST', ajaxurl);
		xhr.responseType = 'json';
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.send(
			'action=apple_news_dismiss_notice' +
			'&message=' + encodeURIComponent(message) +
			'&nonce=' + encodeURIComponent(nonce) +
			'&type=' + encodeURIComponent(type)
		);
	}

	/**
	 * A function to attempt to bind a click to a notice dismiss button.
	 * If the notice dismiss button is not found, it waits 1/4 of a second
	 * and tries again.
	 * @param {Object} notice - The notice DOM element to look in.
	 */
	function maybeBindClick( notice ) {
		// Try to get the dismiss button for this notice.
		var dismissButton = notice.querySelector( '.notice-dismiss' );
		if ( ! dismissButton ) {
			window.setTimeout( maybeBindClick, 250, notice );
		} else {
			bindClick( dismissButton );
		}
	}

	// Listen for clicks on the dismiss button in Apple News notices.
	var notices = document.querySelectorAll( '.apple-news-notice.is-dismissible' );
	if ( notices && notices.length ) {
		for ( var i = 0; i < notices.length; i += 1 ) {
			maybeBindClick( notices[i] );
		}
	}
})( window, document );
