<?php
/**
 * Runtime dependency checks.
 *
 * @package WooCommerce
 */

namespace WC\SmoothGenerator;

/**
 * Handles required runtime dependency checks.
 */
class Dependencies {
	/**
	 * Map of required runtime classes to their Composer packages.
	 */
	const REQUIRED_CLASSES = array(
		'Faker\\Factory'                      => 'fakerphp/faker',
		'Bezhanov\\Faker\\Provider\\Commerce' => 'mbezhanov/faker-provider-collection',
		'Jdenticon\\Identicon'               => 'jdenticon/jdenticon',
	);

	/**
	 * Determine whether required third-party dependencies are available.
	 *
	 * @return bool
	 */
	public static function has_required_packages(): bool {
		foreach ( array_keys( self::REQUIRED_CLASSES ) as $class_name ) {
			if ( ! class_exists( $class_name ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the names of missing Composer packages.
	 *
	 * @return string[]
	 */
	public static function get_missing_packages(): array {
		$missing_packages = array();

		foreach ( self::REQUIRED_CLASSES as $class_name => $package_name ) {
			if ( ! class_exists( $class_name ) ) {
				$missing_packages[] = $package_name;
			}
		}

		return array_values( array_unique( $missing_packages ) );
	}

	/**
	 * Build a user-facing error message for missing dependencies.
	 *
	 * @return string
	 */
	public static function get_missing_packages_error_message(): string {
		$missing_packages = self::get_missing_packages();

		if ( empty( $missing_packages ) ) {
			return '';
		}

		return sprintf(
			'WooCommerce Smooth Generator is missing required Composer packages (%1$s). '
			. 'Install the plugin release package that includes /vendor, or run "composer install" '
			. 'in the plugin directory before using the generator.',
			implode( ', ', $missing_packages )
		);
	}

	/**
	 * Return a WP_Error when dependencies are missing.
	 *
	 * @return \WP_Error|true
	 */
	public static function validate_runtime_dependencies() {
		if ( self::has_required_packages() ) {
			return true;
		}

		return new \WP_Error(
			'smoothgenerator_missing_dependencies',
			self::get_missing_packages_error_message()
		);
	}

	/**
	 * Render an admin notice when dependencies are missing.
	 *
	 * @return void
	 */
	public static function render_missing_packages_notice(): void {
		$error = self::validate_runtime_dependencies();

		if ( true === $error ) {
			return;
		}

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();

			if ( isset( $screen->id ) && 'tools_page_smoothgenerator' === $screen->id ) {
				return;
			}
		}
		?>
		<div class="notice notice-error">
			<p><?php echo esc_html( $error->get_error_message() ); ?></p>
		</div>
		<?php
	}
}
