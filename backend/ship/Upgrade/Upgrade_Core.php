<?php

/* @see Upgrade_CLI_Commands */

namespace CustomTheme\Upgrade;

class Upgrade_Core {

	public const VER_OPT_KEY = MAIN_NAMESPACE . '_version';

	public const ONCE_OPT_KEY = MAIN_NAMESPACE . '_upgrade_once_done_versions';

	private const SKIP_WP_CLI_COMMANDS = [
		'search-replace',
		'db',
		'import',
	];

	private array $once_done_orig;
	private array $once_done_new = [];
	private ?string $db_ver;
	private ?string $cur_ver;

	private array $res = [
		'before'      => [],
		'activation'  => [],
		'after'       => [],
		'ver_opt_up'  => 'NO',
		'once_opt_up' => 'NO',
	];

	public function __construct( string $current_version ) {
		$this->cur_ver = $current_version;
	}

	public function init(): void {
		$is_allowed_env = is_admin() || isset( $_GET['run_upgrade'] );

		if ( ! $is_allowed_env ) {
			return;
		}

		if ( $this->is_skip_wp_cli_command() ) {
			return;
		}

		// not init on ajax-symply request
		if ( function_exists( 'doing_ajaxs' ) && doing_ajaxs() ) {
			return;
		}

		add_action( 'init', [ $this, 'check_upgrade' ], 99 );
	}

	public function get_results(): array {
		return $this->res;
	}

	/**
	 * Enables or Disables the WP maintenance mode.
	 *
	 * @param string $action One of: 'on', 'off'.
	 */
	public static function wp_maintenance_mode( string $action = 'on' ): void {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';

			WP_Filesystem();

			if ( 'direct' !== $wp_filesystem->method ) {
				/** @noinspection ForgottenDebugOutputInspection */
				wp_die( 'WP_Filesystem need to be `direct` type.' );
			}
		}

		$maintenance_file = $wp_filesystem->abspath() . '.maintenance';

