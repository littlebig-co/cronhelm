/**
 * Functionality related to Cronhelm.
 */

document.addEventListener( 'DOMContentLoaded', () => {
	const checkCustom = () => {
		document.getElementById( 'cronhelm_next_run_date_local_custom' ).checked = true;
	};

	const customDateElement = document.getElementById( 'cronhelm_next_run_date_local_custom_date' );
	const customTimeElement = document.getElementById( 'cronhelm_next_run_date_local_custom_time' );
	const newCronElement = document.querySelector( 'input[value="new_cron"]' );
	const newURLCronElement = document.querySelector( 'input[value="new_url_cron"]' );
	const newPHPCronElement = document.querySelector( 'input[value="new_php_cron"]' );
	const hookCodeElement = document.getElementById( 'cronhelm_hookcode' );
	const hookNameElement = document.getElementById( 'cronhelm_hookname' );
	const hookURLElement = document.getElementById( 'cronhelm_url' );
	const editEventElement = document.querySelector( '.cronhelm-edit-event' );

	customDateElement && customDateElement.addEventListener( 'change', checkCustom );
	customTimeElement && customTimeElement.addEventListener( 'change', checkCustom );

	newCronElement && newCronElement.addEventListener( 'click', () => {
		editEventElement.classList.remove( 'cronhelm-edit-event-url' );
		editEventElement.classList.remove( 'cronhelm-edit-event-php' );
		editEventElement.classList.add( 'cronhelm-edit-event-standard' );
		hookNameElement.setAttribute( 'required', true );
		hookURLElement.removeAttribute( 'required' );
	} );

	newURLCronElement && newURLCronElement.addEventListener( 'click', () => {
		editEventElement.classList.remove( 'cronhelm-edit-event-standard' );
		editEventElement.classList.remove( 'cronhelm-edit-event-php' );
		editEventElement.classList.add( 'cronhelm-edit-event-url' );
		hookURLElement.setAttribute( 'required', true );
		hookNameElement.removeAttribute( 'required' );
	} );

	if ( newPHPCronElement ) {
		newPHPCronElement.addEventListener( 'click', () => {
			editEventElement.classList.remove( 'cronhelm-edit-event-standard' );
			editEventElement.classList.remove( 'cronhelm-edit-event-url' );
			editEventElement.classList.add( 'cronhelm-edit-event-php' );
			hookNameElement.removeAttribute( 'required' );
			hookURLElement.removeAttribute( 'required' );
			if ( ! hookCodeElement.classList.contains( 'cronhelm-editor-initialized' ) ) {
				wp.codeEditor.initialize( 'cronhelm_hookcode', window.wpCronhelm.codeEditor );
			}
			hookCodeElement.classList.add( 'cronhelm-editor-initialized' );
		} );
	} else if ( hookCodeElement ) {
		wp.codeEditor.initialize( 'cronhelm_hookcode', window.wpCronhelm.codeEditor );
	}

	const deleteEventLinks = document.querySelectorAll( '[data-cronhelm-delete-event]' );

	deleteEventLinks.forEach( ( link ) => {
		link.addEventListener( 'click', ( event ) => {
			const result = confirm( window.wpCronhelm.confirmDeleteEvent );
			if ( ! result ) {
				event.preventDefault();
			}
		} );
	} );

	const deleteHookLinks = document.querySelectorAll( '[data-cronhelm-delete-hook]' );

	deleteHookLinks.forEach( ( link ) => {
		link.addEventListener( 'click', ( event ) => {
			const result = confirm( window.wpCronhelm.confirmDeleteHook );
			if ( ! result ) {
				event.preventDefault();
			}
		} );
	} );
} );
