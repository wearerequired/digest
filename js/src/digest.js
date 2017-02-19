/**
 * Digest Notifications
 *
 * Copyright (c) 2015-2017 required
 * Licensed under the GPLv2+ license.
 */

(function() {
	var frequency_period      = document.getElementById( 'digest_frequency_period' ),
	    frequency_day_wrapper = document.getElementById( 'digest_frequency_day_wrapper' );

	function hideAndSeek() {
		if ( 'weekly' === ( this.value || frequency_period.value ) ) {
			frequency_day_wrapper.className = '';
		} else {
			frequency_day_wrapper.className = 'digest-hidden';
		}
	}

	frequency_period.onchange = hideAndSeek;

	hideAndSeek();
})();
