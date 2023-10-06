/* global Cyr2LatImportExportObject */

( function( $ ) {
	const ImportExport = {
		/**
		 * Selectors.
		 */
		selectors: {
			$exportForm: $( '#ctl-export' ),
			$importForm: $( '#ctl-import' ),
			$errorWrapper: $( '#ctl-error' ),
			$successWrapper: $( '#ctl-success' ),
			$body: $( 'body' )
		},

		/**
		 * Initialize.
		 */
		init: function() {
			ImportExport.exportAction();
			ImportExport.importAction();
		},

		/**
		 * Export action.
		 */
		exportAction: function() {
			ImportExport.selectors.$exportForm.on( 'submit', function( event ) {
				event.preventDefault();

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

							ImportExport.selectors.$body.append( $downloadLink );

							$downloadLink[ 0 ].click();
							$downloadLink.remove();

							URL.revokeObjectURL( url );

							ImportExport.showSuccess( response.data.message );
						} else {
							ImportExport.showError( response.data );
						}
					},
					error: function( error ) {
						console.error( 'AJAX request failed:', error );
					}
				} );
			} );
		},

		/**
		 * Import action.
		 */
		importAction: function() {
			ImportExport.selectors.$importForm.on( 'submit', function( event ) {
				event.preventDefault();

				const $form = this;
				const formData = new FormData( $form );

				$.ajax( {
					type: 'POST',
					url: Cyr2LatImportExportObject.ajaxUrl,
					data: formData,
					processData: false,
					contentType: false,
					success: function( response ) {
						if ( response.success ) {
							ImportExport.showSuccess( response.data );
							$form.reset();
						} else {
							ImportExport.showError( response.data );
						}
					},
					error: function( error ) {
						console.error( 'AJAX request failed:', error );
					}
				} );
			} );
		},

		/**
		 * Show error message.
		 *
		 * @param message
		 */
		showError: function( message ) {
			ImportExport.selectors.$errorWrapper.html( message ).addClass( 'active' );

			setTimeout( function() {
				ImportExport.clearMessages();
			}, 5000 );
		},

		/**
		 * Show success message.
		 *
		 * @param message
		 */
		showSuccess: function( message ) {
			ImportExport.selectors.$successWrapper.html( message ).addClass( 'active' );

			setTimeout( function() {
				ImportExport.clearMessages();
			}, 5000 );
		},

		/**
		 * Clear messages.
		 */
		clearMessages: function() {
			ImportExport.selectors.$errorWrapper.html( '' ).removeClass( 'active' );
			ImportExport.selectors.$successWrapper.html( '' ).removeClass( 'active' );
		}
	};

	ImportExport.init();
}( jQuery ) );
