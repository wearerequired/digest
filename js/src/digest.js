/**
 * Digest Notifications
 *
 * Copyright (c) 2015-2021 required
 * Licensed under the GPLv2+ license.
 */
( function () {
	const frequencyPeriod = document.getElementById( 'digest_frequency_period' );
	const frequencyDayWrapper = document.getElementById( 'digest-frequency-day-wrapper' );

	function hideAndSeek() {
		if ( 'weekly' === ( this.value || frequencyPeriod.value ) ) {
			frequencyDayWrapper.className = '';
		} else {
			frequencyDayWrapper.className = 'digest-hidden';
		}
	}

	frequencyPeriod.onchange = hideAndSeek;

	hideAndSeek();
} )();
