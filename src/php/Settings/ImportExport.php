<?php
/**
 * ImportExport class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Settings;

use CyrToLat\Settings\Abstracts\SettingsBase;

/**
 * Class ImportExport
 *
 * Settings page "Import & Export".
 */
class ImportExport extends PluginSettingsBase {

	/**
	 * Admin script handle.
	 */
	const HANDLE = 'cyr-to-lat-import-export';

	/**
	 * Script localization object.
	 */
	const OBJECT = 'Cyr2LatImportExportObject';

	/**
	 * Nonce.
	 */
	const EXPORT_NONCE = 'cyr-to-lat-export-nonce';

	/**
	 * Nonce.
	 */
	const IMPORT_NONCE = 'cyr-to-lat-import-nonce';

	/**
	 * Export ajax action.
	 */
	const EXPORT_ACTION = 'ctl-export';

	/**
	 * Import ajax action.
	 */
	const IMPORT_ACTION = 'ctl-import';

	/**
	 * Init class hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_action( 'wp_ajax_' . self::EXPORT_ACTION, [ $this, 'export_settings' ] );
		add_action( 'wp_ajax_' . self::IMPORT_ACTION, [ $this, 'import_settings' ] );
	}

	/**
	 * Export settings.
	 */
	public function export_settings() {
		// Run a security check.
		if ( ! check_ajax_referer( self::EXPORT_NONCE, 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Your session has expired. Please reload the page.', 'cyr2lat' ) );
		}

		// Check for permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'cyr2lat' ) );
		}

		$settings = get_option( $this->option_name() );
		$settings = wp_json_encode( $settings );

		// Send response.
		wp_send_json_success(
			[
				'settings' => $settings,
				'message'  => esc_html__( 'Settings exported successfully.', 'cyr2lat' ),
			]
		);
	}

	/**
	 * Import settings.
	 */
	public function import_settings() {
		// Run a security check.
		if ( ! check_ajax_referer( self::IMPORT_NONCE, 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Your session has expired. Please reload the page.', 'cyr2lat' ) );
		}

		// Check for permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'cyr2lat' ) );
		}

		if ( isset( $_FILES['ctl-import-file'] ) ) {
			$file = wp_unslash( $_FILES['ctl-import-file'] );

			if ( ! $file['error'] ) {
				$allowed_types = [ 'application/json' ];

				if ( in_array( $file['type'], $allowed_types, true ) ) {

					$settings = file_get_contents( $file['tmp_name'] );

					if ( $settings ) {
						$settings = json_decode( $settings, true );

						// We have only one table returned, so this is loop is executed once.
						foreach ( $settings as $new_key => $new_value ) {
							$key   = sanitize_text_field( $new_key );
							$value = [];

							foreach ( $new_value as $k => $v ) {
								$value[ sanitize_text_field( $k ) ] = sanitize_text_field( $v );
							}

							$this->update_option( $key, $value );
						}

						wp_send_json_success( esc_html__( 'Settings imported successfully.', 'cyr2lat' ) );
					}
				} else {
					wp_send_json_error( esc_html__( 'Invalid file type. Allowed type: json', 'cyr2lat' ) );
				}
			} else {
				/* translators: %s: error code */
				wp_send_json_error( sprintf( esc_html__( 'File upload failed with error code: %s', 'cyr2lat' ), $file['error'] ) );
			}
		}
	}

	/**
	 * Get page title.
	 *
	 * @return string
	 */
	protected function page_title(): string {
		return __( 'Import & Export', 'cyr2lat' );
	}

	/**
	 * Get section title.
	 *
	 * @return string
	 */
	protected function section_title(): string {
		return 'import-export';
	}

	/**
	 * Enqueue class scripts.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script(
			self::HANDLE,
			constant( 'CYR_TO_LAT_URL' ) . "/assets/js/import-export$this->min_prefix.js",
			[],
			constant( 'CYR_TO_LAT_VERSION' ),
			true
		);

		wp_localize_script(
			self::HANDLE,
			self::OBJECT,
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			]
		);

		wp_enqueue_style(
			self::HANDLE,
			constant( 'CYR_TO_LAT_URL' ) . "/assets/css/import-export$this->min_prefix.css",
			[ SettingsBase::HANDLE ],
			constant( 'CYR_TO_LAT_VERSION' )
		);
	}

	/**
	 * Section callback.
	 *
	 * @param array $arguments Section arguments.
	 */
	public function section_callback( array $arguments ) {
	}

	/**
	 * Show settings page.
	 */
	public function settings_page() {
		parent::settings_page();

		?>
		<div id="ctl-error"></div>
		<div id="ctl-success"></div>
		<?php

		$this->export_form();
		$this->import_form();
	}

	/**
	 * Export form.
	 */
	private function export_form() {
		?>
		<h3 class="title">
			<?php echo esc_html__( 'Export', 'cyr2lat' ); ?>
		</h3>
		<p>
			<?php echo esc_html__( 'Export your settings to a file by clicking the button below.', 'cyr2lat' ); ?>
		</p>
		<form id="ctl-export" method="post">
			<input type="hidden" name="action" value="ctl-export"/>
			<?php wp_nonce_field( self::EXPORT_NONCE ); ?>
			<?php submit_button( __( 'Export', 'cyr2lat' ) ); ?>
		</form>
		<?php
	}

	/**
	 * Import form.
	 */
	private function import_form() {
		?>
		<h3 class="title">
			<?php echo esc_html__( 'Import', 'cyr2lat' ); ?>
		</h3>
		<p>
			<?php echo esc_html__( 'Import your settings from a file by clicking the button below.', 'cyr2lat' ); ?>
		</p>
		<form id="ctl-import" method="post" enctype="multipart/form-data">
			<input type="hidden" name="action" value="ctl-import"/>
			<input type="file" name="ctl-import-file" accept=".json"/>
			<?php wp_nonce_field( self::IMPORT_NONCE ); ?>
			<?php submit_button( __( 'Import', 'cyr2lat' ) ); ?>
		</form>
		<?php
	}
}
