/* global Cyr2LatImportExportObject */

( function( $ ) {
	const $errorWrapper = $( '#ctl-error' );
	const $successWrapper = $( '#ctl-success' );
	let msgTimer;

	// Export.
	$( '#ctl-export' ).on( 'submit', function( event ) {
		event.preventDefault();

		const $form = $( this );

		const formData = new FormData( this );

		$.ajax( {
			type: 'POST',
			url: Cyr2LatImportExportObject.ajaxUrl,
			data: formData,
			processData: false,
			contentType: false,
			success: function( response ) {
				if ( response.success && response.data.settings ) {
					const blob = new Blob( [ response.data.settings ], { type: 'application/json' } );
					const url = URL.createObjectURL( blob );

					let currentDateTime = new Date().toISOString();
					currentDateTime = currentDateTime.replace( /:/g, '-' ).replace( 'T', '_' ).slice( 0, -5 );

					const $downloadLink = $( '<a>' )
						.attr( 'href', url )
						.attr( 'download', 'Settings_' + currentDateTime + '.json' )
						.css( 'display', 'none' );

					$( 'body' ).append( $downloadLink );

					$downloadLink[ 0 ].click();
					$downloadLink.remove();

					URL.revokeObjectURL( url );

					showSuccess( response.data.message );
				} else {
					showError( response.data );
				}
			},
			error: function( error ) {
				console.error( 'AJAX request failed:', error );
			}
		} );
	} );

	// Import.
	$( '#ctl-import' ).on( 'submit', function( event ) {
		event.preventDefault();

		const formData = new FormData( this );

		$.ajax( {
			type: 'POST',
			url: Cyr2LatImportExportObject.ajaxUrl,
			data: formData,
			processData: false,
			contentType: false,
			success: function( response ) {
				if ( response.success ) {
					showSuccess( response.data );
				} else {
					showError( response.data );
				}
			},
			error: function( error ) {
				console.error( 'AJAX request failed:', error );
			}
		} );
	} );

	function showError( message ) {
		$errorWrapper.html( message ).addClass( 'active' );

		setTimeout( () => {
			clearMessages();
		}, 5000 );
	}

	function showSuccess( message ) {
		$successWrapper.html( message ).addClass( 'active' );

		setTimeout( () => {
			clearMessages();
		}, 5000 );
	}

	function clearMessages() {
		$errorWrapper.html( '' ).removeClass( 'active' );
		$successWrapper.html( '' ).removeClass( 'active' );
	}
}( jQuery ) );
