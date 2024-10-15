<?php
/**
 * Runs after activation. After DB tables auto-update.
 *
 * Create method with name `ver_{version}()` and it will automatically run on upgrade.
 * Method with `_once` suffix - `ver_{version}_once()` will run only once (not run on force upgrade).
 */

namespace CustomTheme\Upgrade;

class Functions_After {

	use Helper;

	public static function ver_1_0_1_once( array &$res ) {
		// error_log( print_r( 'Functions_After', true ) );
	}

}