		// Create maintenance file to signal that we are upgrading.
		if ( 'on' === $action ) {
			$maintenance_string = sprintf( '<?php $upgrading = %d; ?>', time() );

			$wp_filesystem->delete( $maintenance_file );
			$wp_filesystem->put_contents( $maintenance_file, $maintenance_string );
		} // Remove maintenance file, we're done with potential site-breaking changes.
		else {
			$wp_filesystem->delete( $maintenance_file );
		}
	}

	/**
	 * Upgrade Plugin. Must be run on activation and new version upgrade.
	 *
	 * @param string $force_from_version
	 *
	 * @return false|void|null
	 */
	public function check_upgrade( string $force_from_version = '' ) {
		$this->set_db_ver();

		if ( ! $this->db_ver ) {
			$this->res['activation'] = Activation::init(); // creat/update tables
			$this->res['ver_opt_up'] = $this->update_option( self::VER_OPT_KEY, '0.1' ) ? 'YES' : 'NO';

			return false;
		}

		// update not needed
		if ( $this->db_ver === $this->cur_ver && ! $force_from_version ) {
			return false;
		}

		$this->db_ver = $force_from_version ?: '0.1';

		$this->run_upgrade();
	}

	private function set_db_ver(): void {
		if ( is_multisite() ) {
			$this->db_ver = get_site_option( self::VER_OPT_KEY, null );

			if ( null === $this->db_ver ) {
				$this->move_option_to_network();
				$this->db_ver = get_site_option( self::VER_OPT_KEY, null );
			}
		} else {
			$this->db_ver = get_option( self::VER_OPT_KEY, null );
		}
	}

	private function run_upgrade(): void {
		wp_cache_flush();
		set_time_limit( 300 );

		$once_opt_val = is_multisite()
			? get_site_option( self::ONCE_OPT_KEY, '[]' )
			: get_option( self::ONCE_OPT_KEY, '[]' );

		$this->once_done_orig = (array) json_decode( $once_opt_val );

		// upgrade
		$this->res['before']     = $this->before_activation();
		$this->res['activation'] = Activation::init(); // creat/update tables
		$this->res['after']      = $this->after_activation();

		// update options
		$this->res['ver_opt_up'] = $this->update_option( self::VER_OPT_KEY, $this->cur_ver ) ? 'YES' : 'NO';

		$once_opt_value           = array_merge( $this->once_done_orig, $this->once_done_new );
		$once_opt_value           = array_unique( $once_opt_value );
		$once_opt_value           = wp_json_encode( $once_opt_value );
		$this->res['once_opt_up'] = $this->update_option( self::ONCE_OPT_KEY, $once_opt_value ) ? 'YES' : 'NO';

		// todo Добавить Логгер
		// $this->log_upgrade_result();

		wp_cache_flush();
	}

	private function log_upgrade_result(): void {
		if ( ! class_exists( \CustomTheme\Logger\Default_Logger::class ) ) {
			return;
		}

		$res = $this->res;
		unset( $res['activation'] );
		$msg = 'UPGRADE RESULT LOG JSON: ' . json_encode( $res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		if ( defined( 'WP_CLI' ) ) {
			( new \CustomTheme\Logger\Telegram_Logger() )->send( "<code>$msg</code>" );
			\WP_CLI::success( $msg );
		} else {
			hlogger()->alert( $msg );
		}
	}

	/**
	 * Code Need to be executed before {@see Activation::init()} runs.
	 */
	private function before_activation(): array {
		$res = $this->check_run_upgrade_functions( Functions_Before::class );

		return array_filter( $res );
	}

	/**
	 * Code Need to be executed after {@see Activation::init()} runs.
	 */
	private function after_activation(): array {
		$res = $this->check_run_upgrade_functions( Functions_After::class );

		return array_filter( $res );
	}

	private function check_run_upgrade_functions( $class_name ) {
		$res = [];

		$methods = get_class_methods( $class_name );
		$methods = $this->parse_methods_names( $methods );

		// run upgrade functions
		foreach ( $methods as $method => $method_ver ) {
			$is_once = strpos( $method, '_once' );

			// already done
			if ( $is_once && in_array( $method_ver, $this->once_done_orig, true ) ) {
				continue;
			}

			// call method
			if (
				// methods must be less or equal to current version
				version_compare( $method_ver, $this->cur_ver, '<=' )
				&&
				// methods must be bigger then last updated version
				version_compare( $method_ver, $this->db_ver, '>' )
			) {
				$res[ $method_ver ] = call_user_func_array( [ $class_name, $method ], [ & $res ] );

				if ( $is_once ) {
					$this->once_done_new[] = $method_ver;
				}
			}
		}

		return $res;
	}

	private function update_option( string $option_name, string $option_val ): bool {
		return (bool) is_multisite()
			? update_site_option( $option_name, $option_val )
			: update_option( $option_name, $option_val );
	}

	private function parse_methods_names( $methods ): array {
		$methods = array_filter( $methods, static fn( $method ) => str_starts_with( $method, 'ver_' ) );
		$methods = array_flip( $methods );

		foreach ( $methods as $method => & $ver ) {
			preg_match( '/[\d_]+/', $method, $mm );
			$ver = trim( $mm[0], '_' );
			$ver = str_replace( '_', '.', $ver ); // 2.3.2
		}
		unset( $ver );

		// order from low to high
		uasort( $methods, static fn( $a, $b ) => version_compare( $a, $b ) );

		return $methods;
	}

	/**
	 * Should run once on migration from simple WP install to Multisite WP install.
	 */
	private function move_option_to_network(): void {
		$blog_opt = get_option( self::VER_OPT_KEY, null );

		if ( null !== $blog_opt ) {
			update_site_option( self::VER_OPT_KEY, get_option( self::VER_OPT_KEY, '0.0.1' ) );
			update_site_option( self::ONCE_OPT_KEY, get_option( self::ONCE_OPT_KEY, '[]' ) );

			delete_option( self::VER_OPT_KEY );
			delete_option( self::ONCE_OPT_KEY );
		}
	}

	private function is_skip_wp_cli_command(): bool {
		global $argv;

		if ( ! defined( 'WP_CLI' ) ) {
			return false;
		}

		$current_command = trim( $argv[1] ?? '' );

		return in_array( $current_command, self::SKIP_WP_CLI_COMMANDS, true );
	}

}

